<?php

namespace App\Http\Controllers\User;

use App\Models\Incident;
use App\Http\Controllers\Controller;
use App\Models\MediaEvidence;
use App\Events\IncidentReported;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use App\Mail\IncidentReportedEmail;
use App\Jobs\ValidateIncidentLocation;

class UserIncidentController extends Controller
{
    public function create()
    {
        return view('user.report');
    }

    public function store(Request $request)
    {
        // Increase execution time limit for this operation
        set_time_limit(300);

        // Check if the user is suspended. This compared against 'suspended' while the
        // admin panel writes 'Suspended' — a strict PHP comparison, so unlike the database
        // lookups it was never rescued by a case-insensitive collation. Suspended accounts
        // could file reports as normal.
        $user = Auth::user();
        if ($user->status === 'Suspended') {
            return response()->json([
                'error' => 'Your account is suspended. You cannot report new incidents.'
            ], 403);
        }
        DB::beginTransaction();

        try {
            // Validate the request
            $validated = $request->validate([
                'incident_type' => 'required|string|max:255',
                'description' => 'required|string',
                'latitude' => 'required|numeric|between:-90,90',
                'longitude' => 'required|numeric|between:-180,180',
                'photos' => 'sometimes|array',
                'photos.*' => 'sometimes|string',
                'videos' => 'sometimes|array',
                'video_blobs' => 'sometimes|array',
                'photos_latitude' => 'sometimes|array',
                'photos_longitude' => 'sometimes|array',
                'videos_latitude' => 'sometimes|array',
                'videos_longitude' => 'sometimes|array',
            ]);

            // Allowed municipalities
            $allowedMunicipalities = [
                'Sta Praxedes', 'Claveria', 'Sanchez Mira',
                'Pamplona', 'Abulug', 'Ballesteros', 'Calayan'
            ];

            // Quick validation: check if at least incident_type and description are valid
            // Location validation done in background job (non-blocking)
            if (empty($validated['incident_type'])) {
                return response()->json([
                    'error' => 'Incident type is required'
                ], 422);
            }

            if (empty($validated['description'])) {
                return response()->json([
                    'error' => 'Description is required'
                ], 422);
            }

            // Create the incident
            $incident = Incident::create([
                'user_id' => Auth::id(),
                'title' => $this->generateIncidentTitle($validated['incident_type']),
                'incident_type' => $validated['incident_type'],
                'description' => $validated['description'],
                'incident_date' => Carbon::now()->format('Y-m-d'),
                'incident_time' => Carbon::now()->format('H:i:s'),
                'status' => Incident::STATUS_PENDING,
                'priority' => Incident::PRIORITY_NORMAL,
            ]);

            // Dispatch event to notify admins about new incident
            IncidentReported::dispatch($incident);

            // Handle photo uploads (base64 images)
            if (!empty($validated['photos'])) {
                foreach ($validated['photos'] as $index => $photoData) {
                    $this->processPhoto($photoData, $incident->id, $validated, $index);
                }
            }

            // Handle video uploads (blob data)
            if (!empty($validated['video_blobs'])) {
                foreach ($validated['video_blobs'] as $index => $videoData) {
                    $this->processVideo($videoData, $incident->id, $validated, $index);
                }
            }

            // Handle file uploads (if any)
            if ($request->hasFile('media_files')) {
                foreach ($request->file('media_files') as $file) {
                    $this->processFileUpload($file, $incident->id, $validated);
                }
            }

            $user = Auth::user();

            // Dispatch background jobs (non-blocking)
            ValidateIncidentLocation::dispatch(
                $incident,
                $validated['photos_latitude'] ?? [],
                $validated['photos_longitude'] ?? [],
                $validated['videos_latitude'] ?? [],
                $validated['videos_longitude'] ?? []
            );

            // Send email asynchronously via queue (don't wait for it)
            Mail::to($user->email)->queue(new IncidentReportedEmail($incident, $user));

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Incident reported successfully!',
                'incident' => $incident,
                'data' => [
                    'id' => $incident->id,
                    'title' => $incident->title,
                    'reference_number' => $incident->reference_number,
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Incident reporting error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            return response()->json([
                'error' => 'Failed to report incident. Please try again. Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate incident title based on type
     */
    private function generateIncidentTitle($incidentType): string
    {
        $typeMap = [
            'Illegal Logging' => 'Illegal Logging',
            'Pollution' => 'Pollution',
            'Wildlife Crime' => 'Wildlife Crime',
            'Illegal Waste Disposal' => 'Illegal Waste Disposal',
            'Other' => 'Other Environmental Incident'
        ];

        $typeName = $typeMap[$incidentType] ?? 'Environmental Incident';
        $timestamp = Carbon::now()->format('M j, Y g:i A');

        return "{$typeName} - {$timestamp}";
    }

    /**
     * Process photo data (base64)
     */
    private function processPhoto($photoData, $incidentId, $validated, $index)
    {
        try {
            // Extract base64 data
            if (preg_match('/^data:image\/(\w+);base64,/', $photoData, $matches)) {
                $imageType = $matches[1];
                $imageData = substr($photoData, strpos($photoData, ',') + 1);
                $imageData = base64_decode($imageData);

                if ($imageData === false) {
                    throw new \Exception('Invalid base64 image data');
                }

                // Generate unique filename
                $filename = 'photo_' . time() . '_' . Str::random(10) . '.' . $imageType;
                $filePath = 'incidents/media/' . $filename;

                // Store the file using Laravel's storage
                Storage::disk('public')->put($filePath, $imageData);

                // Get location data for this photo
                $latitude = isset($validated['photos_latitude'][$index]) ?
                    (float)$validated['photos_latitude'][$index] : null;
                $longitude = isset($validated['photos_longitude'][$index]) ?
                    (float)$validated['photos_longitude'][$index] : null;

                // Create media evidence record
                MediaEvidence::create([
                    'incident_id' => $incidentId,
                    'file_path' => $filePath,
                    'file_name' => $filename,
                    'mime_type' => 'image/' . $imageType,
                    'file_size' => Storage::disk('public')->size($filePath),
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Photo processing error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Process video data (base64 blob)
     */
    private function processVideo($videoData, $incidentId, $validated, $index)
    {
        try {
            // Extract base64 data
            if (preg_match('/^data:video\/(\w+);base64,/', $videoData, $matches)) {
                $videoType = $matches[1];
                $videoBinary = substr($videoData, strpos($videoData, ',') + 1);
                $videoBinary = base64_decode($videoBinary);

                if ($videoBinary === false) {
                    throw new \Exception('Invalid base64 video data');
                }

                // Generate unique filename
                $filename = 'video_' . time() . '_' . Str::random(10) . '.' . $videoType;
                $filePath = 'incidents/media/' . $filename;

                // Store the file using Laravel's storage
                Storage::disk('public')->put($filePath, $videoBinary);

                // Get location data for this video
                $latitude = isset($validated['videos_latitude'][$index]) ?
                    (float)$validated['videos_latitude'][$index] : null;
                $longitude = isset($validated['videos_longitude'][$index]) ?
                    (float)$validated['videos_longitude'][$index] : null;

                // Create media evidence record
                MediaEvidence::create([
                    'incident_id' => $incidentId,
                    'file_path' => $filePath,
                    'file_name' => $filename,
                    'mime_type' => 'video/' . $videoType,
                    'file_size' => Storage::disk('public')->size($filePath),
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Video processing error: ' . $e->getMessage());
            throw $e;
        }
    }

    private function processFileUpload($file, $incidentId, $validated)
    {
        try {
            // Validate file type and size
            $validated = validator(['file' => $file], [
                'file' => 'required|file|mimes:jpeg,png,jpg,gif,mp4,avi,mov,wmv|max:102400' // 100MB max
            ])->validate();

            // Generate unique filename
            $filename = time() . '_' . Str::random(10) . '_' . $file->getClientOriginalName();

            // Store in public disk
            $publicPath = $file->storeAs('incidents/media', $filename, 'public');

            // Create media evidence record
            MediaEvidence::create([
                'incident_id' => $incidentId,
                'file_path' => $publicPath, // or you can use $mediaPath depending on your app
                'file_name' => $filename,
                'mime_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
                'latitude' => $validated['latitude'] ?? null,
                'longitude' => $validated['longitude'] ?? null,
            ]);

        } catch (\Exception $e) {
            Log::error('File upload processing error: ' . $e->getMessage());
            throw $e;
        }
    }


    /**
     * Show a single incident belonging to the signed-in reporter.
     *
     * Notification emails link here. Rather than duplicating the incident detail markup
     * into a second template, this sends the reporter to their own list filtered to the
     * one reference number, which guarantees the incident is on the first page and reuses
     * the detail modal already rendered there.
     *
     * The ownership check is deliberately explicit for now; M2 replaces it with a policy.
     */
    public function show(Incident $incident)
    {
        abort_unless($incident->user_id === Auth::id(), 403);

        return redirect()->route('incidents', ['search' => $incident->reference_number]);
    }

    public function index(Request $request)
    {
        $query = Incident::with(['mediaEvidence', 'assignedTo', 'followups'])
            ->where('user_id', Auth::id())
            ->latest();

        // Apply filters - use filled() instead of has() for better validation
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('incident_type', $request->type);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%")
                ->orWhere('reference_number', 'like', "%{$search}%");
            });
        }

        $incidents = $query->paginate(10);

        // foreach ($incidents as $incident) {
        //     foreach ($incident->mediaEvidence as $media) {
        //         $address = $media->address; // This will automatically call the accessor
        //     }
        // }

        // Statistics - apply same filters for accurate counts
        $statsQuery = Incident::where('user_id', Auth::id());

        if ($request->filled('status')) {
            $statsQuery->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $statsQuery->where('incident_type', $request->type);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $statsQuery->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%")
                ->orWhere('reference_number', 'like', "%{$search}%");
            });
        }

        $stats = [
            'total' => $statsQuery->count(),
            'pending' => (clone $statsQuery)->where('status', 'Pending')->count(),
            'in_progress' => (clone $statsQuery)->where('status', 'In Progress')->count(),
            'resolved' => (clone $statsQuery)->where('status', 'Resolved')->count(),
        ];

        // Badge classes for status and priority
        $statusBadgeClasses = [
            'Pending' => 'bg-warning',
            'Assigned' => 'bg-primary',
            'In Progress' => 'bg-info',
            'Resolved' => 'bg-success',
            'Rejected' => 'bg-danger'
        ];

        $priorityBadgeClasses = [
            'Normal' => 'bg-secondary',
            'High' => 'bg-warning',
            'Urgent' => 'bg-danger'
        ];

        // Incident type mapping (for display purposes)
        $incidentTypes = [
            'illegal_logging' => 'Illegal Logging',
            'pollution' => 'Pollution',
            'wildlife_crime' => 'Wildlife Crime',
            'illegal_waste_disposal' => 'Illegal Waste Disposal',
            'other' => 'Other'
        ];

        return view('user.incidents', compact(
            'incidents',
            'stats',
            'statusBadgeClasses',
            'priorityBadgeClasses',
            'incidentTypes'
        ));
    }
}
