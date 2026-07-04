<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Incident;
use App\Models\MediaEvidence;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Get statistics for the authenticated user
        $stats = [
            'total' => Incident::where('user_id', $user->id)->count(),
            'pending' => Incident::where('user_id', $user->id)->where('status', 'Pending')->count(),
            'in_progress' => Incident::where('user_id', $user->id)->where('status', 'In Progress')->count(),
            'resolved' => Incident::where('user_id', $user->id)->where('status', 'Resolved')->count(),
        ];

        // Get recent incidents (last 5)
        $recentIncidents = Incident::with(['mediaEvidence'])
            ->where('user_id', $user->id)
            ->latest()
            ->take(5)
            ->get();

        return view('user.dashboard', compact('stats', 'recentIncidents'));
    }

    /**
     * Get location from media evidence
     */
    private function getLocationFromMedia($mediaEvidence)
    {
        // Try to get location from media with coordinates
        $mediaWithLocation = $mediaEvidence->first(function ($media) {
            return $media->latitude && $media->longitude;
        });

        if ($mediaWithLocation) {
            // You can implement reverse geocoding here if needed
            return "Lat: {$mediaWithLocation->latitude}, Lng: {$mediaWithLocation->longitude}";
        }

        return 'Location not specified';
    }

}
