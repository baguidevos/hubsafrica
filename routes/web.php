<?php

use App\Http\Controllers\CoachInvitationController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

// Parcours d'inscription Coach via lien d'invitation
Route::get('/coach/invite/{token}', [CoachInvitationController::class, 'show'])->name('coach.invite');
Route::post('/coach/invite/{token}', [CoachInvitationController::class, 'register'])->name('coach.invite.register');
