<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use App\Models\User;
use App\Models\Incident;
use App\Models\Municipality;
use App\Models\Barangay;
use App\Support\IdCardStorage;

class AdminPoliceController extends Controller
{
    /**
     * Display a listing of police officers.
     */
    public function index()
    {
        $query = User::where('role', 'police')
                    ->with(['municipality', 'barangay'])
                    ->withCount([
                        'assignedIncidents as total_assigned_reports',
                        'assignedIncidents as not_yet_responded_reports' => function($q) {
                            $q->where('status', 'Assigned');
                        },
                        'assignedIncidents as in_progress_reports' => function($q) {
                            $q->where('status', 'In Progress');
                        },
                        'assignedIncidents as resolved_reports' => function($q) {
                            $q->where('status', 'Resolved');
                        }
                    ]);

        // Apply filters
        if (request('status') && request('status') !== 'all') {
            $query->where('status', request('status'));
        }

        if (request('search')) {
            $search = request('search');
            $query->where(function($q) use ($search) {
                $q->where('fname', 'like', "%{$search}%")
                ->orWhere('lname', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $police = $query->orderBy('created_at', 'desc')->paginate(10);

        // Get overall statistics for police
        $stats = [
            'total' => User::where('role', 'police')->count(),
            'active' => User::where('role', 'police')->where('status', 'Active')->count(),
            'suspended' => User::where('role', 'police')->where('status', 'Suspended')->count(),
            'assigned_reports' => \App\Models\Incident::whereNotNull('assigned_to')
                                        ->whereHas('assignedTo', function($q) {
                                            $q->where('role', 'police');
                                        })
                                        ->count(),
            'not_yet_responded_reports' => \App\Models\Incident::whereNotNull('assigned_to')
                                        ->where('status', 'Assigned')
                                        ->whereHas('assignedTo', function($q) {
                                            $q->where('role', 'police');
                                        })
                                        ->count(),
            'in_progress_reports' => \App\Models\Incident::whereNotNull('assigned_to')
                                        ->where('status', 'In Progress')
                                        ->whereHas('assignedTo', function($q) {
                                            $q->where('role', 'police');
                                        })
                                        ->count(),
            'resolved_reports' => \App\Models\Incident::whereNotNull('assigned_to')
                                    ->where('status', 'Resolved')
                                    ->whereHas('assignedTo', function($q) {
                                        $q->where('role', 'police');
                                    })
                                    ->count(),
        ];

        $municipalities = Municipality::all();

        return view('admin.police', [
            'police' => $police,
            'stats' => $stats,
            'municipalities' => $municipalities,
        ]);
    }

    /**
     * Display the specified police officer.
     */
    public function show($id)
    {
        $police = User::where('role', 'police')
                    ->withCount(['assignedIncidents as total_reports',
                                'assignedIncidents as in_progress_reports' => function($q) {
                                    $q->where('status', 'In Progress');
                                },
                                'assignedIncidents as resolved_reports' => function($q) {
                                    $q->where('status', 'Resolved');
                                }])
                    ->with(['assignedIncidents' => function($q) {
                        $q->with(['user'])
                            ->latest()
                            ->limit(10);
                    }])
                    ->findOrFail($id);

        $recentIncidents = $police->assignedIncidents;

        return view('admin.police.show', compact('police', 'recentIncidents'));
    }

    /**
     * Update police officer status.
     */
    public function updateStatus(Request $request, $id)
    {
        $police = User::where('role', 'police')->findOrFail($id);

        $request->validate([
            'status' => 'required|in:Active,Suspended'
        ]);

        $police->update(['status' => $request->status]);

        return redirect()->back()->with('success', "Police Officer status updated to {$request->status} successfully.");
    }

    /**
     * Store a new police officer.
     */
    public function store(Request $request)
    {
        $request->validate([
            'fname' => ['required', 'string', 'max:255'],
            'mname' => ['nullable', 'string', 'max:255'],
            'lname' => ['required', 'string', 'max:255'],
            'extname' => ['nullable', 'string', 'max:10'],
            'phone' => ['required', 'string', 'max:20'],
            'municipality_id' => ['required', 'exists:municipalities,id'],
            'id_card' => ['required', 'image', 'mimes:jpeg,jpg,png', 'max:5120'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $idCardPath = IdCardStorage::store($request->file('id_card'));

        $user = User::create([
            'fname' => $request->fname,
            'mname' => $request->mname,
            'lname' => $request->lname,
            'extname' => $request->extname,
            'phone' => $request->phone,
            'municipality_id' => $request->municipality_id,
            'id_card_path' => $idCardPath,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'police',
            'status' => 'Active',
        ]);

        return redirect()->route('admin.police')->with('success', "Police Officer added successfully.");
    }

    /**
     * Update police officer profile.
     */
    public function update(Request $request, $id)
    {
        $police = User::where('role', 'police')->findOrFail($id);

        $validated = $request->validate([
            'fname' => 'required|string|max:255',
            'mname' => 'nullable|string|max:255',
            'lname' => 'required|string|max:255',
            'extname' => 'nullable|string|max:10',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|unique:users,email,' . $id,
            'municipality_id' => 'required|exists:municipalities,id',
            'status' => 'required|in:Active,Suspended',
            'id_card' => 'nullable|image|mimes:jpeg,jpg,png|max:5120',
        ]);

        // Process ID card image if provided - store as BLOB
        if ($request->hasFile('id_card')) {
            $previousPath = $police->id_card_path;
            $validated['id_card_path'] = IdCardStorage::store($request->file('id_card'));
            IdCardStorage::delete($previousPath);
        }

        unset($validated['id_card']);

        // Update police officer
        $police->update($validated);

        return redirect()->route('admin.police')
            ->with('success', 'Police officer updated successfully!');
    }
}
