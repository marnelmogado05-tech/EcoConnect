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

class AdminbfpController extends Controller
{
    /**
     * Display a listing of bfp officers.
     */
    public function index()
    {
        $query = User::where('role', 'bfp')
                    ->with(['municipality', 'barangay'])
                    ->withCount([
                        'assignedIncidents as total_assigned_reports',
                        'assignedIncidents as not_yet_responded_reports' => function($q) {
                            $q->where('status', 'Assigned');
                        },
                        'assignedIncidents as in_progress_reports' => function($q) {
                            $q->where('status', 'In Progress');
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

        $bfp = $query->orderBy('created_at', 'desc')->paginate(10);

        // Get overall statistics for bfp
        $stats = [
            'total' => User::where('role', 'bfp')->count(),
            'active' => User::where('role', 'bfp')->where('status', 'Active')->count(),
            'suspended' => User::where('role', 'bfp')->where('status', 'Suspended')->count(),
            'assigned_reports' => \App\Models\Incident::whereNotNull('assigned_to')
                                        ->whereHas('assignedTo', function($q) {
                                            $q->where('role', 'bfp');
                                        })
                                        ->count(),
            'not_yet_responded_reports' => \App\Models\Incident::whereNotNull('assigned_to')
                                        ->where('status', 'Assigned')
                                        ->whereHas('assignedTo', function($q) {
                                            $q->where('role', 'bfp');
                                        })
                                        ->count(),
            'in_progress_reports' => \App\Models\Incident::whereNotNull('assigned_to')
                                        ->where('status', 'In Progress')
                                        ->whereHas('assignedTo', function($q) {
                                            $q->where('role', 'bfp');
                                        })
                                        ->count(),
            'resolved_reports' => \App\Models\Incident::whereNotNull('assigned_to')
                                    ->where('status', 'Resolved')
                                    ->whereHas('assignedTo', function($q) {
                                        $q->where('role', 'bfp');
                                    })
                                    ->count(),
        ];

        $municipalities = Municipality::all();

        return view('admin.bfp', [
            'bfp' => $bfp,
            'stats' => $stats,
            'municipalities' => $municipalities,
        ]);
    }

    /**
     * Display the specified bfp officer.
     */
    public function show($id)
    {
        $bfp = User::where('role', 'bfp')
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

        $recentIncidents = $bfp->assignedIncidents;

        return view('admin.bfp.show', compact('bfp', 'recentIncidents'));
    }

    /**
     * Update bfp officer status.
     */
    public function updateStatus(Request $request, $id)
    {
        $bfp = User::where('role', 'bfp')->findOrFail($id);

        $request->validate([
            'status' => 'required|in:Active,Suspended'
        ]);

        $bfp->update(['status' => $request->status]);

        return redirect()->back()->with('success', "bfp Officer status updated to {$request->status} successfully.");
    }

    /**
     * Store a new bfp officer.
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

        // Convert image to BLOB
        $idCardBlob = file_get_contents($request->file('id_card')->getRealPath());

        $user = User::create([
            'fname' => $request->fname,
            'mname' => $request->mname,
            'lname' => $request->lname,
            'extname' => $request->extname,
            'phone' => $request->phone,
            'municipality_id' => $request->municipality_id,
            'id_card' => $idCardBlob,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'bfp',
            'status' => 'Active',
        ]);

        return redirect()->route('admin.bfp')->with('success', "bfp Officer added successfully.");
    }

    /**
     * Update bfp officer profile.
     */
    public function update(Request $request, $id)
    {
        $bfp = User::where('role', 'bfp')->findOrFail($id);

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
            $validated['id_card'] = file_get_contents($request->file('id_card')->getRealPath());
        } else {
            // Keep the existing ID card
            unset($validated['id_card']);
        }

        // Update bfp officer
        $bfp->update($validated);

        return redirect()->route('admin.bfp')
            ->with('success', 'bfp officer updated successfully!');
    }
}
