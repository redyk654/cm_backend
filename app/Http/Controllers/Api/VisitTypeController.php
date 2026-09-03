<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\VisitType\StoreVisitTypeRequest;
use App\Http\Requests\VisitType\UpdateVisitTypeRequest;
use App\Http\Resources\VisitTypeResource;
use App\Models\VisitType;
use App\Services\VisitTypeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Référentiel des types de visite. Lecture : reference.view ; écriture : reference.manage.
 */
class VisitTypeController extends Controller
{
    public function __construct(
        private readonly VisitTypeService $service,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'is_active', 'sort_by', 'sort_order', 'per_page']);

        $visitTypes = $this->service->list($filters)
            ->through(fn (VisitType $visitType): VisitTypeResource => new VisitTypeResource($visitType));

        return $this->successResponse($visitTypes);
    }

    public function store(StoreVisitTypeRequest $request): JsonResponse
    {
        $visitType = $this->service->create($request->validated());

        return $this->createdResponse(new VisitTypeResource($visitType));
    }

    public function show(VisitType $visitType): JsonResponse
    {
        return $this->successResponse(new VisitTypeResource($visitType));
    }

    public function update(UpdateVisitTypeRequest $request, VisitType $visitType): JsonResponse
    {
        $updated = $this->service->update($visitType, $request->validated());

        return $this->successResponse(new VisitTypeResource($updated), 'Type de visite mis à jour');
    }

    public function destroy(VisitType $visitType): JsonResponse
    {
        $this->service->delete($visitType);

        return $this->noContentResponse();
    }
}
