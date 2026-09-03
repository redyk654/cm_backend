<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Visit\StoreVisitRequest;
use App\Http\Requests\Visit\UpdateVisitRequest;
use App\Http\Resources\VisitResource;
use App\Models\Patient;
use App\Models\Visit;
use App\Services\VisitService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Visites médicales.
 */
class VisitController extends Controller
{
    public function __construct(
        private readonly VisitService $service,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only([
            'search', 'patient_id', 'visit_type_id', 'statut',
            'date_from', 'date_to', 'sort_by', 'sort_order', 'per_page',
        ]);

        $visits = $this->service->list($filters)
            ->through(fn (Visit $visit): VisitResource => new VisitResource($visit));

        return $this->successResponse($visits);
    }

    public function store(StoreVisitRequest $request): JsonResponse
    {
        $visit = $this->service->create($request->validated());

        return $this->createdResponse(
            new VisitResource($visit->load(['patient:id,nom,prenom,numero_dossier', 'visitType:id,code,label', 'medecin:id,nom,prenom'])),
        );
    }

    public function show(Visit $visit): JsonResponse
    {
        return $this->successResponse(
            new VisitResource($visit->load(['patient', 'visitType', 'medecin:id,nom,prenom'])),
        );
    }

    public function update(UpdateVisitRequest $request, Visit $visit): JsonResponse
    {
        $updated = $this->service->update($visit, $request->validated());

        return $this->successResponse(new VisitResource($updated), 'Visite mise à jour');
    }

    public function destroy(Visit $visit): JsonResponse
    {
        $this->service->delete($visit);

        return $this->noContentResponse();
    }

    /**
     * Historique des visites d'un patient (de la plus récente à la plus ancienne).
     */
    public function patientHistory(Request $request, Patient $patient): JsonResponse
    {
        $visits = $this->service
            ->historyForPatient($patient->id, (int) $request->input('per_page', 15))
            ->through(fn (Visit $visit): VisitResource => new VisitResource($visit));

        return $this->successResponse($visits);
    }

    /**
     * Dernière visite périodique validée du patient (suivi précédent).
     */
    public function previousPeriodic(Request $request, Patient $patient): JsonResponse
    {
        $visit = $this->service->previousPeriodicVisit($patient, $request->input('before'));

        return $this->successResponse([
            'visit' => $visit !== null ? new VisitResource($visit) : null,
        ]);
    }
}
