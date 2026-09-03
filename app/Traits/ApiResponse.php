<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Standardise les réponses API JSON sous la forme :
 * { success, message, data, errors, [meta] }
 */
trait ApiResponse
{
    /**
     * Réponse de succès. Si $data est un paginateur, ajoute un bloc meta.
     */
    protected function successResponse(
        mixed $data,
        string $message = 'Opération réussie',
        int $code = 200
    ): JsonResponse {
        $response = [
            'success' => true,
            'message' => $message,
            'data' => $data,
            'errors' => null,
        ];

        if ($data instanceof LengthAwarePaginator) {
            $response['meta'] = [
                'current_page' => $data->currentPage(),
                'last_page' => $data->lastPage(),
                'per_page' => $data->perPage(),
                'total' => $data->total(),
                'from' => $data->firstItem(),
                'to' => $data->lastItem(),
            ];

            $response['data'] = $data->items();
        }

        return response()->json($response, $code);
    }

    /**
     * Réponse d'erreur générique.
     */
    protected function errorResponse(
        string $message,
        mixed $errors = null,
        int $code = 400
    ): JsonResponse {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data' => null,
            'errors' => $errors,
        ], $code);
    }

    /**
     * Réponse 404 Not Found.
     */
    protected function notFoundResponse(string $message = 'Ressource introuvable'): JsonResponse
    {
        return $this->errorResponse($message, null, 404);
    }

    /**
     * Réponse 403 Forbidden.
     */
    protected function forbiddenResponse(string $message = 'Action non autorisée'): JsonResponse
    {
        return $this->errorResponse($message, null, 403);
    }

    /**
     * Réponse 401 Unauthorized.
     */
    protected function unauthorizedResponse(string $message = 'Non authentifié'): JsonResponse
    {
        return $this->errorResponse($message, null, 401);
    }

    /**
     * Réponse 422 Unprocessable Entity (erreurs de validation).
     */
    protected function validationErrorResponse(mixed $errors, string $message = 'Erreur de validation'): JsonResponse
    {
        return $this->errorResponse($message, $errors, 422);
    }

    /**
     * Réponse 201 Created.
     */
    protected function createdResponse(mixed $data, string $message = 'Ressource créée avec succès'): JsonResponse
    {
        return $this->successResponse($data, $message, 201);
    }

    /**
     * Réponse 204 No Content.
     */
    protected function noContentResponse(): JsonResponse
    {
        return response()->json(null, 204);
    }
}
