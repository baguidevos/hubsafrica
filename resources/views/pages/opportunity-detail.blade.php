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

                {{-- Visuel d'illustration de l'opportunité --}}
                @if($opportunity->image_url)
                    <div class="rounded-3xl overflow-hidden border {{ $isClosed ? 'border-red-300' : 'border-slate-200/90' }} shadow-sm bg-slate-100 max-h-96 relative">
                        <img
                            src="{{ $opportunity->image_url }}"
                            alt="{{ $opportunity->title }}"
                            class="w-full h-72 sm:h-96 object-cover hover:scale-105 transition-transform duration-700 {{ $isClosed ? 'grayscale-30' : '' }}"
                        >
                        @if($isClosed)
                            <div class="absolute inset-0 bg-red-950/20 pointer-events-none"></div>
                        @endif
                    </div>
                @endif

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

                {{-- Bloc de Partage Sécurisé & Multi-canaux --}}
                <div class="bg-white rounded-3xl p-6 border border-slate-200/90 shadow-xs space-y-4" x-data="{ copied: false }">
                    <div class="flex items-center justify-between">
                        <div class="text-xs font-bold text-slate-400 uppercase tracking-wider flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-[#2F3284]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                            </svg>
                            <span>Partager l'opportunité</span>
                        </div>
                        <span class="text-[10px] text-emerald-600 font-bold" x-show="copied" x-transition style="display: none;">
                            ✓ Lien officiel copié !
                        </span>
                    </div>

                    <p class="text-xs text-slate-500">
                        Diffusez cet appel à projets auprès de votre réseau d'entrepreneurs et de partenaires.
                    </p>

                    {{-- Bouton Copier le lien direct protégé --}}
                    <div class="relative">
                        <button
                            type="button"
                            @click="navigator.clipboard.writeText('{{ $opportunity->share_url }}'); copied = true; setTimeout(() => copied = false, 2500)"
                            class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 hover:bg-slate-100 text-slate-700 text-xs font-semibold transition group"
                            title="Copier le lien public sécurisé"
                        >
                            <span class="truncate font-mono text-[11px] text-slate-500 text-left max-w-[200px]">
                                {{ $opportunity->share_url }}
                            </span>
                            <span class="inline-flex items-center gap-1 text-[11px] font-bold text-[#2F3284] shrink-0 ml-2">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                </svg>
                                <span x-text="copied ? 'Copié !' : 'Copier'">Copier</span>
                            </span>
                        </button>
                    </div>

                    {{-- Boutons Réseaux Sociaux Directs --}}
                    @php
                        $shareLinks = $opportunity->share_links;
                    @endphp
                    <div class="grid grid-cols-4 gap-2 pt-1">
                        {{-- WhatsApp --}}
                        <a
                            href="{{ $shareLinks['whatsapp'] }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="flex flex-col items-center justify-center py-2.5 px-2 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-700 transition group"
                            title="Partager sur WhatsApp"
                        >
                            <svg class="w-5 h-5 text-emerald-600 group-hover:scale-110 transition-transform" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.312.045-.634.074-1.782-.401-1.378-.57-2.27-1.956-2.339-2.048-.069-.092-.556-.738-.556-1.408 0-.67.348-.999.472-1.134.124-.135.27-.17.36-.17.09 0 .18.001.258.006.082.005.192-.031.3.23.112.27.382.932.416 1.001.034.069.056.15.011.24-.045.09-.068.146-.135.225-.068.079-.142.176-.203.237-.068.068-.139.141-.06.277.079.135.351.58.753.939.519.463.957.606 1.093.674.135.069.214.06.293-.031.079-.092.338-.393.428-.528.09-.135.18-.112.304-.068.124.045.788.371.923.439.135.068.225.101.258.158.034.056.034.327-.11.732z"/>
                            </svg>
                            <span class="text-[10px] font-bold mt-1">WhatsApp</span>
                        </a>

                        {{-- LinkedIn --}}
                        <a
                            href="{{ $shareLinks['linkedin'] }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="flex flex-col items-center justify-center py-2.5 px-2 rounded-xl bg-blue-50 hover:bg-blue-100 text-blue-700 transition group"
                            title="Partager sur LinkedIn"
                        >
                            <svg class="w-5 h-5 text-blue-600 group-hover:scale-110 transition-transform" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M19 3a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h14m-.5 15.5v-5.3a3.26 3.26 0 0 0-3.26-3.26c-.85 0-1.84.52-2.28 1.3v-1.11h-2.79v8.37h2.79v-4.93c0-.77.62-1.4 1.39-1.4a1.4 1.4 0 0 1 1.4 1.4v4.93h2.75M6.88 8.56a1.68 1.68 0 0 0 1.68-1.68c0-.93-.75-1.69-1.68-1.69a1.69 1.69 0 0 0-1.69 1.69c0 .93.76 1.68 1.69 1.68m1.39 9.94v-8.37H5.5v8.37h2.77z"/>
                            </svg>
                            <span class="text-[10px] font-bold mt-1">LinkedIn</span>
                        </a>

                        {{-- X / Twitter --}}
                        <a
                            href="{{ $shareLinks['twitter'] }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="flex flex-col items-center justify-center py-2.5 px-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 transition group"
                            title="Partager sur X"
                        >
                            <svg class="w-5 h-5 text-slate-900 group-hover:scale-110 transition-transform" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                            </svg>
                            <span class="text-[10px] font-bold mt-1">X</span>
                        </a>

                        {{-- Email --}}
                        <a
                            href="{{ $shareLinks['email'] }}"
                            class="flex flex-col items-center justify-center py-2.5 px-2 rounded-xl bg-amber-50 hover:bg-amber-100 text-amber-800 transition group"
                            title="Partager par email"
                        >
                            <svg class="w-5 h-5 text-[#F8B81D] group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            <span class="text-[10px] font-bold mt-1">Email</span>
                        </a>
                    </div>
                </div>

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
