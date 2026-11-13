<x-layouts.public>
    @php
        $isOpen = $program->isAcceptingApplications();
        $daysRemaining = $program->registration_end ? (int) now()->startOfDay()->diffInDays($program->registration_end->copy()->startOfDay(), false) : null;
        $openCohortsCount = $program->cohorts ? $program->cohorts->where('status', \App\Enums\CohortStatus::OUVERTE)->count() : 0;
    @endphp

    {{-- 1. Hero Section Immersive --}}
    <section class="relative overflow-hidden bg-slate-950 text-white border-b border-slate-800/80">
        {{-- Halos décoratifs subtils --}}
        <div class="pointer-events-none absolute -top-24 -right-24 h-96 w-96 rounded-full bg-[#F8B81D]/10 blur-3xl" aria-hidden="true"></div>
        <div class="pointer-events-none absolute -bottom-32 -left-24 h-96 w-96 rounded-full bg-[#2F3284]/25 blur-3xl" aria-hidden="true"></div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-14 space-y-8">
            {{-- Breadcrumb --}}
            <nav aria-label="Fil d'ariane" class="flex items-center gap-2 text-xs text-slate-400">
                <a href="{{ route('home') }}" class="hover:text-[#F8B81D] transition">Accueil</a>
                <span class="text-slate-600">/</span>
                <a href="{{ route('public.programmes') }}" class="hover:text-[#F8B81D] transition">Programmes</a>
                <span class="text-slate-600">/</span>
                <span class="text-slate-200 font-medium truncate">{{ $program->name }}</span>
            </nav>

            {{-- En-tête principal --}}
            <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-8">
                <div class="space-y-4 max-w-3xl">
                    {{-- Badges statut & niveau --}}
                    <div class="flex flex-wrap items-center gap-2.5">
                        @if($isOpen)
                            <span class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full text-xs font-bold bg-[#F8B81D]/15 text-[#F8B81D] border border-[#F8B81D]/40">
                                <span class="w-2 h-2 rounded-full bg-[#F8B81D] animate-pulse"></span>
                                Candidatures Ouvertes
                            </span>
                            @if($daysRemaining !== null && $daysRemaining >= 0)
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-500/15 text-emerald-300 border border-emerald-500/30">
                                    @if($daysRemaining === 0)
                                        Dernier jour pour postuler
                                    @else
                                        Clôture dans {{ $daysRemaining }} jour{{ $daysRemaining > 1 ? 's' : '' }}
                                    @endif
                                </span>
                            @endif
                        @else
                            <span class="inline-flex items-center gap-1.5 px-3.5 py-1 rounded-full text-xs font-bold bg-slate-800 text-slate-300 border border-slate-700">
                                Inscriptions Clôturées
                            </span>
                        @endif

                        @if($program->level)
                            <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1 bg-white/5 text-slate-200 border border-white/15 rounded-full">
                                Niveau : {{ $program->level->name }}
                            </span>
                        @endif
                    </div>

                    {{-- Titre --}}
                    <h1 class="text-3xl sm:text-4xl lg:text-5xl font-black text-white tracking-tight leading-[1.12]">
                        {{ $program->name }}
                    </h1>

                    {{-- Organisateur --}}
                    <div class="flex flex-wrap items-center gap-3 pt-1">
                        @if($program->sae?->logo_url)
                            <img src="{{ $program->sae->logo_url }}" alt="{{ $program->sae->name }}" class="w-8 h-8 rounded-lg object-contain bg-white p-0.5 border border-slate-700 shrink-0">
                        @endif
                        <p class="text-xs sm:text-sm text-slate-300 flex flex-wrap items-center gap-x-2 gap-y-1">
                            <span class="text-slate-400">Organisé par</span>
                            @if($program->sae_id)
                                <a href="{{ route('public.structure.detail', $program->sae_id) }}" class="text-white font-bold hover:text-[#F8B81D] underline decoration-slate-600 underline-offset-4 transition">
                                    {{ $program->sae?->name ?: 'Hubs Africa Régional' }}
                                </a>
                            @else
                                <strong class="text-white font-bold">{{ $program->sae?->name ?: 'Hubs Africa Régional' }}</strong>
                            @endif
                            @if($program->sae?->address)
                                <span class="text-slate-500">&bull;</span>
                                <span class="text-slate-400">{{ $program->sae->address }}</span>
                            @endif
                        </p>
                    </div>
                </div>

                {{-- CTA Hero --}}
                <div class="shrink-0 flex flex-wrap items-center gap-3">
                    @if($isOpen)
                        <a
                            href="{{ $program->public_apply_url }}"
                            class="inline-flex items-center justify-center gap-2.5 px-7 py-4 rounded-2xl bg-[#F8B81D] hover:bg-[#e5a510] text-slate-950 font-extrabold text-sm shadow-lg shadow-[#F8B81D]/15 transition transform hover:-translate-y-0.5"
                        >
                            <span>Déposer ma Candidature</span>
                            <svg class="w-4 h-4 text-slate-950" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </a>
                    @endif
                    @if($openCohortsCount > 0)
                        <a
                            href="#cohortes"
                            class="inline-flex items-center justify-center gap-2 px-5 py-4 rounded-2xl bg-white/5 hover:bg-white/10 text-white border border-white/15 font-bold text-sm transition"
                        >
                            <span>Voir les {{ $openCohortsCount }} cohorte{{ $openCohortsCount > 1 ? 's' : '' }} ouverte{{ $openCohortsCount > 1 ? 's' : '' }}</span>
                        </a>
                    @endif
                </div>
            </div>

            {{-- Barre de repères clés --}}
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 pt-4 border-t border-white/10">
                <div class="rounded-2xl bg-white/[0.04] border border-white/10 px-4 py-3.5">
                    <span class="block text-[11px] uppercase tracking-wider text-slate-400 font-semibold">Clôture candidatures</span>
                    <span class="mt-1 block text-sm sm:text-base font-bold text-white">
                        {{ $program->registration_end ? $program->registration_end->translatedFormat('d M Y') : 'À déterminer' }}
                    </span>
                </div>
                <div class="rounded-2xl bg-white/[0.04] border border-white/10 px-4 py-3.5">
                    <span class="block text-[11px] uppercase tracking-wider text-slate-400 font-semibold">Démarrage</span>
                    <span class="mt-1 block text-sm sm:text-base font-bold text-white">
                        {{ $program->start_date ? $program->start_date->translatedFormat('d M Y') : 'Prochainement' }}
                    </span>
                </div>
                <div class="rounded-2xl bg-white/[0.04] border border-white/10 px-4 py-3.5">
                    <span class="block text-[11px] uppercase tracking-wider text-slate-400 font-semibold">Fin d'accompagnement</span>
                    <span class="mt-1 block text-sm sm:text-base font-bold text-white">
                        {{ $program->end_date ? $program->end_date->translatedFormat('d M Y') : 'Selon cycle' }}
                    </span>
                </div>
                <div class="rounded-2xl bg-white/[0.04] border border-white/10 px-4 py-3.5">
                    <span class="block text-[11px] uppercase tracking-wider text-slate-400 font-semibold">Capacité d'accueil</span>
                    <span class="mt-1 block text-sm sm:text-base font-bold text-[#F8B81D]">
                        {{ $program->capacity ?: '20' }} startups
                    </span>
                </div>
            </div>
        </div>
    </section>

    {{-- 2. Sous-navigation par ancres --}}
    <div class="bg-white/95 backdrop-blur border-b border-slate-200 sticky top-0 z-30">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <nav aria-label="Sections du programme" class="flex items-center gap-6 overflow-x-auto py-3.5 text-xs sm:text-sm font-semibold text-slate-600 no-scrollbar">
                <a href="#presentation" class="whitespace-nowrap hover:text-[#2F3284] transition">Présentation</a>
                @if($program->objectives)
                    <a href="#objectifs" class="whitespace-nowrap hover:text-[#2F3284] transition">Objectifs</a>
                @endif
                <a href="#eligibilite" class="whitespace-nowrap hover:text-[#2F3284] transition">Éligibilité</a>
                @if($program->deliverableDefinitions && $program->deliverableDefinitions->isNotEmpty())
                    <a href="#livrables" class="whitespace-nowrap hover:text-[#2F3284] transition">Livrables ({{ $program->deliverableDefinitions->count() }})</a>
                @endif
                @if($program->cohorts && $program->cohorts->isNotEmpty())
                    <a href="#cohortes" class="whitespace-nowrap hover:text-[#2F3284] transition">Cohortes ({{ $program->cohorts->count() }})</a>
                @endif
            </nav>
        </div>
    </div>

    {{-- 3. Corps de la fiche programme (2 colonnes) --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-14">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-10 items-start">

            {{-- Colonne Gauche : Contenu principal (8 cols) --}}
            <div class="lg:col-span-8 space-y-8">

                {{-- Visuel d'illustration du programme (affiché en entier avec visionneuse plein écran) --}}
                @if($program->image_url)
                    <div
                        class="relative rounded-3xl overflow-hidden border border-slate-800 shadow-xl bg-slate-950 group cursor-pointer"
                        x-data
                        data-image-url="{{ $program->image_url }}"
                        data-image-title="{{ $program->name }}"
                        @click="$dispatch('open-image-lightbox', { url: $el.dataset.imageUrl, title: $el.dataset.imageTitle })"
                        role="button"
                        tabindex="0"
                        @keydown.enter="$dispatch('open-image-lightbox', { url: $el.dataset.imageUrl, title: $el.dataset.imageTitle })"
                        title="Cliquer pour afficher l'affiche en plein écran"
                    >
                        <div class="absolute inset-0 overflow-hidden pointer-events-none opacity-30 select-none" aria-hidden="true">
                            <img
                                src="{{ $program->image_url }}"
                                alt=""
                                class="w-full h-full object-cover filter blur-2xl scale-125"
                            >
                        </div>

                        <div class="relative z-10 flex items-center justify-center p-4 sm:p-6 min-h-[260px] sm:min-h-[380px] max-h-[580px]">
                            <img
                                src="{{ $program->image_url }}"
                                alt="{{ $program->name }}"
                                class="w-auto h-auto max-w-full max-h-[520px] object-contain rounded-2xl shadow-2xl transition duration-500 group-hover:scale-[1.015]"
                            >
                        </div>

                        <div class="absolute bottom-3 right-3 sm:bottom-4 sm:right-4 z-20">
                            <span
                                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-slate-900/85 group-hover:bg-slate-900 text-white text-xs font-semibold backdrop-blur-md shadow-lg border border-white/20 transition transform group-hover:scale-105"
                            >
                                <svg class="w-4 h-4 text-[#F8B81D]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v6m3-3H7"/>
                                </svg>
                                <span>Agrandir l'affiche</span>
                            </span>
                        </div>
                    </div>
                @endif

                {{-- Présentation du Programme --}}
                <div id="presentation" class="scroll-mt-20 bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/90 shadow-xs space-y-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-[#2F3284]/10 text-[#2F3284] flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <h2 class="text-lg sm:text-xl font-extrabold text-slate-900">
                            Présentation du Programme
                        </h2>
                    </div>
                    <div class="text-sm sm:text-[15px] text-slate-700 leading-relaxed whitespace-pre-line">
                        {{ $program->description ?: 'Ce programme a pour vocation d\'accompagner les startups à fort potentiel dans la consolidation de leur modèle économique, le déploiement opérationnel et la mise en conformité de leur gestion selon les meilleures pratiques.' }}
                    </div>
                </div>

                {{-- Objectifs pédagogiques --}}
                @if($program->objectives)
                    <div id="objectifs" class="scroll-mt-20 bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/90 shadow-xs space-y-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-[#F8B81D]/15 text-amber-700 flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <h2 class="text-lg sm:text-xl font-extrabold text-slate-900">
                                Objectifs &amp; Compétences Visées
                            </h2>
                        </div>
                        <div class="text-sm sm:text-[15px] text-slate-700 leading-relaxed whitespace-pre-line">
                            {{ $program->objectives }}
                        </div>
                    </div>
                @endif

                {{-- Critères d'éligibilité --}}
                <div id="eligibilite" class="scroll-mt-20 bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/90 shadow-xs space-y-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-700 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                        </div>
                        <h2 class="text-lg sm:text-xl font-extrabold text-slate-900">
                            Critères d'Éligibilité
                        </h2>
                    </div>

                    @if($program->eligibility)
                        <div class="text-sm sm:text-[15px] text-slate-700 leading-relaxed whitespace-pre-line">
                            {{ $program->eligibility }}
                        </div>
                    @else
                        <ul class="grid grid-cols-1 gap-3 text-xs sm:text-sm text-slate-700">
                            <li class="flex items-start gap-3 p-3.5 rounded-2xl bg-slate-50 border border-slate-200/70">
                                <svg class="w-5 h-5 text-[#F8B81D] shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                <span>Entrepreneurs et co-fondateurs porteurs d'un projet formalisé ou en cours d'immatriculation.</span>
                            </li>
                            <li class="flex items-start gap-3 p-3.5 rounded-2xl bg-slate-50 border border-slate-200/70">
                                <svg class="w-5 h-5 text-[#F8B81D] shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                <span>Solution répondant à un besoin concret de marché (innovation d'usage, technologique ou de procédé).</span>
                            </li>
                            <li class="flex items-start gap-3 p-3.5 rounded-2xl bg-slate-50 border border-slate-200/70">
                                <svg class="w-5 h-5 text-[#F8B81D] shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                <span>Disponibilité avérée de l'équipe fondatrice pour suivre les séances de mentorat individuel et les ateliers collectifs.</span>
                            </li>
                        </ul>
                    @endif
                </div>

                {{-- Livrables Méthodologiques Attendus --}}
                @if($program->deliverableDefinitions && $program->deliverableDefinitions->isNotEmpty())
                    <div id="livrables" class="scroll-mt-20 bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/90 shadow-xs space-y-5">
                        <div class="flex items-center justify-between gap-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-[#2F3284]/10 text-[#2F3284] flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                </div>
                                <h2 class="text-lg sm:text-xl font-extrabold text-slate-900">
                                    Livrables Méthodologiques Validés
                                </h2>
                            </div>
                            <span class="text-xs font-bold px-3 py-1 rounded-full bg-slate-100 text-slate-700 border border-slate-200">
                                {{ $program->deliverableDefinitions->count() }} jalon{{ $program->deliverableDefinitions->count() > 1 ? 's' : '' }}
                            </span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                            @foreach($program->deliverableDefinitions as $index => $def)
                                <div class="p-4 rounded-2xl border border-slate-200/80 bg-slate-50/70 hover:bg-white hover:border-[#2F3284]/30 transition flex items-center gap-3.5">
                                    <div class="w-9 h-9 rounded-xl bg-[#2F3284] text-white flex items-center justify-center font-extrabold text-xs shrink-0">
                                        {{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="text-xs sm:text-sm font-bold text-slate-900 truncate">{{ $def->name }}</div>
                                        <div class="text-[11px] text-slate-500 mt-0.5">
                                            Seuil de validation : <strong class="text-[#2F3284]">{{ $def->passing_score }}%</strong>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Promotions & Cohortes du Programme --}}
                @if($program->cohorts && $program->cohorts->isNotEmpty())
                    <div id="cohortes" class="scroll-mt-20 bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/90 shadow-xs space-y-6">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-[#F8B81D]/15 text-amber-700 flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                </div>
                                <div>
                                    <h2 class="text-lg sm:text-xl font-extrabold text-slate-900">
                                        Promotions &amp; Cohortes du Programme
                                    </h2>
                                    <p class="text-xs text-slate-500 mt-0.5">
                                        Consultez les sessions planifiées et postulez directement à une cohorte ouverte.
                                    </p>
                                </div>
                            </div>
                            <span class="self-start sm:self-auto text-xs font-bold px-3 py-1 bg-slate-100 text-slate-700 rounded-full border border-slate-200">
                                {{ $program->cohorts->count() }} promotion(s)
                            </span>
                        </div>

                        <div class="space-y-4">
                            @foreach($program->cohorts as $cohort)
                                @php
                                    $isCohortOpen = $cohort->status === \App\Enums\CohortStatus::OUVERTE;
                                    $isCohortClosed = $cohort->status === \App\Enums\CohortStatus::CLOTUREE;
                                @endphp
                                <div class="p-5 rounded-2xl border @if($isCohortOpen) border-amber-300 bg-gradient-to-br from-amber-50/60 to-white shadow-xs @else border-slate-200 bg-slate-50/50 @endif transition space-y-3">
                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                        <div class="space-y-1.5">
                                            <div class="flex flex-wrap items-center gap-2.5">
                                                <h3 class="font-extrabold text-slate-900 text-base">
                                                    {{ $cohort->name }}
                                                </h3>
                                                @if($isCohortOpen)
                                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                                        Inscriptions ouvertes
                                                    </span>
                                                @elseif($isCohortClosed)
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-slate-200 text-slate-600">
                                                        Clôturée
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-blue-100 text-blue-800">
                                                        En cours
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-slate-600">
                                                <span>Démarrage : <strong class="text-slate-900">{{ $cohort->start_date ? $cohort->start_date->format('d/m/Y') : 'À déterminer' }}</strong></span>
                                                <span class="text-slate-300">&bull;</span>
                                                <span>Clôture : <strong class="text-slate-900">{{ $cohort->end_date ? $cohort->end_date->format('d/m/Y') : 'À déterminer' }}</strong></span>
                                                <span class="text-slate-300">&bull;</span>
                                                <span>Capacité : <strong class="text-[#2F3284]">{{ $cohort->capacity ?: 20 }} startups</strong></span>
                                            </div>
                                        </div>

                                        <div class="shrink-0">
                                            @if($isCohortOpen)
                                                <a
                                                    href="{{ $cohort->public_join_url }}"
                                                    class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-[#F8B81D] hover:bg-[#e5a510] text-slate-950 font-bold text-xs shadow-sm transition"
                                                >
                                                    <span>Rejoindre cette cohorte</span>
                                                    <svg class="w-3.5 h-3.5 text-slate-950" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                                </a>
                                            @else
                                                <span class="inline-block text-xs text-slate-400 font-medium px-3 py-1.5 rounded-lg bg-slate-100">
                                                    Non disponible
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    @if($cohort->objectives)
                                        <p class="text-xs text-slate-600 pt-2.5 border-t border-slate-200/60 leading-relaxed">
                                            {{ $cohort->objectives }}
                                        </p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            {{-- Colonne Droite : Sidebar Sticky Institutionnelle & Actions (4 cols) --}}
            <aside class="lg:col-span-4 space-y-6 lg:sticky lg:top-16">

                {{-- Carte d'action prioritaire --}}
                <div class="bg-white rounded-3xl p-6 sm:p-7 border-2 @if($isOpen) border-[#F8B81D]/60 shadow-lg shadow-amber-500/5 @else border-slate-200 shadow-sm @endif space-y-6">
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-xs uppercase font-bold text-slate-400 tracking-wider">
                            Modalités de la session
                        </span>
                        @if($isOpen)
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                Ouvert
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-slate-100 text-slate-600">
                                Clos
                            </span>
                        @endif
                    </div>

                    <div class="space-y-3.5 text-xs text-slate-700">
                        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                            <span class="text-slate-500">Clôture des candidatures</span>
                            <strong class="text-slate-900 font-bold">
                                {{ $program->registration_end ? $program->registration_end->format('d/m/Y') : 'À déterminer' }}
                            </strong>
                        </div>

                        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                            <span class="text-slate-500">Démarrage cohorte</span>
                            <strong class="text-slate-900 font-bold">
                                {{ $program->start_date ? $program->start_date->format('d/m/Y') : 'Prochainement' }}
                            </strong>
                        </div>

                        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                            <span class="text-slate-500">Fin d'accompagnement</span>
                            <strong class="text-slate-900 font-bold">
                                {{ $program->end_date ? $program->end_date->format('d/m/Y') : 'Selon cycle' }}
                            </strong>
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="text-slate-500">Capacité d'accueil</span>
                            <strong class="text-[#2F3284] font-bold">
                                {{ $program->capacity ?: '20' }} startups
                            </strong>
                        </div>
                    </div>

                    @if($isOpen)
                        <a
                            href="{{ $program->public_apply_url }}"
                            class="w-full flex items-center justify-center gap-2 py-4 px-6 rounded-2xl bg-[#F8B81D] hover:bg-[#e5a510] text-slate-950 font-extrabold text-xs sm:text-sm text-center shadow-md transition transform hover:-translate-y-0.5"
                        >
                            <span>Postuler à ce programme</span>
                            <span>&rarr;</span>
                        </a>
                    @else
                        <div class="p-3.5 rounded-2xl bg-slate-100 text-center text-xs text-slate-500 font-medium">
                            Les inscriptions pour cette session sont actuellement closes.
                        </div>
                    @endif
                </div>

                {{-- Fiche de la Structure (SAE) --}}
                <div class="bg-white rounded-3xl p-6 sm:p-7 border border-slate-200 shadow-xs space-y-4">
                    <div class="text-xs uppercase font-bold text-slate-400 tracking-wider">
                        Structure d'Accueil (SAE)
                    </div>

                    <div class="flex items-center gap-3.5">
                        @if($program->sae?->logo_url)
                            <img src="{{ $program->sae->logo_url }}" alt="{{ $program->sae->name }}" class="w-12 h-12 rounded-xl object-contain bg-white border border-slate-200 p-1 shrink-0 shadow-xs">
                        @else
                            <div class="w-12 h-12 rounded-xl bg-slate-100 text-[#2F3284] font-black text-base flex items-center justify-center border border-slate-200 shrink-0">
                                {{ substr($program->sae?->name ?: 'HA', 0, 2) }}
                            </div>
                        @endif
                        <div class="min-w-0 flex-1">
                            <h4 class="font-extrabold text-base text-slate-900 truncate">{{ $program->sae?->name }}</h4>
                            @if($program->sae?->types && $program->sae->types->isNotEmpty())
                                <div class="flex flex-wrap gap-1 mt-1">
                                    @foreach($program->sae->types as $t)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-semibold bg-[#2F3284]/10 text-[#2F3284]">
                                            {{ $t->name }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>

                    @if($program->sae?->description)
                        <p class="text-xs text-slate-600 line-clamp-3 leading-relaxed">
                            {{ $program->sae->description }}
                        </p>
                    @endif

                    <div class="space-y-2 text-xs text-slate-600 pt-2 border-t border-slate-100">
                        @if($program->sae?->email)
                            <div class="flex items-center gap-2">
                                <span class="text-slate-400">Email :</span>
                                <a href="mailto:{{ $program->sae->email }}" class="text-[#2F3284] font-semibold hover:underline truncate">{{ $program->sae->email }}</a>
                            </div>
                        @endif
                        @if($program->sae?->phone)
                            <div class="flex items-center gap-2">
                                <span class="text-slate-400">Tél :</span>
                                <span class="font-semibold text-slate-800">{{ $program->sae->phone }}</span>
                            </div>
                        @endif
                    </div>

                    @if($program->sae_id)
                        <div class="pt-2">
                            <a
                                href="{{ route('public.structure.detail', $program->sae_id) }}"
                                class="block w-full py-3 text-center rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-50 hover:border-slate-400 font-bold text-xs transition"
                            >
                                Consulter la fiche incubateur
                            </a>
                        </div>
                    @endif
                </div>
            </aside>
        </div>
    </section>
</x-layouts.public>
