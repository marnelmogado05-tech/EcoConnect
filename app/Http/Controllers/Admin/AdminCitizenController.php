<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Incident;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;


class AdminCitizenController extends Controller
{
    /**
     * Display a listing of citizens.
     */
    public function index(Request $request)
    {
        $filters = [
            'status' => $request->get('status', 'active'),
            'search' => $request->get('search')
        ];

        $citizens = $this->getFilteredCitizens($filters)->paginate(15);

        $stats = [
            'total' => User::where('role', 'user')->count(),
            'active' => User::where('role', 'citizen')->where('status', 'active')->count(),
            'suspended' => User::where('role', 'citizen')->where('status', 'suspended')->count(),
            'reports' => Incident::count(),
        ];

        return view('admin.citizens', compact('citizens', 'stats', 'filters'));
    }

    /**
     * Get filtered citizens based on request parameters
     */
    private function getFilteredCitizens($filters = [])
    {
        $query = User::where('role', 'user')
                    ->withCount(['incidents as total_reports',
                                'incidents as pending_reports' => function($q) {
                                    $q->where('status', 'Pending');
                                },
                                'incidents as resolved_reports' => function($q) {
                                    $q->where('status', 'Resolved');
                                }])
                    ->orderBy('lname');

        // Apply status filter
        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $query->where('status', $filters['status']);
        }

        // Apply search filter
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('fname', 'like', "%{$search}%")
                  ->orWhere('lname', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        return $query;
    }

    /**
     * Display the specified citizen.
     */
    public function show($id)
    {
        $citizen = User::where('role', 'user')
                      ->withCount(['incidents as total_reports',
                                  'incidents as pending_reports' => function($q) {
                                      $q->where('status', 'Pending');
                                  },
                                  'incidents as resolved_reports' => function($q) {
                                      $q->where('status', 'Resolved');
                                  }])
                      ->with(['incidents' => function($q) {
                          $q->latest()->limit(10);
                      }])
                      ->findOrFail($id);

        $recentIncidents = $citizen->incidents;

        return view('admin.citizens.show', compact('citizen', 'recentIncidents'));
    }

    /**
     * Update citizen status.
     */
    public function updateStatus(Request $request, $id)
    {
        $citizen = User::where('role', 'user')->findOrFail($id);

        $request->validate([
            'status' => 'required|in:Active,Suspended'
        ]);

        try {
            // $previousStatus = $citizen->status;
            $citizen->update(['status' => $request->status]);

            return redirect()->back()->with('success', "Citizen status updated to {$request->status} successfully.");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to update citizen status. Please try again.');
        }
    }

    /**
     * Reset citizen password.
     */
    public function resetPassword(Request $request, $id)
    {
        $citizen = User::where('role', 'user')->findOrFail($id);

        $request->validate([
            'password' => 'required|min:8|confirmed'
        ]);

        $citizen->update([
            'password' => Hash::make($request->password)
        ]);

        return redirect()->back()->with('success', 'Password reset successfully.');
    }
}
