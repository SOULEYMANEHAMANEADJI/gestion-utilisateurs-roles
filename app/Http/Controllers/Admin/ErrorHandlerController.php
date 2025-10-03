<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ErrorHandlerController extends Controller
{
    /**
     * Gestion centralisée des erreurs pour l'administration
     */
    public static function handleError(\Exception $e, string $context = 'admin', array $additionalData = [])
    {
        // Log de l'erreur
        Log::error("Erreur dans {$context}", [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
            'additional_data' => $additionalData,
            'user_id' => auth()->id(),
            'url' => request()->fullUrl(),
            'method' => request()->method(),
        ]);

        // Retourner une réponse appropriée selon le contexte
        if (request()->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue. Veuillez réessayer.',
                'error_code' => $e->getCode(),
                'debug' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }

        return back()->with('error', 'Une erreur est survenue. Veuillez réessayer.');
    }

    /**
     * Validation des permissions avant action
     */
    public static function checkPermission(string $permission, $user = null)
    {
        $user = $user ?? auth()->user();
        
        if (!$user) {
            abort(401, 'Non authentifié');
        }

        if (!$user->hasPermission($permission)) {
            abort(403, "Permission requise : {$permission}");
        }

        return true;
    }

    /**
     * Validation des données d'entrée
     */
    public static function validateInput(array $data, array $rules, array $messages = [])
    {
        $validator = validator($data, $rules, $messages);
        
        if ($validator->fails()) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Données invalides',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            return back()->withErrors($validator)->withInput();
        }

        return null;
    }

    /**
     * Réponse de succès standardisée
     */
    public static function successResponse(string $message, $data = null, int $statusCode = 200)
    {
        $response = [
            'success' => true,
            'message' => $message
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Réponse d'erreur standardisée
     */
    public static function errorResponse(string $message, int $statusCode = 400, $errors = null)
    {
        $response = [
            'success' => false,
            'message' => $message
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $statusCode);
    }
}
