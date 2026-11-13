<x-filament-panels::page>
    {{-- Bannière d'affichage de la clé nouvellement générée --}}
    @if ($newlyCreatedPlainToken)
        <div class="p-6 mb-6 rounded-2xl bg-amber-500/10 border-2 border-amber-500/40 text-amber-950 dark:text-amber-100 shadow-md">
            <div class="flex items-start justify-between gap-4">
                <div class="space-y-2 flex-1">
                    <div class="flex items-center gap-2">
                        <span class="flex h-3 w-3 relative">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-3 w-3 bg-amber-500"></span>
                        </span>
                        <h3 class="text-base font-bold text-amber-900 dark:text-amber-200">
                            Nouvelle Clé API MCP générée : « {{ $newlyCreatedKeyName }} »
                        </h3>
                    </div>
                    <p class="text-xs text-amber-800 dark:text-amber-300">
                        Copiez immédiatement ce jeton secret. <strong>Pour des raisons de sécurité, il ne sera plus jamais affiché.</strong>
                    </p>

                    <div class="mt-3 flex items-center gap-2 max-w-2xl bg-white dark:bg-slate-900 p-2.5 rounded-xl border border-amber-300 dark:border-amber-700/60 font-mono text-xs select-all text-slate-800 dark:text-slate-100 shadow-inner">
                        <span class="truncate flex-1 font-semibold text-emerald-600 dark:text-emerald-400">{{ $newlyCreatedPlainToken }}</span>
                        <button
                            type="button"
                            x-data
                            @click="navigator.clipboard.writeText('{{ $newlyCreatedPlainToken }}'); $tooltip('Copié dans le presse-papier !', { timeout: 2000 })"
                            class="px-3 py-1.5 rounded-lg bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold text-xs transition shrink-0"
                        >
                            Copier
                        </button>
                    </div>
                </div>

                <button
                    type="button"
                    wire:click="dismissNewKeyBanner"
                    class="text-amber-700 dark:text-amber-400 hover:text-amber-950 p-1"
                    title="Fermer ce message"
                >
                    <x-heroicon-m-x-mark class="w-5 h-5" />
                </button>
            </div>
        </div>
    @endif

    {{-- Section Connecteur 1-Clic OAuth 2.0 (Claude & ChatGPT) --}}
    <div class="mb-8 p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs space-y-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-2xl bg-[#2F3284]/10 dark:bg-indigo-500/20 text-[#2F3284] dark:text-indigo-400 flex items-center justify-center shrink-0">
                    <x-heroicon-o-bolt class="w-6 h-6" />
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Connecteur 1-Clic OAuth 2.0 (ChatGPT &amp; Claude)</h3>
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                            Recommandé
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        Permet aux utilisateurs de se connecter d'un simple clic depuis ChatGPT ou Claude avec l'écran d'autorisation officiel Hubs Africa, sans copier de clé manuellement.
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2 shrink-0">
                <a
                    href="{{ $this->testConnectUrl }}"
                    target="_blank"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-[#2F3284] hover:bg-[#2F3284]/90 text-white font-semibold text-xs shadow-xs transition"
                >
                    <x-heroicon-m-arrow-top-right-on-square class="w-4 h-4" />
                    <span>Tester la connexion 1-Clic</span>
                </a>
                <button
                    type="button"
                    wire:click="regenerateOAuthSecret"
                    wire:confirm="Êtes-vous certain de vouloir régénérer le Client Secret OAuth ? Les connecteurs ChatGPT/Claude devront être mis à jour."
                    class="px-3 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-semibold text-xs transition"
                    title="Régénérer le Secret OAuth"
                >
                    <x-heroicon-m-arrow-path class="w-4 h-4" />
                </button>
            </div>
        </div>

        {{-- Paramètres OAuth Pré-remplis --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 pt-2">
            {{-- Client ID --}}
            <div class="bg-slate-50 dark:bg-slate-950 p-3.5 rounded-2xl border border-slate-200 dark:border-slate-800 space-y-1">
                <div class="text-[11px] font-semibold text-slate-500 dark:text-slate-400">Client ID OAuth</div>
                <div class="flex items-center gap-2 font-mono text-xs select-all">
                    <span class="truncate flex-1 font-bold text-slate-900 dark:text-white">{{ $this->oAuthClient?->client_id }}</span>
                    <button
                        type="button"
                        x-data
                        @click="navigator.clipboard.writeText('{{ $this->oAuthClient?->client_id }}'); $tooltip('Copié !', { timeout: 2000 })"
                        class="text-[#2F3284] dark:text-indigo-400 hover:opacity-80"
                    >
                        <x-heroicon-m-clipboard class="w-4 h-4" />
                    </button>
                </div>
            </div>

            {{-- Client Secret --}}
            <div class="bg-slate-50 dark:bg-slate-950 p-3.5 rounded-2xl border border-slate-200 dark:border-slate-800 space-y-1" x-data="{ show: false }">
                <div class="flex items-center justify-between text-[11px] font-semibold text-slate-500 dark:text-slate-400">
                    <span>Client Secret OAuth</span>
                    <button type="button" @click="show = !show" class="text-[10px] text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                        <span x-show="!show">Afficher</span>
                        <span x-show="show" style="display: none;">Masquer</span>
                    </button>
                </div>
                <div class="flex items-center gap-2 font-mono text-xs select-all">
                    <span class="truncate flex-1 font-bold text-slate-900 dark:text-white" x-text="show ? '{{ $this->oAuthClient?->client_secret }}' : '••••••••••••••••••••••••'">••••••••••••••••••••••••</span>
                    <button
                        type="button"
                        x-data
                        @click="navigator.clipboard.writeText('{{ $this->oAuthClient?->client_secret }}'); $tooltip('Copié !', { timeout: 2000 })"
                        class="text-[#2F3284] dark:text-indigo-400 hover:opacity-80"
                    >
                        <x-heroicon-m-clipboard class="w-4 h-4" />
                    </button>
                </div>
            </div>

            {{-- Authorization URL --}}
            <div class="bg-slate-50 dark:bg-slate-950 p-3.5 rounded-2xl border border-slate-200 dark:border-slate-800 space-y-1">
                <div class="text-[11px] font-semibold text-slate-500 dark:text-slate-400">Authorization URL</div>
                <div class="flex items-center gap-2 font-mono text-xs select-all">
                    <span class="truncate flex-1 text-slate-700 dark:text-slate-300">{{ $this->authorizeUrl }}</span>
                    <button
                        type="button"
                        x-data
                        @click="navigator.clipboard.writeText('{{ $this->authorizeUrl }}'); $tooltip('Copié !', { timeout: 2000 })"
                        class="text-[#2F3284] dark:text-indigo-400 hover:opacity-80"
                    >
                        <x-heroicon-m-clipboard class="w-4 h-4" />
                    </button>
                </div>
            </div>

            {{-- Token URL --}}
            <div class="bg-slate-50 dark:bg-slate-950 p-3.5 rounded-2xl border border-slate-200 dark:border-slate-800 space-y-1">
                <div class="text-[11px] font-semibold text-slate-500 dark:text-slate-400">Token URL</div>
                <div class="flex items-center gap-2 font-mono text-xs select-all">
                    <span class="truncate flex-1 text-slate-700 dark:text-slate-300">{{ $this->tokenUrl }}</span>
                    <button
                        type="button"
                        x-data
                        @click="navigator.clipboard.writeText('{{ $this->tokenUrl }}'); $tooltip('Copié !', { timeout: 2000 })"
                        class="text-[#2F3284] dark:text-indigo-400 hover:opacity-80"
                    >
                        <x-heroicon-m-clipboard class="w-4 h-4" />
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Cartes d'adresses d'intégration --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        {{-- Point d'accès MCP Natif --}}
        <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs space-y-3">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-[#2F3284]/10 dark:bg-indigo-500/20 text-[#2F3284] dark:text-indigo-400 flex items-center justify-center font-bold">
                    <x-heroicon-o-cpu-chip class="w-6 h-6" />
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">Point d'accès Serveur MCP</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Pour Claude Desktop, Claude Web, Cursor, OpenWebUI</p>
                </div>
            </div>

            <div class="flex items-center gap-2 bg-slate-50 dark:bg-slate-950 p-2.5 rounded-xl border border-slate-200 dark:border-slate-800 font-mono text-xs select-all text-slate-700 dark:text-slate-300">
                <span class="truncate flex-1">{{ $this->mcpEndpointUrl }}</span>
                <button
                    type="button"
                    x-data
                    @click="navigator.clipboard.writeText('{{ $this->mcpEndpointUrl }}'); $tooltip('Copié !', { timeout: 2000 })"
                    class="px-2.5 py-1 rounded bg-slate-200 dark:bg-slate-800 hover:bg-slate-300 text-xs font-semibold"
                >
                    Copier
                </button>
            </div>
            <div class="text-[11px] text-slate-400 flex items-center gap-1.5">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                <span>Protocole MCP officiel (Streamable HTTP / SSE)</span>
            </div>
        </div>

        {{-- Schéma OpenAPI pour ChatGPT --}}
        <div class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs space-y-3">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center font-bold">
                    <x-heroicon-o-sparkles class="w-6 h-6" />
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">Spécification OpenAPI (ChatGPT)</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Pour les Custom GPTs OpenAI (Actions)</p>
                </div>
            </div>

            <div class="flex items-center gap-2 bg-slate-50 dark:bg-slate-950 p-2.5 rounded-xl border border-slate-200 dark:border-slate-800 font-mono text-xs select-all text-slate-700 dark:text-slate-300">
                <span class="truncate flex-1">{{ $this->openApiSpecUrl }}</span>
                <button
                    type="button"
                    x-data
                    @click="navigator.clipboard.writeText('{{ $this->openApiSpecUrl }}'); $tooltip('Copié !', { timeout: 2000 })"
                    class="px-2.5 py-1 rounded bg-slate-200 dark:bg-slate-800 hover:bg-slate-300 text-xs font-semibold"
                >
                    Copier
                </button>
            </div>
            <div class="text-[11px] text-slate-400 flex items-center gap-1.5">
                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                <span>OpenAPI 3.0 avec schéma JSON synchronisé</span>
            </div>
        </div>
    </div>

    {{-- Tableau des clés d'API actives --}}
    <div class="mt-8 bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-xs">
        <div class="p-6 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <div>
                <h3 class="text-base font-bold text-slate-900 dark:text-white">Clés d'API MCP Actives</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400">Chaque clé est isolée aux données de la structure <strong>{{ $this->activeTenant?->name }}</strong>.</p>
            </div>
            <span class="text-xs font-semibold px-3 py-1 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
                {{ $this->apiKeys->count() }} clé(s) configurée(s)
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 dark:bg-slate-950/60 text-slate-500 border-b border-slate-200/80 dark:border-slate-800">
                    <tr>
                        <th class="py-3 px-6 font-semibold">Libellé / Usage</th>
                        <th class="py-3 px-6 font-semibold">Préfixe</th>
                        <th class="py-3 px-6 font-semibold">Créée par</th>
                        <th class="py-3 px-6 font-semibold">Dernière utilisation</th>
                        <th class="py-3 px-6 font-semibold">Expiration</th>
                        <th class="py-3 px-6 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($this->apiKeys as $key)
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition">
                            <td class="py-4 px-6 font-bold text-slate-900 dark:text-white">
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full {{ $key->isExpired() ? 'bg-red-500' : 'bg-emerald-500' }}"></span>
                                    <span>{{ $key->name }}</span>
                                </div>
                            </td>
                            <td class="py-4 px-6 font-mono text-slate-500 dark:text-slate-400">
                                {{ $key->key_prefix }}
                            </td>
                            <td class="py-4 px-6 text-slate-600 dark:text-slate-300">
                                {{ $key->user?->name ?? 'Système' }}
                            </td>
                            <td class="py-4 px-6 text-slate-600 dark:text-slate-300">
                                {{ $key->last_used_at ? $key->last_used_at->diffForHumans() : 'Jamais utilisée' }}
                            </td>
                            <td class="py-4 px-6">
                                @if($key->isExpired())
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-100 text-red-700 dark:bg-red-950/50 dark:text-red-400">Expirée</span>
                                @elseif($key->expires_at)
                                    <span class="text-slate-600 dark:text-slate-300">{{ $key->expires_at->format('d/m/Y') }}</span>
                                @else
                                    <span class="text-slate-400">Illimitée</span>
                                @endif
                            </td>
                            <td class="py-4 px-6 text-right">
                                <button
                                    type="button"
                                    wire:click="revokeKey({{ $key->id }})"
                                    wire:confirm="Êtes-vous certain de vouloir révoquer cette clé ? Les agents connectés ne pourront plus accéder à vos données."
                                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-semibold text-red-600 hover:text-red-700 hover:bg-red-50 dark:hover:bg-red-950/40 transition"
                                >
                                    <x-heroicon-m-trash class="w-3.5 h-3.5" />
                                    <span>Révoquer</span>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 px-6 text-center text-slate-400">
                                Aucune clé API active pour le moment. Cliquez sur « Générer une nouvelle clé MCP » pour démarrer.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Guide de connexion MCP (ChatGPT & Claude) --}}
    <div class="mt-8 space-y-6" x-data="{ tab: 'chatgpt' }">
        <div class="border-b border-slate-200 dark:border-slate-800 flex items-center gap-6">
            <button
                type="button"
                @click="tab = 'chatgpt'"
                :class="tab === 'chatgpt' ? 'border-[#F8B81D] text-slate-900 dark:text-white font-bold' : 'border-transparent text-slate-500 hover:text-slate-700 dark:hover:text-slate-300'"
                class="py-3 px-2 border-b-2 text-sm transition flex items-center gap-2"
            >
                <x-heroicon-o-chat-bubble-left-right class="w-4 h-4 text-emerald-500" />
                <span>ChatGPT (MCP personnalisé)</span>
            </button>

            <button
                type="button"
                @click="tab = 'claude'"
                :class="tab === 'claude' ? 'border-[#F8B81D] text-slate-900 dark:text-white font-bold' : 'border-transparent text-slate-500 hover:text-slate-700 dark:hover:text-slate-300'"
                class="py-3 px-2 border-b-2 text-sm transition flex items-center gap-2"
            >
                <x-heroicon-o-sparkles class="w-4 h-4 text-amber-500" />
                <span>Claude</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20">
                    Bientôt
                </span>
            </button>
        </div>

        {{-- Onglet ChatGPT --}}
        <div x-show="tab === 'chatgpt'" class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs space-y-5">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                <div>
                    <h4 class="font-bold text-slate-900 dark:text-white text-sm">Connecter Hubs Africa dans ChatGPT (Serveur MCP personnalisé)</h4>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                        Ajoutez votre espace Hubs Africa comme plugin MCP dans ChatGPT en quelques clics, sans saisir de clé API ni d'identifiants OAuth manuellement.
                    </p>
                </div>
                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 shrink-0 self-start sm:self-center">
                    Connexion OAuth automatique
                </span>
            </div>

            <ol class="space-y-3 text-xs text-slate-600 dark:text-slate-300 leading-relaxed">
                <li class="flex items-start gap-3">
                    <span class="w-6 h-6 rounded-full bg-[#2F3284]/10 dark:bg-indigo-500/20 text-[#2F3284] dark:text-indigo-400 font-bold flex items-center justify-center shrink-0">1</span>
                    <div class="pt-0.5">
                        Dans le menu latéral gauche de ChatGPT, cliquez sur <strong>Plugins</strong>.
                    </div>
                </li>
                <li class="flex items-start gap-3">
                    <span class="w-6 h-6 rounded-full bg-[#2F3284]/10 dark:bg-indigo-500/20 text-[#2F3284] dark:text-indigo-400 font-bold flex items-center justify-center shrink-0">2</span>
                    <div class="pt-0.5">
                        En haut à droite de la page Plugins, cliquez sur <strong>Ajouter</strong>.
                    </div>
                </li>
                <li class="flex items-start gap-3">
                    <span class="w-6 h-6 rounded-full bg-[#2F3284]/10 dark:bg-indigo-500/20 text-[#2F3284] dark:text-indigo-400 font-bold flex items-center justify-center shrink-0">3</span>
                    <div class="pt-0.5">
                        Dans le menu déroulant, sélectionnez <strong>Ajouter un serveur MCP personnalisé</strong>.
                    </div>
                </li>
                <li class="flex items-start gap-3">
                    <span class="w-6 h-6 rounded-full bg-[#2F3284]/10 dark:bg-indigo-500/20 text-[#2F3284] dark:text-indigo-400 font-bold flex items-center justify-center shrink-0">4</span>
                    <div class="pt-0.5">
                        Dans le champ <strong>Nom</strong>, saisissez un nom reconnaissable (par exemple <code class="px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-800 font-mono text-slate-800 dark:text-slate-200">Hubs-Africa</code>).
                    </div>
                </li>
                <li class="flex items-start gap-3">
                    <span class="w-6 h-6 rounded-full bg-[#2F3284]/10 dark:bg-indigo-500/20 text-[#2F3284] dark:text-indigo-400 font-bold flex items-center justify-center shrink-0">5</span>
                    <div class="pt-0.5 space-y-2 flex-1">
                        <div>
                            Dans la section <strong>Connexion</strong> (onglet <strong>URL du serveur</strong>), collez l'URL du serveur MCP fournie sur ce dashboard et laissez <strong>Authentification</strong> sur <strong>OAuth</strong> :
                        </div>
                        <div class="flex items-center gap-2 max-w-xl bg-slate-50 dark:bg-slate-950 p-2 rounded-xl border border-slate-200 dark:border-slate-800 font-mono text-xs select-all text-slate-700 dark:text-slate-300">
                            <span class="truncate flex-1">{{ $this->mcpEndpointUrl }}</span>
                            <button
                                type="button"
                                x-data
                                @click="navigator.clipboard.writeText('{{ $this->mcpEndpointUrl }}'); $tooltip('Copié !', { timeout: 2000 })"
                                class="text-[#2F3284] dark:text-indigo-400 hover:opacity-80 shrink-0"
                                title="Copier l'URL MCP"
                            >
                                <x-heroicon-m-clipboard class="w-4 h-4" />
                            </button>
                        </div>
                    </div>
                </li>
                <li class="flex items-start gap-3">
                    <span class="w-6 h-6 rounded-full bg-[#2F3284]/10 dark:bg-indigo-500/20 text-[#2F3284] dark:text-indigo-400 font-bold flex items-center justify-center shrink-0">6</span>
                    <div class="pt-0.5">
                        Cochez la case <strong>Je comprends et souhaite continuer</strong>.
                    </div>
                </li>
                <li class="flex items-start gap-3">
                    <span class="w-6 h-6 rounded-full bg-[#2F3284]/10 dark:bg-indigo-500/20 text-[#2F3284] dark:text-indigo-400 font-bold flex items-center justify-center shrink-0">7</span>
                    <div class="pt-0.5">
                        Cliquez sur <strong>Créer en tant que plugin</strong>, puis acceptez l'autorisation sur la fenêtre Hubs Africa qui s'affiche pour finaliser la connexion.
                    </div>
                </li>
            </ol>
        </div>

        {{-- Onglet Claude (Bientôt) --}}
        <div x-show="tab === 'claude'" style="display: none;" class="p-6 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xs space-y-3">
            <div class="flex items-center justify-between gap-2">
                <h4 class="font-bold text-slate-900 dark:text-white text-sm">Connecteur MCP pour Claude</h4>
                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20">
                    Bientôt disponible
                </span>
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                Le guide pas-à-pas pour connecter Hubs Africa directement dans Claude sera disponible très prochainement.
            </p>
        </div>
    </div>
</x-filament-panels::page>
