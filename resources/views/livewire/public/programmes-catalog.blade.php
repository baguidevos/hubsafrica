<div>
    <div class="space-y-8">
        {{-- Barre de Filtres et Recherche Interactive --}}
        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-sm space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-center">
                
                {{-- Champ Recherche --}}
                <div class="md:col-span-5 relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Rechercher par titre, mot-clé, SAE..."
                        class="w-full pl-11 pr-4 py-3 rounded-2xl border border-slate-300 focus:border-emerald-600 focus:ring-4 focus:ring-emerald-600/10 text-sm text-slate-900 placeholder:text-slate-400 outline-none transition"
                    >
                    <div wire:loading wire:target="search" class="absolute inset-y-0 right-0 pr-3.5 flex items-center">
                        <svg class="animate-spin h-4 w-4 text-emerald-600" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                    </div>
                </div>

                {{-- Sélecteur de SAE organisatrice --}}
                <div class="md:col-span-3">
                    <select
                        wire:model.live="selectedSae"
                        class="w-full px-4 py-3 rounded-2xl border border-slate-300 focus:border-emerald-600 focus:ring-4 focus:ring-emerald-600/10 text-sm text-slate-800 bg-white outline-none transition"
                    >
                        <option value="all">Toutes les structures (SAE)</option>
                        @foreach($saes as $s)
                            <option value="{{ $s->id }}">{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Filtre Statut d'inscriptions --}}
                <div class="md:col-span-2">
                    <select
                        wire:model.live="selectedStatus"
                        class="w-full px-4 py-3 rounded-2xl border border-slate-300 focus:border-emerald-600 focus:ring-4 focus:ring-emerald-600/10 text-sm text-slate-800 bg-white outline-none transition"
                    >
                        <option value="all">Tous les statuts</option>
                        <option value="open">Candidatures Ouvertes</option>
                        <option value="closed">Clôturés / En cours</option>
                    </select>
                </div>

                {{-- Filtre Phase / Niveau --}}
                <div class="md:col-span-2">
                    <select
                        wire:model.live="selectedLevel"
                        class="w-full px-4 py-3 rounded-2xl border border-slate-300 focus:border-emerald-600 focus:ring-4 focus:ring-emerald-600/10 text-sm text-slate-800 bg-white outline-none transition"
                    >
                        <option value="all">Toutes les phases</option>
                        @foreach($levels as $lvl)
                            <option value="{{ $lvl->id }}">{{ $lvl->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Filtres actifs & Bouton #[Js] Reset --}}
            @if($search !== '' || $selectedSae !== 'all' || $selectedStatus !== 'all' || $selectedLevel !== 'all')
                <div class="flex items-center justify-between pt-3 border-t border-slate-100 text-xs">
                    <div class="text-slate-500">
                        Filtres appliqués &bull; {{ $programs->total() }} programme(s) trouvé(s)
                    </div>
                    <button
                        type="button"
                        wire:click="resetFilters"
                        class="inline-flex items-center gap-1.5 font-bold text-amber-700 hover:text-amber-800 transition"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        <span>Réinitialiser les filtres</span>
                    </button>
                </div>
            @endif
        </div>

        {{-- État de chargement skeleton réactif lors du filtrage/recherche --}}
        <div wire:loading.delay.shorter class="w-full">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 animate-pulse">
                @for($i = 0; $i < 6; $i++)
                    <div class="bg-white rounded-3xl border border-slate-200/90 p-6 space-y-5">
                        <div class="flex justify-between items-center">
                            <div class="h-5 bg-slate-200 rounded-full w-28"></div>
                            <div class="h-5 bg-slate-200 rounded-md w-20"></div>
                        </div>
                        <div class="h-6 bg-slate-200 rounded-xl w-3/4"></div>
                        <div class="h-4 bg-slate-200 rounded w-1/2"></div>
                        <div class="space-y-2 pt-4 border-t border-slate-100">
                            <div class="h-3 bg-slate-200 rounded w-full"></div>
                            <div class="h-3 bg-slate-200 rounded w-5/6"></div>
                        </div>
                        <div class="pt-4 flex justify-between items-center">
                            <div class="h-4 bg-slate-200 rounded w-24"></div>
                            <div class="h-9 bg-slate-200 rounded-xl w-28"></div>
                        </div>
                    </div>
                @endfor
            </div>
        </div>

        {{-- Grille des Programmes d'Accompagnement --}}
        <div wire:loading.remove class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @forelse($programs as $p)
                @php
                    $now = now()->toDateString();
                    $isOpen = $p->status === \App\Enums\ProgramStatus::ACTIF && (!$p->registration_end || $p->registration_end->toDateString() >= $now);
                    $daysRemaining = $p->registration_end ? now()->diffInDays($p->registration_end, false) : null;
                @endphp

                <article class="bg-white rounded-3xl border border-slate-200/90 shadow-xs hover:shadow-xl transition-all duration-300 flex flex-col justify-between overflow-hidden group hover:-translate-y-1">
                    {{-- Bandeau supérieur & Badges --}}
                    <div>
                        <div class="p-6 pb-4 border-b border-slate-100 bg-gradient-to-br from-slate-50 to-white">
                            <div class="flex items-center justify-between gap-2 mb-3">
                                {{-- Statut Inscriptions --}}
                                @if($isOpen)
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                        Candidatures Ouvertes
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold bg-slate-100 text-slate-600 border border-slate-200">
                                        Clôturé / En cours
                                    </span>
                                @endif

                                {{-- Phase / Niveau --}}
                                @if($p->level)
                                    <span class="text-[10px] font-bold uppercase tracking-wider px-2.5 py-1 bg-amber-50 text-amber-800 border border-amber-200 rounded-lg">
                                        {{ $p->level->name }}
                                    </span>
                                @endif
                            </div>

                            {{-- Titre & Structure Organisatrice --}}
                            <h3 class="text-lg font-extrabold text-slate-900 group-hover:text-emerald-700 transition line-clamp-1">
                                <a href="{{ route('public.programme.detail', $p->id) }}">
                                    {{ $p->name }}
                                </a>
                            </h3>

                            <div class="flex items-center gap-2 mt-2 text-xs text-slate-600 font-medium">
                                <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                <span class="truncate">{{ $p->sae?->name ?: 'Hub Africa Régional' }}</span>
                            </div>
                        </div>

                        {{-- Corps descriptif --}}
                        <div class="p-6 space-y-4">
                            <p class="text-xs text-slate-600 line-clamp-3 leading-relaxed">
                                {{ $p->description ?: 'Programme d\'accompagnement entrepreneurial combinant mentorat individuel, ateliers pratiques et mise en relation investisseurs.' }}
                            </p>

                            {{-- Métadonnées de dates et capacité --}}
                            <div class="grid grid-cols-2 gap-3 pt-2 text-[11px] text-slate-600">
                                <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-200/60">
                                    <div class="text-[10px] text-slate-400 uppercase font-semibold">Date limite</div>
                                    <div class="font-bold text-slate-800 mt-0.5">
                                        {{ $p->registration_end ? $p->registration_end->format('d/m/Y') : 'À déterminer' }}
                                    </div>
                                    @if($isOpen && $daysRemaining !== null)
                                        <div class="text-[9px] text-emerald-700 font-bold mt-0.5">
                                            @if($daysRemaining == 0)
                                                Dernier jour !
                                            @else
                                                Reste {{ $daysRemaining }} j.
                                            @endif
                                        </div>
                                    @endif
                                </div>

                                <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-200/60">
                                    <div class="text-[10px] text-slate-400 uppercase font-semibold">Capacité promotion</div>
                                    <div class="font-bold text-slate-800 mt-0.5">
                                        {{ $p->capacity ?: '20' }} startups
                                    </div>
                                    <div class="text-[9px] text-slate-400 mt-0.5">
                                        {{ $p->cohorts_count }} cohorte(s)
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Boutons d'action --}}
                    <div class="p-6 pt-0 flex items-center justify-between gap-3">
                        <a
                            href="{{ route('public.programme.detail', $p->id) }}"
                            class="flex-1 text-center py-2.5 px-3 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-100 text-xs font-bold transition"
                        >
                            Détails &amp; Critères
                        </a>

                        @if($isOpen)
                            <a
                                href="{{ route('public.programme.candidater', $p->id) }}"
                                class="flex-1 text-center py-2.5 px-3 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-bold shadow-sm transition"
                            >
                                Candidater &rarr;
                            </a>
                        @else
                            <button
                                disabled
                                class="flex-1 text-center py-2.5 px-3 rounded-xl bg-slate-100 text-slate-400 text-xs font-medium cursor-not-allowed"
                            >
                                Clôturé
                            </button>
                        @endif
                    </div>
                </article>
            @empty
                <div class="col-span-full bg-white rounded-3xl p-12 text-center border border-dashed border-slate-300 space-y-4">
                    <div class="w-16 h-16 rounded-full bg-slate-100 flex items-center justify-center mx-auto text-slate-400">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900">Aucun programme ne correspond à vos critères</h3>
                    <p class="text-sm text-slate-500 max-w-md mx-auto">
                        Essayez de modifier vos mots-clés ou réinitialisez les filtres pour consulter tous les programmes d'accompagnement disponibles.
                    </p>
                    <button
                        type="button"
                        wire:click="resetFilters"
                        class="px-5 py-2.5 rounded-xl bg-emerald-700 text-white text-xs font-bold shadow hover:bg-emerald-800 transition"
                    >
                        Réinitialiser tous les filtres
                    </button>
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        <div class="pt-6">
            {{ $programs->links() }}
        </div>
    </div>
</div>
