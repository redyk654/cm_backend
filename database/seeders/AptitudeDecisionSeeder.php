<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\AptitudeDecision;
use Illuminate\Database\Seeder;

/**
 * Décisions d'aptitude standard prononçables à l'issue d'une visite.
 * Idempotent : réexécutable sans créer de doublon.
 */
class AptitudeDecisionSeeder extends Seeder
{
    /**
     * @var list<array{code: string, label: string, requires_restriction: bool, sort_order: int}>
     */
    private array $decisions = [
        ['code' => 'APTE', 'label' => 'Apte', 'requires_restriction' => false, 'sort_order' => 10],
        ['code' => 'APTE_TEMPORAIRE', 'label' => 'Apte temporaire', 'requires_restriction' => false, 'sort_order' => 20],
        ['code' => 'INAPTITUDE_TEMPORAIRE', 'label' => 'Inaptitude temporaire', 'requires_restriction' => false, 'sort_order' => 30],
        ['code' => 'INAPTITUDE_DEFINITIVE', 'label' => 'Inaptitude définitive', 'requires_restriction' => false, 'sort_order' => 40],
        ['code' => 'APTE_AVEC_RESTRICTION', 'label' => 'Apte avec restriction', 'requires_restriction' => true, 'sort_order' => 50],
    ];

    public function run(): void
    {
        foreach ($this->decisions as $decision) {
            AptitudeDecision::updateOrCreate(
                ['code' => $decision['code']],
                [
                    'label' => $decision['label'],
                    'requires_restriction' => $decision['requires_restriction'],
                    'description' => null,
                    'is_active' => true,
                    'sort_order' => $decision['sort_order'],
                ],
            );
        }
    }
}
