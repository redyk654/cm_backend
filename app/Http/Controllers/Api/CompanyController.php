<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Company\StoreCompanyRequest;
use App\Http\Requests\Company\UpdateCompanyRequest;
use App\Http\Resources\CompanyResource;
use App\Http\Resources\PatientResource;
use App\Models\Company;
use App\Models\Patient;
use App\Services\CompanyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Entreprises clientes et liste de leurs salariés.
 */
class CompanyController extends Controller
{
    public function __construct(
        private readonly CompanyService $service,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'is_active', 'sort_by', 'sort_order', 'per_page']);

        $companies = $this->service->list($filters)
            ->through(fn (Company $company): CompanyResource => new CompanyResource($company));

        return $this->successResponse($companies);
    }

    public function store(StoreCompanyRequest $request): JsonResponse
    {
        $company = $this->service->create($request->validated());

        return $this->createdResponse(new CompanyResource($company));
    }

    public function show(Company $company): JsonResponse
    {
        return $this->successResponse(new CompanyResource($company->loadCount('patients')));
    }

    public function update(UpdateCompanyRequest $request, Company $company): JsonResponse
    {
        $updated = $this->service->update($company, $request->validated());

        return $this->successResponse(new CompanyResource($updated), 'Entreprise mise à jour');
    }

    public function destroy(Company $company): JsonResponse
    {
        $this->service->delete($company);

        return $this->noContentResponse();
    }

    /**
     * Liste paginée des salariés rattachés à l'entreprise.
     */
    public function patients(Request $request, Company $company): JsonResponse
    {
        $filters = $request->only(['search', 'sort_by', 'sort_order', 'per_page']);

        $patients = $this->service->listPatients($company, $filters)
            ->through(fn (Patient $patient): PatientResource => new PatientResource($patient));

        return $this->successResponse($patients);
    }
}
