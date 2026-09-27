<?php

use App\Http\Controllers\CoachInvitationController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\OrientationController;
use App\Http\Controllers\Public\ProgramPublicController;
use App\Http\Controllers\Public\StructurePublicController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Routes Publiques Institutionnelles — Hub Africa
|--------------------------------------------------------------------------
*/

// Accueil Institutionnelle avec métriques d'impact
Route::get('/', [HomeController::class, 'index'])->name('home');

// Programmes d'Accompagnement & Candidatures
Route::get('/programmes', [ProgramPublicController::class, 'index'])->name('public.programmes');
Route::get('/programmes/{id}', [ProgramPublicController::class, 'show'])->name('public.programme.detail');
Route::get('/programmes/{id}/candidater', [ProgramPublicController::class, 'candidater'])->name('public.programme.candidater');

// Annuaire & Fiches des Structures d'Accompagnement (SAEs)
Route::get('/structures', [StructurePublicController::class, 'index'])->name('public.structures');
Route::get('/structures/{id}', [StructurePublicController::class, 'show'])->name('public.structure.detail');

// Portail d'Orientation & Choix de Profil
Route::get('/rejoindre', [OrientationController::class, 'index'])->name('public.rejoindre');
Route::redirect('/account-type-selection', '/rejoindre');

// Parcours d'inscription Coach via lien d'invitation sécurisé
Route::get('/coach/invite/{token}', [CoachInvitationController::class, 'show'])->name('coach.invite');
Route::post('/coach/invite/{token}', [CoachInvitationController::class, 'register'])->name('coach.invite.register');
Route::get('/register/coach', function (\Illuminate\Http\Request $request) {
    $token = $request->query('token');
    return $token
        ? redirect()->route('coach.invite', ['token' => $token])
        : redirect()->route('public.rejoindre');
});

// Redirection conviviale pour l'espace Coach/Mentor
Route::redirect('/mentor', '/coach');

// Comptes de Démonstration & Connexion Instantanée 1-Clic
use App\Http\Controllers\DevLoginController;
Route::get('/comptes-test', [DevLoginController::class, 'index'])->name('dev.comptes');
Route::get('/demo', [DevLoginController::class, 'index'])->name('dev.demo');
Route::get('/demo/login', [DevLoginController::class, 'login'])->name('dev.login');
Route::get('/demo/logout', [DevLoginController::class, 'logout'])->name('dev.logout');

