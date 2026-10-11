<x-layouts.public>
    @php
        $now = now()->toDateString();
        $isOpen = $program->status === \App\Enums\ProgramStatus::ACTIF && (!$program->registration_end || $program->registration_end->toDateString() >= $now);
        $daysRemaining = $program->registration_end ? now()->diffInDays($program->registration_end, false) : null;
    @endphp

    {{-- Fil d'ariane & En-tête --}}
    <section class="bg-slate-900 text-white border-b border-slate-800 py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
            {{-- Breadcrumb --}}
            <nav class="flex items-center gap-2 text-xs text-slate-400">
                <a href="{{ route('home') }}" class="hover:text-[#F8B81D]">Accueil</a>
                <span>/</span>
                <a href="{{ route('public.programmes') }}" class="hover:text-[#F8B81D]">Programmes</a>
                <span>/</span>
                <span class="text-slate-200 truncate">{{ $program->name }}</span>
            </nav>

            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 pt-2">
                <div class="space-y-2">
                    <div class="flex items-center gap-3">
                        @if($isOpen)
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-[#F8B81D]/20 text-[#F8B81D] border border-[#F8B81D]/40">
                                <span class="w-2 h-2 rounded-full bg-[#F8B81D] animate-pulse"></span>
                                Candidatures Ouvertes
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-slate-800 text-slate-400 border border-slate-700">
                                Inscriptions Clôturées
                            </span>
                        @endif

                        @if($program->level)
                            <span class="text-xs font-semibold px-3 py-1 bg-slate-800 text-slate-300 border border-slate-700 rounded-lg">
                                Niveau : {{ $program->level->name }}
                            </span>
                        @endif
                    </div>

                    <h1 class="text-2xl sm:text-4xl font-black text-white tracking-tight">
                        {{ $program->name }}
                    </h1>

                    <p class="text-xs sm:text-sm text-slate-400 flex items-center gap-2">
                        <span>Organisé par</span>
                        <strong class="text-slate-200 font-bold">{{ $program->sae?->name ?: 'Hub Africa Régional' }}</strong>
                        @if($program->sae?->address)
                            <span>&bull; {{ $program->sae->address }}</span>
                        @endif
                    </p>
                </div>

                @if($isOpen)
                    <div class="shrink-0">
                        <a
                            href="{{ route('public.programme.candidater', $program->id) }}"
                            class="px-6 py-3.5 rounded-xl bg-[#F8B81D] hover:bg-[#e5a510] text-slate-950 font-bold text-xs sm:text-sm shadow-md transition transform hover:-translate-y-0.5 flex items-center justify-center gap-2"
                        >
                            <span>Déposer ma Candidature</span>
                            <svg class="w-4 h-4 text-slate-950" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </section>

    {{-- Corps de la fiche programme (2 colonnes) --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            
            {{-- Colonne Gauche : Détails & Contenu Pédagogique (8 cols) --}}
            <div class="lg:col-span-8 space-y-8">
                
                {{-- Présentation & Résumé --}}
                <div class="bg-white rounded-3xl p-8 border border-slate-200/90 shadow-xs space-y-4">
                    <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-[#2F3284]"></span>
                        Présentation du Programme
                    </h2>
                    <div class="text-sm text-slate-700 leading-relaxed whitespace-pre-line space-y-3">
                        {{ $program->description ?: 'Ce programme a pour vocation d\'accompagner les startups à fort potentiel dans la consolidation de leur modèle économique, le déploiement opérationnel et la mise en conformité de leur gestion selon les meilleures pratiques.' }}
                    </div>
                </div>

                {{-- Objectifs pédagogiques --}}
                @if($program->objectives)
                    <div class="bg-white rounded-3xl p-8 border border-slate-200/90 shadow-xs space-y-4">
                        <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-[#F8B81D]"></span>
                            Objectifs &amp; Compétences Visées
                        </h2>
                        <div class="text-sm text-slate-700 leading-relaxed whitespace-pre-line">
                            {{ $program->objectives }}
                        </div>
                    </div>
                @endif

                {{-- Critères d'éligibilité --}}
                <div class="bg-white rounded-3xl p-8 border border-slate-200/90 shadow-xs space-y-4">
                    <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-[#2F3284]"></span>
                        Critères d'Éligibilité
                    </h2>
                    @if($program->eligibility)
                        <div class="text-sm text-slate-700 leading-relaxed whitespace-pre-line">
                            {{ $program->eligibility }}
                        </div>
                    @else
                        <ul class="space-y-3 text-xs sm:text-sm text-slate-700">
                            <li class="flex items-start gap-2.5">
                                <svg class="w-4 h-4 text-[#F8B81D] shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span>Entrepreneurs et co-fondateurs porteurs d'un projet formalisé ou en cours d'immatriculation.</span>
                            </li>
                            <li class="flex items-start gap-2.5">
                                <svg class="w-4 h-4 text-[#F8B81D] shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span>Solution répondant à un besoin concret de marché (innovation d'usage, technologique ou de procédé).</span>
                            </li>
                            <li class="flex items-start gap-2.5">
                                <svg class="w-4 h-4 text-[#F8B81D] shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span>Disponibilité avérée de l'équipe fondatrice pour suivre les séances de mentorat individuel et les ateliers collectifs.</span>
                            </li>
                        </ul>
                    @endif
                </div>

                {{-- Livrables Méthodologiques Attendus --}}
                @if($program->deliverableDefinitions && $program->deliverableDefinitions->isNotEmpty())
                    <div class="bg-white rounded-3xl p-8 border border-slate-200/90 shadow-xs space-y-4">
                        <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-[#2F3284]"></span>
                            Livrables Méthodologiques Validés
                        </h2>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            @foreach($program->deliverableDefinitions as $def)
                                <div class="p-3.5 rounded-xl border border-slate-200/80 bg-slate-50 flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-[#F8B81D]/20 text-[#2F3284] flex items-center justify-center font-bold text-xs shrink-0">
                                        ✓
                                    </div>
                                    <div>
                                        <div class="text-xs font-bold text-slate-900">{{ $def->name }}</div>
                                        <div class="text-[10px] text-slate-500">Seuil de passage : {{ $def->passing_score }}%</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Promotions & Cohortes du Programme --}}
                @if($program->cohorts && $program->cohorts->isNotEmpty())
                    <div class="bg-white rounded-3xl p-8 border border-slate-200/90 shadow-xs space-y-6">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full bg-[#F8B81D]"></span>
                                    Promotions &amp; Cohortes du Programme
                                </h2>
                                <p class="text-xs text-slate-500 mt-1">
                                    Consultez les promotions planifiées pour ce programme et postulez directement à une cohorte ouverte.
                                </p>
                            </div>
                            <span class="text-xs font-bold px-3 py-1 bg-slate-100 text-slate-700 rounded-full border border-slate-200">
                                {{ $program->cohorts->count() }} promotion(s)
                            </span>
                        </div>

                        <div class="space-y-4">
                            @foreach($program->cohorts as $cohort)
                                @php
                                    $isCohortOpen = $cohort->status === \App\Enums\CohortStatus::OUVERTE;
                                    $isCohortClosed = $cohort->status === \App\Enums\CohortStatus::CLOTUREE;
                                @endphp
                                <div class="p-5 rounded-2xl border @if($isCohortOpen) border-amber-300 bg-amber-50/40 @else border-slate-200 bg-slate-50/50 @endif transition space-y-3">
                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                        <div class="space-y-1">
                                            <div class="flex items-center gap-2.5">
                                                <h3 class="font-extrabold text-slate-900 text-base">
                                                    {{ $cohort->name }}
                                                </h3>
                                                @if($isCohortOpen)
                                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                                        Inscriptions ouvertes
                                                    </span>
                                                @elseif($isCohortClosed)
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-200 text-slate-600">
                                                        Clôturée
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800">
                                                        En cours
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="flex flex-wrap items-center gap-3 text-xs text-slate-600">
                                                <span>Démarrage : <strong>{{ $cohort->start_date ? $cohort->start_date->format('d/m/Y') : 'À déterminer' }}</strong></span>
                                                <span>&bull;</span>
                                                <span>Clôture : <strong>{{ $cohort->end_date ? $cohort->end_date->format('d/m/Y') : 'À déterminer' }}</strong></span>
                                                <span>&bull;</span>
                                                <span>Capacité : <strong class="text-[#2F3284]">{{ $cohort->capacity ?: 20 }} startups</strong></span>
                                            </div>
                                        </div>

                                        <div class="shrink-0">
                                            @if($isCohortOpen)
                                                <a
                                                    href="{{ route('public.cohort.join', $cohort->id) }}"
                                                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-[#F8B81D] hover:bg-[#e5a510] text-slate-950 font-bold text-xs shadow-sm transition"
                                                >
                                                    <span>Rejoindre cette cohorte</span>
                                                    <svg class="w-3.5 h-3.5 text-slate-950" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                                </a>
                                            @else
                                                <span class="text-xs text-slate-400 font-medium px-3 py-1.5 rounded-lg bg-slate-100">
                                                    Non disponible
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    @if($cohort->objectives)
                                        <p class="text-xs text-slate-600 pt-2 border-t border-slate-200/60 leading-relaxed">
                                            {{ $cohort->objectives }}
                                        </p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            {{-- Colonne Droite : Sidebar Institutionnelle & Actions (4 cols) --}}
            <div class="lg:col-span-4 space-y-6">
                
                {{-- Carte d'action prioritaire --}}
                <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-md space-y-6">
                    <div class="text-xs uppercase font-bold text-slate-400 tracking-wider">
                        Modalités de la promotion
                    </div>

                    <div class="space-y-4 text-xs text-slate-700">
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

                        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                            <span class="text-slate-500">Capacité d'accueil</span>
                            <strong class="text-[#2F3284] font-bold">
                                {{ $program->capacity ?: '20' }} startups
                            </strong>
                        </div>
                    </div>

                    @if($isOpen)
                        <a
                            href="{{ route('public.programme.candidater', $program->id) }}"
                            class="w-full block py-4 px-6 rounded-xl bg-[#F8B81D] hover:bg-[#e5a510] text-slate-950 font-extrabold text-xs text-center shadow-md transition transform hover:-translate-y-0.5"
                        >
                            Postuler à ce programme &rarr;
                        </a>
                    @else
                        <div class="p-3 rounded-xl bg-slate-100 text-center text-xs text-slate-500 font-medium">
                            Les inscriptions pour cette session sont actuellement closes.
                        </div>
                    @endif
                </div>

                {{-- Fiche de la Structure (SAE) --}}
                <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-xs space-y-4">
                    <div class="text-xs uppercase font-bold text-slate-400 tracking-wider">
                        Structure d'Accueil (SAE)
                    </div>

                    <div class="flex items-center gap-3">
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

                    <div class="pt-2">
                        <a
                            href="{{ route('public.structure.detail', $program->sae_id) }}"
                            class="block w-full py-2.5 text-center rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-50 font-bold text-xs transition"
                        >
                            Consulter la fiche incubateur
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
</x-layouts.public>
