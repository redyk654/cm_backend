<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RbacSeeder::class);

        // Référentiels métier (après le RBAC).
        $this->call(VisitTypeSeeder::class);
        $this->call(AptitudeDecisionSeeder::class);

        // Mot de passe par défaut de la factory : « password »
        $admin = User::factory()->create([
            'nom' => 'Admin',
            'prenom' => 'Centre',
            'email' => 'admin@centre-medical.local',
        ]);

        $admin->assignRole('ADMINISTRATEUR');
    }
}
