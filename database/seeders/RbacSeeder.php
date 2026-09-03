<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RbacSeeder extends Seeder
{
    /**
     * Permissions du périmètre Module 1 (code => libellé).
     *
     * @var array<string, string>
     */
    private array $permissions = [
        'patient.view' => 'Consulter les patients',
        'patient.create' => 'Créer un patient',
        'patient.update' => 'Modifier un patient',
        'patient.delete' => 'Supprimer un patient',

        'company.view' => 'Consulter les entreprises',
        'company.create' => 'Créer une entreprise',
        'company.update' => 'Modifier une entreprise',
        'company.delete' => 'Supprimer une entreprise',

        'reference.view' => 'Consulter les référentiels',
        'reference.manage' => 'Gérer les référentiels',

        'visit.view' => 'Consulter les visites',
        'visit.create' => 'Créer une visite',
        'visit.update' => 'Modifier une visite',
        'visit.delete' => 'Supprimer une visite',

        'medical_report.view' => 'Consulter les rapports médicaux',
        'medical_report.create' => 'Créer un rapport médical',
        'medical_report.update' => 'Modifier un rapport médical',
        'medical_report.validate' => 'Valider un rapport médical',

        'certificate.view' => "Consulter les certificats d'aptitude",
        'certificate.generate' => "Générer un certificat d'aptitude",

        'audit.view' => "Consulter le journal d'audit",

        'user.view' => 'Consulter les utilisateurs',
        'user.create' => 'Créer un utilisateur',
        'user.update' => 'Modifier un utilisateur',
        'user.delete' => 'Supprimer un utilisateur',

        'role.view' => 'Consulter les rôles',
        'role.manage' => 'Gérer les rôles et permissions',
    ];

    /**
     * Rôles du §4 du PRD (code => libellé).
     *
     * @var array<string, string>
     */
    private array $roles = [
        'ADMINISTRATEUR' => 'Administrateur',
        'RECEPTIONNISTE' => 'Réceptionniste',
        'MEDECIN' => 'Médecin',
        'INFIRMIER' => 'Infirmier',
        'FACTURIER' => 'Facturier',
        'CAISSIER' => 'Caissier',
        'RESPONSABLE_FACTURATION' => 'Responsable facturation',
        'DIRECTION' => 'Direction',
    ];

    public function run(): void
    {
        foreach ($this->permissions as $name => $label) {
            Permission::updateOrCreate(
                ['name' => $name],
                ['label' => $label, 'is_system' => true],
            );
        }

        $allNames = array_keys($this->permissions);
        $viewNames = array_values(array_filter(
            $allNames,
            fn (string $name): bool => str_ends_with($name, '.view'),
        ));

        $matrix = [
            'ADMINISTRATEUR' => $allNames,
            'RECEPTIONNISTE' => [
                'patient.view', 'patient.create', 'patient.update', 'patient.delete',
                'company.view', 'company.create', 'company.update', 'company.delete',
                'reference.view',
                'visit.view', 'visit.create', 'visit.update',
                'certificate.view',
            ],
            'MEDECIN' => [
                'patient.view',
                'visit.view', 'visit.create', 'visit.update',
                'medical_report.view', 'medical_report.create', 'medical_report.update', 'medical_report.validate',
                'certificate.view', 'certificate.generate',
                'reference.view',
            ],
            'INFIRMIER' => [
                'patient.view',
                'visit.view',
                'medical_report.view', 'medical_report.update',
                'reference.view',
            ],
            'FACTURIER' => [
                'patient.view', 'company.view', 'visit.view', 'certificate.view',
            ],
            'CAISSIER' => [
                'patient.view', 'visit.view',
            ],
            'RESPONSABLE_FACTURATION' => [
                'patient.view', 'company.view', 'visit.view', 'audit.view',
            ],
            'DIRECTION' => array_values(array_unique([...$viewNames, 'audit.view'])),
        ];

        foreach ($this->roles as $name => $label) {
            $role = Role::updateOrCreate(
                ['name' => $name],
                ['label' => $label, 'is_system' => true],
            );

            $permissionIds = Permission::whereIn('name', $matrix[$name] ?? [])->pluck('id')->all();
            $role->permissions()->sync($permissionIds);
        }
    }
}
