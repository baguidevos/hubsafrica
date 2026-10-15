<x-layouts.public>
    <section class="bg-slate-950 text-white py-12 sm:py-16 border-b border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#F8B81D]/10 border border-[#F8B81D]/30 text-xs font-bold text-[#F8B81D]">
                <span>🧪 Espace Développement &amp; Recette</span>
            </div>

            <h1 class="text-3xl sm:text-4xl font-black text-white tracking-tight">
                Comptes de Test &amp; Accès Rapide aux Panels
            </h1>

            <p class="text-sm text-slate-300 max-w-3xl leading-relaxed">
                Retrouvez ci-dessous l'ensemble des comptes de démonstration pré-configurés pour tester chaque rôle de l'écosystème Hub Africa. Utilisez le bouton <strong>« Connexion 1 Clic »</strong> pour vous authentifier instantanément sans ressaisir vos identifiants.
            </p>

            @if(session('success'))
                <div class="p-4 rounded-xl bg-emerald-950/80 border border-emerald-600/50 text-emerald-200 text-xs flex items-center justify-between">
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="p-4 rounded-xl bg-red-950/80 border border-red-600/50 text-red-200 text-xs flex items-center justify-between">
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            {{-- Statut de la session en cours --}}
            <div class="pt-2">
                @if($currentUser)
                    <div class="p-4 rounded-2xl bg-slate-900 border border-slate-700/80 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <div class="relative flex h-3 w-3 shrink-0">
                                <div class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></div>
                                <div class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></div>
                            </div>
                            <div class="text-xs">
                                <span class="text-slate-400">Session active :</span>
                                <strong class="text-white ml-1">{{ $currentUser->name }}</strong>
                                <span class="text-slate-400 font-mono">({{ $currentUser->email }})</span>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <a
                                href="{{ route('dev.logout') }}"
                                class="px-3.5 py-1.5 rounded-lg border border-red-500/40 hover:bg-red-950/50 text-red-400 text-xs font-bold transition"
                            >
                                Déconnexion
                            </a>
                        </div>
                    </div>
                @else
                    <div class="p-3.5 rounded-xl bg-slate-900/60 border border-slate-800 text-xs text-slate-400 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-slate-500"></span>
                        <span>Aucune session active actuellement. Cliquez sur un profil ci-dessous pour démarrer une session.</span>
                    </div>
                @endif
            </div>
        </div>
    </section>

    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 space-y-12">
        {{-- Grille des comptes par rôle --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($accounts as $key => $acc)
                @php
                    $isCurrent = $currentUser && $currentUser->email === $acc['email'];
                @endphp
                <div class="bg-white rounded-3xl p-6 border {{ $isCurrent ? 'border-2 border-[#F8B81D] shadow-lg ring-4 ring-[#F8B81D]/10' : 'border-slate-200 shadow-xs hover:shadow-md' }} transition flex flex-col justify-between space-y-5">
                    <div class="space-y-4">
                        {{-- En-tête de carte --}}
                        <div class="flex items-start justify-between gap-2">
                            <div class="flex items-center gap-3">
                                <div class="w-11 h-11 rounded-2xl bg-[#2F3284] text-[#F8B81D] font-black text-sm flex items-center justify-center shrink-0">
                                    {{ substr($acc['name'], 0, 2) }}
                                </div>
                                <div>
                                    <h2 class="text-base font-bold text-slate-900 leading-tight">
                                        {{ $acc['name'] }}
                                    </h2>
                                    <span class="text-xs font-semibold text-slate-500">
                                        {{ $acc['role'] }}
                                    </span>
                                </div>
                            </div>

                            @if($isCurrent)
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold uppercase bg-emerald-100 text-emerald-800">
                                    Connecté
                                </span>
                            @endif
                        </div>

                        <p class="text-xs text-slate-600 leading-relaxed min-h-[36px]">
                            {{ $acc['description'] }}
                        </p>

                        {{-- Détails d'accès --}}
                        <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-2 text-xs">
                            <div class="flex items-center justify-between">
                                <span class="text-slate-400 text-[11px]">Espace / Panel :</span>
                                <strong class="text-slate-800 font-bold">{{ $acc['panel_name'] }}</strong>
                            </div>

                            @if($acc['sae_name'])
                                <div class="flex items-center justify-between">
                                    <span class="text-slate-400 text-[11px]">Structure / SAE :</span>
                                    <span class="text-slate-700 font-medium truncate max-w-[170px]" title="{{ $acc['sae_name'] }}">
                                        {{ $acc['sae_name'] }}
                                    </span>
                                </div>
                            @endif

                            <div class="flex items-center justify-between pt-1 border-t border-slate-200">
                                <span class="text-slate-400 text-[11px]">Email :</span>
                                <div class="flex items-center gap-1.5">
                                    <span class="font-mono text-slate-800 font-semibold text-[11px]">{{ $acc['email'] }}</span>
                                    <button
                                        type="button"
                                        onclick="navigator.clipboard.writeText('{{ $acc['email'] }}')"
                                        title="Copier l'email"
                                        class="text-slate-400 hover:text-slate-700 text-[10px]"
                                    >
                                        📋
                                    </button>
                                </div>
                            </div>

                            <div class="flex items-center justify-between">
                                <span class="text-slate-400 text-[11px]">Mot de passe :</span>
                                <div class="flex items-center gap-1.5">
                                    <code class="font-mono font-bold text-slate-800 text-[11px] bg-slate-200 px-1.5 py-0.5 rounded">{{ $acc['password'] }}</code>
                                    <button
                                        type="button"
                                        onclick="navigator.clipboard.writeText('{{ $acc['password'] }}')"
                                        title="Copier le mot de passe"
                                        class="text-slate-400 hover:text-slate-700 text-[10px]"
                                    >
                                        📋
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="space-y-2 pt-2 border-t border-slate-100">
                        <a
                            href="{{ route('dev.login', ['as' => $key]) }}"
                            class="w-full py-2.5 px-4 rounded-xl bg-[#F8B81D] hover:bg-[#e5a510] text-slate-950 font-extrabold text-xs shadow-md transition flex items-center justify-center gap-2 transform hover:-translate-y-0.5"
                        >
                            <span>⚡ Connexion 1 Clic ({{ $acc['panel_name'] }})</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </a>

                        <div class="flex items-center justify-between text-[11px] px-1 text-slate-500">
                            <span>Formulaire classique :</span>
                            <a href="{{ $acc['login_url'] }}" class="text-[#2F3284] hover:underline font-semibold">
                                Aller sur {{ $acc['login_url'] }}
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Aide technique pour re-seeder --}}
        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-xs space-y-4">
            <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                <span>⚙️ Commandes Utiles pour Réinitialiser l'Environnement de Démo</span>
            </h3>
            <p class="text-xs text-slate-600 leading-relaxed">
                Si des comptes sont modifiés ou que vous souhaitez réinitialiser l'ensemble des données d'accompagnement (programmes, cohortes, coachs, candidatures et livrables) :
            </p>
            <div class="p-3.5 rounded-2xl bg-slate-950 font-mono text-xs text-slate-200 space-y-1">
                <div class="text-slate-400"># Réinitialiser et recharger tous les comptes de test :</div>
                <div class="text-[#F8B81D] font-bold">php artisan migrate:fresh --seed</div>
            </div>
        </div>
    </section>
</x-layouts.public>
