<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Incident;

class AdminDashboardController extends Controller
{
    public function index()
    {
        // Get statistics for the authenticated user
        $stats = [
            'total' => Incident::count(),
            'pending' => Incident::where('status', 'Pending')->count(),
            'in_progress' => Incident::where('status', 'In Progress')->count(),
            'resolved' => Incident::where('status', 'Resolved')->count(),
        ];

        // Get recent incidents (last 5)
        $recentIncidents = Incident::with(['mediaEvidence'])
            ->latest()
            ->take(5)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentIncidents'));
    }
    
}
