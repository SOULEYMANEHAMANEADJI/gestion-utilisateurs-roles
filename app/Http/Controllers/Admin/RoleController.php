<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\RoleRequest;
use App\Models\Role;
use App\Models\User;
use App\Services\NotificationService;
use App\Exceptions\UserManagementException;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RoleController extends Controller
{
    /**
     * Afficher la liste des rôles avec filtres
     */
    public function index(Request $request): View
    {
        try {
            $query = Role::withCount('users');

            // Application des filtres
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('display_name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }

            if ($request->filled('level')) {
                $query->where('level', '>=', $request->level);
            }

            if ($request->filled('is_default')) {
                $query->where('is_default', $request->is_default === '1');
            }

            // Tri
            $sortField = $request->get('sort', 'level');
            $sortDirection = $request->get('direction', 'desc');
            $query->orderBy($sortField, $sortDirection);

            $roles = $query->paginate(15)->appends($request->query());

            // Statistiques rapides
            $stats = $this->getRoleStats();

            return view('admin.roles.index', compact('roles', 'stats'));

        } catch (\Exception $e) {
            Log::error('Erreur lors du chargement des rôles', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            NotificationService::error('role.load_failed');
            return view('admin.roles.index', [
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
        $permissions = $this->getAvailablePermissions();
        $maxLevel = $this->getMaxAllowedLevel();

        return view('admin.roles.create', compact('permissions', 'maxLevel'));
    }

    /**
     * Créer un nouveau rôle
     */
    public function store(RoleRequest $request): RedirectResponse
    {
        DB::beginTransaction();

        try {
            // Vérifier les doublons de nom
            if (Role::where('name', $request->name)->exists()) {
                throw new UserManagementException("Un rôle avec ce nom existe déjà.", 'duplicate_role_name');
            }

            // Valider le niveau de rôle
            $this->validateRoleLevel($request->level);

            $data = $request->validated();
            $data['permissions'] = $request->input('permissions', []);

            // Définir automatiquement la couleur si non fournie
            if (empty($data['color'])) {
                $data['color'] = $this->generateRandomColor();
            }

            $role = Role::create($data);

            DB::commit();

            NotificationService::success('role.created');
            Log::info('Rôle créé avec succès', [
                'role_id' => $role->id,
                'role_name' => $role->name,
                'created_by' => auth()->id()
            ]);

            return redirect()->route('admin.roles.index');

        } catch (UserManagementException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la création de rôle', [
                'error' => $e->getMessage(),
                'data' => $request->except(['password'])
            ]);

            NotificationService::error('role.creation_failed');
            return back()->withInput();
        }
    }

    /**
     * Afficher un rôle
     */
    public function show(Role $role): View
    {
        $role->loadCount('users');
        $role->load(['users' => function ($query) {
            $query->with('roles')->take(10); // Limiter pour la performance
        }]);

        return view('admin.roles.show', compact('role'));
    }

    /**
     * Afficher le formulaire d'édition
     */
    public function edit(Role $role): View
    {
        $this->authorizeRoleAccess($role, 'edit');

        $permissions = $this->getAvailablePermissions();
        $maxLevel = $this->getMaxAllowedLevel();

        return view('admin.roles.edit', compact('role', 'permissions', 'maxLevel'));
    }

    /**
     * Mettre à jour un rôle
     */
    public function update(RoleRequest $request, Role $role): RedirectResponse
    {
        DB::beginTransaction();

        try {
            $this->authorizeRoleAccess($role, 'edit');

            // Vérifier les doublons de nom (sauf pour le rôle actuel)
            if (Role::where('name', $request->name)->where('id', '!=', $role->id)->exists()) {
                throw new UserManagementException("Un rôle avec ce nom existe déjà.", 'duplicate_role_name');
            }

            // Valider le niveau de rôle
            $this->validateRoleLevel($request->level);

            $data = $request->validated();
            $data['permissions'] = $request->input('permissions', []);

            $role->update($data);

            DB::commit();

            NotificationService::success('role.updated');
            Log::info('Rôle mis à jour', [
                'role_id' => $role->id,
                'updated_by' => auth()->id(),
                'changes' => $role->getChanges()
            ]);

            return redirect()->route('admin.roles.index');

        } catch (UserManagementException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la mise à jour du rôle', [
                'role_id' => $role->id,
                'error' => $e->getMessage()
            ]);

            NotificationService::error('role.update_failed');
            return back()->withInput();
        }
    }

    /**
     * Supprimer un rôle
     */
    public function destroy(Role $role): RedirectResponse
    {
        try {
            $this->authorizeRoleAccess($role, 'delete');

            // Vérifier si le rôle est utilisé
            if ($role->users()->count() > 0) {
                throw new UserManagementException(
                    "Impossible de supprimer ce rôle car il est assigné à {$role->users()->count()} utilisateur(s).",
                    'role_in_use'
                );
            }

            // Empêcher la suppression de rôles système critiques
            $systemRoles = ['super_admin', 'admin'];
            if (in_array($role->name, $systemRoles)) {
                throw new UserManagementException(
                    "Impossible de supprimer ce rôle système critique.",
                    'system_role_deletion'
                );
            }

            $roleName = $role->name;
            $role->delete();

            NotificationService::success('role.deleted');
            Log::info('Rôle supprimé', [
                'role_name' => $roleName,
                'deleted_by' => auth()->id()
            ]);

            return redirect()->route('admin.roles.index');

        } catch (UserManagementException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Erreur lors de la suppression du rôle', [
                'role_id' => $role->id,
                'error' => $e->getMessage()
            ]);

            NotificationService::error('role.delete_failed');
            return back();
        }
    }

    /**
     * Dupliquer un rôle
     */
    public function duplicate(Role $role): RedirectResponse
    {
        DB::beginTransaction();

        try {
            $this->authorizeRoleAccess($role, 'create');

            $newRole = $role->replicate();
            $newRole->name = $role->name . '_copy_' . now()->format('YmdHis');
            $newRole->display_name = $role->display_name . ' (Copie)';
            $newRole->is_default = false;
            $newRole->save();

            DB::commit();

            NotificationService::success('role.duplicated');
            Log::info('Rôle dupliqué', [
                'original_role_id' => $role->id,
                'new_role_id' => $newRole->id,
                'duplicated_by' => auth()->id()
            ]);

            return redirect()->route('admin.roles.edit', $newRole);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la duplication du rôle', [
                'role_id' => $role->id,
                'error' => $e->getMessage()
            ]);

            NotificationService::error('role.duplicate_failed');
            return back();
        }
    }

    /**
     * Obtenir les utilisateurs d'un rôle (AJAX)
     */
    public function users(Role $role): JsonResponse
    {
        try {
            $users = $role->users()
                ->with('roles')
                ->get()
                ->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'status' => $user->status,
                        'avatar' => $user->avatar ? asset('storage/' . $user->avatar) : null,
                        'roles_count' => $user->roles->count()
                    ];
                });

            return response()->json([
                'success' => true,
                'users' => $users,
                'count' => $users->count()
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des utilisateurs du rôle', [
                'role_id' => $role->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Erreur lors de la récupération des utilisateurs.'
            ], 500);
        }
    }

    /**
     * Méthodes privées d'aide
     */
    private function authorizeRoleAccess(Role $role, string $action): void
    {
        /** @var User $currentUser */
        $currentUser = auth()->user();
        $currentUserMaxLevel = $currentUser->roles->max('level') ?? 0;

        // Super admin peut tout faire
        if ($currentUser->hasRole('super_admin')) {
            return;
        }

        // Vérifier si l'utilisateur peut modifier ce rôle
        if ($role->level >= $currentUserMaxLevel) {
            throw UserManagementException::permissionDenied($action, [
                'role_id' => $role->id,
                'role_level' => $role->level,
                'user_level' => $currentUserMaxLevel
            ]);
        }
    }

    private function validateRoleLevel(int $level): void
    {
        /** @var User $currentUser */
        $currentUser = auth()->user();
        $currentUserMaxLevel = $currentUser->roles->max('level') ?? 0;

        // Super admin peut créer n'importe quel niveau
        if ($currentUser->hasRole('super_admin')) {
            return;
        }

        if ($level >= $currentUserMaxLevel) {
            throw new UserManagementException(
                "Vous ne pouvez pas créer un rôle de niveau supérieur ou égal au vôtre (niveau {$currentUserMaxLevel}).",
                'invalid_role_level'
            );
        }
    }

    private function getAvailablePermissions(): array
    {
        return [
            'users' => [
                'users.view' => 'Voir les utilisateurs',
                'users.create' => 'Créer des utilisateurs',
                'users.edit' => 'Modifier les utilisateurs',
                'users.delete' => 'Supprimer les utilisateurs',
                'users.export' => 'Exporter les utilisateurs',
                'users.bulk_actions' => 'Actions en lot sur les utilisateurs'
            ],
            'roles' => [
                'roles.view' => 'Voir les rôles',
                'roles.create' => 'Créer des rôles',
                'roles.edit' => 'Modifier les rôles',
                'roles.delete' => 'Supprimer les rôles'
            ],
            'system' => [
                'analytics.view' => 'Voir les analytics',
                'logs.view' => 'Voir les logs système',
                'settings.manage' => 'Gérer les paramètres système'
            ]
        ];
    }

    private function getMaxAllowedLevel(): int
    {
        /** @var User $currentUser */
        $currentUser = auth()->user();
        $currentUserMaxLevel = $currentUser->roles->max('level') ?? 0;

        // Super admin peut créer jusqu'au niveau 100
        if ($currentUser->hasRole('super_admin')) {
            return 100;
        }

        // Les autres ne peuvent créer que des niveaux inférieurs au leur
        return max(1, $currentUserMaxLevel - 1);
    }

    private function generateRandomColor(): string
    {
        $colors = [
            '#3B82F6', '#EF4444', '#10B981', '#F59E0B',
            '#8B5CF6', '#EC4899', '#06B6D4', '#84CC16',
            '#F97316', '#6366F1', '#14B8A6', '#F43F5E'
        ];

        return $colors[array_rand($colors)];
    }

    private function getRoleStats(): array
    {
        return [
            'total' => Role::count(),
            'with_users' => Role::has('users')->count(),
            'default_roles' => Role::where('is_default', true)->count(),
            'system_roles' => Role::whereIn('name', ['super_admin', 'admin', 'manager'])->count()
        ];
    }
}
