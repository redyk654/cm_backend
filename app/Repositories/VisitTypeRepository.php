<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\VisitType;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Seul point d'accès Eloquent pour le référentiel des types de visite.
 */
class VisitTypeRepository
{
    /** Colonnes autorisées au tri (garde-fou contre l'injection via sort_by). */
    private const SORTABLE = ['sort_order', 'label', 'code', 'created_at', 'is_active'];

    /**
     * Liste paginée et filtrée.
     *
     * Filtres reconnus : search, is_active, sort_by, sort_order, per_page.
     *
     * @param  array<string, mixed>  $filters
     */
    public function search(array $filters): LengthAwarePaginator
    {
        $query = VisitType::query();

        if (! empty($filters['search'])) {
            $term = (string) $filters['search'];
            $query->where(function ($q) use ($term): void {
                $q->where('code', 'like', "%{$term}%")
                    ->orWhere('label', 'like', "%{$term}%");
            });
        }

        if ($this->hasFilter($filters, 'is_active')) {
            $query->where('is_active', filter_var($filters['is_active'], FILTER_VALIDATE_BOOLEAN));
        }

        $sortBy = in_array($filters['sort_by'] ?? null, self::SORTABLE, true)
            ? $filters['sort_by']
            : 'sort_order';
        $sortOrder = ($filters['sort_order'] ?? 'asc') === 'desc' ? 'desc' : 'asc';

        $query->orderBy($sortBy, $sortOrder)->orderBy('label');

        return $query->paginate((int) ($filters['per_page'] ?? 15));
    }

    public function findOrFail(string $id): VisitType
    {
        return VisitType::query()->findOrFail($id);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): VisitType
    {
        return VisitType::query()->create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(VisitType $visitType, array $data): VisitType
    {
        $visitType->update($data);

        return $visitType->refresh();
    }

    public function delete(VisitType $visitType): bool
    {
        return (bool) $visitType->delete();
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
