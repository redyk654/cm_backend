<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CertificateResource;
use App\Models\AuditLog;
use App\Models\Visit;
use App\Services\AuditLogger;
use App\Services\CertificateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Certificat d'aptitude (PDF) d'une visite.
 */
class CertificateController extends Controller
{
    public function __construct(
        private readonly CertificateService $service,
        private readonly AuditLogger $auditLogger,
    ) {}

    public function show(Visit $visit): JsonResponse
    {
        $certificate = $this->service->getForVisit($visit);

        return $this->successResponse(
            $certificate !== null ? new CertificateResource($certificate) : null,
        );
    }

    public function store(Request $request, Visit $visit): JsonResponse
    {
        $certificate = $this->service->generate($visit, $request->user());

        return $this->createdResponse(new CertificateResource($certificate));
    }

    public function download(Visit $visit): StreamedResponse|JsonResponse
    {
        $certificate = $this->service->getForVisit($visit);

        if ($certificate === null || $certificate->pdf_path === null) {
            return $this->notFoundResponse('Aucun certificat généré pour cette visite.');
        }

        $this->auditLogger->log(
            AuditLog::ACTION_DOWNLOAD,
            $certificate,
            null,
            null,
            'Téléchargement du certificat',
            ['visit_id' => $visit->id, 'reference' => $certificate->reference],
        );

        return Storage::disk('local')->download(
            $this->service->pathFor($certificate),
            "certificat-{$certificate->reference}.pdf",
        );
    }
}
