<x-filament-panels::page.simple>
    <div class="relative w-full">
        {{-- Décoration d'arrière-plan avec dégradé chaud et motifs géométriques --}}
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-slate-950 via-[#191c4d] to-slate-900 p-6 sm:p-10 mb-8 border border-white/10 shadow-2xl text-white">
            {{-- Effets de halo lumineux --}}
            <div class="absolute -top-24 -right-24 w-80 h-80 bg-[#F8B81D]/25 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute -bottom-24 -left-24 w-80 h-80 bg-teal-500/20 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-96 h-48 bg-[#2F3284]/40 rounded-full blur-3xl pointer-events-none"></div>

            {{-- Trame subtile en arrière-plan --}}
            <div class="absolute inset-0 bg-[radial-gradient(rgba(255,255,255,0.08)_1px,transparent_1px)] [background-size:20px_20px] pointer-events-none opacity-60"></div>

            <div class="relative z-10 flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
                <div class="space-y-3 max-w-2xl">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 border border-white/15 text-[#F8B81D] text-xs font-semibold backdrop-blur-md">
                        <svg class="w-4 h-4 text-[#F8B81D]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        <span>Hubs Africa — Réseau des Écosystèmes d'Innovation</span>
                    </div>

                    <h1 class="text-2xl sm:text-3xl lg:text-4xl font-black tracking-tight text-white">
                        Enregistrer votre Structure d'Accompagnement (SAE)
                    </h1>

                    <p class="text-xs sm:text-sm text-slate-300 leading-relaxed font-normal">
                        Configurez l'identité, la vision stratégique, la mission et l'offre d'accompagnement de votre incubateur ou accélérateur. Un modèle de dossier de candidature et vos paliers de maturité seront automatiquement initialisés.
                    </p>
                </div>

                {{-- Badges d'engagement --}}
                <div class="hidden md:flex flex-col gap-2 shrink-0 bg-white/5 border border-white/10 rounded-2xl p-4 backdrop-blur-md text-xs text-slate-200">
                    <div class="flex items-center gap-2 text-emerald-400 font-semibold">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        Socle Panafricain Standardisé
                    </div>
                    <div class="text-[11px] text-slate-400">
                        3 étapes simples &bull; Prêt en 2 minutes
                    </div>
                </div>
            </div>
        </div>

        {{-- Conteneur du Wizard --}}
        <div class="rounded-3xl border border-slate-200/80 dark:border-slate-800 bg-white/95 dark:bg-slate-900/90 backdrop-blur-xl shadow-xl p-6 sm:p-10 transition-all">
            {{ $this->content }}
        </div>
    </div>
</x-filament-panels::page.simple>
