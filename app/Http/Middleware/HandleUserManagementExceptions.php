<?php

namespace App\Http\Middleware;

use App\Exceptions\UserManagementException;
use App\Services\NotificationService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class HandleUserManagementExceptions
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            return $next($request);
        } catch (UserManagementException $e) {
            // Logger l'exception avec le contexte
            Log::warning('User Management Exception', [
                'type' => $e->getErrorType(),
                'message' => $e->getMessage(),
                'context' => $e->getContext(),
                'user_id' => auth()->id(),
                'ip' => $request->ip(),
                'url' => $request->fullUrl(),
                'user_agent' => $request->userAgent()
            ]);

            // Réponse JSON pour les requêtes AJAX
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => $e->getMessage(),
                    'type' => $e->getErrorType(),
                    'context' => $e->getContext()
                ], $this->getStatusCode($e->getErrorType()));
            }

            // Redirection avec notification pour les requêtes web
            return redirect()->back()
                ->with('error', $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Obtenir le code de statut HTTP approprié
     */
    private function getStatusCode(string $errorType): int
    {
        return match($errorType) {
            'permission_denied', 'role_hierarchy_violation' => 403,
            'user_not_found', 'role_not_found' => 404,
            'validation_failed' => 422,
            'last_super_admin', 'duplicate_email', 'role_in_use' => 409,
            default => 400
        };
    }
}
