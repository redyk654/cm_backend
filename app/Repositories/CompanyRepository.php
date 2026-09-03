<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Company;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Seul point d'accès Eloquent pour les entreprises.
 */
class CompanyRepository
{
    /** Colonnes autorisées au tri (garde-fou contre l'injection via sort_by). */
    private const SORTABLE = ['raison_sociale', 'created_at', 'is_active'];

    /**
     * Liste paginée et filtrée.
     *
     * Filtres reconnus : search, is_active, sort_by, sort_order, per_page.
     * On ne charge pas la collection des patients (trop lourd) : seul le
     * compteur `patients_count` est calculé.
     *
     * @param  array<string, mixed>  $filters
     */
    public function search(array $filters): LengthAwarePaginator
    {
        $query = Company::query()->withCount('patients');

        if (! empty($filters['search'])) {
            $query->search((string) $filters['search']);
        }

        if ($this->hasFilter($filters, 'is_active')) {
            $query->where('is_active', filter_var($filters['is_active'], FILTER_VALIDATE_BOOLEAN));
        }

        $sortBy = in_array($filters['sort_by'] ?? null, self::SORTABLE, true)
            ? $filters['sort_by']
            : 'raison_sociale';
        $sortOrder = ($filters['sort_order'] ?? 'asc') === 'desc' ? 'desc' : 'asc';

        $query->orderBy($sortBy, $sortOrder);

        return $query->paginate((int) ($filters['per_page'] ?? 15));
    }

    public function findOrFail(string $id): Company
    {
        return Company::query()->findOrFail($id);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Company
    {
        return Company::query()->create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Company $company, array $data): Company
    {
        $company->update($data);

        return $company->refresh();
    }

    public function delete(Company $company): bool
    {
        return (bool) $company->delete();
    }

    /**
     * Le filtre est-il réellement fourni (présent et non vide) ?
     *
     * @param  array<string, mixed>  $filters
     */
    private function hasFilter(array $filters, string $key): bool
    {
        return array_key_exists($key, $filters)
            && $filters[$key] !== null
            && $filters[$key] !== '';
    }
}
