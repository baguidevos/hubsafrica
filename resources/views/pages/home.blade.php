<x-layouts.public>
    {{-- 1. HERO SECTION INSTITUTIONNELLE --}}
    <section class="relative bg-gradient-to-b from-slate-950 via-slate-900 to-slate-950 text-white overflow-hidden pt-12 pb-24 lg:pt-20 lg:pb-32">
        {{-- Effets de fond et halos lumineux --}}
        <div class="absolute inset-0 opacity-20 pointer-events-none bg-[radial-gradient(#10b981_1px,transparent_1px)] [background-size:24px_24px]"></div>
        <div class="absolute top-1/4 left-1/2 -translate-x-1/2 w-[700px] h-[350px] bg-emerald-600/15 rounded-full blur-3xl pointer-events-none"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="text-center max-w-3xl mx-auto space-y-6">
                
                {{-- Badge Officiel --}}
                <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-emerald-950/80 border border-emerald-700/60 text-emerald-300 text-xs font-bold tracking-wide">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span>CADRE PANAFRICAIN D'ACCOMPAGNEMENT ET DE MESURE D'IMPACT</span>
                </div>

                {{-- Titre Institutionnel Majeur --}}
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black tracking-tight leading-tight">
                    Propulser les <span class="bg-gradient-to-r from-emerald-400 via-teal-300 to-amber-300 bg-clip-text text-transparent">startups africaines</span> vers l'excellence
                </h1>

                {{-- Sous-titre institutionnel --}}
                <p class="text-base sm:text-lg text-slate-300 leading-relaxed font-normal">
                    Hub Africa connecte les structures d'accompagnement (SAE), les mentors certifiés et les entrepreneurs innovants sous le standard de mesure de performance <strong class="text-white font-semibold">CUBE S&amp;E</strong>.
                </p>

                {{-- CTAs Principaux --}}
                <div class="pt-4 flex flex-col sm:flex-row items-center justify-center gap-4">
                    <a
                        href="{{ route('public.programmes') }}"
                        class="w-full sm:w-auto px-8 py-4 rounded-2xl bg-gradient-to-r from-emerald-500 to-emerald-700 hover:from-emerald-600 hover:to-emerald-800 text-white font-extrabold text-sm shadow-xl shadow-emerald-900/30 transition transform hover:-translate-y-0.5 flex items-center justify-center gap-2.5"
                    >
                        <span>Explorer les Appels à Candidatures</span>
                        <svg class="w-4 h-4 text-emerald-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </a>

                    <a
                        href="{{ route('public.structures') }}"
                        class="w-full sm:w-auto px-7 py-4 rounded-2xl bg-slate-900/80 hover:bg-slate-800 text-slate-200 border border-slate-700/80 font-bold text-sm transition flex items-center justify-center gap-2"
                    >
                        <span>Découvrir les Incubateurs (SAEs)</span>
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    </a>
                </div>
            </div>

            {{-- 2. BANDEAU DE MÉTRIQUES D'IMPACT EN TEMPS RÉEL --}}
            <div class="mt-16 grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 pt-10 border-t border-slate-800/80">
                <div class="bg-slate-900/60 backdrop-blur-md p-6 rounded-3xl border border-slate-800 text-center">
                    <div class="text-3xl sm:text-4xl font-black text-emerald-400 tabular-nums">
                        {{ $metrics['projects'] }}+
                    </div>
                    <div class="text-xs font-bold text-slate-300 uppercase tracking-wider mt-1.5">Startups Accompagnées</div>
                    <div class="text-[11px] text-slate-500 mt-0.5">Parcours d'incubation actifs</div>
                </div>

                <div class="bg-slate-900/60 backdrop-blur-md p-6 rounded-3xl border border-slate-800 text-center">
                    <div class="text-3xl sm:text-4xl font-black text-amber-400 tabular-nums">
                        {{ $metrics['saes'] }}
                    </div>
                    <div class="text-xs font-bold text-slate-300 uppercase tracking-wider mt-1.5">Structures (SAE)</div>
                    <div class="text-[11px] text-slate-500 mt-0.5">Incubateurs &amp; technopoles</div>
                </div>

                <div class="bg-slate-900/60 backdrop-blur-md p-6 rounded-3xl border border-slate-800 text-center">
                    <div class="text-3xl sm:text-4xl font-black text-teal-400 tabular-nums">
                        {{ $metrics['hours'] }} h
                    </div>
                    <div class="text-xs font-bold text-slate-300 uppercase tracking-wider mt-1.5">Mentorat Dispensé</div>
                    <div class="text-[11px] text-slate-500 mt-0.5">Séances individuelles CUBE</div>
                </div>

                <div class="bg-slate-900/60 backdrop-blur-md p-6 rounded-3xl border border-slate-800 text-center">
                    <div class="text-2xl sm:text-3xl font-black text-emerald-300 truncate">
                        {{ $metrics['funding'] }}
                    </div>
                    <div class="text-xs font-bold text-slate-300 uppercase tracking-wider mt-1.5">Fonds Mobilisés</div>
                    <div class="text-[11px] text-slate-500 mt-0.5">Subventions &amp; investissements</div>
                </div>
            </div>
        </div>
    </section>

    {{-- 3. SECTION PROGRAMMES EN VEDETTE --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-12">
            <div>
                <span class="text-xs font-bold uppercase tracking-widest text-emerald-700 bg-emerald-50 px-3 py-1 rounded-lg border border-emerald-200">
                    Opportunités Récentes
                </span>
                <h2 class="text-2xl sm:text-3xl font-black text-slate-900 mt-3">
                    Appels à Candidatures Ouverts
                </h2>
                <p class="text-sm text-slate-500 mt-1 max-w-xl">
                    Postulez directement aux promotions d'incubation certifiées et bénéficiez de l'accompagnement de mentors experts.
                </p>
            </div>

            <a
                href="{{ route('public.programmes') }}"
                class="inline-flex items-center gap-2 text-xs font-extrabold text-emerald-700 hover:text-emerald-800 transition"
            >
                <span>Consulter tous les programmes ({{ \App\Models\Program::count() }})</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @forelse($featuredPrograms as $p)
                <div class="bg-white rounded-3xl border border-slate-200/90 shadow-xs hover:shadow-xl transition-all duration-300 flex flex-col justify-between overflow-hidden group hover:-translate-y-1">
                    <div class="p-6 sm:p-8 space-y-4">
                        <div class="flex items-center justify-between gap-2">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                Candidatures Ouvertes
                            </span>
                            @if($p->level)
                                <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 bg-amber-50 text-amber-800 border border-amber-200 rounded">
                                    {{ $p->level->name }}
                                </span>
                            @endif
                        </div>

                        <h3 class="text-lg font-bold text-slate-900 group-hover:text-emerald-700 transition">
                            <a href="{{ route('public.programme.detail', $p->id) }}">
                                {{ $p->name }}
                            </a>
                        </h3>

                        <p class="text-xs text-slate-600 line-clamp-3 leading-relaxed">
                            {{ $p->description ?: 'Programme intensif d\'incubation méthodologique certifié CUBE S&E pour les startups innovantes.' }}
                        </p>

                        <div class="pt-2 flex items-center gap-2 text-xs text-slate-500 font-medium border-t border-slate-100">
                            <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            <span class="truncate">{{ $p->sae?->name ?: 'Hub Africa' }}</span>
                        </div>
                    </div>

                    <div class="p-6 pt-0 border-t border-slate-100 bg-slate-50/50 flex items-center justify-between gap-3">
                        <a
                            href="{{ route('public.programme.detail', $p->id) }}"
                            class="text-xs font-bold text-slate-600 hover:text-slate-900"
                        >
                            En savoir plus
                        </a>
                        <a
                            href="{{ route('public.programme.candidater', $p->id) }}"
                            class="px-4 py-2 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-xs shadow-sm transition"
                        >
                            Candidater &rarr;
                        </a>
                    </div>
                </div>
            @empty
                <div class="col-span-full bg-white rounded-3xl p-12 text-center border border-dashed border-slate-300">
                    <p class="text-sm text-slate-500">Aucun programme en vedette pour le moment.</p>
                </div>
            @endforelse
        </div>
    </section>

    {{-- 4. SECTION MÉTHODOLOGIE & CADRE CUBE S&E --}}
    <section id="cube" class="bg-slate-900 text-white py-24 border-y border-slate-800 relative overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-16">
            <div class="text-center max-w-3xl mx-auto space-y-4">
                <span class="text-xs font-bold uppercase tracking-widest text-amber-400 bg-amber-950/60 px-3.5 py-1.5 rounded-full border border-amber-700/60">
                    Standard de Suivi &amp; Évaluation CUBE
                </span>
                <h2 class="text-3xl sm:text-4xl font-black">
                    Une Méthodologie Rigoureuse et Certifiée
                </h2>
                <p class="text-sm sm:text-base text-slate-400">
                    Hub Africa déploie le standard CUBE S&amp;E pour garantir que chaque étape de maturation de votre entreprise est tracée, évaluée et valorisée auprès des investisseurs.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                <div class="bg-slate-950/70 p-8 rounded-3xl border border-slate-800 space-y-4 hover:border-emerald-700 transition">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-900/50 text-emerald-400 flex items-center justify-center font-black text-lg">
                        01
                    </div>
                    <h3 class="text-base font-bold text-white">Diagnostic Initial (Baseline)</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Évaluation holistique à l'entrée du programme : gouvernance, potentiel marché, viabilité financière et besoins d'accompagnement prioritaires.
                    </p>
                </div>

                <div class="bg-slate-950/70 p-8 rounded-3xl border border-slate-800 space-y-4 hover:border-teal-700 transition">
                    <div class="w-12 h-12 rounded-2xl bg-teal-900/50 text-teal-400 flex items-center justify-center font-black text-lg">
                        02
                    </div>
                    <h3 class="text-base font-bold text-white">Coaching &amp; Jalons de Maturité</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Mentorat individuel dispensé par des experts sectoriels, délivrance de livrables méthodologiques et franchissement des paliers de croissance.
                    </p>
                </div>

                <div class="bg-slate-950/70 p-8 rounded-3xl border border-slate-800 space-y-4 hover:border-amber-700 transition">
                    <div class="w-12 h-12 rounded-2xl bg-amber-900/50 text-amber-400 flex items-center justify-center font-black text-lg">
                        03
                    </div>
                    <h3 class="text-base font-bold text-white">Data Room &amp; Financement</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Constitution d'un dossier d'investissement auditable : états financiers, pitch deck validé, conformité juridique et mise en relation investisseurs.
                    </p>
                </div>

                <div class="bg-slate-950/70 p-8 rounded-3xl border border-slate-800 space-y-4 hover:border-emerald-700 transition">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-900/50 text-emerald-400 flex items-center justify-center font-black text-lg">
                        04
                    </div>
                    <h3 class="text-base font-bold text-white">Mesure d'Impact (Endline)</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Suivi post-programme à 6 et 12 mois : emplois décents créés, chiffre d'affaires généré, contribution aux Objectifs de Développement Durable (ODD).
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- 5. SECTION INCUBATEURS ET STRUCTURES PARTENAIRES --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
        <div class="text-center max-w-2xl mx-auto space-y-4 mb-14">
            <span class="text-xs font-bold uppercase tracking-widest text-slate-500">Réseau Territorial</span>
            <h2 class="text-2xl sm:text-3xl font-black text-slate-900">
                Structures d'Accompagnement Membres
            </h2>
            <p class="text-xs sm:text-sm text-slate-600">
                Les incubateurs et accélérateurs membres déploient leurs programmes et encadrent les startups sur l'ensemble du continent.
            </p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($featuredSaes as $sae)
                <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-xs hover:shadow-md transition flex items-center gap-4">
                    <div class="w-14 h-14 rounded-2xl bg-emerald-50 text-emerald-800 font-extrabold flex items-center justify-center text-xl shrink-0 border border-emerald-100">
                        {{ substr($sae->name, 0, 2) }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <h4 class="font-bold text-slate-900 truncate">{{ $sae->name }}</h4>
                        <p class="text-xs text-slate-500 truncate mt-0.5">{{ $sae->address ?: 'Pôle d\'innovation régional' }}</p>
                        <a href="{{ route('public.structure.detail', $sae->id) }}" class="text-[11px] font-bold text-emerald-700 hover:underline mt-1 inline-block">
                            Voir les programmes &rarr;
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    {{-- 6. BANNIÈRE D'ORIENTATION & REJOINDRE --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-20">
        <div class="bg-gradient-to-r from-emerald-900 via-emerald-800 to-slate-900 rounded-3xl p-8 sm:p-14 text-white shadow-2xl relative overflow-hidden flex flex-col md:flex-row items-center justify-between gap-8">
            <div class="space-y-3 max-w-xl text-center md:text-left">
                <span class="text-xs font-bold uppercase tracking-widest text-amber-300">Prêt à franchir le pas ?</span>
                <h3 class="text-2xl sm:text-3xl font-black">
                    Rejoignez la dynamique Hub Africa aujourd'hui
                </h3>
                <p class="text-xs sm:text-sm text-slate-200 leading-relaxed">
                    Que vous soyez porteur d'un projet innovant, mentor chevronné ou responsable d'une structure d'incubation, Hub Africa vous apporte le cadre et les outils pour réussir.
                </p>
            </div>

            <div class="flex flex-col sm:flex-row gap-4 shrink-0 w-full md:w-auto">
                <a
                    href="{{ route('public.rejoindre') }}"
                    class="px-8 py-4 rounded-xl bg-amber-400 hover:bg-amber-300 text-slate-950 font-black text-xs text-center shadow-lg transition transform hover:-translate-y-0.5"
                >
                    Sélectionner mon profil &rarr;
                </a>
            </div>
        </div>
    </section>
</x-layouts.public>
