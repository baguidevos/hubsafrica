<?php

use App\Http\Controllers\CoachInvitationController;
use App\Http\Controllers\DevLoginController;
use App\Http\Controllers\EntrepreneurInvitationController;
use App\Http\Controllers\Public\CoachJoinRequestPublicController;
use App\Http\Controllers\Public\CohortPublicController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\OrientationController;
use App\Http\Controllers\Public\ProgramPublicController;
use App\Http\Controllers\Public\StructurePublicController;
use App\Http\Controllers\Sae\ReportPrintController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Routes Publiques Institutionnelles — Hubs Africa
|--------------------------------------------------------------------------
*/

// Accueil Institutionnelle avec métriques d'impact
Route::get('/', [HomeController::class, 'index'])->name('home');

// Programmes d'Accompagnement & Candidatures
Route::get('/programmes', [ProgramPublicController::class, 'index'])->name('public.programmes');
Route::get('/programmes/{id}', [ProgramPublicController::class, 'show'])->name('public.programme.detail');
Route::get('/programmes/{id}/candidater', [ProgramPublicController::class, 'candidater'])->name('public.programme.candidater');

// Accès Direct & Candidature à une Cohorte / Promotion spécifique
Route::get('/cohortes/{id}/rejoindre', [CohortPublicController::class, 'join'])->name('public.cohort.join');
Route::post('/cohortes/{id}/login', [CohortPublicController::class, 'login'])->name('public.cohort.login');
Route::post('/cohortes/{id}/register', [CohortPublicController::class, 'register'])->name('public.cohort.register');

// Annuaire & Fiches des Structures d'Accompagnement (SAEs)
Route::get('/structures', [StructurePublicController::class, 'index'])->name('public.structures');
Route::get('/structures/{id}', [StructurePublicController::class, 'show'])->name('public.structure.detail');

// Portail d'Orientation & Choix de Profil
Route::get('/rejoindre', [OrientationController::class, 'index'])->name('public.rejoindre');
Route::redirect('/account-type-selection', '/rejoindre');

// Candidature Coach / Demande d'adhésion à une structure SAE
Route::get('/coach/postuler', [CoachJoinRequestPublicController::class, 'create'])->name('public.coach.join');
Route::post('/coach/postuler', [CoachJoinRequestPublicController::class, 'store'])->name('public.coach.join.store');

// Parcours d'inscription Coach via lien d'invitation sécurisé
Route::get('/coach/invite/{token}', [CoachInvitationController::class, 'show'])->name('coach.invite');
Route::post('/coach/invite/{token}', [CoachInvitationController::class, 'register'])->name('coach.invite.register');

// Parcours d'inscription Entrepreneur via lien d'invitation sécurisé
Route::get('/entrepreneur/invite/{token}', [EntrepreneurInvitationController::class, 'show'])->name('entrepreneur.invite');
Route::post('/entrepreneur/invite/{token}', [EntrepreneurInvitationController::class, 'register'])->name('entrepreneur.invite.register');
Route::get('/register/coach', function (Request $request) {
    $token = $request->query('token');

    return $token
        ? redirect()->route('coach.invite', ['token' => $token])
        : redirect()->route('public.coach.join');
});

// Redirection conviviale pour l'espace Coach/Mentor
Route::redirect('/mentor', '/coach');

// Comptes de Démonstration & Connexion Instantanée 1-Clic
Route::get('/comptes-test', [DevLoginController::class, 'index'])->name('dev.comptes');
Route::get('/demo', [DevLoginController::class, 'index'])->name('dev.demo');
Route::get('/demo/login', [DevLoginController::class, 'login'])->name('dev.login');
Route::get('/demo/logout', [DevLoginController::class, 'logout'])->name('dev.logout');

// Édition & Exportation des Rapports Institutionnels SAE (Impression haute fidélité, HTML autonome & CSV)
Route::middleware(['auth'])->prefix('sae/{tenant:slug}/rapports')->name('sae.reports.')->group(function () {
    Route::get('/impact/print', [ReportPrintController::class, 'impactPrint'])->name('impact.print');
    Route::get('/impact/download', [ReportPrintController::class, 'impactDownload'])->name('impact.download');
    Route::get('/impact/csv', [ReportPrintController::class, 'impactCsv'])->name('impact.csv');

    Route::get('/analytics/print', [ReportPrintController::class, 'analyticsPrint'])->name('analytics.print');
    Route::get('/analytics/download', [ReportPrintController::class, 'analyticsDownload'])->name('analytics.download');
    Route::get('/analytics/csv', [ReportPrintController::class, 'analyticsCsv'])->name('analytics.csv');
});
