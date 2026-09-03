<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AptitudeAssessment\SaveAptitudeAssessmentRequest;
use App\Http\Resources\AptitudeAssessmentResource;
use App\Models\Visit;
use App\Services\AptitudeAssessmentService;
use Illuminate\Http\JsonResponse;

/**
 * Décision d'aptitude d'une visite.
 */
class AptitudeAssessmentController extends Controller
{
    public function __construct(
        private readonly AptitudeAssessmentService $service,
    ) {}

    public function show(Visit $visit): JsonResponse
    {
        $assessment = $this->service->getForVisit($visit);

        return $this->successResponse(
            $assessment !== null ? new AptitudeAssessmentResource($assessment) : null,
        );
    }

    public function update(SaveAptitudeAssessmentRequest $request, Visit $visit): JsonResponse
    {
        $assessment = $this->service->save($visit, $request->validated(), $request->user());

        return $this->successResponse(new AptitudeAssessmentResource($assessment), 'Décision enregistrée');
    }
}
