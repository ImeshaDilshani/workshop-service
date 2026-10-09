<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Workshop;
use App\Models\Registration;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Admin sees user management stats (they can't access workshops/registrations)
        if ($user->isAdmin()) {
            $totalUsers = User::count();
            $usersByRole = [
                'admin' => User::where('role', 'admin')->count(),
                'manager' => User::where('role', 'manager')->count(),
                'staff' => User::where('role', 'staff')->count(),
            ];
            $recentUsers = User::orderByDesc('created_at')->limit(10)->get();

            return view('dashboard', compact('totalUsers', 'usersByRole', 'recentUsers'));
        }

        // Manager & Staff see workshop and registration stats
        $totalWorkshops = Workshop::count();
        $scheduledWorkshops = Workshop::where('status', 'scheduled')->count();
        $totalRegistrations = Registration::where('status', 'active')->count();

        $upcomingWorkshops = Workshop::where('status', 'scheduled')
            ->where('start_date', '>=', now())
            ->withCount(['registrations as active_registrations_count' => fn($q) => $q->where('status', 'active')])
            ->orderBy('start_date')
            ->limit(5)
            ->get();

        $recentRegistrations = Registration::with(['workshop', 'registeredByUser'])
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return view('dashboard', compact(
            'totalWorkshops',
            'scheduledWorkshops',
            'totalRegistrations',
            'upcomingWorkshops',
            'recentRegistrations'
        ));
    }
}
