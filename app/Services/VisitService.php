<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Patient;
use App\Models\Visit;
use App\Repositories\PatientRepository;
use App\Repositories\VisitRepository;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Règles métier des visites : snapshot du contexte employeur/poste, historique.
 */
class VisitService
{
    public function __construct(
        private readonly VisitRepository $repository,
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
     * Crée une visite. `employeur` / `poste` sont figés depuis le dossier du
     * patient au moment de la création ; le statut initial est BROUILLON.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Visit
    {
        /** @var Patient $patient */
        $patient = $this->patientRepository->findOrFail((string) $data['patient_id']);
        $patient->loadMissing('company:id,raison_sociale');

        $data['employeur'] = $patient->company?->raison_sociale;
        $data['poste'] = $patient->poste;
        $data['statut'] = Visit::STATUT_BROUILLON;

        return $this->repository->create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Visit $visit, array $data): Visit
    {
        // Le patient d'une visite n'est pas modifiable ; le statut est piloté par
        // le cycle de vie du rapport, pas par une mise à jour directe.
        unset($data['patient_id'], $data['statut'], $data['employeur'], $data['poste']);

        return $this->repository->update($visit, $data);
    }

    public function delete(Visit $visit): void
    {
        if ($visit->statut === Visit::STATUT_VALIDE) {
            throw new \DomainException('Une visite validée ne peut pas être supprimée.');
        }

        $this->repository->delete($visit);
    }

    public function historyForPatient(string $patientId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->historyForPatient($patientId, $perPage);
    }

    public function previousPeriodicVisit(Patient $patient, ?string $before = null): ?Visit
    {
        return $this->repository->previousPeriodicVisit($patient, $before);
    }
}
