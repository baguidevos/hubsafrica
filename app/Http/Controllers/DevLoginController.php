<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DevLoginController extends Controller
{
    /**
     * Comptes de test pré-configurés pour chaque profil et panel
     */
    public static function getDemoAccounts(): array
    {
        return [
            'admin' => [
                'id' => 'admin',
                'name' => 'Directeur Hub Africa',
                'email' => 'admin@hubafrica.org',
                'password' => 'password',
                'role' => 'Super Administrateur',
                'panel_name' => 'Admin Central',
                'panel_id' => 'admin',
                'panel_url' => '/admin',
                'login_url' => '/admin/login',
                'sae_name' => 'Plateforme Globale',
                'sae_slug' => null,
                'badge' => 'Super Admin',
                'color' => 'slate',
                'description' => 'Supervision panafricaine, gestion des SAE labellisées, formulaires et indicateurs.',
            ],
            'sae' => [
                'id' => 'sae',
                'name' => 'Fatou Diop',
                'email' => 'fatou.diop@teranga.sn',
                'password' => 'password',
                'role' => 'Responsable de Structure SAE',
                'panel_name' => 'Structure SAE',
                'panel_id' => 'sae',
                'panel_url' => '/sae/teranga-africa',
                'login_url' => '/sae/login',
                'sae_name' => 'Teranga Africa Hub (Dakar)',
                'sae_slug' => 'teranga-africa',
                'badge' => 'Responsable SAE',
                'color' => 'teal',
                'description' => 'Gestion de l\'incubateur, des cohortes, programmes, coachs et portefeuille startups.',
            ],
            'sae2' => [
                'id' => 'sae2',
                'name' => 'Amadou Koné',
                'email' => 'amadou.kone@jokkolabs.ci',
                'password' => 'password',
                'role' => 'Responsable de Structure SAE',
                'panel_name' => 'Structure SAE',
                'panel_id' => 'sae',
                'panel_url' => '/sae/jokkolabs-abidjan',
                'login_url' => '/sae/login',
                'sae_name' => 'Jokkolabs Abidjan (Côte d\'Ivoire)',
                'sae_slug' => 'jokkolabs-abidjan',
                'badge' => 'Responsable SAE',
                'color' => 'teal',
                'description' => 'Deuxième structure d\'accompagnement partenaire pour tester le multi-tenant.',
            ],
            'coach' => [
                'id' => 'coach',
                'name' => 'Marie Sarr',
                'email' => 'marie.sarr@coach.sn',
                'password' => 'password',
                'role' => 'Coach & Mentor Senior',
                'panel_name' => 'Espace Coach',
                'panel_id' => 'coach',
                'panel_url' => '/coach/teranga-africa',
                'login_url' => '/coach/login',
                'sae_name' => 'Teranga Africa Hub',
                'sae_slug' => 'teranga-africa',
                'badge' => 'Coach & Mentor',
                'color' => 'indigo',
                'description' => 'Suivi individuel des startups affectées, séances de mentorat et évaluations.',
            ],
            'coach2' => [
                'id' => 'coach2',
                'name' => 'Ibrahima Diallo',
                'email' => 'ibrahima.diallo@coach.sn',
                'password' => 'password',
                'role' => 'Coach Marketing & Pitch',
                'panel_name' => 'Espace Coach',
                'panel_id' => 'coach',
                'panel_url' => '/coach/teranga-africa',
                'login_url' => '/coach/login',
                'sae_name' => 'Teranga Africa Hub',
                'sae_slug' => 'teranga-africa',
                'badge' => 'Coach & Mentor',
                'color' => 'indigo',
                'description' => 'Expertises : Marketing Digital, Pitch commercial et Agro-industrie.',
            ],
            'entrepreneur' => [
                'id' => 'entrepreneur',
                'name' => 'Ousmane Fall',
                'email' => 'ousmane.fall@ecobat.sn',
                'password' => 'password',
                'role' => 'Entrepreneur Porteur de Projet',
                'panel_name' => 'Portail Entrepreneur',
                'panel_id' => 'entrepreneur',
                'panel_url' => '/portal/teranga-africa',
                'login_url' => '/portal/login',
                'sae_name' => 'Teranga Africa Hub (Projet EcoBat)',
                'sae_slug' => 'teranga-africa',
                'badge' => 'Entrepreneur',
                'color' => 'amber',
                'description' => 'Startup EcoBat Sénégal (BTP durable). Dépôt des livrables, questionnaires et KPI.',
            ],
            'entrepreneur2' => [
                'id' => 'entrepreneur2',
                'name' => 'Awa Ndiaye',
                'email' => 'awa.ndiaye@agritech.sn',
                'password' => 'password',
                'role' => 'Entrepreneure Porteuse de Projet',
                'panel_name' => 'Portail Entrepreneur',
                'panel_id' => 'entrepreneur',
                'panel_url' => '/portal/teranga-africa',
                'login_url' => '/portal/login',
                'sae_name' => 'Teranga Africa Hub (SunuAgri)',
                'sae_slug' => 'teranga-africa',
                'badge' => 'Entrepreneure',
                'color' => 'amber',
                'description' => 'Startup SunuAgri Tech. Accompagnement en incubation et mentorat.',
            ],
        ];
    }

    /**
     * Page visuelle récapitulative des comptes de test
     */
    public function index(): View
    {
        return view('pages.comptes-test', [
            'accounts' => self::getDemoAccounts(),
            'currentUser' => Auth::user(),
        ]);
    }

    /**
     * Connexion directe en 1 clic
     */
    public function login(Request $request): RedirectResponse
    {
        $as = $request->query('as');
        $email = $request->query('email');
        $targetRedirect = $request->query('redirect');

        $accounts = self::getDemoAccounts();

        if ($as && isset($accounts[$as])) {
            $email = $accounts[$as]['email'];
            $targetRedirect = $targetRedirect ?: $accounts[$as]['panel_url'];
        }

        if (! $email) {
            return redirect()->route('dev.comptes');
        }

        $user = User::where('email', $email)->first();

        if (! $user) {
            return redirect()->route('dev.comptes')->with('error', "Le compte [{$email}] n'existe pas en base. Veuillez lancer 'php artisan db:seed'.");
        }

        // Connexion automatique et régénération de session
        Auth::login($user, remember: true);
        $request->session()->regenerate();

        if ($targetRedirect) {
            return redirect($targetRedirect);
        }

        return redirect()->intended('/admin');
    }

    /**
     * Déconnexion rapide pour tester un autre profil
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $redirect = $request->query('redirect', route('dev.comptes'));

        return redirect($redirect)->with('success', 'Session déconnectée. Vous pouvez maintenant choisir un autre profil.');
    }
}
