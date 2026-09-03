<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Patient;
use App\Repositories\PatientRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

/**
 * Règles métier des patients : détection de doublon d'identité et génération
 * du numéro de dossier.
 */
class PatientService
{
    public function __construct(
        private readonly PatientRepository $repository,
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     */
    public function list(array $filters): LengthAwarePaginator
    {
        return $this->repository->search($filters);
    }

    /**
     * Crée un patient.
     *
     * Sauf `force=true`, refuse la création si un patient de même identité existe
     * déjà (même nom + prénom, et même date de naissance si elle est fournie).
     * Le numéro de dossier est généré : PAT-<année>-<séquence sur 5 chiffres>.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Patient
    {
        $force = ! empty($data['force']);
        unset($data['force']);

        if (! $force) {
            $duplicate = $this->checkDuplicate($data);

            if ($duplicate !== null) {
                throw new \DomainException(
                    "Un patient identique existe déjà (dossier {$duplicate->numero_dossier}). "
                    .'Renvoyez avec force=true pour créer quand même.'
                );
            }
        }

        return DB::transaction(function () use ($data): Patient {
            $year = (int) date('Y');
            $sequence = $this->repository->countCreatedInYear($year) + 1;

            $data['numero_dossier'] = sprintf(
                'PAT-%d-%s',
                $year,
                str_pad((string) $sequence, 5, '0', STR_PAD_LEFT),
            );

            return $this->repository->create($data);
        });
    }

    /**
     * Expose publiquement la détection de doublon pour l'endpoint dédié.
     *
     * @param  array<string, mixed>  $data
     */
    public function checkDuplicate(array $data): ?Patient
    {
        return $this->repository->findProbableDuplicate(
            (string) ($data['nom'] ?? ''),
            (string) ($data['prenom'] ?? ''),
            isset($data['date_naissance']) && $data['date_naissance'] !== ''
                ? (string) $data['date_naissance']
                : null,
        );
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Patient $patient, array $data): Patient
    {
        unset($data['force']);

        return $this->repository->update($patient, $data);
    }

    public function delete(Patient $patient): void
    {
        $this->repository->delete($patient);
    }
}
