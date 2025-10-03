<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

class Role extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'display_name',
        'description',
        'color',
        'permissions',
        'level',
        'is_default',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'permissions' => 'array',
        'is_default' => 'boolean',
        'level' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $dates = ['deleted_at'];

    /**
     * Get the users that belong to the role.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'role_user')
                    ->withTimestamps()
                    ->withPivot('assigned_by', 'assigned_at');
    }

    // Scopes
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    public function scopeByLevel($query, $level)
    {
        return $query->where('level', '>=', $level);
    }

    /**
     * Get the number of users with this role.
     */
    public function getUsersCountAttribute(): int
    {
        return $this->users()->count();
    }

    /**
     * Check if the role has a specific permission.
     */
    public function hasPermission($permission): bool
    {
        return in_array($permission, $this->permissions ?? []);
    }

    /**
     * Add a permission to the role.
     */
    public function addPermission($permission)
    {
        $permissions = $this->permissions ?? [];
        if (!in_array($permission, $permissions)) {
            $permissions[] = $permission;
            $this->permissions = $permissions;
            $this->save();
        }
        return $this;
    }

    /**
     * Remove a permission from the role.
     */
    public function removePermission($permission)
    {
        $permissions = $this->permissions ?? [];
        $this->permissions = array_diff($permissions, [$permission]);
        $this->save();
        return $this;
    }

    /**
     * Check if the role has any users.
     */
    public function hasUsers(): bool
    {
        return $this->users()->exists();
    }

    /**
     * Get all role names as a collection.
     */
    public static function getRoleNames(): Collection
    {
        return static::pluck('name');
    }

    /**
     * Find a role by name.
     */
    public static function findByName(string $name): ?self
    {
        return static::where('name', $name)->first();
    }

    /**
     * Create a new role if it doesn't exist.
     */
    public static function firstOrCreateRole(string $name, ?string $description = null): self
    {
        return static::firstOrCreate(
            ['name' => $name],
            ['description' => $description]
        );
    }

    /**
     * Get the role's display name with user count.
     */
    public function getDisplayNameAttribute(): string
    {
        $count = $this->users_count;
        return $count > 0 ? "{$this->name} ({$count})" : $this->name;
    }
}
