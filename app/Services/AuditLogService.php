<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\AuditLogRepository;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Consultation du journal d'audit (lecture seule — l'écriture passe par AuditLogger).
 */
class AuditLogService
{
    public function __construct(
        private readonly AuditLogRepository $repository,
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     */
    public function list(array $filters): LengthAwarePaginator
    {
        return $this->repository->search($filters);
    }

    /**
     * @return array<int, string>
     */
    public function availableActions(): array
    {
        return $this->repository->distinctActions();
    }
}
