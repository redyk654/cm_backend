<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AuditLogResource;
use App\Models\AuditLog;
use App\Services\AuditLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Consultation du journal d'audit (§10 du PRD).
 */
class AuditLogController extends Controller
{
    public function __construct(
        private readonly AuditLogService $service,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only([
            'search', 'action', 'user_id', 'auditable_type', 'date_from', 'date_to', 'per_page',
        ]);

        $logs = $this->service->list($filters)
            ->through(fn (AuditLog $log): AuditLogResource => new AuditLogResource($log));

        return $this->successResponse($logs);
    }

    /**
     * Types d'actions présents dans le journal (alimente le filtre).
     */
    public function actions(): JsonResponse
    {
        return $this->successResponse($this->service->availableActions());
    }
}
