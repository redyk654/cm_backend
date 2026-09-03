<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\AptitudeAssessment;

/**
 * Seul point d'accès Eloquent pour les décisions d'aptitude.
 */
class AptitudeAssessmentRepository
{
    private const EAGER = [
        'decidedBy:id,nom,prenom',
    ];

    public function findByVisit(string $visitId): ?AptitudeAssessment
    {
        return AptitudeAssessment::query()
            ->with(self::EAGER)
            ->where('visit_id', $visitId)
            ->first();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): AptitudeAssessment
    {
        $assessment = new AptitudeAssessment;
        // forceFill : visit_id et les colonnes de snapshot / décision ne sont pas
        // mass-assignables mais sont pilotées par le service.
        $assessment->forceFill($data)->save();

        return $assessment->refresh()->load(self::EAGER);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(AptitudeAssessment $assessment, array $data): AptitudeAssessment
    {
        $assessment->forceFill($data)->save();

        return $assessment->refresh()->load(self::EAGER);
    }

    /**
     * Décision de la visite, instance neuve (non persistée) si elle n'existe pas.
     */
    public function firstOrNewForVisit(string $visitId): AptitudeAssessment
    {
        return $this->findByVisit($visitId)
            ?? tap(new AptitudeAssessment, fn (AptitudeAssessment $a) => $a->visit_id = $visitId);
    }
}
