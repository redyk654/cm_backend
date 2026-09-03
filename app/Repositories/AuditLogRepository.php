<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\AuditLog;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Seul point d'accès Eloquent pour le journal d'audit (lecture seule).
 */
class AuditLogRepository
{
    /**
     * Liste paginée et filtrée, de la plus récente à la plus ancienne.
     *
     * Filtres : search, action, user_id, auditable_type, date_from, date_to, per_page.
     *
     * @param  array<string, mixed>  $filters
     */
    public function search(array $filters): LengthAwarePaginator
    {
        $query = AuditLog::query()->with('user:id,nom,prenom');

        if (! empty($filters['search'])) {
            $query->search((string) $filters['search']);
        }
        if (! empty($filters['action'])) {
            $query->where('action', $filters['action']);
        }
        if (! empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }
        if (! empty($filters['auditable_type'])) {
            $query->where('auditable_type', 'like', '%'.$filters['auditable_type']);
        }
        if (! empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }
        if (! empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        return $query->orderByDesc('created_at')->paginate((int) ($filters['per_page'] ?? 25));
    }

    /**
     * Valeurs distinctes de `action` présentes dans le journal (pour le filtre).
     *
     * @return array<int, string>
     */
    public function distinctActions(): array
    {
        return AuditLog::query()->distinct()->orderBy('action')->pluck('action')->all();
    }
}
