<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Patient;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Seul point d'accès Eloquent pour les patients.
 */
class PatientRepository
{
    /** Colonnes autorisées au tri (garde-fou contre l'injection via sort_by). */
    private const SORTABLE = ['nom', 'prenom', 'numero_dossier', 'created_at'];

    /**
     * Liste paginée et filtrée.
     *
     * Filtres reconnus : search, company_id, sort_by, sort_order, per_page.
     *
     * @param  array<string, mixed>  $filters
     */
    public function search(array $filters): LengthAwarePaginator
    {
        $query = Patient::query()->with('company:id,raison_sociale');

        if (! empty($filters['search'])) {
            $query->search((string) $filters['search']);
        }

        if (! empty($filters['company_id'])) {
            $query->where('company_id', $filters['company_id']);
        }

        $sortBy = in_array($filters['sort_by'] ?? null, self::SORTABLE, true)
            ? $filters['sort_by']
            : 'nom';
        $sortOrder = ($filters['sort_order'] ?? 'asc') === 'desc' ? 'desc' : 'asc';

        $query->orderBy($sortBy, $sortOrder);

        return $query->paginate((int) ($filters['per_page'] ?? 15));
    }

    public function findOrFail(string $id): Patient
    {
        return Patient::query()->findOrFail($id);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Patient
    {
        return Patient::query()->create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Patient $patient, array $data): Patient
    {
        $patient->update($data);

        return $patient->refresh();
    }

    public function delete(Patient $patient): bool
    {
        return (bool) $patient->delete();
    }

    /**
     * Nombre de dossiers patients ouverts sur une année civile.
     *
     * Inclut les patients supprimés logiquement : leur `numero_dossier` reste
     * réservé (contrainte d'unicité), la numérotation ne doit donc pas le réattribuer.
     */
    public function countCreatedInYear(int $year): int
    {
        return Patient::withTrashed()
            ->whereYear('created_at', $year)
            ->count();
    }

    /**
     * Recherche un patient déjà enregistré avec la même identité :
     * même nom + même prénom (insensibles à la casse) et, si fournie,
     * même date de naissance.
     */
    public function findProbableDuplicate(string $nom, string $prenom, ?string $dateNaissance): ?Patient
    {
        $query = Patient::query()
            ->whereRaw('LOWER(nom) = ?', [mb_strtolower($nom)])
            ->whereRaw('LOWER(prenom) = ?', [mb_strtolower($prenom)]);

        if ($dateNaissance !== null && $dateNaissance !== '') {
            $query->whereDate('date_naissance', $dateNaissance);
        }

        return $query->first();
    }
}
