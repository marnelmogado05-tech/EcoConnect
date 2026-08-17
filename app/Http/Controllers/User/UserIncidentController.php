<?php

namespace App\Http\Controllers\User;

use App\Enums\IncidentPriority;
use App\Enums\IncidentStatus;
use App\Enums\IncidentType;
use App\Enums\UserStatus;
use App\Models\Incident;
use App\Http\Controllers\Controller;
use App\Models\MediaEvidence;
use App\Events\IncidentReported;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\IncidentReportedEmail;
use App\Jobs\ResolveIncidentLocation;

class UserIncidentController extends Controller
{
    /**
     * Upload bounds.
     *
     * The character limits are on the base64 text, which is roughly a third larger than
     * the bytes it encodes: 8 MB of characters is about 6 MB of image, and 40 MB is
     * about 30 MB of video.
     */
    private const MAX_PHOTOS = 10;

    private const MAX_VIDEOS = 3;

    private const MAX_PHOTO_CHARS = 8_000_000;

    private const MAX_VIDEO_CHARS = 40_000_000;

    /**
     * Image types accepted for evidence.
     */
    private const ALLOWED_IMAGE_TYPES = ['jpeg', 'jpg', 'png', 'gif', 'webp'];

    /**
     * Video types accepted for evidence.
     */
    private const ALLOWED_VIDEO_TYPES = ['mp4', 'webm', 'quicktime', 'mov', 'ogg'];

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
        if ($user->status === UserStatus::Suspended) {
            return response()->json([
                'error' => 'Your account is suspended. You cannot report new incidents.'
            ], 403);
        }
        DB::beginTransaction();

        try {
            // The media arrives as base64 in the request body. That is not a shape worth
            // defending long-term — real multipart uploads belong here — but until then
            // the bounds have to be explicit: the previous rules were 'sometimes|array'
            // and 'sometimes|string' with no limit on count or size, so a single request
            // could carry unlimited payload straight into base64_decode().
            $validated = $request->validate([
                'incident_type' => ['required', Rule::enum(IncidentType::class)],
                'description' => ['required', 'string', 'max:5000'],
                'latitude' => ['required', 'numeric', 'between:-90,90'],
                'longitude' => ['required', 'numeric', 'between:-180,180'],

                'photos' => ['sometimes', 'array', 'max:'.self::MAX_PHOTOS],
                'photos.*' => ['string', 'max:'.self::MAX_PHOTO_CHARS],
                'video_blobs' => ['sometimes', 'array', 'max:'.self::MAX_VIDEOS],
                'video_blobs.*' => ['string', 'max:'.self::MAX_VIDEO_CHARS],

                'photos_latitude' => ['sometimes', 'array', 'max:'.self::MAX_PHOTOS],
                'photos_latitude.*' => ['nullable', 'numeric', 'between:-90,90'],
                'photos_longitude' => ['sometimes', 'array', 'max:'.self::MAX_PHOTOS],
                'photos_longitude.*' => ['nullable', 'numeric', 'between:-180,180'],
                'videos_latitude' => ['sometimes', 'array', 'max:'.self::MAX_VIDEOS],
                'videos_latitude.*' => ['nullable', 'numeric', 'between:-90,90'],
                'videos_longitude' => ['sometimes', 'array', 'max:'.self::MAX_VIDEOS],
                'videos_longitude.*' => ['nullable', 'numeric', 'between:-180,180'],
            ], [
                'photos.max' => 'You can attach at most '.self::MAX_PHOTOS.' photos to a report.',
                'photos.*.max' => 'One of the photos is too large. Please use a smaller image.',
                'video_blobs.max' => 'You can attach at most '.self::MAX_VIDEOS.' videos to a report.',
                'video_blobs.*.max' => 'One of the videos is too large. Please record a shorter clip.',
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
                'status' => IncidentStatus::Pending,
                'priority' => IncidentPriority::Normal,
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

            // Resolve where this happened, once, off the request path. The job reads the
            // coordinates from the media rows written above rather than taking them as
            // constructor arguments, so there is one source of truth and no chance of the
            // two drifting apart — the previous job took three arguments while this call
            // site passed five, silently discarding the video coordinates.
            ResolveIncidentLocation::dispatch($incident);

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
            Log::error('Incident reporting error: '.$e->getMessage(), ['exception' => $e]);

            // The message is logged, not returned: exception text routinely carries SQL
            // fragments, column names and absolute paths, and the reporter can act on
            // none of it.
            return response()->json([
                'error' => 'We could not submit your report. Please try again.'
            ], 500);
        }
    }

    /**
     * Generate incident title based on type
     */
    private function generateIncidentTitle($incidentType): string
    {
        $typeName = IncidentType::tryFrom((string) $incidentType)?->label() ?? 'Environmental Incident';
        $timestamp = Carbon::now()->format('M j, Y g:i A');

        return "{$typeName} - {$timestamp}";
    }

    /**
     * Decode a base64 data URI, refusing anything not on the allow-list.
     *
     * The subtype used to be taken straight from the data URI and appended to the
     * filename, so the caller chose the stored file's extension.
     *
     * @param  array<int, string>  $allowedSubtypes
     * @return array{extension: string, bytes: string}|null
     */
    private function decodeDataUri(string $payload, string $kind, array $allowedSubtypes): ?array
    {
        if (! preg_match('#^data:'.$kind.'/([a-zA-Z0-9.+-]+);base64,#', $payload, $matches)) {
            return null;
        }

        $subtype = strtolower($matches[1]);

        if (! in_array($subtype, $allowedSubtypes, true)) {
            Log::warning("Rejected {$kind} upload with disallowed subtype: {$subtype}");

            return null;
        }

        $bytes = base64_decode(substr($payload, strpos($payload, ',') + 1), true);

        if ($bytes === false || $bytes === '') {
            return null;
        }

        return [
            'extension' => $subtype === 'quicktime' ? 'mov' : $subtype,
            'bytes' => $bytes,
        ];
    }

    /**
     * Process photo data (base64)
     */
    private function processPhoto($photoData, $incidentId, $validated, $index)
    {
        $decoded = $this->decodeDataUri((string) $photoData, 'image', self::ALLOWED_IMAGE_TYPES);

        if ($decoded === null) {
            return;
        }

        $this->storeEvidence($decoded, 'photo', 'image', $incidentId, [
            'latitude' => $validated['photos_latitude'][$index] ?? null,
            'longitude' => $validated['photos_longitude'][$index] ?? null,
        ]);
    }

    /**
     * Process video data (base64 blob)
     */
    private function processVideo($videoData, $incidentId, $validated, $index)
    {
        $decoded = $this->decodeDataUri((string) $videoData, 'video', self::ALLOWED_VIDEO_TYPES);

        if ($decoded === null) {
            return;
        }

        $this->storeEvidence($decoded, 'video', 'video', $incidentId, [
            'latitude' => $validated['videos_latitude'][$index] ?? null,
            'longitude' => $validated['videos_longitude'][$index] ?? null,
        ]);
    }

    /**
     * Write a decoded upload to private storage and record it.
     *
     * @param  array{extension: string, bytes: string}  $decoded
     * @param  array{latitude: mixed, longitude: mixed}  $coordinates
     */
    private function storeEvidence(array $decoded, string $prefix, string $kind, int $incidentId, array $coordinates): void
    {
        $filename = $prefix.'_'.now()->timestamp.'_'.Str::random(10).'.'.$decoded['extension'];
        $filePath = 'incidents/media/'.$filename;

        // Private disk: evidence is legal material with coordinates attached and must
        // not be reachable by URL without an authorisation check.
        Storage::disk('local')->put($filePath, $decoded['bytes']);

        MediaEvidence::create([
            'incident_id' => $incidentId,
            'file_path' => $filePath,
            'file_name' => $filename,
            'mime_type' => $kind.'/'.$decoded['extension'],
            'file_size' => strlen($decoded['bytes']),
            'latitude' => is_numeric($coordinates['latitude']) ? (float) $coordinates['latitude'] : null,
            'longitude' => is_numeric($coordinates['longitude']) ? (float) $coordinates['longitude'] : null,
        ]);
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
            $storedPath = $file->storeAs('incidents/media', $filename, 'local');

            // Create media evidence record
            MediaEvidence::create([
                'incident_id' => $incidentId,
                'file_path' => $storedPath,
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
     */
    public function show(Incident $incident)
    {
        $this->authorize('view', $incident);

        return redirect()->route('incidents', ['search' => $incident->reference_number]);
    }

    public function index(Request $request)
    {
        $incidents = $this->filtered($request)
            ->with(['mediaEvidence', 'assignedTo', 'followups'])
            ->latest()
            ->paginate(10)
            ->withQueryString();

        // One grouped query rather than four. The filter block used to be written out
        // twice — once for the list and once for the counts — and the counts then ran a
        // separate COUNT per status on top.
        $counts = $this->filtered($request)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $stats = [
            'total' => (int) $counts->sum(),
            'pending' => (int) $counts->get(IncidentStatus::Pending->value, 0),
            'in_progress' => (int) $counts->get(IncidentStatus::InProgress->value, 0),
            'resolved' => (int) $counts->get(IncidentStatus::Resolved->value, 0),
        ];

        // Badge classes come from the enums. This copy also keyed incidentTypes by
        // snake_case values the database never held, so the type filter here matched
        // nothing — the same defect as the admin list, in a second place.
        $incidentTypes = IncidentType::options();

        return view('user.incidents', compact(
            'incidents',
            'stats',
            'incidentTypes'
        ));
    }

    /**
     * The reporter's own incidents, narrowed by whatever filters are on the request.
     *
     * Declared once and used by both the list and the counts, so the two can no longer
     * disagree about what is being shown.
     */
    private function filtered(Request $request): Builder
    {
        return Incident::query()
            ->where('user_id', Auth::id())
            ->when($request->filled('status'), fn (Builder $q) => $q->where('status', $request->status))
            ->when($request->filled('type'), fn (Builder $q) => $q->where('incident_type', $request->type))
            ->when($request->filled('search'), function (Builder $q) use ($request) {
                $search = $request->search;

                $q->where(function (Builder $inner) use ($search) {
                    $inner->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('reference_number', 'like', "%{$search}%");
                });
            });
    }
}
