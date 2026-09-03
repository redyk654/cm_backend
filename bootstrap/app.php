<?php

use App\Http\Middleware\CheckPermission;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');
        $middleware->alias([
            'permission' => CheckPermission::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Détecte les requêtes attendant une réponse API JSON.
        $isApiRequest = fn ($request) => $request->expectsJson()
            || $request->is('api/*')
            || str_starts_with($request->path(), 'api/');

        // Non authentifié.
        $exceptions->renderable(function (AuthenticationException $e, $request) use ($isApiRequest) {
            if ($isApiRequest($request)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Non authentifié',
                    'data' => null,
                    'errors' => null,
                ], 401);
            }
        });

        // Ressource introuvable (findOrFail).
        $exceptions->renderable(function (ModelNotFoundException $e, $request) use ($isApiRequest) {
            if ($isApiRequest($request)) {
                $model = class_basename($e->getModel());

                return response()->json([
                    'success' => false,
                    'message' => "Ressource introuvable ({$model})",
                    'data' => null,
                    'errors' => null,
                ], 404);
            }
        });

        // Route inexistante.
        $exceptions->renderable(function (NotFoundHttpException $e, $request) use ($isApiRequest) {
            if ($isApiRequest($request)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Route introuvable',
                    'data' => null,
                    'errors' => null,
                ], 404);
            }
        });

        // Erreurs de validation.
        $exceptions->renderable(function (ValidationException $e, $request) use ($isApiRequest) {
            if ($isApiRequest($request)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur de validation',
                    'data' => null,
                    'errors' => $e->errors(),
                ], 422);
            }
        });

        // Erreurs de règle métier levées depuis les Services.
        $exceptions->renderable(function (DomainException $e, $request) use ($isApiRequest) {
            if ($isApiRequest($request)) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                    'data' => null,
                    'errors' => null,
                ], 422);
            }
        });

        // HttpException générique (403, 405, 500, etc.).
        $exceptions->renderable(function (HttpException $e, $request) use ($isApiRequest) {
            if ($isApiRequest($request)) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage() ?: 'Erreur serveur',
                    'data' => null,
                    'errors' => null,
                ], $e->getStatusCode());
            }
        });

        // Contraintes SQL : seul le doublon (1062) est renvoyé comme erreur utilisateur.
        $exceptions->renderable(function (QueryException $e, $request) use ($isApiRequest) {
            if (! $isApiRequest($request) || (string) $e->getCode() !== '23000') {
                return null;
            }

            $driverErrorCode = (int) ($e->errorInfo[1] ?? 0);

            if ($driverErrorCode === 1062) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur de validation',
                    'data' => null,
                    'errors' => [
                        'general' => ['Cette donnée existe déjà. Vérifiez les informations saisies.'],
                    ],
                ], 422);
            }

            return response()->json([
                'success' => false,
                'message' => 'Erreur interne du serveur',
                'data' => null,
                'errors' => null,
            ], 500);
        });

        // Filet de sécurité : toute autre exception en mode API (hors debug).
        $exceptions->renderable(function (Throwable $e, $request) use ($isApiRequest) {
            if ($isApiRequest($request) && ! app()->hasDebugModeEnabled()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur interne du serveur',
                    'data' => null,
                    'errors' => null,
                ], 500);
            }
        });
    })->create();
