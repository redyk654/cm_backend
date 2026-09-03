<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\VisitType;
use App\Repositories\VisitTypeRepository;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Règles métier du référentiel des types de visite.
 *
 * CRUD simple : aucune contrainte de suppression tant qu'aucune table « visite »
 * ne référence ce référentiel.
 */
class VisitTypeService
{
    public function __construct(
        private readonly VisitTypeRepository $repository,
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     */
    public function list(array $filters): LengthAwarePaginator
    {
        return $this->repository->search($filters);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): VisitType
    {
        return $this->repository->create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(VisitType $visitType, array $data): VisitType
    {
        return $this->repository->update($visitType, $data);
    }

    public function delete(VisitType $visitType): void
    {
        // Suppression logique simple : aucune visite n'existe encore pour bloquer l'opération.
        $this->repository->delete($visitType);
    }
}
