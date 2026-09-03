<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AptitudeDecision;
use App\Repositories\AptitudeDecisionRepository;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Règles métier du référentiel des décisions d'aptitude.
 *
 * CRUD simple : aucune contrainte de suppression tant qu'aucune table métier
 * ne référence ce référentiel.
 */
class AptitudeDecisionService
{
    public function __construct(
        private readonly AptitudeDecisionRepository $repository,
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
    public function create(array $data): AptitudeDecision
    {
        return $this->repository->create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(AptitudeDecision $aptitudeDecision, array $data): AptitudeDecision
    {
        return $this->repository->update($aptitudeDecision, $data);
    }

    public function delete(AptitudeDecision $aptitudeDecision): void
    {
        $this->repository->delete($aptitudeDecision);
    }
}
