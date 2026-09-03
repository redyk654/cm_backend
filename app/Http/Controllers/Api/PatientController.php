<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Patient\CheckDuplicatePatientRequest;
use App\Http\Requests\Patient\StorePatientRequest;
use App\Http\Requests\Patient\UpdatePatientRequest;
use App\Http\Resources\PatientResource;
use App\Models\Patient;
use App\Services\PatientService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Patients (salariés) suivis par le centre médical.
 */
class PatientController extends Controller
{
    public function __construct(
        private readonly PatientService $service,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'company_id', 'sort_by', 'sort_order', 'per_page']);

        $patients = $this->service->list($filters)
            ->through(fn (Patient $patient): PatientResource => new PatientResource($patient));

        return $this->successResponse($patients);
    }

    public function store(StorePatientRequest $request): JsonResponse
    {
        $patient = $this->service->create($request->validated());

        return $this->createdResponse(new PatientResource($patient->load('company')));
    }

    public function show(Patient $patient): JsonResponse
    {
        return $this->successResponse(new PatientResource($patient->load('company')));
    }

    public function update(UpdatePatientRequest $request, Patient $patient): JsonResponse
    {
        $updated = $this->service->update($patient, $request->validated());

        return $this->successResponse(new PatientResource($updated->load('company')), 'Patient mis à jour');
    }

    public function destroy(Patient $patient): JsonResponse
    {
        $this->service->delete($patient);

        return $this->noContentResponse();
    }

    /**
     * Détection préalable d'un doublon d'identité, avant saisie complète du dossier.
     */
    public function checkDuplicate(CheckDuplicatePatientRequest $request): JsonResponse
    {
        $duplicate = $this->service->checkDuplicate($request->validated());

        return $this->successResponse([
            'duplicate' => $duplicate !== null ? new PatientResource($duplicate->load('company')) : null,
        ]);
    }
}
