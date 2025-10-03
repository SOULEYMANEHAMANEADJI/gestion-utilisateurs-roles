<?php
/**
 * UserManagementException.php
 *
 * This file contains the UserManagementException class.
 */

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Class UserManagementException
 *
 * Exception class for user management errors.
 */
class UserManagementException extends Exception
{
    protected $errorType;
    protected $context;

    /**
     * UserManagementException constructor.
     *
     * @param string $message  The error message
     * @param string $errorType The error type
     * @param array  $context   The context of the error
     * @param int    $code      The error code
     * @param Exception|null $previous  The previous exception
     */
    public function __construct(string $message = "", string $errorType = 'general', array $context = [], int $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->errorType = $errorType;
        $this->context = $context;
    }

    /**
     * Get the error type
     */
    public function getErrorType(): string
    {
        return $this->errorType;
    }

    /**
     * Get the context
     */
    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * Render the exception into an HTTP response.
     */
    public function render(Request $request)
    {
        $message = $this->getMessage();
        $statusCode = $this->getStatusCode();

        if ($request->expectsJson()) {
            return response()->json([
                'error' => true,
                'type' => $this->errorType,
                'message' => $message,
                'context' => $this->context,
            ], $statusCode);
        }

        return response()->redirectBack()->with('error', $message)->withInput();
    }

    /**
     * Get appropriate HTTP status code based on error type
     */
    private function getStatusCode(): int
    {
        return match($this->errorType) {
            'permission_denied' => 403,
            'user_not_found' => 404,
            'validation_failed' => 422,
            'role_hierarchy_violation' => 403,
            'last_super_admin' => 409,
            'duplicate_email' => 409,
            default => 400
        };
    }

    /**
     * Static factory methods for common exceptions
     */
    public static function permissionDenied(string $action = '', array $context = []): self
    {
        return new self(
            "Vous n'avez pas les permissions pour effectuer cette action" . ($action ? " : {$action}" : "."),
            'permission_denied',
            $context
        );
    }

    public static function userNotFound(int $userId = null): self
    {
        return new self(
            "L'utilisateur demandé n'existe pas.",
            'user_not_found',
            ['user_id' => $userId]
        );
    }

    public static function lastSuperAdmin(): self
    {
        return new self(
            "Impossible de supprimer ou modifier le dernier Super Administrateur du système.",
            'last_super_admin'
        );
    }

    public static function roleHierarchyViolation(string $role = ''): self
    {
        return new self(
            "Vous ne pouvez pas assigner un rôle de niveau supérieur ou égal au vôtre" . ($role ? " : {$role}" : "."),
            'role_hierarchy_violation',
            ['attempted_role' => $role]
        );
    }

    public static function duplicateEmail(string $email): self
    {
        return new self(
            "Cette adresse email est déjà utilisée par un autre utilisateur.",
            'duplicate_email',
            ['email' => $email]
        );
    }
}
