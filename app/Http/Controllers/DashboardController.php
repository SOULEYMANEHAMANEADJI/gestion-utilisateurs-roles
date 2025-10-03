<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Statistiques générales
        $stats = [
            'total_users' => User::count(),
            'active_users' => User::where('status', 'active')->count(),
            'total_roles' => Role::count(),
            'users_today' => User::whereDate('created_at', today())->count(),
            'users_this_month' => User::whereMonth('created_at', now()->month)->count(),
            'recent_logins' => User::whereNotNull('last_login_at')
                                  ->where('last_login_at', '>=', now()->subDays(7))
                                  ->count(),
        ];

        // Répartition des utilisateurs par rôle
        $usersByRole = Role::withCount('users')
                          ->orderBy('users_count', 'desc')
                          ->get()
                          ->map(function ($role) {
                              return [
                                  'name' => $role->display_name,
                                  'count' => $role->users_count,
                                  'color' => $role->color,
                                  'percentage' => $role->users_count > 0 
                                      ? round(($role->users_count / User::count()) * 100, 1) 
                                      : 0
                              ];
                          });

        // Utilisateurs récents
        $recentUsers = User::with('roles')
                          ->latest()
                          ->take(5)
                          ->get();

        // Activité par mois (12 derniers mois)
        $monthlyActivity = collect();
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthlyActivity->push([
                'month' => $date->format('M Y'),
                'users' => User::whereYear('created_at', $date->year)
                             ->whereMonth('created_at', $date->month)
                             ->count(),
                'logins' => User::whereYear('last_login_at', $date->year)
                              ->whereMonth('last_login_at', $date->month)
                              ->count(),
            ]);
        }

        // Utilisateurs par statut
        $usersByStatus = User::select('status', DB::raw('count(*) as count'))
                            ->groupBy('status')
                            ->get()
                            ->mapWithKeys(function ($item) {
                                return [$item->status => $item->count];
                            });

        return view('dashboard.index', compact(
            'stats',
            'usersByRole',
            'recentUsers',
            'monthlyActivity',
            'usersByStatus'
        ));
    }
}
