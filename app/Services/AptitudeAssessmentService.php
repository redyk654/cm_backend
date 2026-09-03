<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AptitudeAssessment;
use App\Models\User;
use App\Models\Visit;
use App\Repositories\AptitudeAssessmentRepository;
use App\Repositories\AptitudeDecisionRepository;

/**
 * Règles métier de la décision d'aptitude : une décision par visite, avec
 * snapshot du référentiel `aptitude_decisions` au moment du choix.
 */
class AptitudeAssessmentService
{
    public function __construct(
        private readonly AptitudeAssessmentRepository $repository,
        private readonly AptitudeDecisionRepository $decisionRepository,
    ) {}

    public function getForVisit(Visit $visit): ?AptitudeAssessment
    {
        return $this->repository->findByVisit($visit->id);
    }

    /**
     * Crée ou remplace la décision d'aptitude d'une visite.
     *
     * @param  array<string, mixed>  $data
     */
    public function save(Visit $visit, array $data, User $user): AptitudeAssessment
    {
        $decision = $this->decisionRepository->findOrFail((string) $data['aptitude_decision_id']);

        $restriction = $data['restriction'] ?? null;

        if ($decision->requires_restriction && trim((string) $restriction) === '') {
            throw new \DomainException('La restriction est obligatoire pour cette décision.');
        }

        $assessment = $this->repository->firstOrNewForVisit($visit->id);

        $payload = [
            'visit_id' => $visit->id,
            'aptitude_decision_id' => $decision->id,
            // Snapshot du référentiel.
            'decision_code' => $decision->code,
            'decision_label' => $decision->label,
            'requires_restriction' => $decision->requires_restriction,
            'restriction' => $restriction,
            'recommandations' => $data['recommandations'] ?? null,
            'duree_validite_mois' => $data['duree_validite_mois'] ?? 12,
            'decided_by' => $user->id,
            'decided_at' => now(),
        ];

        return $assessment->exists
            ? $this->repository->update($assessment, $payload)
            : $this->repository->create($payload);
    }
}
