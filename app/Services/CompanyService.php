<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Company;
use App\Repositories\CompanyRepository;
use App\Repositories\PatientRepository;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Règles métier des entreprises.
 */
class CompanyService
{
    public function __construct(
        private readonly CompanyRepository $repository,
        private readonly PatientRepository $patientRepository,
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     */
    public function list(array $filters): LengthAwarePaginator
    {
        return $this->repository->search($filters);
    }

    /**
     * Salariés rattachés à une entreprise (liste paginée).
     *
     * @param  array<string, mixed>  $filters
     */
    public function listPatients(Company $company, array $filters): LengthAwarePaginator
    {
        return $this->patientRepository->search(['company_id' => $company->id] + $filters);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Company
    {
        return $this->repository->create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Company $company, array $data): Company
    {
        return $this->repository->update($company, $data);
    }

    /**
     * Suppression logique. Interdite si des salariés sont encore rattachés :
     * il faut d'abord les détacher ou les rattacher à une autre entreprise.
     */
    public function delete(Company $company): void
    {
        if ($company->patients()->exists()) {
            throw new \DomainException('Impossible de supprimer une entreprise avec des salariés rattachés.');
        }

        $this->repository->delete($company);
    }
}
