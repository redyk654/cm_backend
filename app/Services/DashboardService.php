<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\DashboardRepository;
use Illuminate\Support\Carbon;

/**
 * Compose la synthèse de la page d'accueil.
 */
class DashboardService
{
    public function __construct(
        private readonly DashboardRepository $repository,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function summary(): array
    {
        $now = Carbon::now();
        $certTotal = $this->repository->certificatesTotal();
        $certValid = $this->repository->certificatesValid();

        return [
            'patients_total' => $this->repository->patientsTotal(),
            'visits_this_month' => $this->repository->visitsBetween($now->copy()->startOfMonth(), $now->copy()->endOfMonth()),
            'visits_this_year' => $this->repository->visitsBetween($now->copy()->startOfYear(), $now->copy()->endOfYear()),
            'visits_last_month' => $this->repository->visitsBetween(
                $now->copy()->subMonthNoOverflow()->startOfMonth(),
                $now->copy()->subMonthNoOverflow()->endOfMonth(),
            ),
            'reports_pending' => $this->repository->reportsPending(),
            'certificates_expiring_30d' => $this->repository->certificatesExpiringWithin(30),
            'certificates_up_to_date_pct' => $certTotal > 0
                ? round($certValid / $certTotal * 100, 1)
                : null,
            'recent_visits' => $this->repository->recentVisits(5)->map(fn ($visit) => [
                'id' => $visit->id,
                'date_visite' => $visit->date_visite?->format('Y-m-d'),
                'statut' => $visit->statut,
                'statut_label' => $visit->statut_label,
                'patient' => $visit->patient ? [
                    'id' => $visit->patient->id,
                    'nom' => $visit->patient->nom,
                    'prenom' => $visit->patient->prenom,
                ] : null,
                'visit_type_label' => $visit->visitType?->label,
                'employeur' => $visit->employeur,
            ])->all(),
            'expiring_certificates' => $this->repository->expiringCertificates(60, 5)->map(fn ($cert) => [
                'id' => $cert->id,
                'visit_id' => $cert->visit_id,
                'reference' => $cert->reference,
                'patient_nom' => $cert->patient_nom,
                'patient_prenom' => $cert->patient_prenom,
                'date_expiration' => $cert->date_expiration?->format('Y-m-d'),
                'days_left' => $cert->date_expiration
                    ? (int) Carbon::now()->startOfDay()->diffInDays($cert->date_expiration, false)
                    : null,
            ])->all(),
        ];
    }
}
