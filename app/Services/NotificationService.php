<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class NotificationService
{
    const SUCCESS = 'success';
    const ERROR = 'error';
    const WARNING = 'warning';
    const INFO = 'info';

    /**
     * Messages prédéfinis pour la cohérence
     */
    private static array $messages = [
        // Messages de succès
        'user.created' => 'Utilisateur créé avec succès.',
        'user.updated' => 'Utilisateur mis à jour avec succès.',
        'user.deleted' => 'Utilisateur supprimé avec succès.',
        'user.restored' => 'Utilisateur restauré avec succès.',
        'user.archived' => 'Utilisateur archivé avec succès.',
        'user.status_changed' => 'Statut de l\'utilisateur modifié avec succès.',
        'user.role_assigned' => 'Rôle assigné avec succès.',
        'user.bulk_action' => ':count utilisateurs traités avec succès.',
        'user.exported' => 'Export des utilisateurs terminé avec succès.',

        // Messages de rôles
        'role.created' => 'Rôle créé avec succès.',
        'role.updated' => 'Rôle mis à jour avec succès.',
        'role.deleted' => 'Rôle supprimé avec succès.',

        // Messages d'erreur
        'user.not_found' => 'Utilisateur introuvable.',
        'user.cannot_delete_self' => 'Vous ne pouvez pas supprimer votre propre compte.',
        'user.cannot_modify_superior' => 'Vous ne pouvez pas modifier un utilisateur de niveau supérieur.',
        'user.last_super_admin' => 'Impossible de supprimer le dernier Super Administrateur.',
        'user.email_already_exists' => 'Cette adresse email est déjà utilisée.',
        'user.invalid_avatar' => 'Le fichier avatar n\'est pas valide.',
        'user.upload_failed' => 'Échec du téléchargement du fichier.',

        // Messages d'avertissement
        'user.status_inactive' => 'Attention : cet utilisateur est inactif.',
        'user.no_roles' => 'Attention : cet utilisateur n\'a aucun rôle assigné.',
        'user.multiple_admin_roles' => 'Attention : cet utilisateur a plusieurs rôles administratifs.',

        // Messages d'information
        'user.login_required' => 'Veuillez vous connecter pour accéder à cette page.',
        'user.profile_incomplete' => 'Votre profil est incomplet. Veuillez le compléter.',
    ];

    /**
     * Ajouter une notification de succès
     */
    public static function success(string $key, array $params = []): void
    {
        self::flash(self::SUCCESS, $key, $params);
    }

    /**
     * Ajouter une notification d'erreur
     */
    public static function error(string $key, array $params = []): void
    {
        self::flash(self::ERROR, $key, $params);
        self::logError($key, $params);
    }

    /**
     * Ajouter une notification d'avertissement
     */
    public static function warning(string $key, array $params = []): void
    {
        self::flash(self::WARNING, $key, $params);
    }

    /**
     * Ajouter une notification d'information
     */
    public static function info(string $key, array $params = []): void
    {
        self::flash(self::INFO, $key, $params);
    }

    /**
     * Ajouter une notification personnalisée
     */
    public static function custom(string $type, string $message): void
    {
        Session::flash('notification', [
            'type' => $type,
            'message' => $message,
            'timestamp' => now()->toISOString()
        ]);
    }

    /**
     * Obtenir le message formaté
     */
    public static function getMessage(string $key, array $params = []): string
    {
        $message = self::$messages[$key] ?? $key;

        // Remplacer les paramètres dans le message
        foreach ($params as $param => $value) {
            $message = str_replace(":{$param}", $value, $message);
        }

        return $message;
    }

    /**
     * Ajouter une notification en session
     */
    private static function flash(string $type, string $key, array $params = []): void
    {
        $message = self::getMessage($key, $params);

        Session::flash('notification', [
            'type' => $type,
            'message' => $message,
            'key' => $key,
            'timestamp' => now()->toISOString()
        ]);
    }

    /**
     * Logger les erreurs importantes
     */
    private static function logError(string $key, array $params = []): void
    {
        if (in_array($key, [
            'user.last_super_admin',
            'user.cannot_modify_superior',
            'user.upload_failed'
        ])) {
            Log::warning("User Management Error: {$key}", [
                'params' => $params,
                'user_id' => auth()->id(),
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);
        }
    }

    /**
     * Obtenir toutes les notifications pour l'affichage
     */
    public static function getNotifications(): array
    {
        $notifications = [];

        // Récupérer la notification principale
        if (Session::has('notification')) {
            $notifications[] = Session::get('notification');
        }

        // Récupérer les notifications de succès/erreur Laravel classiques
        foreach ([self::SUCCESS, self::ERROR, self::WARNING, self::INFO] as $type) {
            if (Session::has($type)) {
                $notifications[] = [
                    'type' => $type,
                    'message' => Session::get($type),
                    'timestamp' => now()->toISOString()
                ];
            }
        }

        return $notifications;
    }

    /**
     * Notifications pour les réponses JSON
     */
    public static function jsonResponse(string $type, string $key, array $params = [], int $statusCode = 200): array
    {
        return [
            'success' => $type === self::SUCCESS,
            'notification' => [
                'type' => $type,
                'message' => self::getMessage($key, $params),
                'key' => $key,
                'timestamp' => now()->toISOString()
            ],
            'status_code' => $statusCode
        ];
    }

    /**
     * Valider les actions en lot et retourner les notifications appropriées
     */
    public static function validateBulkAction(string $action, array $userIds): array
    {
        $results = [
            'success' => [],
            'errors' => [],
            'warnings' => []
        ];

        foreach ($userIds as $userId) {
            // Logique de validation selon l'action
            switch ($action) {
                case 'delete':
                    if (auth()->id() == $userId) {
                        $results['errors'][] = "Vous ne pouvez pas supprimer votre propre compte.";
                    } else {
                        $results['success'][] = $userId;
                    }
                    break;

                case 'activate':
                case 'deactivate':
                case 'archive':
                    $results['success'][] = $userId;
                    break;

                default:
                    $results['errors'][] = "Action non reconnue : {$action}";
            }
        }

        return $results;
    }
}
