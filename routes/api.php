<?php

use App\Http\Controllers\Api\AptitudeAssessmentController;
use App\Http\Controllers\Api\AptitudeDecisionController;
use App\Http\Controllers\Api\AuditLogController;
use App\Http\Controllers\Api\CertificateController;
use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\MedicalReportController;
use App\Http\Controllers\Api\PatientController;
use App\Http\Controllers\Api\VisitController;
use App\Http\Controllers\Api\VisitTypeController;
use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);
    });
});

Route::middleware('auth:sanctum')->group(function () {
    // Synthèse de la page d'accueil (tout utilisateur authentifié).
    Route::get('dashboard/summary', [DashboardController::class, 'summary']);

    /*
     * Référentiel — Types de visite
     * Lecture : reference.view ; écriture : reference.manage
     */
    Route::apiResource('visit-types', VisitTypeController::class)
        ->only(['index', 'show'])
        ->middleware('permission:reference.view');
    Route::apiResource('visit-types', VisitTypeController::class)
        ->only(['store', 'update', 'destroy'])
        ->middleware('permission:reference.manage');

    /*
     * Référentiel — Décisions d'aptitude
     * Lecture : reference.view ; écriture : reference.manage
     */
    Route::apiResource('aptitude-decisions', AptitudeDecisionController::class)
        ->only(['index', 'show'])
        ->middleware('permission:reference.view');
    Route::apiResource('aptitude-decisions', AptitudeDecisionController::class)
        ->only(['store', 'update', 'destroy'])
        ->middleware('permission:reference.manage');

    /*
     * Entreprises
     */
    Route::get('companies/{company}/patients', [CompanyController::class, 'patients'])
        ->middleware('permission:company.view');
    Route::apiResource('companies', CompanyController::class)
        ->only(['index', 'show'])
        ->middleware('permission:company.view');
    Route::apiResource('companies', CompanyController::class)
        ->only(['store'])
        ->middleware('permission:company.create');
    Route::apiResource('companies', CompanyController::class)
        ->only(['update'])
        ->middleware('permission:company.update');
    Route::apiResource('companies', CompanyController::class)
        ->only(['destroy'])
        ->middleware('permission:company.delete');

    /*
     * Patients
     */
    Route::post('patients/check-duplicate', [PatientController::class, 'checkDuplicate'])
        ->middleware('permission:patient.view');
    Route::apiResource('patients', PatientController::class)
        ->only(['index', 'show'])
        ->middleware('permission:patient.view');
    Route::apiResource('patients', PatientController::class)
        ->only(['store'])
        ->middleware('permission:patient.create');
    Route::apiResource('patients', PatientController::class)
        ->only(['update'])
        ->middleware('permission:patient.update');
    Route::apiResource('patients', PatientController::class)
        ->only(['destroy'])
        ->middleware('permission:patient.delete');

    /*
     * Visites médicales
     */
    Route::get('patients/{patient}/visits', [VisitController::class, 'patientHistory'])
        ->middleware('permission:visit.view');
    Route::get('patients/{patient}/previous-periodic-visit', [VisitController::class, 'previousPeriodic'])
        ->middleware('permission:visit.view');
    Route::apiResource('visits', VisitController::class)
        ->only(['index', 'show'])
        ->middleware('permission:visit.view');
    Route::apiResource('visits', VisitController::class)
        ->only(['store'])
        ->middleware('permission:visit.create');
    Route::apiResource('visits', VisitController::class)
        ->only(['update'])
        ->middleware('permission:visit.update');
    Route::apiResource('visits', VisitController::class)
        ->only(['destroy'])
        ->middleware('permission:visit.delete');

    /*
     * Rapport médical d'une visite (§6.4 du PRD)
     */
    Route::get('visits/{visit}/medical-report', [MedicalReportController::class, 'show'])
        ->middleware('permission:medical_report.view');
    Route::put('visits/{visit}/medical-report', [MedicalReportController::class, 'update'])
        ->middleware('permission:medical_report.update');
    Route::get('visits/{visit}/medical-report/download', [MedicalReportController::class, 'download'])
        ->middleware('permission:medical_report.view');
    Route::post('visits/{visit}/medical-report/validate', [MedicalReportController::class, 'validateReport'])
        ->middleware('permission:medical_report.validate');

    /*
     * Décision d'aptitude d'une visite
     * Gérée dans la continuité du rapport médical : mêmes permissions
     * (lecture medical_report.view, écriture medical_report.validate).
     */
    Route::get('visits/{visit}/aptitude-assessment', [AptitudeAssessmentController::class, 'show'])
        ->middleware('permission:medical_report.view');
    Route::put('visits/{visit}/aptitude-assessment', [AptitudeAssessmentController::class, 'update'])
        ->middleware('permission:medical_report.validate');

    /*
     * Certificat d'aptitude (PDF) d'une visite
     */
    Route::get('visits/{visit}/certificate', [CertificateController::class, 'show'])
        ->middleware('permission:certificate.view');
    Route::post('visits/{visit}/certificate', [CertificateController::class, 'store'])
        ->middleware('permission:certificate.generate');
    Route::get('visits/{visit}/certificate/download', [CertificateController::class, 'download'])
        ->middleware('permission:certificate.view');

    /*
     * Journal d'audit (lecture seule, §10 du PRD)
     */
    Route::get('audit-logs', [AuditLogController::class, 'index'])
        ->middleware('permission:audit.view');
    Route::get('audit-logs/actions', [AuditLogController::class, 'actions'])
        ->middleware('permission:audit.view');
});
