<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Certificate;
use App\Models\MedicalReport;
use App\Models\Patient;
use App\Models\Visit;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Agrégats de la page d'accueil (lecture seule).
 */
class DashboardRepository
{
    public function patientsTotal(): int
    {
        return Patient::query()->count();
    }

    public function visitsBetween(Carbon $from, Carbon $to): int
    {
        return Visit::query()->whereBetween('date_visite', [$from->toDateString(), $to->toDateString()])->count();
    }

    /**
     * Rapports en attente : visites « en cours » dont le rapport n'est pas validé.
     */
    public function reportsPending(): int
    {
        return MedicalReport::query()
            ->where('statut', MedicalReport::STATUT_BROUILLON)
            ->whereHas('visit', fn ($q) => $q->whereIn('statut', [Visit::STATUT_EN_COURS, Visit::STATUT_BROUILLON]))
            ->count();
    }

    public function certificatesExpiringWithin(int $days): int
    {
        return Certificate::query()
            ->whereNotNull('date_expiration')
            ->whereBetween('date_expiration', [now()->toDateString(), now()->addDays($days)->toDateString()])
            ->count();
    }

    public function certificatesTotal(): int
    {
        return Certificate::query()->count();
    }

    public function certificatesValid(): int
    {
        return Certificate::query()
            ->whereNotNull('date_expiration')
            ->whereDate('date_expiration', '>=', now()->toDateString())
            ->count();
    }

    /**
     * @return Collection<int, Visit>
     */
    public function recentVisits(int $limit = 5)
    {
        return Visit::query()
            ->with(['patient:id,nom,prenom,numero_dossier', 'visitType:id,code,label'])
            ->orderByDesc('date_visite')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * @return Collection<int, Certificate>
     */
    public function expiringCertificates(int $days = 60, int $limit = 5)
    {
        return Certificate::query()
            ->whereNotNull('date_expiration')
            ->whereBetween('date_expiration', [now()->toDateString(), now()->addDays($days)->toDateString()])
            ->orderBy('date_expiration')
            ->limit($limit)
            ->get();
    }
}
