<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequest;
use App\Models\User;
use App\Models\Role;
use App\Services\NotificationService;
use App\Exceptions\UserManagementException;
use App\Exports\UsersExport;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class UserController extends Controller
{
    /**
     * Afficher la liste des utilisateurs avec filtres avancés
     */
    public function index(Request $request): View
    {
        try {
            $query = User::with(['roles', 'archivedBy']);

            // Application des filtres
            $this->applyFilters($query, $request);

            // Tri
            $sortField = $request->get('sort', 'created_at');
            $sortDirection = $request->get('direction', 'desc');
            $query->orderBy($sortField, $sortDirection);

            $users = $query->paginate(15)->appends($request->query());
            $roles = Role::all();

            // Statistiques rapides
            $stats = $this->getUserStats();

            return view('admin.users.index', compact('users', 'roles', 'stats'));
        } catch (\Exception $e) {
            Log::error('Erreur lors du chargement des utilisateurs', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            NotificationService::error('user.load_failed');
            return view('admin.users.index', [
                'users' => collect(),
                'roles' => collect(),
                'stats' => []
            ]);
        }
    }

    /**
     * Afficher le formulaire de création
     */
    public function create(): View
    {
        $roles = $this->getAvailableRoles();
        return view('admin.users.create', compact('roles'));
    }

    /**
     * Créer un nouvel utilisateur
     */
    public function store(UserRequest $request): RedirectResponse
    {
        DB::beginTransaction();

        try {
            // Vérifier les doublons d'email
            if (User::where('email', $request->email)->exists()) {
                throw UserManagementException::duplicateEmail($request->email);
            }

            $data = $request->validated();

            // Gestion de l'avatar
            if ($request->hasFile('avatar')) {
                $data['avatar'] = $this->handleAvatarUpload($request->file('avatar'));
            }

            $data['password'] = Hash::make($data['password']);
            $data['status'] = $data['status'] ?? 'active';

            $user = User::create($data);

            // Assignation des rôles avec validation de hiérarchie
            if ($request->filled('roles')) {
                $this->assignRolesWithValidation($user, $request->roles);
            }

            DB::commit();

            NotificationService::success('user.created');
            Log::info('Utilisateur créé avec succès', [
                'user_id' => $user->id,
                'created_by' => auth()->id(),
                'roles' => $request->roles ?? []
            ]);

            return redirect()->route('admin.users.index');

        } catch (UserManagementException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la création utilisateur', [
                'error' => $e->getMessage(),
                'data' => $request->except(['password'])
            ]);

            NotificationService::error('user.creation_failed');
            return back()->withInput();
        }
    }

    /**
     * Afficher un utilisateur
     */
    public function show(User $user): View
    {
        $this->authorizeUserAccess($user, 'view');

        $user->load(['roles', 'archivedBy']);
        $activityLog = $this->getUserActivityLog($user);

        return view('admin.users.show', compact('user', 'activityLog'));
    }

    /**
     * Afficher le formulaire d'édition
     */
    public function edit(User $user): View
    {
        $this->authorizeUserAccess($user, 'edit');

        $roles = $this->getAvailableRoles();
        $user->load('roles');

        return view('admin.users.edit', compact('user', 'roles'));
    }

    /**
     * Mettre à jour un utilisateur
     */
    public function update(UserRequest $request, User $user): RedirectResponse
    {
        DB::beginTransaction();

        try {
            $this->authorizeUserAccess($user, 'edit');

            // Vérifier les doublons d'email (sauf pour l'utilisateur actuel)
            if (User::where('email', $request->email)->where('id', '!=', $user->id)->exists()) {
                throw UserManagementException::duplicateEmail($request->email);
            }

            $data = $request->validated();

            // Gestion de l'avatar
            if ($request->hasFile('avatar')) {
                $this->deleteOldAvatar($user->avatar);
                $data['avatar'] = $this->handleAvatarUpload($request->file('avatar'));
            }

            // Gestion du mot de passe
            if (!empty($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            } else {
                unset($data['password']);
            }

            $user->update($data);

            // Mise à jour des rôles
            if ($request->filled('roles')) {
                $this->assignRolesWithValidation($user, $request->roles);
            }

            DB::commit();

            NotificationService::success('user.updated');
            Log::info('Utilisateur mis à jour', [
                'user_id' => $user->id,
                'updated_by' => auth()->id(),
                'changes' => $user->getChanges()
            ]);

            return redirect()->route('admin.users.index');

        } catch (UserManagementException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la mise à jour utilisateur', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            NotificationService::error('user.update_failed');
            return back()->withInput();
        }
    }

    /**
     * Supprimer/Archiver un utilisateur
     */
    public function destroy(User $user): RedirectResponse
    {
        try {
            $this->authorizeUserAccess($user, 'delete');

            // Vérifier si c'est le dernier super_admin
            if ($user->hasRole('super_admin') && $this->isLastSuperAdmin($user)) {
                throw UserManagementException::lastSuperAdmin();
            }

            // Archiver au lieu de supprimer définitivement
            $user->update([
                'status' => 'archived',
                'archived_at' => now(),
                'archived_by' => auth()->id()
            ]);

            NotificationService::success('user.archived');
            Log::info('Utilisateur archivé', [
                'user_id' => $user->id,
                'archived_by' => auth()->id()
            ]);

            return redirect()->route('admin.users.index');

        } catch (UserManagementException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'archivage utilisateur', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            NotificationService::error('user.archive_failed');
            return back();
        }
    }

    /**
     * Basculer le statut d'un utilisateur
     */
    public function toggleStatus(User $user): RedirectResponse
    {
        try {
            $this->authorizeUserAccess($user, 'edit');

            $newStatus = $user->status === 'active' ? 'inactive' : 'active';
            $user->update(['status' => $newStatus]);

            NotificationService::success('user.status_changed');
            Log::info('Statut utilisateur modifié', [
                'user_id' => $user->id,
                'old_status' => $user->status,
                'new_status' => $newStatus,
                'changed_by' => auth()->id()
            ]);

            return back();

        } catch (UserManagementException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Erreur lors du changement de statut', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            NotificationService::error('user.status_change_failed');
            return back();
        }
    }

    /**
     * Restaurer un utilisateur archivé
     */
    public function restore(User $user): RedirectResponse
    {
        try {
            $this->authorizeUserAccess($user, 'edit');

            $user->update([
                'status' => 'active',
                'archived_at' => null,
                'archived_by' => null
            ]);

            NotificationService::success('user.restored');
            Log::info('Utilisateur restauré', [
                'user_id' => $user->id,
                'restored_by' => auth()->id()
            ]);

            return redirect()->route('admin.users.index');

        } catch (UserManagementException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Erreur lors de la restauration utilisateur', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            NotificationService::error('user.restore_failed');
            return back();
        }
    }

    /**
     * Actions en lot sur les utilisateurs
     */
    public function bulkAction(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'action' => 'required|in:delete,activate,deactivate,archive',
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'integer|exists:users,id'
        ]);

        DB::beginTransaction();

        try {
            $action = $request->action;
            $userIds = $request->user_ids;
            $results = NotificationService::validateBulkAction($action, $userIds);

            $processedCount = 0;

            foreach ($results['success'] as $userId) {
                $user = User::find($userId);
                if (!$user) continue;

                try {
                    $this->authorizeUserAccess($user, $action);

                    switch ($action) {
                        case 'delete':
                        case 'archive':
                            if (!($user->hasRole('super_admin') && $this->isLastSuperAdmin($user))) {
                                $user->update([
                                    'status' => 'archived',
                                    'archived_at' => now(),
                                    'archived_by' => auth()->id()
                                ]);
                                $processedCount++;
                            }
                            break;

                        case 'activate':
                            $user->update(['status' => 'active']);
                            $processedCount++;
                            break;

                        case 'deactivate':
                            $user->update(['status' => 'inactive']);
                            $processedCount++;
                            break;
                    }
                } catch (UserManagementException $e) {
                    $results['errors'][] = "Utilisateur #{$userId}: " . $e->getMessage();
                }
            }

            DB::commit();

            if ($processedCount > 0) {
                NotificationService::success('user.bulk_action', ['count' => $processedCount]);
                Log::info('Action en lot exécutée', [
                    'action' => $action,
                    'processed_count' => $processedCount,
                    'executed_by' => auth()->id()
                ]);
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'processed' => $processedCount,
                    'errors' => $results['errors']
                ]);
            }

            return back();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de l\'action en lot', [
                'action' => $action,
                'error' => $e->getMessage()
            ]);

            NotificationService::error('user.bulk_action_failed');

            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
            }

            return back();
        }
    }

    /**
     * Exporter les utilisateurs vers Excel
     */
    public function export(Request $request)
    {
        try {
            $query = User::with('roles');
            $this->applyFilters($query, $request);

            $users = $query->get();

            // Préparer les données pour l'export
            $exportData = $users->map(function ($user) {
                return [
                    'ID' => $user->id,
                    'Nom' => $user->name,
                    'Email' => $user->email,
                    'Téléphone' => $user->phone ?? 'N/A',
                    'Adresse' => $user->address ?? 'N/A',
                    'Date de naissance' => $user->birth_date ? $user->birth_date->format('d/m/Y') : 'N/A',
                    'Statut' => ucfirst($user->status),
                    'Rôles' => $user->roles->pluck('display_name')->join(', '),
                    'Date de création' => $user->created_at->format('d/m/Y H:i'),
                    'Dernière connexion' => $user->last_login_at ? $user->last_login_at->format('d/m/Y H:i') : 'Jamais',
                    'Archivé le' => $user->archived_at ? $user->archived_at->format('d/m/Y H:i') : 'Non',
                ];
            });

            $filename = 'utilisateurs_' . now()->format('Y-m-d_H-i-s') . '.xlsx';

            NotificationService::success('user.exported');
            Log::info('Export utilisateurs', [
                'count' => $users->count(),
                'exported_by' => auth()->id(),
                'filename' => $filename
            ]);

            return Excel::download(new UsersExport($exportData), $filename);

        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'export', ['error' => $e->getMessage()]);
            NotificationService::error('user.export_failed');
            return back();
        }
    }

    /**
     * Recherche rapide d'utilisateurs (pour AJAX)
     */
    public function quickSearch(Request $request): JsonResponse
    {
        try {
            $search = $request->get('q', '');

            if (strlen($search) < 2) {
                return response()->json(['results' => []]);
            }

            $users = User::where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%");
            })
            ->with('roles')
            ->limit(10)
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'status' => $user->status,
                    'roles' => $user->roles->pluck('display_name')->join(', '),
                    'avatar' => $user->avatar ? asset('storage/' . $user->avatar) : null,
                ];
            });

            return response()->json(['results' => $users]);

        } catch (\Exception $e) {
            Log::error('Erreur recherche rapide', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Erreur lors de la recherche'], 500);
        }
    }

    /**
     * Obtenir des suggestions pour l'autocomplétion
     */
    public function suggestions(Request $request): JsonResponse
    {
        try {
            $field = $request->get('field', 'name');
            $query = $request->get('q', '');

            if (strlen($query) < 2) {
                return response()->json(['suggestions' => []]);
            }

            $suggestions = [];

            switch ($field) {
                case 'name':
                    $suggestions = User::where('name', 'like', "%{$query}%")
                        ->distinct()
                        ->pluck('name')
                        ->take(5)
                        ->toArray();
                    break;

                case 'email':
                    $suggestions = User::where('email', 'like', "%{$query}%")
                        ->distinct()
                        ->pluck('email')
                        ->take(5)
                        ->toArray();
                    break;

                case 'phone':
                    $suggestions = User::where('phone', 'like', "%{$query}%")
                        ->whereNotNull('phone')
                        ->distinct()
                        ->pluck('phone')
                        ->take(5)
                        ->toArray();
                    break;
            }

            return response()->json(['suggestions' => $suggestions]);

        } catch (\Exception $e) {
            Log::error('Erreur suggestions', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Erreur lors de la récupération des suggestions'], 500);
        }
    }

    /**
     * Dashboard analytique des utilisateurs
     */
    public function analytics(Request $request): View
    {
        try {
            $period = $request->get('period', '30'); // 30 jours par défaut
            $startDate = now()->subDays($period);

            $analytics = [
                'total_users' => User::count(),
                'active_users' => User::where('status', 'active')->count(),
                'inactive_users' => User::where('status', 'inactive')->count(),
                'archived_users' => User::where('status', 'archived')->count(),

                // Nouvelles inscriptions par période
                'new_registrations' => User::where('created_at', '>=', $startDate)
                    ->groupBy(DB::raw('DATE(created_at)'))
                    ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
                    ->orderBy('date')
                    ->get(),

                // Répartition par rôles
                'role_distribution' => Role::withCount('users')
                    ->get()
                    ->map(function ($role) {
                        return [
                            'name' => $role->display_name,
                            'count' => $role->users_count,
                            'color' => $role->color
                        ];
                    }),

                // Activité récente
                'recent_activity' => User::where('last_login_at', '>=', $startDate)
                    ->count(),

                // Top domaines email
                'email_domains' => User::selectRaw('SUBSTRING_INDEX(email, "@", -1) as domain, COUNT(*) as count')
                    ->groupBy('domain')
                    ->orderByDesc('count')
                    ->limit(10)
                    ->get(),

                // Statistiques par mois
                'monthly_stats' => User::selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, COUNT(*) as count')
                    ->where('created_at', '>=', now()->subMonths(12))
                    ->groupByRaw('YEAR(created_at), MONTH(created_at)')
                    ->orderByRaw('year DESC, month DESC')
                    ->get()
            ];

            return view('admin.analytics.users', compact('analytics', 'period'));

        } catch (\Exception $e) {
            Log::error('Erreur analytics', ['error' => $e->getMessage()]);
            NotificationService::error('analytics.load_failed');

            return view('admin.analytics.users', [
                'analytics' => [],
                'period' => $period ?? '30'
            ]);
        }
    }

    /**
     * Mettre à jour le statut d'un utilisateur (PATCH)
     */
    public function updateStatus(Request $request, User $user): JsonResponse
    {
        try {
            $request->validate([
                'status' => 'required|in:active,inactive,suspended,archived'
            ]);

            $this->authorizeUserAccess($user, 'edit');

            $oldStatus = $user->status;
            $newStatus = $request->status;

            // Vérifications spéciales pour certains statuts
            if ($newStatus === 'archived') {
                if ($user->hasRole('super_admin') && $this->isLastSuperAdmin($user)) {
                    throw UserManagementException::lastSuperAdmin();
                }

                $user->update([
                    'status' => $newStatus,
                    'archived_at' => now(),
                    'archived_by' => auth()->id()
                ]);
            } else {
                $updateData = ['status' => $newStatus];

                // Si on reactive un utilisateur archivé, nettoyer les champs d'archivage
                if ($oldStatus === 'archived' && $newStatus === 'active') {
                    $updateData['archived_at'] = null;
                    $updateData['archived_by'] = null;
                }

                $user->update($updateData);
            }

            Log::info('Statut utilisateur modifié via PATCH', [
                'user_id' => $user->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'changed_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => "Statut modifié de '{$oldStatus}' vers '{$newStatus}' avec succès.",
                'new_status' => $newStatus,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'status' => $user->status
                ]
            ]);

        } catch (UserManagementException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la mise à jour du statut', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Erreur lors de la mise à jour du statut.'
            ], 500);
        }
    }

    /**
     * Méthodes privées d'aide
     */
    private function applyFilters($query, Request $request): void
    {
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->whereHas('roles', function ($q) use ($request) {
                $q->where('name', $request->role);
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
    }

    /**
     * Récupérer l'utilisateur courant avec le bon typage
     */
    private function getCurrentUser(): User
    {
        $user = auth()->user();

        if (!$user instanceof User) {
            throw new \Exception('Utilisateur non authentifié ou type incorrect');
        }

        return $user;
    }

    private function authorizeUserAccess(User $targetUser, string $action): void
    {
        $currentUser = $this->getCurrentUser();

        // Vérifier si l'utilisateur peut modifier/voir cet utilisateur
        if (!$this->canModifyUser($targetUser)) {
            throw UserManagementException::permissionDenied($action, [
                'target_user_id' => $targetUser->id,
                'current_user_id' => $currentUser->id
            ]);
        }

        // Empêcher l'auto-suppression
        if ($action === 'delete' && $currentUser->id === $targetUser->id) {
            throw new UserManagementException("Vous ne pouvez pas supprimer votre propre compte.", 'self_deletion_forbidden');
        }
    }

    private function handleAvatarUpload($file): string
    {
        try {
            return $file->store('avatars', 'public');
        } catch (\Exception $e) {
            Log::error('Erreur upload avatar', ['error' => $e->getMessage()]);
            throw new UserManagementException("Échec du téléchargement de l'avatar.", 'upload_failed');
        }
    }

    private function deleteOldAvatar(?string $avatarPath): void
    {
        if ($avatarPath && Storage::disk('public')->exists($avatarPath)) {
            Storage::disk('public')->delete($avatarPath);
        }
    }

    private function assignRolesWithValidation(User $user, array $roleIds): void
    {
        $this->validateRoleAssignment($roleIds);

        $user->roles()->sync($roleIds, [
            'assigned_by' => auth()->id(),
            'assigned_at' => now()
        ]);
    }

    private function getAvailableRoles()
    {
        try {
            $currentUser = $this->getCurrentUser();
            $currentUserMaxLevel = $currentUser->roles->max('level') ?? 0;

            if ($currentUser->hasRole('super_admin')) {
                return Role::all();
            }

            return Role::where('level', '<', $currentUserMaxLevel)->get();
        } catch (\Exception $e) {
            return Role::all(); // Fallback par sécurité
        }
    }

    private function getUserStats(): array
    {
        return [
            'total' => User::count(),
            'active' => User::where('status', 'active')->count(),
            'inactive' => User::where('status', 'inactive')->count(),
            'archived' => User::where('status', 'archived')->count(),
            'new_this_month' => User::whereMonth('created_at', now()->month)->count()
        ];
    }

    private function getUserActivityLog(User $user): array
    {
        // Placeholder pour un futur système de logs d'activité
        return [
            'last_login' => $user->last_login_at,
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
            'archived_at' => $user->archived_at
        ];
    }

    /**
     * Vérifier si l'utilisateur connecté peut modifier un autre utilisateur
     */
    private function canModifyUser(User $targetUser): bool
    {
        try {
            $currentUser = $this->getCurrentUser();

            // Super admin peut tout faire
            if ($currentUser->hasRole('super_admin')) {
                return true;
            }

            // Admin ne peut pas modifier les super_admin
            if ($currentUser->hasRole('admin') && $targetUser->hasRole('super_admin')) {
                return false;
            }

            // Admin ne peut pas modifier d'autres admin
            if ($currentUser->hasRole('admin') && $targetUser->hasRole('admin')) {
                return false;
            }

            // Vérifier la hiérarchie des niveaux
            $currentUserMaxLevel = $currentUser->roles->max('level') ?? 0;
            $targetUserMaxLevel = $targetUser->roles->max('level') ?? 0;

            return $currentUserMaxLevel > $targetUserMaxLevel;
        } catch (\Exception $e) {
            return false; // Pas d'accès en cas d'erreur
        }
    }

    /**
     * Vérifier si c'est le dernier super_admin
     */
    private function isLastSuperAdmin(User $user): bool
    {
        return User::whereHas('roles', function ($query) {
            $query->where('name', 'super_admin');
        })->where('id', '!=', $user->id)->count() === 0;
    }


    /**
     * Valider l'assignation de rôles
     */
    private function validateRoleAssignment(array $roleIds): void
    {
        $currentUser = $this->getCurrentUser();
        $currentUserMaxLevel = $currentUser->roles->max('level') ?? 0;

        $roles = Role::whereIn('id', $roleIds)->get();

        foreach ($roles as $role) {
            // Super admin peut s'assigner n'importe quel rôle
            if ($currentUser->hasRole('super_admin')) {
                continue;
            }

            if ($role->level >= $currentUserMaxLevel) {
                throw new \Exception("Vous ne pouvez pas assigner le rôle {$role->display_name}.");
            }
        }
    }
}
