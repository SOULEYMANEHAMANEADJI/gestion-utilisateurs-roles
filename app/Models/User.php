<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Auth;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'address',
        'birth_date',
        'avatar',
        'status',
        'last_login_at',
        'email_verified_at',
        'archived_at',
        'archived_by',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'birth_date' => 'date',
            'last_login_at' => 'datetime',
            'archived_at' => 'datetime',
        ];
    }

    protected $dates = ['deleted_at'];

    /**
     * Get the roles that belong to the user.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_user')
                    ->withTimestamps()
                    ->withPivot('assigned_by', 'assigned_at');
    }

    /**
     * Get the user who archived this user.
     */
    public function archivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'archived_by');
    }

    /**
     * Get roles assigned by this user.
     */
    public function assignedRoles()
    {
        return $this->belongsToMany(Role::class, 'role_user', 'assigned_by', 'role_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeWithRole($query, $role)
    {
        return $query->whereHas('roles', function ($q) use ($role) {
            $q->where('name', $role);
        });
    }

    // Accesseurs
    protected function fullName(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->name,
        );
    }

    protected function isActive(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->status === 'active',
        );
    }

    /**
     * Check if the user has a specific role.
     */
    public function hasRole($role): bool
    {
        if (is_string($role)) {
            return $this->roles->contains('name', $role);
        }
        
        return $this->roles->contains($role);
    }

    /**
     * Check if the user has any of the given roles.
     */
    public function hasAnyRole(array $roles): bool
    {
        return $this->roles->whereIn('name', $roles)->isNotEmpty();
    }

    /**
     * Check if the user has all of the given roles.
     */
    public function hasAllRoles(array $roles): bool
    {
        return $this->roles()->whereIn('name', $roles)->count() === count($roles);
    }

    /**
     * Check if the user has a specific permission.
     */
    public function hasPermission($permission): bool
    {
        return $this->roles->flatMap->permissions->contains('name', $permission);
    }

    /**
     * Check if the user is an admin.
     */
    public function isAdmin(): bool
    {
        return $this->hasAnyRole(['super_admin', 'admin']);
    }

    /**
     * Check if the user is a super admin.
     */
    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super_admin');
    }

    /**
     * Check if the user can manage users.
     */
    public function canManageUsers(): bool
    {
        return $this->hasAnyRole(['super_admin', 'admin', 'manager']);
    }

    /**
     * Check if the user can manage roles.
     */
    public function canManageRoles(): bool
    {
        return $this->hasAnyRole(['super_admin', 'admin']);
    }

    /**
     * Check if the user can export data.
     */
    public function canExportData(): bool
    {
        return $this->hasAnyRole(['super_admin', 'admin', 'manager']);
    }

    /**
     * Check if the user can delete users.
     */
    public function canDeleteUsers(): bool
    {
        return $this->hasAnyRole(['super_admin', 'admin']);
    }

    /**
     * Check if the user has administrative privileges.
     */
    public function hasAdminPrivileges(): bool
    {
        return $this->isSuperAdmin() || $this->isAdmin() || $this->hasRole('manager');
    }

    /**
     * Check if the user is an author.
     */
    public function isAuthor(): bool
    {
        return $this->hasRole('author');
    }

    /**
     * Check if the user is a user.
     */
    public function isUser(): bool
    {
        return $this->hasRole('user');
    }

    /**
     * Get the user's role names as a collection.
     */
    public function getRoleNames(): Collection
    {
        return $this->roles->pluck('name');
    }

    /**
     * Assign a role to the user.
     */
    public function assignRole($role)
    {
        if (is_string($role)) {
            $role = Role::where('name', $role)->first();
        }

        if ($role && !$this->hasRole($role)) {
            $this->roles()->attach($role->id, [
                'assigned_by' => Auth::check() ? Auth::id() : null,
                'assigned_at' => now(),
            ]);
        }

        return $this;
    }

    /**
     * Remove a role from the user.
     */
    public function removeRole($role)
    {
        if (is_string($role)) {
            $role = Role::where('name', $role)->first();
        }

        if ($role) {
            $this->roles()->detach($role->id);
        }

        return $this;
    }

    /**
     * Sync roles for the user.
     */
    public function syncRoles(array $roles): void
    {
        $roleIds = Role::whereIn('name', $roles)->pluck('id');
        $this->roles()->sync($roleIds);
    }

    /**
     * Get the user's avatar URL.
     */
    public function getAvatarUrlAttribute(): string
    {
        return $this->avatar 
            ? asset('storage/avatars/' . $this->avatar)
            : 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&color=7F9CF5&background=EBF4FF';
    }

    /**
     * Get the user's full name with role information.
     */
    public function getFullNameWithRolesAttribute(): string
    {
        $roles = $this->getRoleNames()->implode(', ');
        return $roles ? "{$this->name} ({$roles})" : $this->name;
    }
}
