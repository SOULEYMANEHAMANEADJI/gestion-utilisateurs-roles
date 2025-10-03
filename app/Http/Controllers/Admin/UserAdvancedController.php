<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\ErrorHandlerController;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Exception;

class UserAdvancedController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:super_admin,admin']);
    }

    /**
     * Affichage avancé avec pagination et filtres
     */
    public function index(Request $request)
    {
        try {
            // Cache des filtres pour éviter les requêtes répétées
            $cacheKey = 'users_index_' . md5(serialize($request->all()));
            
            // Filtrer les utilisateurs selon la hiérarchie
            $currentUser = auth()->user();
            $currentUserMaxLevel = $currentUser->roles->max('level') ?? 0;
            
            $query = User::with('roles')
                ->whereHas('roles', function ($q) use ($currentUserMaxLevel) {
                    $q->where('level', '<', $currentUserMaxLevel);
                })
                ->when($request->search, function ($q) use ($request) {
                    $q->where(function ($query) use ($request) {
                        $query->where('name', 'like', "%{$request->search}%")
                              ->orWhere('email', 'like', "%{$request->search}%")
                              ->orWhere('phone', 'like', "%{$request->search}%")
                              ->orWhere('address', 'like', "%{$request->search}%");
                    });
                })
                ->when($request->role, function ($q) use ($request) {
                    $q->whereHas('roles', function ($query) use ($request) {
                        $query->where('name', $request->role);
                    });
                })
                ->when($request->status, function ($q) use ($request) {
                    $q->where('status', $request->status);
                })
                ->when($request->sort, function ($q) use ($request) {
                    $direction = $request->direction === 'asc' ? 'asc' : 'desc';
                    
                    if ($request->sort === 'last_login_at') {
                        $q->orderBy('last_login_at', $direction);
                    } elseif ($request->sort === 'name') {
                        $q->orderBy('name', $direction);
                    } elseif ($request->sort === 'status') {
                        $q->orderBy('status', $direction);
                    } else {
                        $q->orderBy('created_at', $direction);
                    }
                }, function ($q) {
                    $q->orderBy('created_at', 'desc');
                });

            $perPage = min($request->get('per_page', 5), 100); // Limite de sécurité, 5 par défaut
            $users = $query->paginate($perPage);

            // Ajouter l'URL de l'avatar
            foreach ($users as $user) {
                $user->avatar_url = $user->avatar 
                    ? asset('storage/' . $user->avatar) 
                    : "https://ui-avatars.com/api/?name=" . urlencode($user->name) . "&background=6366f1&color=fff";
                
                // Formater la date de dernière connexion
                if ($user->last_login_at) {
                    $user->last_login_at = Carbon::parse($user->last_login_at)->diffForHumans();
                }
            }

            // Statistiques en temps réel avec cache
            $stats = Cache::remember('user_stats_' . $cacheKey, 300, function () {
                return $this->getUserStats();
            });

            // Récupérer tous les rôles pour les filtres avec cache
            $roles = Cache::remember('roles_list', 3600, function () {
                return Role::select('id', 'name', 'display_name', 'color', 'level')
                    ->orderBy('level')
                    ->get();
            });

            // Préparer les données JavaScript pour éviter les erreurs de parsing
            $jsData = [
                'users' => $users->items(),
                'pagination' => [
                    'current_page' => $users->currentPage() ?? 1,
                    'last_page' => $users->lastPage() ?? 1,
                    'per_page' => $users->perPage() ?? 15,
                    'total' => $users->total() ?? 0,
                    'from' => $users->firstItem() ?? 0,
                    'to' => $users->lastItem() ?? 0,
                ],
                'stats' => $stats ?? [],
                'filters' => [
                    'search' => $request->get('search', ''),
                    'role' => $request->get('role', ''),
                    'status' => $request->get('status', ''),
                    'perPage' => $request->get('per_page', 15),
                    'sort' => $request->get('sort', 'created_at'),
                    'direction' => $request->get('direction', 'desc')
                ]
            ];

            // Si c'est une requête AJAX, retourner JSON
            if ($request->ajax()) {
                return response()->json([
                    'users' => $users->items(),
                    'pagination' => [
                        'current_page' => $users->currentPage(),
                        'last_page' => $users->lastPage(),
                        'per_page' => $users->perPage(),
                        'total' => $users->total(),
                        'from' => $users->firstItem(),
                        'to' => $users->lastItem(),
                    ],
                    'stats' => $stats
                ]);
            }

            return view('admin.users.advanced-index', compact('users', 'stats', 'roles', 'jsData'));

        } catch (Exception $e) {
            return ErrorHandlerController::handleError($e, 'users_index', [
                'filters' => $request->all()
            ]);
        }
    }

    /**
     * Statistiques des utilisateurs avec cache
     */
    private function getUserStats()
    {
        return Cache::remember('user_stats', 300, function () {
            return [
                'total' => User::count(),
                'active' => User::where('status', 'active')->count(),
                'inactive' => User::where('status', 'inactive')->count(),
                'suspended' => User::where('status', 'suspended')->count(),
                'new_this_month' => User::whereMonth('created_at', now()->month)
                                        ->whereYear('created_at', now()->year)
                                        ->count(),
                'online_now' => User::where('last_login_at', '>=', now()->subMinutes(5))->count(),
            ];
        });
    }

    /**
     * Export des utilisateurs en CSV
     */
    public function export(Request $request)
    {
        // Filtrer selon la hiérarchie
        $currentUser = auth()->user();
        $currentUserMaxLevel = $currentUser->roles->max('level') ?? 0;

        $query = User::with('roles')
            ->whereHas('roles', function ($q) use ($currentUserMaxLevel) {
                $q->where('level', '<', $currentUserMaxLevel);
            })
            ->when($request->search, function ($q) use ($request) {
                $q->where(function ($query) use ($request) {
                    $query->where('name', 'like', "%{$request->search}%")
                          ->orWhere('email', 'like', "%{$request->search}%")
                          ->orWhere('phone', 'like', "%{$request->search}%");
                });
            })
            ->when($request->role, function ($q) use ($request) {
                $q->whereHas('roles', function ($query) use ($request) {
                    $query->where('name', $request->role);
                });
            })
            ->when($request->status, function ($q) use ($request) {
                $q->where('status', $request->status);
            });

        $users = $query->get();

        // Générer le CSV
        $filename = 'utilisateurs_' . now()->format('Y-m-d_H-i-s') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($users) {
            $file = fopen('php://output', 'w');
            
            // En-têtes CSV
            fputcsv($file, [
                'ID', 'Nom', 'Email', 'Téléphone', 'Adresse', 
                'Date de naissance', 'Statut', 'Rôles', 
                'Date de création', 'Dernière connexion'
            ]);

            // Données
            foreach ($users as $user) {
                fputcsv($file, [
                    $user->id,
                    $user->name,
                    $user->email,
                    $user->phone,
                    $user->address,
                    $user->birth_date ? Carbon::parse($user->birth_date)->format('d/m/Y') : '',
                    ucfirst($user->status),
                    $user->roles->pluck('display_name')->join(', '),
                    $user->created_at->format('d/m/Y H:i'),
                    $user->last_login_at ? Carbon::parse($user->last_login_at)->format('d/m/Y H:i') : 'Jamais',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Tableau de bord avec analytics avancés
     */
    public function dashboard()
    {
        $stats = [
            'total_users' => User::count(),
            'active_users' => User::where('status', 'active')->count(),
            'new_users_today' => User::whereDate('created_at', today())->count(),
            'new_users_this_week' => User::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'new_users_this_month' => User::whereMonth('created_at', now()->month)->count(),
            'top_roles' => DB::table('role_user')
                ->join('roles', 'role_user.role_id', '=', 'roles.id')
                ->select('roles.display_name', DB::raw('count(*) as count'))
                ->groupBy('roles.id', 'roles.display_name')
                ->orderBy('count', 'desc')
                ->limit(5)
                ->get(),
        ];

        // Données pour les graphiques
        $chartData = [
            'users_by_month' => $this->getUsersByMonth(),
            'users_by_status' => $this->getUsersByStatus(),
            'users_by_role' => $this->getUsersByRole(),
            'login_activity' => $this->getLoginActivity(),
        ];

        return view('admin.dashboard.analytics', compact('stats', 'chartData'));
    }

    /**
     * Utilisateurs par mois (12 derniers mois)
     */
    private function getUsersByMonth()
    {
        $data = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $count = User::whereYear('created_at', $date->year)
                        ->whereMonth('created_at', $date->month)
                        ->count();
            
            $data[] = [
                'month' => $date->format('M Y'),
                'count' => $count
            ];
        }
        return $data;
    }

    /**
     * Répartition des utilisateurs par statut
     */
    private function getUsersByStatus()
    {
        return User::select('status', DB::raw('count(*) as count'))
                  ->groupBy('status')
                  ->get()
                  ->map(function ($item) {
                      return [
                          'status' => ucfirst($item->status),
                          'count' => $item->count
                      ];
                  });
    }

    /**
     * Répartition des utilisateurs par rôle
     */
    private function getUsersByRole()
    {
        return DB::table('role_user')
                ->join('roles', 'role_user.role_id', '=', 'roles.id')
                ->select('roles.display_name as role', DB::raw('count(*) as count'))
                ->groupBy('roles.id', 'roles.display_name')
                ->orderBy('count', 'desc')
                ->get();
    }

    /**
     * Activité de connexion (7 derniers jours)
     */
    private function getLoginActivity()
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $count = User::whereDate('last_login_at', $date)->count();
            
            $data[] = [
                'date' => $date->format('d/m'),
                'count' => $count
            ];
        }
        return $data;
    }

    /**
     * Recherche rapide d'utilisateurs (API)
     */
    public function quickSearch(Request $request)
    {
        $query = $request->get('q');
        
        if (strlen($query) < 2) {
            return response()->json([]);
        }

        // Filtrer selon la hiérarchie
        $currentUser = auth()->user();
        $currentUserMaxLevel = $currentUser->roles->max('level') ?? 0;

        $users = User::where(function($q) use ($query) {
                        $q->where('name', 'like', "%{$query}%")
                          ->orWhere('email', 'like', "%{$query}%");
                    })
                    ->whereHas('roles', function ($q) use ($currentUserMaxLevel) {
                        $q->where('level', '<', $currentUserMaxLevel);
                    })
                    ->with('roles')
                    ->limit(10)
                    ->get()
                    ->map(function ($user) {
                        return [
                            'id' => $user->id,
                            'name' => $user->name,
                            'email' => $user->email,
                            'avatar' => $user->avatar 
                                ? asset('storage/' . $user->avatar) 
                                : "https://ui-avatars.com/api/?name=" . urlencode($user->name),
                            'roles' => $user->roles->pluck('display_name')->join(', ')
                        ];
                    });

        return response()->json($users);
    }

    /**
     * Actions en lot
     */
    public function bulkAction(Request $request)
    {
        try {
            // Validation des permissions
            ErrorHandlerController::checkPermission('users.bulk_action');

            $request->validate([
                'action' => 'required|in:activate,deactivate,suspend,delete',
                'user_ids' => 'required|array|min:1|max:100', // Limite de sécurité
                'user_ids.*' => 'exists:users,id'
            ]);

            $userIds = $request->user_ids;
            $action = $request->action;
            $currentUser = auth()->user();
            $currentUserMaxLevel = $currentUser->roles->max('level') ?? 0;

            // Vérifications de sécurité
            if (in_array(Auth::id(), $userIds) && $action === 'delete') {
                return ErrorHandlerController::errorResponse(
                    'Vous ne pouvez pas vous supprimer vous-même', 
                    422
                );
            }

            // Vérifier la hiérarchie pour chaque utilisateur
            $users = User::whereIn('id', $userIds)->with('roles')->get();
            $unauthorizedUsers = [];

            foreach ($users as $user) {
                $targetUserMaxLevel = $user->roles->max('level') ?? 0;
                if ($currentUserMaxLevel <= $targetUserMaxLevel) {
                    $unauthorizedUsers[] = $user->name;
                }
            }

            if (!empty($unauthorizedUsers)) {
                return ErrorHandlerController::errorResponse(
                    'Vous n\'avez pas les permissions pour modifier : ' . implode(', ', $unauthorizedUsers), 
                    403
                );
            }

            // Vérifier que l'utilisateur a le droit de supprimer
            if ($action === 'delete' && !Auth::user()->canDeleteUsers()) {
                return ErrorHandlerController::errorResponse(
                    'Vous n\'avez pas le droit de supprimer des utilisateurs', 
                    403
                );
            }

            DB::beginTransaction();
            
            $message = '';
            switch ($action) {
                case 'activate':
                    User::whereIn('id', $userIds)->update(['status' => 'active']);
                    $message = count($userIds) . ' utilisateur(s) activé(s)';
                    break;
                    
                case 'deactivate':
                    User::whereIn('id', $userIds)->update(['status' => 'inactive']);
                    $message = count($userIds) . ' utilisateur(s) désactivé(s)';
                    break;
                    
                case 'suspend':
                    User::whereIn('id', $userIds)->update(['status' => 'suspended']);
                    $message = count($userIds) . ' utilisateur(s) suspendu(s)';
                    break;
                    
                case 'delete':
                    // Archiver au lieu de supprimer définitivement
                    User::whereIn('id', $userIds)->update([
                        'status' => 'archived',
                        'archived_at' => now(),
                        'archived_by' => $currentUser->id
                    ]);
                    $message = count($userIds) . ' utilisateur(s) archivé(s)';
                    break;
            }

            DB::commit();
            
            // Effacer le cache des stats
            Cache::forget('user_stats');

            return ErrorHandlerController::successResponse($message);

        } catch (Exception $e) {
            DB::rollback();
            return ErrorHandlerController::handleError($e, 'bulk_action', [
                'action' => $request->action,
                'user_count' => count($request->user_ids ?? [])
            ]);
        }
    }

    /**
     * Mise à jour du statut en temps réel
     */
    public function updateStatus(Request $request, User $user)
    {
        $request->validate([
            'status' => 'required|in:active,inactive,suspended'
        ]);

        $user->update(['status' => $request->status]);

        // Effacer le cache
        Cache::forget('user_stats');

        return response()->json([
            'success' => true,
            'message' => 'Statut mis à jour',
            'user' => $user->load('roles')
        ]);
    }

    /**
     * Suggestions d'utilisateurs pour l'autocomplétion
     */
    public function suggestions(Request $request)
    {
        $query = $request->get('q', '');
        
        $users = User::where('name', 'like', "%{$query}%")
                    ->orWhere('email', 'like', "%{$query}%")
                    ->select('id', 'name', 'email', 'avatar')
                    ->limit(5)
                    ->get();

        return response()->json($users);
    }
}