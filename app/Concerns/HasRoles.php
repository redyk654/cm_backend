<?php

declare(strict_types=1);

namespace App\Concerns;

use App\Models\Role;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Cache;

/**
 * Attribution de rôles et résolution des permissions pour un utilisateur.
 *
 * La liste des permissions résolues est mise en cache (tag `rbac:user:{id}`,
 * 30 min) et invalidée à chaque mutation de rôle.
 */
trait HasRoles
{
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_user', 'user_id', 'role_id')
            ->withTimestamps();
    }

    /**
     * Attribue un rôle (par instance ou par code).
     */
    public function assignRole(Role|string $role): void
    {
        $role = $this->resolveRole($role);

        if (! $this->roles()->whereKey($role->getKey())->exists()) {
            $this->roles()->attach($role->getKey());
            $this->forgetPermissionCache();
        }
    }

    /**
     * Retire un rôle (par instance ou par code).
     */
    public function removeRole(Role|string $role): void
    {
        $role = $this->resolveRole($role);

        $this->roles()->detach($role->getKey());
        $this->forgetPermissionCache();
    }

    /**
     * Possède l'un des rôles donnés (code ou liste de codes) ?
     */
    public function hasRole(string|array $names): bool
    {
        $names = (array) $names;

        return $this->roles()->whereIn('name', $names)->exists();
    }

    /**
     * Liste (mise en cache) des codes de permissions de l'utilisateur.
     *
     * @return array<int, string>
     */
    public function permissionNames(): array
    {
        return Cache::tags([$this->permissionCacheTag()])->remember(
            "rbac:user:{$this->getKey()}:permissions",
            now()->addMinutes(30),
            fn (): array => $this->roles()
                ->with('permissions:id,name')
                ->get()
                ->flatMap(fn (Role $role) => $role->permissions->pluck('name'))
                ->unique()
                ->values()
                ->all(),
        );
    }

    public function hasPermission(string $permission): bool
    {
        return in_array($permission, $this->permissionNames(), true);
    }

    /**
     * Possède au moins une des permissions données ?
     *
     * @param  array<int, string>  $permissions
     */
    public function hasAnyPermission(array $permissions): bool
    {
        return count(array_intersect($permissions, $this->permissionNames())) > 0;
    }

    public function forgetPermissionCache(): void
    {
        Cache::tags([$this->permissionCacheTag()])->flush();
    }

    private function permissionCacheTag(): string
    {
        return "rbac:user:{$this->getKey()}";
    }

    private function resolveRole(Role|string $role): Role
    {
        return $role instanceof Role
            ? $role
            : Role::query()->where('name', $role)->firstOrFail();
    }
}
