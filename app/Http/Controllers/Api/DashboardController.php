<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Illuminate\Http\JsonResponse;

/**
 * Synthèse de la page d'accueil.
 */
class DashboardController extends Controller
{
    public function __construct(
        private readonly DashboardService $service,
    ) {}

    public function summary(): JsonResponse
    {
        return $this->successResponse($this->service->summary());
    }
}
