<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Patient;
use App\Models\Visit;
use App\Models\VisitType;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Seul point d'accès Eloquent pour les visites.
 */
class VisitRepository
{
    /** Colonnes autorisées au tri. */
    private const SORTABLE = ['date_visite', 'created_at', 'statut'];

    private const EAGER = [
        'patient:id,nom,prenom,numero_dossier',
        'visitType:id,code,label',
        'medecin:id,nom,prenom',
    ];

    /**
     * Liste paginée et filtrée.
     *
     * Filtres : search, patient_id, visit_type_id, statut, date_from, date_to,
     * sort_by, sort_order, per_page.
     *
     * @param  array<string, mixed>  $filters
     */
    public function search(array $filters): LengthAwarePaginator
    {
        $query = Visit::query()->with(self::EAGER);

        if (! empty($filters['search'])) {
            $query->search((string) $filters['search']);
        }
        if (! empty($filters['patient_id'])) {
            $query->where('patient_id', $filters['patient_id']);
        }
        if (! empty($filters['visit_type_id'])) {
            $query->where('visit_type_id', $filters['visit_type_id']);
        }
        if (! empty($filters['statut'])) {
            $query->where('statut', $filters['statut']);
        }
        if (! empty($filters['date_from'])) {
            $query->whereDate('date_visite', '>=', $filters['date_from']);
        }
        if (! empty($filters['date_to'])) {
            $query->whereDate('date_visite', '<=', $filters['date_to']);
        }

        $sortBy = in_array($filters['sort_by'] ?? null, self::SORTABLE, true)
            ? $filters['sort_by']
            : 'date_visite';
        $sortOrder = ($filters['sort_order'] ?? 'desc') === 'asc' ? 'asc' : 'desc';
        $query->orderBy($sortBy, $sortOrder)->orderBy('created_at', 'desc');

        return $query->paginate((int) ($filters['per_page'] ?? 15));
    }

    public function findOrFail(string $id): Visit
    {
        return Visit::query()->with(self::EAGER)->findOrFail($id);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Visit
    {
        return Visit::query()->create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Visit $visit, array $data): Visit
    {
        $visit->update($data);

        return $visit->refresh()->load(self::EAGER);
    }

    public function delete(Visit $visit): bool
    {
        return (bool) $visit->delete();
    }

    /**
     * Historique complet d'un patient, de la plus récente à la plus ancienne.
     */
    public function historyForPatient(string $patientId, int $perPage = 15): LengthAwarePaginator
    {
        return Visit::query()
            ->with(self::EAGER)
            ->where('patient_id', $patientId)
            ->orderBy('date_visite', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Dernière visite périodique VALIDÉE d'un patient (avant une date éventuelle).
     * Sert à retrouver le suivi précédent lors d'une nouvelle visite périodique.
     */
    public function previousPeriodicVisit(Patient $patient, ?string $before = null): ?Visit
    {
        return Visit::query()
            ->with(self::EAGER)
            ->where('patient_id', $patient->id)
            ->where('statut', Visit::STATUT_VALIDE)
            ->whereHas('visitType', fn ($q) => $q->where('code', 'PERIODIQUE'))
            ->when($before !== null, fn ($q) => $q->whereDate('date_visite', '<', $before))
            ->orderBy('date_visite', 'desc')
            ->first();
    }

    /**
     * @return array<int, string> codes de statut connus
     */
    public function statuts(): array
    {
        return Visit::STATUTS;
    }

    public function existsForType(VisitType $visitType): bool
    {
        return Visit::query()->where('visit_type_id', $visitType->id)->exists();
    }
}
