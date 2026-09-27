<x-filament-panels::page>
    <div class="space-y-6">
        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <h3 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                <x-heroicon-o-bookmark-square class="w-6 h-6 text-teal-600" />
                Guide d'utilisation & Base de connaissances SIGAE
            </h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                Documentation complète sur les modules, les parcours d'accompagnement, la gouvernance des données et le fonctionnement multi-tenant.
            </p>
        </div>

        <div class="space-y-4">
            {{-- Module 1 : Multi-tenant & Sécurité --}}
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <h4 class="text-base font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                    <x-heroicon-o-shield-check class="w-5 h-5 text-teal-600" />
                    1. Architecture Multi-Tenant & Règle d'Isolation
                </h4>
                <div class="mt-3 space-y-2 text-sm text-gray-600 dark:text-gray-300">
                    <p>• <strong>Isolation stricte :</strong> Chaque Structure d'Accompagnement à l'Entrepreneuriat (SAE) dispose de son propre périmètre étanche. Toutes les requêtes sont filtrées par <code>TenantScope</code> et <code>BelongsToTenant</code>.</p>
                    <p>• <strong>Règle 1 Utilisateur = 1 Structure SAE :</strong> Un utilisateur ne peut créer qu'une unique structure SAE sur la plateforme. Toute tentative supplémentaire est bloquée par contrainte d'unicité en base de données et dans la logique métier.</p>
                    <p>• <strong>Panels dédiés :</strong> Les Responsables opèrent sur <code>/sae</code>, les Coachs sur <code>/coach</code>, les Entrepreneurs sur <code>/portal</code>, et les Administrateurs généraux sur <code>/admin</code>.</p>
                </div>
            </div>

            {{-- Module 2 : Portefeuille & Parcours --}}
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <h4 class="text-base font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                    <x-heroicon-o-light-bulb class="w-5 h-5 text-amber-500" />
                    2. Portefeuille & Suivi des Projets
                </h4>
                <div class="mt-3 space-y-2 text-sm text-gray-600 dark:text-gray-300">
                    <p>• <strong>Entrepreneurs :</strong> Fiches d'identité détaillées avec coordonnées, profil sociologique, secteur d'activité et localisation géographique.</p>
                    <p>• <strong>Projets :</strong> Cycle de vie du projet (Idéation, Incubation, Accélération), niveau de maturité, suivi des jalons et journal des modifications.</p>
                    <p>• <strong>Historique des parcours :</strong> Journal chronologique des événements jalons (changement de niveau, levée de fonds, signature de convention).</p>
                </div>
            </div>

            {{-- Module 3 : Programmes & Affectations --}}
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <h4 class="text-base font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                    <x-heroicon-o-academic-cap class="w-5 h-5 text-indigo-600" />
                    3. Programmes, Cohortes & Coachs
                </h4>
                <div class="mt-3 space-y-2 text-sm text-gray-600 dark:text-gray-300">
                    <p>• <strong>Programmes & Cohortes :</strong> Structuration des cohortes avec dates de début/fin, quotas d'admission et livrables attendus.</p>
                    <p>• <strong>Invitation Coachs :</strong> Les coachs rejoignent l'incubateur via un lien d'invitation sécurisé unique généré par la SAE (<code>/coach/invite/{token}</code>).</p>
                    <p>• <strong>Capacité d'accompagnement :</strong> Chaque coach a un plafond maximal d'entreprises assignées, empêchant la surcharge de travail.</p>
                </div>
            </div>

            {{-- Module 4 : Suivi & Évaluation CUBE --}}
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <h4 class="text-base font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                    <x-heroicon-o-chart-pie class="w-5 h-5 text-emerald-600" />
                    4. Suivi & Évaluation, Livrables & Financements
                </h4>
                <div class="mt-3 space-y-2 text-sm text-gray-600 dark:text-gray-300">
                    <p>• <strong>Livrables :</strong> Définition des attendus par niveau, soumission par l'entrepreneur, révision et notation par le coach.</p>
                    <p>• <strong>Formulaires & Diagnostics :</strong> Créateur de formulaires visuel intégré pour les enquêtes de satisfaction, diagnostics et évaluations mi-parcours.</p>
                    <p>• <strong>Financements :</strong> Enregistrement des subventions, prêts d'honneur, prises de participation et décaissements.</p>
                    <p>• <strong>Data Room :</strong> Coffre-fort numérique partagé pour le dépôt et la validation des documents légaux et financiers (statuts, bilans, RIB, contrats).</p>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
