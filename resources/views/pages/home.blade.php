<x-layouts.public>
    {{-- 1. SECTION HERO INSTITUTIONNELLE & HUMAINE --}}
    <section class="relative bg-slate-950 text-white overflow-hidden pt-12 pb-20 lg:pt-18 lg:pb-28 border-b border-slate-800/80">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="text-center max-w-3xl mx-auto space-y-6">
                
                {{-- Badge sobre --}}
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-slate-900 border border-[#F8B81D]/30 text-[#F8B81D] text-xs font-semibold">
                    <span class="w-2 h-2 rounded-full bg-[#F8B81D]"></span>
                    <span>Réseau Panafricain des Structures d'Accompagnement</span>
                </div>

                {{-- Titre clair et digne, avec or Hub-Africa V1 --}}
                <h1 class="text-3xl sm:text-5xl lg:text-6xl font-black tracking-tight leading-tight text-white">
                    Faire grandir les entreprises africaines, <span class="text-[#F8B81D]">de l'idée au marché</span>
                </h1>

                {{-- Sous-titre sobre et humain --}}
                <p class="text-base sm:text-lg text-slate-300 leading-relaxed max-w-2xl mx-auto font-normal">
                    Hub Africa réunit les structures d'accompagnement locales, des mentors engagés et les entrepreneurs ambitieux pour bâtir des entreprises solides, pérennes et créatrices d'emplois.
                </p>

                {{-- CTAs aux couleurs Version 1 --}}
                <div class="pt-4 flex flex-col sm:flex-row items-center justify-center gap-3 sm:gap-4">
                    <a
                        href="{{ route('public.programmes') }}"
                        class="w-full sm:w-auto px-6 sm:px-8 py-3.5 rounded-xl bg-[#F8B81D] hover:bg-[#e5a510] text-slate-950 font-bold text-sm shadow-md transition transform hover:-translate-y-0.5 flex items-center justify-center gap-2"
                    >
                        <span>Découvrir les Programmes</span>
                        <svg class="w-4 h-4 text-slate-950" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </a>

                    <a
                        href="{{ route('public.structures') }}"
                        class="w-full sm:w-auto px-6 sm:px-7 py-3.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-slate-200 border border-slate-700 hover:border-[#F8B81D]/40 font-semibold text-sm transition flex items-center justify-center gap-2"
                    >
                        <span>Consulter les Incubateurs (SAE)</span>
                    </a>
                </div>
            </div>

            {{-- 2. INDICATEURS D'IMPACT SUR LE TERRAIN --}}
            <div class="mt-14 sm:mt-16 grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 pt-10 border-t border-slate-800/80">
                <div class="bg-slate-900/80 p-5 sm:p-6 rounded-2xl border border-slate-800 text-center">
                    <div class="text-2xl sm:text-3xl font-black text-[#F8B81D] tabular-nums">
                        {{ $metrics['projects'] }}+
                    </div>
                    <div class="text-xs font-bold text-slate-200 mt-1">Startups Accompagnées</div>
                    <div class="text-[11px] text-slate-400 mt-0.5">Projets actifs suivis</div>
                </div>

                <div class="bg-slate-900/80 p-5 sm:p-6 rounded-2xl border border-slate-800 text-center">
                    <div class="text-2xl sm:text-3xl font-black text-[#F9B104] tabular-nums">
                        {{ $metrics['saes'] }}
                    </div>
                    <div class="text-xs font-bold text-slate-200 mt-1">Structures (SAE)</div>
                    <div class="text-[11px] text-slate-400 mt-0.5">Incubateurs &amp; accélérateurs</div>
                </div>

                <div class="bg-slate-900/80 p-5 sm:p-6 rounded-2xl border border-slate-800 text-center">
                    <div class="text-2xl sm:text-3xl font-black text-amber-200 tabular-nums">
                        {{ $metrics['hours'] }} h
                    </div>
                    <div class="text-xs font-bold text-slate-200 mt-1">Mentorat Dispensé</div>
                    <div class="text-[11px] text-slate-400 mt-0.5">Heures d'échange certifiées</div>
                </div>

                <div class="bg-slate-900/80 p-5 sm:p-6 rounded-2xl border border-slate-800 text-center">
                    <div class="text-xl sm:text-2xl font-black text-[#F8B81D] truncate">
                        {{ $metrics['funding'] }}
                    </div>
                    <div class="text-xs font-bold text-slate-200 mt-1">Fonds Mobilisés</div>
                    <div class="text-[11px] text-slate-400 mt-0.5">Subventions &amp; levées</div>
                </div>
            </div>
        </div>
    </section>

    {{-- 3. SECTION PROGRAMMES EN COURS --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-10">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-[#2F3284] bg-amber-50 px-3 py-1 rounded-md border border-amber-200">
                    Appels à Candidatures
                </span>
                <h2 class="text-2xl sm:text-3xl font-black text-slate-900 mt-2.5">
                    Programmes d'Accompagnement Disponibles
                </h2>
                <p class="text-xs sm:text-sm text-slate-600 mt-1 max-w-xl">
                    Postulez directement aux cohortes ouvertes opérées par les incubateurs membres du réseau.
                </p>
            </div>

            <a
                href="{{ route('public.programmes') }}"
                class="inline-flex items-center gap-1.5 text-xs font-bold text-[#2F3284] hover:text-[#F8B81D] transition"
            >
                <span>Voir les {{ \App\Models\Program::count() }} programmes</span>
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8">
            @forelse($featuredPrograms as $p)
                <article class="bg-white rounded-2xl border border-slate-200/90 shadow-xs hover:shadow-lg transition-all duration-200 flex flex-col justify-between overflow-hidden">
                    <div class="p-6 space-y-4">
                        <div class="flex items-center justify-between gap-2">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-50 text-[#2F3284] border border-amber-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-[#F8B81D]"></span>
                                Candidatures Ouvertes
                            </span>
                            @if($p->level)
                                <span class="text-[10px] font-bold uppercase px-2 py-0.5 bg-slate-100 text-slate-700 border border-slate-200 rounded">
                                    {{ $p->level->name }}
                                </span>
                            @endif
                        </div>

                        <h3 class="text-base font-extrabold text-slate-900 hover:text-[#2F3284] transition line-clamp-1">
                            <a href="{{ route('public.programme.detail', $p->id) }}">
                                {{ $p->name }}
                            </a>
                        </h3>

                        <p class="text-xs text-slate-600 line-clamp-3 leading-relaxed">
                            {{ $p->description ?: 'Accompagnement sur mesure combinant coaching individuel, ateliers pratiques et mise en relation investisseurs.' }}
                        </p>

                        <div class="pt-3 flex items-center gap-2 text-xs text-slate-500 font-medium border-t border-slate-100">
                            <svg class="w-3.5 h-3.5 text-[#F8B81D] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            <span class="truncate">{{ $p->sae?->name ?: 'Hub Africa' }}</span>
                        </div>
                    </div>

                    <div class="p-6 pt-0 border-t border-slate-100 bg-slate-50/60 flex items-center justify-between gap-3">
                        <a
                            href="{{ route('public.programme.detail', $p->id) }}"
                            class="text-xs font-bold text-slate-600 hover:text-slate-900"
                        >
                            Détails &amp; Critères
                        </a>
                        <a
                            href="{{ route('public.programme.candidater', $p->id) }}"
                            class="px-3.5 py-1.5 rounded-lg bg-[#F8B81D] hover:bg-[#e5a510] text-slate-950 font-bold text-xs shadow-xs transition"
                        >
                            Candidater &rarr;
                        </a>
                    </div>
                </article>
            @empty
                <div class="col-span-full bg-white rounded-2xl p-10 text-center border border-dashed border-slate-300">
                    <p class="text-sm text-slate-500">Aucun programme ouvert pour l'instant. Consultez l'ensemble du catalogue.</p>
                </div>
            @endforelse
        </div>
    </section>

    {{-- 4. SECTION PARCOURS D'ACCOMPAGNEMENT --}}
    <section id="impact" class="bg-slate-900 text-white py-18 sm:py-20 border-y border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
            <div class="text-center max-w-2xl mx-auto space-y-3">
                <span class="text-xs font-bold uppercase tracking-wider text-[#F8B81D] bg-[#F8B81D]/10 px-3 py-1 rounded-md border border-[#F8B81D]/30">
                    Parcours &amp; Méthode
                </span>
                <h2 class="text-2xl sm:text-4xl font-black">
                    Un accompagnement structuré, de l'idée au financement
                </h2>
                <p class="text-xs sm:text-sm text-slate-300 leading-relaxed">
                    Chaque étape est pensée pour lever les blocages opérationnels, consolider le produit et bâtir une relation de confiance avec les investisseurs.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                {{-- Étape 1 --}}
                <div class="bg-slate-950/80 p-6 rounded-2xl border border-slate-800 space-y-3">
                    <div class="w-10 h-10 rounded-xl bg-[#2F3284]/40 text-[#F8B81D] flex items-center justify-center font-bold text-sm border border-[#F8B81D]/30">
                        01
                    </div>
                    <h3 class="text-sm font-bold text-white">Diagnostic &amp; Cadrage</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Évaluation des forces et axes d'amélioration du projet dès son entrée dans le programme pour définir un plan d'action réaliste.
                    </p>
                </div>

                {{-- Étape 2 --}}
                <div class="bg-slate-950/80 p-6 rounded-2xl border border-slate-800 space-y-3">
                    <div class="w-10 h-10 rounded-xl bg-[#2F3284]/40 text-[#F8B81D] flex items-center justify-center font-bold text-sm border border-[#F8B81D]/30">
                        02
                    </div>
                    <h3 class="text-sm font-bold text-white">Mentorat Individuel</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Séances régulières en tête-à-tête avec un mentor expérimenté pour affiner la proposition de valeur et surmonter les obstacles.
                    </p>
                </div>

                {{-- Étape 3 --}}
                <div class="bg-slate-950/80 p-6 rounded-2xl border border-slate-800 space-y-3">
                    <div class="w-10 h-10 rounded-xl bg-[#2F3284]/40 text-[#F8B81D] flex items-center justify-center font-bold text-sm border border-[#F8B81D]/30">
                        03
                    </div>
                    <h3 class="text-sm font-bold text-white">Structuration Financière</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Validation des livrables de gestion, formalisation comptable et préparation d'un dossier d'investissement clair et auditable.
                    </p>
                </div>

                {{-- Étape 4 --}}
                <div class="bg-slate-950/80 p-6 rounded-2xl border border-slate-800 space-y-3">
                    <div class="w-10 h-10 rounded-xl bg-[#2F3284]/40 text-[#F8B81D] flex items-center justify-center font-bold text-sm border border-[#F8B81D]/30">
                        04
                    </div>
                    <h3 class="text-sm font-bold text-white">Accès aux Marchés</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Mise en relation avec des financeurs, business angels, banques partenaires et clients institutionnels pour accélérer l'activité.
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- 5. SECTION TÉMOIGNAGES (TOUCHE HUMAINE AUTHENTIQUE) --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-18 sm:py-20">
        <div class="text-center max-w-2xl mx-auto space-y-3 mb-12">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">
                Retours d'Expérience
            </span>
            <h2 class="text-2xl sm:text-3xl font-black text-slate-900">
                La parole aux acteurs du terrain
            </h2>
            <p class="text-xs sm:text-sm text-slate-600">
                Découvrez comment les incubateurs et entrepreneurs utilisent Hub Africa pour structurer leur accompagnement au quotidien.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            {{-- Témoignage 1 : Entrepreneure --}}
            <div class="bg-white rounded-2xl p-7 border border-slate-200/90 shadow-xs space-y-4">
                <div class="flex items-center gap-1 text-[#F8B81D] text-sm">
                    ★★★★★
                </div>
                <p class="text-xs sm:text-sm text-slate-700 leading-relaxed italic">
                    « L'accompagnement par notre incubateur via Hub Africa a été le point de bascule. La clarté du diagnostic d'entrée et le suivi individuel nous ont permis de formaliser nos comptes et de signer nos premiers contrats distributeurs. »
                </p>
                <div class="pt-3 border-t border-slate-100 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-[#F8B81D]/20 text-[#2F3284] font-bold flex items-center justify-center text-xs">
                        MC
                    </div>
                    <div>
                        <div class="font-bold text-xs text-slate-900">Marie-Claire A.</div>
                        <div class="text-[11px] text-slate-500">Fondatrice d'une entreprise agroalimentaire &bull; Cotonou</div>
                    </div>
                </div>
            </div>

            {{-- Témoignage 2 : Responsable d'Incubateur --}}
            <div class="bg-white rounded-2xl p-7 border border-slate-200/90 shadow-xs space-y-4">
                <div class="flex items-center gap-1 text-[#F8B81D] text-sm">
                    ★★★★★
                </div>
                <p class="text-xs sm:text-sm text-slate-700 leading-relaxed italic">
                    « Avoir une plateforme centralisée pour suivre les livrables de nos promotions et coordonner les mentors nous fait gagner un temps précieux. Les données d'impact sont immédiatement exploitables pour nos bilans bailleurs. »
                </p>
                <div class="pt-3 border-t border-slate-100 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-[#2F3284]/15 text-[#2F3284] font-bold flex items-center justify-center text-xs">
                        MD
                    </div>
                    <div>
                        <div class="font-bold text-xs text-slate-900">Mamadou D.</div>
                        <div class="text-[11px] text-slate-500">Coordonnateur de Programmes SAE &bull; Dakar</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- 6. SECTION INCUBATEURS PARTENAIRES --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-18">
        <div class="text-center max-w-2xl mx-auto space-y-3 mb-10">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Réseau Local</span>
            <h2 class="text-2xl sm:text-3xl font-black text-slate-900">
                Structures d'Accompagnement Partenaires
            </h2>
            <p class="text-xs sm:text-sm text-slate-600">
                Des incubateurs et technopoles implantés au plus près des réalités de chaque pays.
            </p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach($featuredSaes as $sae)
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs hover:border-[#F8B81D]/60 hover:shadow-md transition flex items-center gap-4">
                    @if($sae->logo_url)
                        <img src="{{ $sae->logo_url }}" alt="{{ $sae->name }}" class="w-12 h-12 rounded-xl object-contain bg-white border border-slate-200 p-1 shrink-0 shadow-xs">
                    @else
                        <div class="w-12 h-12 rounded-xl bg-slate-100 text-[#2F3284] font-extrabold flex items-center justify-center text-base shrink-0 border border-slate-200">
                            {{ substr($sae->name, 0, 2) }}
                        </div>
                    @endif
                    <div class="min-w-0 flex-1">
                        <h4 class="font-bold text-xs sm:text-sm text-slate-900 truncate">{{ $sae->name }}</h4>
                        @if($sae->types->isNotEmpty())
                            <div class="flex flex-wrap gap-1 my-1">
                                @foreach($sae->types->take(2) as $type)
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-[#2F3284]/10 text-[#2F3284]">
                                        {{ $type->name }}
                                    </span>
                                @endforeach
                            </div>
                        @endif
                        <p class="text-[11px] text-slate-500 truncate mt-0.5">{{ $sae->address ?: 'Pôle d\'innovation régional' }}</p>
                        <a href="{{ route('public.structure.detail', $sae->id) }}" class="text-[11px] font-semibold text-[#2F3284] hover:text-[#e5a510] mt-1 inline-block">
                            Voir les programmes &rarr;
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    {{-- 7. BANNIÈRE REJOINDRE (AUX COULEURS VERSION 1) --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-20">
        <div class="bg-[#2F3284] rounded-3xl p-8 sm:p-12 text-white border border-[#2F3284]/60 shadow-xl flex flex-col md:flex-row items-center justify-between gap-6">
            <div class="space-y-2 max-w-xl text-center md:text-left">
                <span class="text-xs font-bold uppercase tracking-wider text-[#F8B81D]">Prêt à démarrer ?</span>
                <h3 class="text-xl sm:text-3xl font-black">
                    Rejoignez le réseau Hub Africa
                </h3>
                <p class="text-xs sm:text-sm text-slate-200 leading-relaxed">
                    Porteur de projet, mentor ou responsable d'une structure d'incubation : sélectionnez votre profil pour débuter.
                </p>
            </div>

            <div class="shrink-0 w-full md:w-auto">
                <a
                    href="{{ route('public.rejoindre') }}"
                    class="block w-full sm:w-auto text-center px-7 py-3.5 rounded-xl bg-[#F8B81D] hover:bg-[#e5a510] text-slate-950 font-bold text-xs shadow-md transition transform hover:-translate-y-0.5"
                >
                    Choisir mon profil &rarr;
                </a>
            </div>
        </div>
    </section>
</x-layouts.public>
