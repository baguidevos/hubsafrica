<div>
    <div class="space-y-8">
        {{-- Barre de Filtres et Recherche Interactive --}}
        <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-sm space-y-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3.5 items-center">

                {{-- Champ Recherche --}}
                <div class="sm:col-span-2 lg:col-span-4 relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Titre, mots-clés, critères..."
                        class="w-full pl-11 pr-4 py-3 rounded-2xl border border-slate-300 focus:border-[#F8B81D] focus:ring-4 focus:ring-[#F8B81D]/20 text-sm text-slate-900 placeholder:text-slate-400 outline-none transition"
                    >
                    <div wire:loading wire:target="search" class="absolute inset-y-0 right-0 pr-3.5 flex items-center">
                        <svg class="animate-spin h-4 w-4 text-[#2F3284]" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>
                    </div>
                </div>

                {{-- Sélecteur Structure SAE --}}
                <div class="sm:col-span-1 lg:col-span-2">
                    <select
                        wire:model.live="selectedSae"
                        class="w-full px-3.5 py-3 rounded-2xl border border-slate-300 focus:border-[#F8B81D] focus:ring-4 focus:ring-[#F8B81D]/20 text-xs sm:text-sm text-slate-800 bg-white outline-none transition"
                    >
                        <option value="all">Toutes les structures</option>
                        @foreach($participatingSaes as $saeItem)
                            <option value="{{ $saeItem->id }}">{{ $saeItem->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Filtre Secteur --}}
                <div class="sm:col-span-1 lg:col-span-2">
                    <select
                        wire:model.live="selectedSector"
                        class="w-full px-3.5 py-3 rounded-2xl border border-slate-300 focus:border-[#F8B81D] focus:ring-4 focus:ring-[#F8B81D]/20 text-xs sm:text-sm text-slate-800 bg-white outline-none transition"
                    >
                        <option value="all">Tous les secteurs</option>
                        @foreach($availableSectors as $sectorName)
                            <option value="{{ $sectorName }}">{{ $sectorName }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Filtre Zone géographique --}}
                <div class="sm:col-span-1 lg:col-span-2">
                    <select
                        wire:model.live="selectedZone"
                        class="w-full px-3.5 py-3 rounded-2xl border border-slate-300 focus:border-[#F8B81D] focus:ring-4 focus:ring-[#F8B81D]/20 text-xs sm:text-sm text-slate-800 bg-white outline-none transition"
                    >
                        <option value="all">Toutes les zones</option>
                        @foreach($availableZones as $zoneName)
                            <option value="{{ $zoneName }}">{{ $zoneName }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Filtre Statut (Ouvert vs Clôturé) --}}
                <div class="sm:col-span-1 lg:col-span-2">
                    <select
                        wire:model.live="selectedStatus"
                        class="w-full px-3.5 py-3 rounded-2xl border border-slate-300 focus:border-[#F8B81D] focus:ring-4 focus:ring-[#F8B81D]/20 text-xs sm:text-sm text-slate-800 bg-white outline-none transition font-medium"
                        title="Filtrer par statut"
                    >
                        <option value="all">Tous les statuts</option>
                        <option value="open">Ouvert</option>
                        <option value="closed">Clôturé</option>
                    </select>
                </div>
            </div>

            {{-- Filtres actifs & Reset --}}
            @if($hasActiveFilters)
                <div class="flex items-center justify-between pt-3 border-t border-slate-100 text-xs">
                    <div class="text-slate-500 font-medium">
                        Filtres appliqués &bull; <span class="font-bold text-slate-800">{{ $opportunities->total() }}</span> opportunité(s) trouvée(s)
                    </div>
                    <button
                        type="button"
                        wire:click="resetFilters"
                        class="inline-flex items-center gap-1.5 font-bold text-amber-700 hover:text-amber-800 transition cursor-pointer"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        <span>Réinitialiser les filtres</span>
                    </button>
                </div>
            @endif
        </div>

        {{-- Squelette de chargement --}}
        <div wire:loading.delay.shorter class="w-full">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 animate-pulse">
                @for($idx = 0; $idx < 6; $idx++)
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

        {{-- Grille des Opportunités --}}
        <div wire:loading.remove class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @forelse($opportunities as $opp)
                @php
                    $isClosed = $opp->isClosed();
                    $remainingDays = $opp->daysRemaining();
                    $plainDetails = strip_tags($opp->details);
                    $excerpt = \Illuminate\Support\Str::limit($plainDetails, 130);
                @endphp

                <article class="rounded-3xl shadow-xs transition-all duration-300 flex flex-col justify-between overflow-hidden group hover:-translate-y-1 {{ $isClosed ? 'bg-red-50/20 border-2 border-red-300 hover:border-red-500 hover:shadow-red-100' : 'bg-white border border-slate-200/90 hover:border-[#2F3284]/30 hover:shadow-xl' }}">
                    <div>
                        {{-- Image d'illustration de l'opportunité si définie (visible en entier + clic plein écran) --}}
                        @if($opp->image_url)
                            <div
                                class="relative h-48 w-full overflow-hidden bg-slate-950 group/img cursor-pointer"
                                x-data
                                data-image-url="{{ $opp->image_url }}"
                                data-image-title="{{ $opp->title }}"
                                @click.prevent.stop="$dispatch('open-image-lightbox', { url: $el.dataset.imageUrl, title: $el.dataset.imageTitle })"
                                role="button"
                                tabindex="0"
                                @keydown.enter.prevent.stop="$dispatch('open-image-lightbox', { url: $el.dataset.imageUrl, title: $el.dataset.imageTitle })"
                                title="Cliquer pour afficher l'affiche en plein écran"
                            >
                                {{-- Halo d'ambiance flouté --}}
                                <img
                                    src="{{ $opp->image_url }}"
                                    alt=""
                                    class="absolute inset-0 w-full h-full object-cover filter blur-md scale-110 opacity-40 pointer-events-none select-none {{ $isClosed ? 'grayscale-40' : '' }}"
                                    aria-hidden="true"
                                >

                                {{-- Image principale centrée visible en entier sans rognage --}}
                                <img
                                    src="{{ $opp->image_url }}"
                                    alt="{{ $opp->title }}"
                                    class="relative z-10 w-full h-full object-contain p-1.5 transition-transform duration-500 group-hover/img:scale-105 {{ $isClosed ? 'grayscale-30' : '' }}"
                                >

                                <div class="absolute inset-0 z-10 {{ $isClosed ? 'bg-red-950/40 mix-blend-multiply' : 'bg-gradient-to-t from-slate-950/80 via-transparent to-transparent' }} pointer-events-none"></div>

                                <div class="absolute top-3 left-3 right-3 z-20 flex items-center justify-between gap-2 pointer-events-none">
                                    @if($isClosed)
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-red-600 text-white shadow-md animate-pulse pointer-events-auto">
                                            <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            Clôturé
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-white/95 text-emerald-700 backdrop-blur-md shadow-xs pointer-events-auto">
                                            <span class="relative flex h-2 w-2">
                                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                                <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                                            </span>
                                            Ouvert
                                        </span>
                                    @endif

                                    @if($opp->deadline)
                                        <span class="text-xs font-bold text-white bg-slate-900/80 px-2.5 py-1 rounded-lg backdrop-blur-md pointer-events-auto">
                                            @if($remainingDays === 0)
                                                Clôture ce jour !
                                            @elseif($remainingDays !== null)
                                                J-{{ $remainingDays }}
                                            @endif
                                        </span>
                                    @endif
                                </div>

                                {{-- Bouton d'agrandissement en survol --}}
                                <div class="absolute bottom-2.5 right-2.5 z-20 opacity-0 group-hover/img:opacity-100 transition-opacity">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-950/85 text-white text-[11px] font-semibold backdrop-blur-md border border-white/20 shadow-md">
                                        <svg class="w-3.5 h-3.5 text-[#F8B81D]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v6m3-3H7"/>
                                        </svg>
                                        Agrandir
                                    </span>
                                </div>
                            </div>
                        @endif
                                        <span class="text-xs font-bold px-2.5 py-1 rounded-lg {{ $isClosed ? 'bg-red-950/80 text-red-200 border border-red-500' : 'bg-slate-900/80 text-white backdrop-blur-md' }}">
                                            @if($isClosed)
                                                Échu
                                            @elseif($remainingDays === 0)
                                                Clôture ce jour !
                                            @elseif($remainingDays !== null)
                                                J-{{ $remainingDays }}
                                            @endif
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @endif

                        {{-- En-tête de carte : badge statut & SAE --}}
                        <div class="p-6 pb-4 border-b {{ $isClosed ? 'border-red-100 bg-red-50/60' : 'border-slate-100 bg-gradient-to-br from-slate-50 to-white' }}">
                            @if(!$opp->image_url)
                                <div class="flex items-center justify-between gap-2 mb-3">
                                    {{-- Statut : En ROUGE vif quand clôturé --}}
                                    @if($isClosed)
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-red-100 text-red-700 border border-red-300 animate-pulse">
                                            <svg class="w-3.5 h-3.5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            Clôturé
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <span class="relative flex h-2 w-2">
                                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                                <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                                            </span>
                                            Candidatures ouvertes
                                        </span>
                                    @endif

                                    {{-- Jours restants ou date limite --}}
                                    @if($opp->deadline)
                                        <span class="text-xs font-semibold {{ $isClosed ? 'text-red-600 font-bold' : ($remainingDays !== null && $remainingDays <= 3 ? 'text-amber-600' : 'text-slate-500') }}">
                                            @if($isClosed)
                                                Échu le {{ $opp->deadline->translatedFormat('d M Y') }}
                                            @elseif($remainingDays === 0)
                                                Clôture ce jour !
                                            @elseif($remainingDays !== null)
                                                J-{{ $remainingDays }}
                                            @endif
                                        </span>
                                    @endif
                                </div>
                            @endif

                            {{-- SAE organisatrice --}}
                            @if($opp->sae)
                                <div class="flex items-center gap-2 mb-2">
                                    <div class="w-6 h-6 rounded-lg bg-[#2F3284]/10 text-[#2F3284] flex items-center justify-center text-xs font-bold">
                                        {{ mb_substr($opp->sae->name, 0, 1) }}
                                    </div>
                                    <a href="{{ route('public.structure.detail', $opp->sae->id) }}" class="text-xs font-semibold text-slate-600 hover:text-[#2F3284] truncate">
                                        {{ $opp->sae->name }}
                                    </a>
                                </div>
                            @endif

                            {{-- Titre de l'opportunité --}}
                            <h3 class="text-lg font-bold text-slate-900 group-hover:text-[#2F3284] transition line-clamp-2">
                                <a href="{{ route('public.opportunities.detail', $opp->slug) }}">
                                    {{ $opp->title }}
                                </a>
                            </h3>
                        </div>

                        {{-- Corps : Secteurs, Zones & Résumé --}}
                        <div class="p-6 space-y-4">
                            {{-- Secteurs ciblés --}}
                            @if(!empty($opp->sectors))
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach(array_slice($opp->sectors, 0, 3) as $sectorTag)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-[11px] font-medium bg-[#2F3284]/10 text-[#2F3284]">
                                            {{ $sectorTag }}
                                        </span>
                                    @endforeach
                                    @if(count($opp->sectors) > 3)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[11px] font-medium bg-slate-100 text-slate-600">
                                            +{{ count($opp->sectors) - 3 }}
                                        </span>
                                    @endif
                                </div>
                            @endif

                            {{-- Zones concernées --}}
                            @if(!empty($opp->target_zones))
                                <div class="flex items-center gap-1.5 text-xs text-slate-600">
                                    <svg class="w-4 h-4 text-[#F8B81D] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    <span class="truncate font-medium">
                                        {{ implode(', ', array_slice($opp->target_zones, 0, 3)) }}
                                        @if(count($opp->target_zones) > 3)
                                            (+{{ count($opp->target_zones) - 3 }})
                                        @endif
                                    </span>
                                </div>
                            @endif

                            {{-- Détails synthétiques --}}
                            @if($excerpt)
                                <p class="text-xs text-slate-600 leading-relaxed line-clamp-3">
                                    {{ $excerpt }}
                                </p>
                            @endif
                        </div>
                    </div>

                    {{-- Pied de carte : Date limite & Actions --}}
                    <div class="p-6 pt-0 space-y-3">
                        {{-- Mention date limite explicite --}}
                        <div class="py-2.5 px-3 rounded-xl {{ $isClosed ? 'bg-red-100/70 border border-red-200 text-red-700' : 'bg-slate-50 border border-slate-100 text-slate-700' }} flex items-center justify-between text-xs">
                            <span class="font-medium {{ $isClosed ? 'text-red-700' : 'text-slate-500' }}">Deadline</span>
                            <span class="font-bold {{ $isClosed ? 'text-red-700' : 'text-slate-900' }}">
                                {{ $opp->deadline ? $opp->deadline->translatedFormat('d M Y à H:i') : 'Non précisée' }}
                            </span>
                        </div>

                        {{-- Boutons d'action avec partage direct --}}
                        <div class="grid grid-cols-12 gap-2" x-data="{ copied: false }">
                            <a
                                href="{{ route('public.opportunities.detail', $opp->slug) }}"
                                class="col-span-4 inline-flex items-center justify-center gap-1 px-2.5 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs transition"
                            >
                                <span>Détails</span>
                            </a>

                            {{-- Bouton Copier le lien de partage direct --}}
                            <button
                                type="button"
                                @click="navigator.clipboard.writeText('{{ $opp->share_url }}'); copied = true; setTimeout(() => copied = false, 2000)"
                                class="col-span-3 inline-flex items-center justify-center gap-1 px-2 py-2.5 rounded-xl border border-slate-200 bg-slate-50 hover:bg-slate-100 text-slate-700 font-semibold text-xs transition"
                                title="Copier le lien direct vers cette opportunité"
                            >
                                <svg x-show="!copied" class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                                </svg>
                                <span x-show="!copied" class="text-[11px]">Partager</span>
                                <span x-show="copied" class="text-[11px] font-bold text-emerald-600" style="display: none;">Copié !</span>
                            </button>

                            {{-- Bouton Postuler / Clôturé --}}
                            <div class="col-span-5 flex">
                                @if($isClosed)
                                    <span
                                        class="w-full inline-flex items-center justify-center gap-1 px-2 py-2.5 rounded-xl bg-red-100 text-red-700 font-bold text-xs border border-red-300 cursor-not-allowed text-center"
                                        title="Les candidatures pour cette opportunité sont désormais clôturées."
                                    >
                                        <span>Clôturé</span>
                                    </span>
                                @elseif($opp->apply_url)
                                    <a
                                        href="{{ $opp->apply_url }}"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="w-full inline-flex items-center justify-center gap-1 px-2 py-2.5 rounded-xl bg-[#F8B81D] hover:bg-[#e5a510] text-slate-950 font-bold text-xs shadow-xs hover:shadow transition transform hover:-translate-y-0.5 text-center"
                                    >
                                        <span>Postuler</span>
                                        <svg class="w-3.5 h-3.5 text-slate-950 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                        </svg>
                                    </a>
                                @else
                                    <a
                                        href="{{ route('public.opportunities.detail', $opp->slug) }}"
                                        class="w-full inline-flex items-center justify-center gap-1 px-2 py-2.5 rounded-xl bg-[#2F3284] hover:bg-[#25286a] text-white font-bold text-xs shadow-xs transition text-center"
                                    >
                                        <span>Consulter</span>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </article>
            @empty
                <div class="col-span-full bg-white rounded-3xl border border-slate-200/80 p-12 text-center space-y-4">
                    <div class="w-16 h-16 rounded-2xl bg-amber-50 text-[#F8B81D] mx-auto flex items-center justify-center">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                    </div>
                    <div class="space-y-1">
                        <h4 class="text-base font-bold text-slate-900">Aucune opportunité ne correspond à vos critères</h4>
                        <p class="text-xs text-slate-500 max-w-md mx-auto">
                            Essayez d'ajuster ou de réinitialiser vos filtres de recherche pour afficher tous les appels à projets disponibles.
                        </p>
                    </div>
                    <div>
                        <button
                            type="button"
                            wire:click="resetFilters"
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-[#2F3284] hover:bg-[#25286a] text-white font-bold text-xs transition"
                        >
                            <span>Voir toutes les opportunités</span>
                        </button>
                    </div>
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        @if($opportunities->hasPages())
            <div class="pt-4">
                {{ $opportunities->links() }}
            </div>
        @endif
    </div>
</div>
