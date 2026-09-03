<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Certificate;

/**
 * Seul point d'accès Eloquent pour les certificats d'aptitude.
 */
class CertificateRepository
{
    private const EAGER = [
        'generatedBy:id,nom,prenom',
    ];

    public function findByVisit(string $visitId): ?Certificate
    {
        return Certificate::query()
            ->with(self::EAGER)
            ->where('visit_id', $visitId)
            ->first();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Certificate
    {
        $certificate = new Certificate;
        // forceFill : tous les champs du certificat sont un snapshot piloté par le
        // service, aucun n'est mass-assignable.
        $certificate->forceFill($data)->save();

        return $certificate->refresh()->load(self::EAGER);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Certificate $certificate, array $data): Certificate
    {
        $certificate->forceFill($data)->save();

        return $certificate->refresh()->load(self::EAGER);
    }

    /**
     * Nombre de certificats créés sur l'année civile (corbeille incluse) : sert à
     * numéroter la référence CERT-AAAA-00001 sans réutiliser une séquence.
     */
    public function countCreatedInYear(int $year): int
    {
        return Certificate::query()
            ->withTrashed()
            ->whereYear('created_at', $year)
            ->count();
    }
}
