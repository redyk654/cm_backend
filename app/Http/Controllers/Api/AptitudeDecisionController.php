<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AptitudeDecision\StoreAptitudeDecisionRequest;
use App\Http\Requests\AptitudeDecision\UpdateAptitudeDecisionRequest;
use App\Http\Resources\AptitudeDecisionResource;
use App\Models\AptitudeDecision;
use App\Services\AptitudeDecisionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Référentiel des décisions d'aptitude. Lecture : reference.view ; écriture : reference.manage.
 */
class AptitudeDecisionController extends Controller
{
    public function __construct(
        private readonly AptitudeDecisionService $service,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'is_active', 'sort_by', 'sort_order', 'per_page']);

        $decisions = $this->service->list($filters)
            ->through(fn (AptitudeDecision $decision): AptitudeDecisionResource => new AptitudeDecisionResource($decision));

        return $this->successResponse($decisions);
    }

    public function store(StoreAptitudeDecisionRequest $request): JsonResponse
    {
        $decision = $this->service->create($request->validated());

        return $this->createdResponse(new AptitudeDecisionResource($decision));
    }

    public function show(AptitudeDecision $aptitudeDecision): JsonResponse
    {
        return $this->successResponse(new AptitudeDecisionResource($aptitudeDecision));
    }

    public function update(UpdateAptitudeDecisionRequest $request, AptitudeDecision $aptitudeDecision): JsonResponse
    {
        $updated = $this->service->update($aptitudeDecision, $request->validated());

        return $this->successResponse(new AptitudeDecisionResource($updated), 'Décision d\'aptitude mise à jour');
    }

    public function destroy(AptitudeDecision $aptitudeDecision): JsonResponse
    {
        $this->service->delete($aptitudeDecision);

        return $this->noContentResponse();
    }
}
