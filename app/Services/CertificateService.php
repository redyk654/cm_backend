<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Certificate;
use App\Models\MedicalReport;
use App\Models\User;
use App\Models\Visit;
use App\Repositories\AptitudeAssessmentRepository;
use App\Repositories\CertificateRepository;
use App\Repositories\MedicalReportRepository;
use App\Repositories\VisitRepository;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Génération du certificat d'aptitude (PDF) à partir du rapport médical validé
 * et de la décision d'aptitude d'une visite.
 *
 * Le certificat est un snapshot : toutes les valeurs imprimées sont figées en
 * base à la génération. Il est régénérable ; dans ce cas la même ligne est
 * conservée, la référence CERT-AAAA-00001 attribuée à la première génération est
 * gardée, seuls le PDF et le snapshot sont rafraîchis.
 */
class CertificateService
{
    public function __construct(
        private readonly CertificateRepository $repository,
        private readonly MedicalReportRepository $reportRepository,
        private readonly AptitudeAssessmentRepository $assessmentRepository,
        private readonly VisitRepository $visitRepository,
        private readonly AuditLogger $auditLogger,
    ) {}

    public function getForVisit(Visit $visit): ?Certificate
    {
        return $this->repository->findByVisit($visit->id);
    }

    /**
     * Génère (ou régénère) le certificat d'aptitude d'une visite.
     */
    public function generate(Visit $visit, User $user): Certificate
    {
        $report = $this->reportRepository->findByVisit($visit->id);

        if ($report === null || $report->statut !== MedicalReport::STATUT_VALIDE) {
            throw new \DomainException('Le rapport médical doit être validé avant de générer le certificat.');
        }

        $assessment = $this->assessmentRepository->findByVisit($visit->id);

        if ($assessment === null) {
            throw new \DomainException('La décision d\'aptitude doit être renseignée avant de générer le certificat.');
        }

        $signataire = $report->medecinSignataire ?? $report->validatedBy;

        if ($signataire === null) {
            throw new \DomainException('Le médecin signataire doit être identifié.');
        }

        $visit->load(['patient', 'visitType']);

        // « Date de la précédente visite périodique » : dernière visite périodique
        // validée du patient, antérieure à la visite courante.
        $datePrecedentePeriodique = $this->visitRepository
            ->previousPeriodicVisit($visit->patient, $visit->date_visite->toDateString())
            ?->date_visite?->toDateString();

        $existing = $this->repository->findByVisit($visit->id);

        $reference = $existing?->reference ?? sprintf(
            'CERT-%s-%s',
            date('Y'),
            str_pad((string) ($this->repository->countCreatedInYear((int) date('Y')) + 1), 5, '0', STR_PAD_LEFT),
        );

        $dureeMois = (int) $assessment->duree_validite_mois;
        $dateExamen = $visit->date_visite->copy();
        $dateExpiration = $dateExamen->copy()->addMonths($dureeMois);

        $snapshot = [
            'patient_nom' => $visit->patient->nom,
            'patient_prenom' => $visit->patient->prenom,
            'patient_date_naissance' => $visit->patient->date_naissance?->toDateString(),
            'employeur' => $visit->employeur,
            'poste' => $visit->poste,
            'type_visite_label' => $visit->visitType->label,
            'type_visite_code' => $visit->visitType->code,
            'date_precedente_visite_periodique' => $datePrecedentePeriodique,
            'decision_label' => $assessment->decision_label,
            'restriction' => $assessment->restriction,
            'recommandations' => $assessment->recommandations,
            'duree_validite_mois' => $dureeMois,
            'date_examen' => $dateExamen->toDateString(),
            'date_expiration' => $dateExpiration->toDateString(),
            'medecin_nom' => $signataire->full_name,
            'lieu' => $existing?->lieu ?? (string) config('centre.lieu_etablissement', 'Douala'),
        ];

        return DB::transaction(function () use ($visit, $existing, $reference, $snapshot, $user): Certificate {
            $pdf = Pdf::loadView('certificates.aptitude', [
                'c' => (object) $snapshot,
                'reference' => $reference,
                'genere_le' => now(),
                'centre' => config('centre'),
            ])->setPaper('a4');

            $path = "certificates/{$reference}.pdf";
            Storage::disk('local')->put($path, $pdf->output());

            $payload = array_merge($snapshot, [
                'visit_id' => $visit->id,
                'reference' => $reference,
                'pdf_path' => $path,
                'generated_by' => $user->id,
                'generated_at' => now(),
            ]);

            $certificate = $existing !== null
                ? $this->repository->update($existing, $payload)
                : $this->repository->create($payload);

            $this->auditLogger->log(
                AuditLog::ACTION_GENERATE,
                $certificate,
                null,
                null,
                'Génération du certificat d\'aptitude',
                ['visit_id' => $visit->id, 'reference' => $reference],
            );

            return $certificate;
        });
    }

    /**
     * Chemin de stockage du PDF (disque « local »), pour le téléchargement.
     */
    public function pathFor(Certificate $certificate): string
    {
        return (string) $certificate->pdf_path;
    }
}
