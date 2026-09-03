<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\MedicalReport;

/**
 * Seul point d'accès Eloquent pour les rapports médicaux.
 */
class MedicalReportRepository
{
    private const EAGER = [
        'medecinSignataire:id,nom,prenom',
        'validatedBy:id,nom,prenom',
    ];

    public function findByVisit(string $visitId): ?MedicalReport
    {
        return MedicalReport::query()
            ->with(self::EAGER)
            ->where('visit_id', $visitId)
            ->first();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): MedicalReport
    {
        $report = new MedicalReport;
        // forceFill : visit_id / statut ne sont pas mass-assignables mais doivent
        // être positionnés à la création du brouillon.
        $report->forceFill($data)->save();

        return $report->refresh()->load(self::EAGER);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(MedicalReport $report, array $data): MedicalReport
    {
        $report->update($data);

        return $report->refresh()->load(self::EAGER);
    }

    /**
     * Rapport de la visite, créé vide (statut BROUILLON) s'il n'existe pas encore.
     */
    public function firstOrCreateForVisit(string $visitId): MedicalReport
    {
        $report = $this->findByVisit($visitId);

        if ($report !== null) {
            return $report;
        }

        return $this->create([
            'visit_id' => $visitId,
            'statut' => MedicalReport::STATUT_BROUILLON,
        ]);
    }
}
