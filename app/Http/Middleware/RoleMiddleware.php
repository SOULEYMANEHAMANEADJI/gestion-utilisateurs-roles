<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!Auth::check()) {
            return redirect('/login');
        }

        $user = Auth::user();
        
        // S'assurer que les rôles sont chargés
        if (!$user->relationLoaded('roles')) {
            $user->load('roles');
        }
        
        // Si aucun rôle spécifique n'est requis, laisser passer
        if (empty($roles)) {
            return $next($request);
        }

        // Super admin a accès à tout
        if ($user->hasRole('super_admin')) {
            return $next($request);
        }

        // Vérifier si l'utilisateur a un des rôles requis
        foreach ($roles as $role) {
            if ($user->hasRole($role)) {
                return $next($request);
            }
        }

        // Vérifier la hiérarchie des rôles - un rôle de niveau supérieur donne accès aux niveaux inférieurs
        $userMaxLevel = $user->roles->max('level') ?? 0;
        $requiredRoles = \App\Models\Role::whereIn('name', $roles)->get();
        
        foreach ($requiredRoles as $requiredRole) {
            if ($userMaxLevel >= $requiredRole->level) {
                return $next($request);
            }
        }

        abort(403, 'Accès non autorisé. Rôle requis : ' . implode(', ', $roles));
    }
}
