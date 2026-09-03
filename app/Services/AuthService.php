<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
    ) {}

    /**
     * Authentifie un utilisateur et émet un token Bearer.
     *
     * @param  array{email: string, password: string}  $credentials
     * @return array{user: User, token: string}
     *
     * @throws ValidationException identifiants invalides
     * @throws \DomainException compte désactivé
     */
    public function login(array $credentials, string $deviceName): array
    {
        /** @var User|null $user */
        $user = User::query()->where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            $this->auditLogger->logAuthEvent(
                AuditLog::ACTION_LOGIN_FAILED,
                $user,
                success: false,
                failureReason: 'Identifiants invalides',
            );

            throw ValidationException::withMessages([
                'email' => ['Identifiants invalides.'],
            ]);
        }

        if (! $user->is_active) {
            $this->auditLogger->logAuthEvent(
                AuditLog::ACTION_LOGIN_FAILED,
                $user,
                success: false,
                failureReason: 'Compte désactivé',
            );

            throw new \DomainException('Votre compte est désactivé. Contactez un administrateur.');
        }

        $token = $user->createToken($deviceName)->plainTextToken;

        $this->auditLogger->logAuthEvent(AuditLog::ACTION_LOGIN, $user);

        return ['user' => $user, 'token' => $token];
    }

    /**
     * Révoque le token utilisé pour la requête courante.
     */
    public function logout(User $user): void
    {
        $token = $user->currentAccessToken();

        if ($token !== null) {
            $token->delete();
        }

        $this->auditLogger->logAuthEvent(AuditLog::ACTION_LOGOUT, $user);
    }
}
