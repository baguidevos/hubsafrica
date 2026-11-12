<?php

use App\Http\Controllers\Api\McpOAuthController;
use App\Http\Controllers\CoachInvitationController;
use App\Http\Controllers\DevLoginController;
use App\Http\Controllers\EntrepreneurInvitationController;
use App\Http\Controllers\Public\CoachJoinRequestPublicController;
use App\Http\Controllers\Public\CohortPublicController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\OpportunityPublicController;
use App\Http\Controllers\Public\OrientationController;
use App\Http\Controllers\Public\ProgramPublicController;
use App\Http\Controllers\Public\StructurePublicController;
use App\Http\Controllers\Sae\ReportPrintController;
use App\Http\Controllers\UnifiedInvitationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

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
Route::get('/join/cohort/{id}', [CohortPublicController::class, 'join'])->name('public.cohort.join.clean');
Route::get('/cohortes/{id}/rejoindre', [CohortPublicController::class, 'join'])->name('public.cohort.join');
Route::post('/cohortes/{id}/login', [CohortPublicController::class, 'login'])->name('public.cohort.login');
Route::post('/cohortes/{id}/register', [CohortPublicController::class, 'register'])->name('public.cohort.register');

// Annuaire & Fiches des Structures d'Accompagnement (SAEs)
Route::get('/structures', [StructurePublicController::class, 'index'])->name('public.structures');
Route::get('/structures/{id}', [StructurePublicController::class, 'show'])->name('public.structure.detail');

// Opportunités & Appels à Projets (Publiés par les SAEs)
Route::get('/opportunites', [OpportunityPublicController::class, 'index'])->name('public.opportunities');
Route::get('/opportunites/{slug}', [OpportunityPublicController::class, 'show'])->name('public.opportunities.detail');

// Portail d'Orientation & Choix de Profil
Route::get('/rejoindre', [OrientationController::class, 'index'])->name('public.rejoindre');
Route::redirect('/account-type-selection', '/rejoindre');

// Candidature Coach / Demande d'adhésion à une structure SAE
Route::get('/coach/postuler', [CoachJoinRequestPublicController::class, 'create'])->name('public.coach.join');
Route::post('/coach/postuler', [CoachJoinRequestPublicController::class, 'store'])->name('public.coach.join.store');

// Parcours d'embarquement unifié et neutre (Coach & Entrepreneur)
Route::get('/join/{token}', [UnifiedInvitationController::class, 'show'])->name('invitation.join');
Route::post('/join/{token}', [UnifiedInvitationController::class, 'register'])->name('invitation.register');

// Rétrocompatibilité avec les liens et tests existants
Route::get('/coach/invite/{token}', [CoachInvitationController::class, 'show'])->name('coach.invite');
Route::post('/coach/invite/{token}', [CoachInvitationController::class, 'register'])->name('coach.invite.register');

Route::get('/entrepreneur/invite/{token}', [EntrepreneurInvitationController::class, 'show'])->name('entrepreneur.invite');
Route::post('/entrepreneur/invite/{token}', [EntrepreneurInvitationController::class, 'register'])->name('entrepreneur.invite.register');

Route::get('/register/coach', function (Request $request) {
    $token = $request->query('token');

    return $token
        ? redirect()->route('invitation.join', ['token' => $token])
        : redirect()->route('public.coach.join');
});

// Redirection conviviale pour l'espace Coach/Mentor
Route::redirect('/mentor', '/coach');

// Redirection conviviale pour l'espace Hubs-HQ (Super Admin)
Route::redirect('/admin', '/hubs-hq');
Route::redirect('/admin/{any}', '/hubs-hq/{any}')->where('any', '.*');

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

// Déploiement temporaire : exécution forcée des migrations en production
Route::get('/maintenance/migrate-force', function (Request $request) {
    $expectedSecret = env('MIGRATION_SECRET', 'hubsafrica-migrate-force-2026');
    $providedKey = $request->query('key');

    $isAuthorized = ($providedKey && hash_equals($expectedSecret, (string) $providedKey))
        || (auth()->check() && method_exists(auth()->user(), 'isSuperAdmin') && auth()->user()->isSuperAdmin());

    if (! $isAuthorized) {
        abort(403, 'Accès interdit. Clé de migration requise (?key=...) ou session Super Admin requise.');
    }

    try {
        Artisan::call('migrate', [
            '--force' => true,
        ]);
        $output = Artisan::output();

        return response("<pre style='background:#0f172a;color:#38bdf8;padding:20px;border-radius:10px;font-family:monospace;white-space:pre-wrap;'>=== MIGRATIONS EXÉCUTÉES AVEC SUCCÈS ===\n\n".e($output).'</pre>');
    } catch (Throwable $e) {
        return response("<pre style='background:#450a0a;color:#fca5a5;padding:20px;border-radius:10px;font-family:monospace;white-space:pre-wrap;'>=== ERREUR MIGRATION ===\n\n".e($e->getMessage())."\n\n".e($e->getTraceAsString()).'</pre>', 500);
    }
})->name('maintenance.migrate.force');

// Déploiement / Maintenance : exécution de php artisan serve (en arrière-plan) ou optimisation serveur
Route::get('/maintenance/serve', function (Request $request) {
    $expectedSecret = env('MIGRATION_SECRET', 'hubsafrica-migrate-force-2026');
    $providedKey = $request->query('key');

    $isAuthorized = ($providedKey && hash_equals($expectedSecret, (string) $providedKey))
        || (auth()->check() && method_exists(auth()->user(), 'isSuperAdmin') && auth()->user()->isSuperAdmin());

    if (! $isAuthorized) {
        abort(403, 'Accès interdit. Clé de maintenance requise (?key=...) ou session Super Admin requise.');
    }

    try {
        $host = $request->query('host', '127.0.0.1');
        $port = (int) $request->query('port', 8000);
        $basePath = base_path();
        $phpBinary = PHP_BINARY ?: 'php';

        if (PHP_OS_FAMILY === 'Windows') {
            pclose(popen("start /B \"\" \"{$phpBinary}\" \"{$basePath}\\artisan\" serve --host={$host} --port={$port}", 'r'));
        } else {
            exec(escapeshellcmd($phpBinary).' '.escapeshellarg($basePath.'/artisan').' serve --host='.escapeshellarg((string) $host).' --port='.$port.' > /dev/null 2>&1 &');
        }

        return response("<pre style='background:#0f172a;color:#34d399;padding:20px;border-radius:10px;font-family:monospace;white-space:pre-wrap;'>=== SERVEUR ARTISAN LANCÉ EN ARRIÈRE-PLAN ===\n\nCommande : php artisan serve --host=".e((string) $host)." --port={$port}\nRépertoire : ".e($basePath).'</pre>');
    } catch (Throwable $e) {
        return response("<pre style='background:#450a0a;color:#fca5a5;padding:20px;border-radius:10px;font-family:monospace;white-space:pre-wrap;'>=== ERREUR SERVE ===\n\n".e($e->getMessage())."\n\n".e($e->getTraceAsString()).'</pre>', 500);
    }
})->name('maintenance.serve');

// Déploiement / Maintenance : création du lien symbolique public/storage (php artisan storage:link)
$storageLinkHandler = function (Request $request) {
    $expectedSecret = env('MIGRATION_SECRET', 'hubsafrica-migrate-force-2026');
    $providedKey = $request->query('key');

    $isAuthorized = ($providedKey && hash_equals($expectedSecret, (string) $providedKey))
        || auth()->check();

    if (! $isAuthorized) {
        abort(403, 'Accès interdit. Clé de maintenance requise (?key=...) ou session utilisateur active requise.');
    }

    try {
        Artisan::call('storage:link', [
            '--force' => true,
        ]);
        $output = Artisan::output();

        Artisan::call('config:clear');

        return response("<pre style='background:#0f172a;color:#34d399;padding:20px;border-radius:10px;font-family:monospace;white-space:pre-wrap;'>=== STORAGE:LINK EXÉCUTÉ AVEC SUCCÈS ===\n\n".e($output)."\nCache de configuration nettoyé (config:clear).</pre>");
    } catch (Throwable $e) {
        return response("<pre style='background:#450a0a;color:#fca5a5;padding:20px;border-radius:10px;font-family:monospace;white-space:pre-wrap;'>=== ERREUR STORAGE:LINK ===\n\n".e($e->getMessage())."\n\n".e($e->getTraceAsString()).'</pre>', 500);
    }
};

Route::get('/maintenance/storage-link', $storageLinkHandler)->name('maintenance.storage.link');
Route::get('/storage-link', $storageLinkHandler)->name('storage.link');

// Fallback pour servir les fichiers du disque public si le symlink public/storage est absent ou bloqué par l'hébergeur
Route::get('/storage/{path}', function (string $path) {
    $cleanPath = ltrim(str_replace('..', '', $path), '/');

    if (! Storage::disk('public')->exists($cleanPath)) {
        abort(404);
    }

    return Storage::disk('public')->response($cleanPath, null, [
        'Cache-Control' => 'public, max-age=86400',
    ]);
})->where('path', '.*')->name('storage.public.fallback');

// Découverte OAuth 2.0 (RFC 8414 & RFC 9728 pour Claude / MCP)
Route::get('/.well-known/oauth-authorization-server', [McpOAuthController::class, 'authorizationServerMetadata'])->name('oauth.metadata.server');
Route::get('/.well-known/oauth-protected-resource', [McpOAuthController::class, 'protectedResourceMetadata'])->name('oauth.metadata.resource');
Route::get('/.well-known/oauth-protected-resource/mcp/sae', [McpOAuthController::class, 'protectedResourceMetadata'])->name('oauth.metadata.resource.mcp');

// Alias racine pour les clients MCP qui utilisent /authorize et /token par défaut
Route::get('/authorize', [McpOAuthController::class, 'authorize'])->name('oauth.authorize.root');
Route::post('/token', [McpOAuthController::class, 'token'])->name('oauth.token.root');

// OAuth 2.0 Connecteurs IA (Claude & ChatGPT)
Route::prefix('oauth')->group(function () {
    Route::get('/authorize', [McpOAuthController::class, 'authorize'])->name('oauth.authorize');
    Route::post('/authorize/approve', [McpOAuthController::class, 'approve'])->name('oauth.approve');
    Route::post('/authorize/deny', [McpOAuthController::class, 'deny'])->name('oauth.deny');
    Route::post('/token', [McpOAuthController::class, 'token'])->name('oauth.token');
});
