<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * RBAC : vérifie que l'utilisateur authentifié possède la permission requise.
 *
 * Usage sur les routes :
 *   ->middleware('permission:patient.view')
 *   ->middleware('permission:patient.update|patient.create')  // OU logique
 */
class CheckPermission
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();
        $required = explode('|', $permission);

        if ($user === null || ! $user->hasAnyPermission($required)) {
            Log::warning('Accès refusé — permission manquante', [
                'user_id' => $user?->getKey(),
                'permission' => $permission,
                'route' => $request->path(),
            ]);

            return response()->json([
                'success' => false,
                'message' => "Vous n'avez pas les droits nécessaires pour effectuer cette action",
                'data' => null,
                'errors' => null,
            ], 403);
        }

        return $next($request);
    }
}
