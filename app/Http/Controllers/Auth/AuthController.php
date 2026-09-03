<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuthService $authService,
    ) {}

    /**
     * Authentification : renvoie l'utilisateur et un token Bearer.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->login(
            $request->only('email', 'password'),
            $request->deviceName(),
        );

        return $this->successResponse([
            'user' => new UserResource($result['user']->load('roles')),
            'token' => $result['token'],
        ], 'Connexion réussie');
    }

    /**
     * Déconnexion : révoque le token courant.
     */
    public function logout(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $this->authService->logout($user);

        return $this->successResponse(null, 'Déconnexion réussie');
    }

    /**
     * Utilisateur authentifié.
     */
    public function me(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        return $this->successResponse(new UserResource($user->load('roles')));
    }
}
