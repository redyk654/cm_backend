<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\VisitType;
use Illuminate\Database\Seeder;

/**
 * Types de visite standard du centre médical (§ cahier des charges Module 1).
 * Idempotent : réexécutable sans créer de doublon.
 */
class VisitTypeSeeder extends Seeder
{
    /**
     * @var list<array{code: string, label: string, sort_order: int}>
     */
    private array $types = [
        ['code' => 'EMBAUCHE', 'label' => "Visite d'embauche", 'sort_order' => 10],
        ['code' => 'PERIODIQUE', 'label' => 'Visite périodique', 'sort_order' => 20],
        ['code' => 'REPRISE', 'label' => 'Visite de reprise', 'sort_order' => 30],
        ['code' => 'MALADIE_PRO', 'label' => 'Visite pour maladie professionnelle', 'sort_order' => 40],
        ['code' => 'ACCIDENT_TRAVAIL', 'label' => 'Accident de travail', 'sort_order' => 50],
        ['code' => 'DEMANDE_EMPLOYEUR', 'label' => "Visite à la demande de l'employeur", 'sort_order' => 60],
        ['code' => 'DEMANDE_MEDECIN', 'label' => 'Visite à la demande du médecin du travail', 'sort_order' => 70],
    ];

    public function run(): void
    {
        foreach ($this->types as $type) {
            VisitType::updateOrCreate(
                ['code' => $type['code']],
                [
                    'label' => $type['label'],
                    'description' => null,
                    'is_active' => true,
                    'sort_order' => $type['sort_order'],
                ],
            );
        }
    }
}
