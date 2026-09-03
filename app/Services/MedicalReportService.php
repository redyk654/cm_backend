<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AuditLog;
use App\Models\MedicalReport;
use App\Models\User;
use App\Models\Visit;
use App\Repositories\MedicalReportRepository;
use App\Repositories\VisitRepository;
use Illuminate\Support\Facades\DB;

/**
 * Règles métier du rapport médical : brouillon progressif puis validation figée.
 *
 * Le cycle de vie du rapport pilote celui de la visite :
 * - première saisie  → la visite passe de BROUILLON à EN_COURS ;
 * - validation        → la visite passe à VALIDE.
 */
class MedicalReportService
{
    public function __construct(
        private readonly MedicalReportRepository $repository,
        private readonly VisitRepository $visitRepository,
        private readonly AuditLogger $auditLogger,
    ) {}

    public function getOrCreateForVisit(Visit $visit): MedicalReport
    {
        return $this->repository->firstOrCreateForVisit($visit->id);
    }

    /**
     * Enregistre le brouillon du rapport d'une visite.
     *
     * @param  array<string, mixed>  $data
     */
    public function saveDraft(Visit $visit, array $data): MedicalReport
    {
        $report = $this->repository->firstOrCreateForVisit($visit->id);

        if ($report->statut === MedicalReport::STATUT_VALIDE) {
            throw new \DomainException('Le rapport est validé et ne peut plus être modifié.');
        }

        // Le statut et la validation ne se pilotent jamais via le payload de saisie.
        unset($data['statut'], $data['validated_at'], $data['validated_by'], $data['visit_id']);

        $report = $this->repository->update($report, $data);

        // Effet de bord : la visite entre « en cours » dès la première saisie.
        if ($visit->statut === Visit::STATUT_BROUILLON) {
            $visit->update(['statut' => Visit::STATUT_EN_COURS]);
        }

        return $report;
    }

    /**
     * Valide (fige) le rapport médical d'une visite.
     */
    public function validate(Visit $visit, ?string $medecinSignataireId, User $validator): MedicalReport
    {
        $report = $this->repository->firstOrCreateForVisit($visit->id);

        if ($report->statut === MedicalReport::STATUT_VALIDE) {
            throw new \DomainException('Le rapport est déjà validé.');
        }

        if (trim((string) $report->conclusion) === '') {
            throw new \DomainException('La conclusion est obligatoire pour valider le rapport.');
        }

        $signataireId = $medecinSignataireId ?? $report->medecin_signataire_id ?? $validator->id;

        if ($signataireId === null) {
            throw new \DomainException('Le médecin signataire doit être identifié.');
        }

        DB::transaction(function () use ($report, $visit, $validator, $signataireId): void {
            $report->forceFill([
                'statut' => MedicalReport::STATUT_VALIDE,
                'validated_at' => now(),
                'validated_by' => $validator->id,
                'medecin_signataire_id' => $signataireId,
            ])->save();

            $visit->update(['statut' => Visit::STATUT_VALIDE]);
        });

        // TODO H5 : mécanisme de rectificatif après validation (non implémenté).

        $this->auditLogger->log(
            AuditLog::ACTION_VALIDATE,
            $report,
            null,
            null,
            'Validation du rapport médical',
            ['visit_id' => $visit->id],
        );

        return $this->repository->firstOrCreateForVisit($visit->id);
    }
}
