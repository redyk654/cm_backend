<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\MedicalReport\SaveMedicalReportRequest;
use App\Http\Requests\MedicalReport\ValidateMedicalReportRequest;
use App\Http\Resources\MedicalReportResource;
use App\Models\Visit;
use App\Services\MedicalReportService;
use Illuminate\Http\JsonResponse;

/**
 * Rapport médical d'une visite (§6.4 du PRD).
 */
class MedicalReportController extends Controller
{
    public function __construct(
        private readonly MedicalReportService $service,
    ) {}

    public function show(Visit $visit): JsonResponse
    {
        $report = $this->service->getOrCreateForVisit($visit)
            ->load(['medecinSignataire:id,nom,prenom', 'validatedBy:id,nom,prenom']);

        return $this->successResponse(new MedicalReportResource($report));
    }

    public function update(SaveMedicalReportRequest $request, Visit $visit): JsonResponse
    {
        $report = $this->service->saveDraft($visit, $request->validated());

        return $this->successResponse(new MedicalReportResource($report), 'Rapport enregistré');
    }

    public function validateReport(ValidateMedicalReportRequest $request, Visit $visit): JsonResponse
    {
        $report = $this->service->validate(
            $visit,
            $request->input('medecin_signataire_id'),
            $request->user(),
        );

        return $this->successResponse(new MedicalReportResource($report), 'Rapport validé');
    }
}
