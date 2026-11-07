<x-layouts.public>
    @php
        $isClosed = $opportunity->isClosed();
        $remainingDays = $opportunity->daysRemaining();
        $sae = $opportunity->sae;
    @endphp

    {{-- Fil d'ariane & En-tête --}}
    <section class="{{ $isClosed ? 'bg-red-950/90 border-b border-red-900' : 'bg-slate-900 border-b border-slate-800' }} text-white py-10 transition">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
            {{-- Breadcrumb --}}
            <nav class="flex items-center gap-2 text-xs {{ $isClosed ? 'text-red-200' : 'text-slate-400' }}">
                <a href="{{ route('home') }}" class="hover:text-[#F8B81D]">Accueil</a>
                <span>/</span>
                <a href="{{ route('public.opportunities') }}" class="hover:text-[#F8B81D]">Opportunités</a>
                <span>/</span>
                <span class="text-white truncate">{{ $opportunity->title }}</span>
            </nav>

            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 pt-2">
                <div class="space-y-3">
                    <div class="flex flex-wrap items-center gap-3">
                        {{-- Statut : En ROUGE vif quand clôturé --}}
                        @if($isClosed)
                            <span class="inline-flex items-center gap-1.5 px-3.5 py-1 rounded-full text-xs font-bold bg-red-600 text-white border border-red-500 shadow-sm animate-pulse">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Clôturé — Candidatures closes
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-3.5 py-1 rounded-full text-xs font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-400/40">
                                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                                Candidatures Ouvertes
                            </span>
                        @endif

                        @if($opportunity->deadline)
                            <span class="text-xs font-semibold px-3 py-1 rounded-lg {{ $isClosed ? 'bg-red-900/60 text-red-200 border border-red-800 font-bold' : 'bg-slate-800 text-slate-300 border border-slate-700' }}">
                                @if($isClosed)
                                    Échéance dépassée le {{ $opportunity->deadline->translatedFormat('d F Y à H:i') }}
                                @elseif($remainingDays === 0)
                                    Clôture aujourd'hui ({{ $opportunity->deadline->translatedFormat('H:i') }})
                                @elseif($remainingDays !== null)
                                    Clôture dans {{ $remainingDays }} jour(s) ({{ $opportunity->deadline->translatedFormat('d F Y') }})
                                @endif
                            </span>
                        @endif
                    </div>

                    <h1 class="text-2xl sm:text-4xl font-black text-white tracking-tight">
                        {{ $opportunity->title }}
                    </h1>

                    @if($sae)
                        <p class="text-xs sm:text-sm {{ $isClosed ? 'text-red-200' : 'text-slate-400' }} flex items-center gap-2">
                            <span>Publié par la structure</span>
                            <a href="{{ route('public.structure.detail', $sae->id) }}" class="text-white font-bold hover:text-[#F8B81D] underline decoration-slate-600 hover:decoration-[#F8B81D]">
                                {{ $sae->name }}
                            </a>
                            @if($sae->city || $sae->country)
                                <span>&bull; {{ $sae->city }}{{ $sae->city && $sae->country ? ', ' : '' }}{{ $sae->country }}</span>
                            @endif
                        </p>
                    @endif
                </div>

                {{-- Boutons d'Action Rapide en En-tête --}}
                <div class="flex flex-wrap items-center gap-3 shrink-0">
                    @if($isClosed)
                        <div class="px-5 py-3 rounded-xl bg-red-600/30 border border-red-500 text-red-100 text-xs font-bold flex items-center gap-2">
                            <svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            <span>Appel à projet clôturé</span>
                        </div>
                    @elseif($opportunity->apply_url)
                        <a
                            href="{{ $opportunity->apply_url }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="px-6 py-3.5 rounded-xl bg-[#F8B81D] hover:bg-[#e5a510] text-slate-950 font-bold text-xs sm:text-sm shadow-md transition transform hover:-translate-y-0.5 flex items-center justify-center gap-2"
                        >
                            <span>Postuler Maintenant</span>
                            <svg class="w-4 h-4 text-slate-950" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                            </svg>
                        </a>
                    @endif

                    @if($opportunity->learn_more_url)
                        <a
                            href="{{ $opportunity->learn_more_url }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="px-5 py-3.5 rounded-xl border {{ $isClosed ? 'border-red-700 bg-red-900/40 text-red-100 hover:bg-red-900/60' : 'border-slate-700 bg-slate-800 text-slate-200 hover:bg-slate-700' }} font-bold text-xs sm:text-sm transition flex items-center justify-center gap-2"
                        >
                            <span>En savoir plus</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                            </svg>
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </section>

    {{-- Bannière d'Avertissement Clôture si l'opportunité est échue --}}
    @if($isClosed)
        <div class="bg-red-600 text-white py-3 px-4 shadow-sm border-b border-red-700">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between gap-4 text-xs sm:text-sm font-semibold">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-white shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <span>
                        <strong>Attention :</strong> Cette opportunité a atteint sa date limite de dépôt le {{ $opportunity->deadline ? $opportunity->deadline->translatedFormat('d F Y à H:i') : 'indéterminée' }}. Les nouvelles candidatures ne sont plus acceptées.
                    </span>
                </div>
                <a href="{{ route('public.opportunities') }}" class="underline text-red-100 hover:text-white shrink-0">
                    Découvrir d'autres opportunités &rarr;
                </a>
            </div>
        </div>
    @endif

    {{-- Corps de la fiche détaillée (2 colonnes) --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

            {{-- Colonne Gauche : Détails, Secteurs, Zones & Description (8 cols) --}}
            <div class="lg:col-span-8 space-y-8">

                {{-- Fiche Principale : Description & Cahier des charges --}}
                <div class="bg-white rounded-3xl p-8 border {{ $isClosed ? 'border-red-200 shadow-sm' : 'border-slate-200/90 shadow-xs' }} space-y-6">
                    <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                        <h2 class="text-xl font-bold text-slate-900 flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full {{ $isClosed ? 'bg-red-500' : 'bg-[#2F3284]' }}"></span>
                            Détails &amp; Modalités de l'Opportunité
                        </h2>
                        @if($isClosed)
                            <span class="px-3 py-1 rounded-full text-xs font-bold bg-red-100 text-red-700 border border-red-300">
                                Clôturé
                            </span>
                        @endif
                    </div>

                    {{-- Contenu éditorial riche --}}
                    <div class="prose max-w-none text-sm text-slate-700 leading-relaxed space-y-4">
                        {!! $opportunity->details !!}
                    </div>
                </div>

                {{-- Secteurs & Zones géographiques --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    {{-- Secteurs ciblés --}}
                    <div class="bg-white rounded-3xl p-6 border border-slate-200/90 shadow-xs space-y-3">
                        <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                            <svg class="w-4 h-4 text-[#2F3284]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                            </svg>
                            Secteurs Concernés
                        </h3>
                        @if(!empty($opportunity->sectors))
                            <div class="flex flex-wrap gap-2">
                                @foreach($opportunity->sectors as $sector)
                                    <span class="inline-flex items-center px-3 py-1 rounded-xl text-xs font-semibold bg-[#2F3284]/10 text-[#2F3284]">
                                        {{ $sector }}
                                    </span>
                                @endforeach
                            </div>
                        @else
                            <p class="text-xs text-slate-500 italic">Tous secteurs d'activité éligibles.</p>
                        @endif
                    </div>

                    {{-- Zones géographiques concernées --}}
                    <div class="bg-white rounded-3xl p-6 border border-slate-200/90 shadow-xs space-y-3">
                        <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                            <svg class="w-4 h-4 text-[#F8B81D]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Zones Géographiques
                        </h3>
                        @if(!empty($opportunity->target_zones))
                            <div class="flex flex-wrap gap-2">
                                @foreach($opportunity->target_zones as $zone)
                                    <span class="inline-flex items-center px-3 py-1 rounded-xl text-xs font-semibold bg-amber-50 text-amber-800 border border-amber-200">
                                        {{ $zone }}
                                    </span>
                                @endforeach
                            </div>
                        @else
                            <p class="text-xs text-slate-500 italic">Panafricain / Sans restriction géographique.</p>
                        @endif
                    </div>
                </div>

            </div>

            {{-- Colonne Droite : Synthèse Échéance & Structure (4 cols) --}}
            <div class="lg:col-span-4 space-y-6">

                {{-- Encadré Candidature & Échéance --}}
                <div class="rounded-3xl p-6 {{ $isClosed ? 'bg-red-50/80 border-2 border-red-300' : 'bg-white border border-slate-200/90 shadow-xs' }} space-y-5">
                    <div class="space-y-2">
                        <div class="text-xs font-bold uppercase tracking-wider {{ $isClosed ? 'text-red-700' : 'text-slate-400' }}">
                            Statut de la Candidature
                        </div>

                        @if($isClosed)
                            <div class="p-3 rounded-2xl bg-red-100 border border-red-300 text-red-800 space-y-1">
                                <div class="font-bold text-sm flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Candidatures Clôturées
                                </div>
                                <div class="text-xs text-red-700">
                                    Cet appel à projets est désormais clos depuis le {{ $opportunity->deadline ? $opportunity->deadline->translatedFormat('d F Y à H:i') : 'indéterminée' }}.
                                </div>
                            </div>
                        @else
                            <div class="p-3 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 space-y-1">
                                <div class="font-bold text-sm flex items-center gap-1.5">
                                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                                    Candidatures Ouvertes
                                </div>
                                <div class="text-xs text-emerald-700">
                                    {{ $opportunity->getDeadlineStatusLabel() }}
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- Détail Date Limite --}}
                    <div class="pt-3 border-t {{ $isClosed ? 'border-red-200' : 'border-slate-100' }} space-y-2 text-xs">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500 font-medium">Date limite de dépôt</span>
                            <span class="font-bold {{ $isClosed ? 'text-red-700' : 'text-slate-900' }}">
                                {{ $opportunity->deadline ? $opportunity->deadline->translatedFormat('d F Y à H:i') : 'Non précisée' }}
                            </span>
                        </div>
                    </div>

                    {{-- Actions Candidater / En savoir plus --}}
                    <div class="pt-2 space-y-2">
                        @if($isClosed)
                            <div class="w-full py-3 px-4 rounded-xl bg-red-100 text-red-700 font-bold text-xs text-center border border-red-300 cursor-not-allowed">
                                Candidatures Clôturées
                            </div>
                        @elseif($opportunity->apply_url)
                            <a
                                href="{{ $opportunity->apply_url }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="w-full py-3.5 px-4 rounded-xl bg-[#F8B81D] hover:bg-[#e5a510] text-slate-950 font-bold text-xs text-center shadow-sm hover:shadow transition flex items-center justify-center gap-2"
                            >
                                <span>Postuler en Ligne</span>
                                <svg class="w-4 h-4 text-slate-950" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                </svg>
                            </a>
                        @endif

                        @if($opportunity->learn_more_url)
                            <a
                                href="{{ $opportunity->learn_more_url }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="w-full py-3 px-4 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs text-center transition flex items-center justify-center gap-2"
                            >
                                <span>Page Officielle / En savoir plus</span>
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                </svg>
                            </a>
                        @endif
                    </div>
                </div>

                {{-- Fiche Structure Organisatrice (SAE) --}}
                @if($sae)
                    <div class="bg-white rounded-3xl p-6 border border-slate-200/90 shadow-xs space-y-4">
                        <div class="text-xs font-bold text-slate-400 uppercase tracking-wider">
                            Structure Organisatrice (SAE)
                        </div>

                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-2xl bg-[#2F3284]/10 text-[#2F3284] flex items-center justify-center text-lg font-black shrink-0">
                                {{ mb_substr($sae->name, 0, 1) }}
                            </div>
                            <div class="min-w-0">
                                <h4 class="text-sm font-bold text-slate-900 truncate">
                                    {{ $sae->name }}
                                </h4>
                                @if($sae->city || $sae->country)
                                    <p class="text-xs text-slate-500 truncate">
                                        {{ $sae->city }}{{ $sae->city && $sae->country ? ', ' : '' }}{{ $sae->country }}
                                    </p>
                                @endif
                            </div>
                        </div>

                        @if($sae->description)
                            <p class="text-xs text-slate-600 line-clamp-3">
                                {{ $sae->description }}
                            </p>
                        @endif

                        <div class="pt-2">
                            <a
                                href="{{ route('public.structure.detail', $sae->id) }}"
                                class="inline-flex items-center gap-1.5 text-xs font-bold text-[#2F3284] hover:text-[#25286a] transition"
                            >
                                <span>Découvrir la structure &amp; ses programmes</span>
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                @endif

                {{-- Partage & Navigation retour --}}
                <div class="p-6 rounded-3xl bg-slate-50 border border-slate-200/80 text-center space-y-3">
                    <p class="text-xs text-slate-500">
                        Consultez régulièrement notre catalogue pour ne manquer aucun appel à projet.
                    </p>
                    <a
                        href="{{ route('public.opportunities') }}"
                        class="inline-flex items-center gap-2 text-xs font-bold text-slate-700 hover:text-[#2F3284] transition"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        <span>Toutes les opportunités</span>
                    </a>
                </div>

            </div>

        </div>
    </section>
</x-layouts.public>
