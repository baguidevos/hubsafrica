<x-layouts.public>
    {{-- Hero Section --}}
    <section class="bg-slate-950 text-white py-12 sm:py-16 border-b border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <nav class="flex items-center gap-2 text-xs text-slate-400">
                <a href="{{ route('home') }}" class="hover:text-[#F8B81D] transition">Accueil</a>
                <span>/</span>
                <a href="{{ route('public.structures') }}" class="hover:text-[#F8B81D] transition">Structures</a>
                <span>/</span>
                <span class="text-slate-200 truncate">{{ $sae->name }}</span>
            </nav>

            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 pt-2">
                <div class="flex items-start sm:items-center gap-4 sm:gap-6">
                    @if($sae->logo_url)
                        <img src="{{ $sae->logo_url }}" alt="{{ $sae->name }}" class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl object-contain bg-white border border-slate-800 p-2 shrink-0 shadow-lg">
                    @else
                        <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-slate-900 text-[#F8B81D] font-black text-2xl sm:text-3xl flex items-center justify-center border border-slate-800 shrink-0 shadow-lg">
                            {{ substr($sae->name, 0, 2) }}
                        </div>
                    @endif
                    <div class="space-y-1.5">
                        <div class="flex flex-wrap items-center gap-2">
                            <h1 class="text-2xl sm:text-3xl font-black text-white">
                                {{ $sae->name }}
                            </h1>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold tracking-wide uppercase bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                Réseau Vérifié
                            </span>
                            @if($sae->origin_type && $sae->isUniversity())
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-500/10 text-amber-300 border border-amber-500/30">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/></svg>
                                    <span>Universitaire{{ $sae->university_name ? ' — ' . $sae->university_name : '' }}</span>
                                </span>
                            @elseif($sae->origin_type && $sae->origin_type->value !== 'INDEPENDANTE')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-white/10 text-slate-300 border border-white/20">
                                    {{ $sae->origin_type->getLabel() }}
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-800 text-slate-300 border border-slate-700">
                                    Structure Indépendante
                                </span>
                            @endif
                        </div>
                        <p class="text-xs sm:text-sm text-slate-400">
                            {{ $sae->provenance_label ?: ($sae->address ?: 'Structure d\'Accompagnement labellisée Hub Africa') }}
                        </p>
                        @if($sae->types->isNotEmpty())
                            <div class="flex flex-wrap gap-1.5 pt-1">
                                @foreach($sae->types as $type)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-white/10 text-slate-200 border border-white/20">
                                        {{ $type->name }}
                                    </span>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                <div class="flex items-center gap-3 text-xs shrink-0">
                    <div class="bg-slate-900/90 px-4 py-2.5 rounded-xl text-center border border-slate-800 min-w-[70px]">
                        <span class="block text-white font-bold text-base">{{ $sae->programs->count() }}</span>
                        <span class="text-slate-400 text-[10px] uppercase font-semibold">Programmes</span>
                    </div>
                    <div class="bg-slate-900/90 px-4 py-2.5 rounded-xl text-center border border-slate-800 min-w-[70px]">
                        <span class="block text-[#F8B81D] font-bold text-base">{{ $sae->projects_count }}</span>
                        <span class="text-slate-400 text-[10px] uppercase font-semibold">Startups</span>
                    </div>
                    <div class="bg-slate-900/90 px-4 py-2.5 rounded-xl text-center border border-slate-800 min-w-[70px]">
                        <span class="block text-amber-400 font-bold text-base">{{ $sae->coaches_count }}</span>
                        <span class="text-slate-400 text-[10px] uppercase font-semibold">Mentors</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Main Content Section --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 space-y-12">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
            
            {{-- Colonne principale (2/3) --}}
            <div class="lg:col-span-2 space-y-8">
                
                {{-- Présentation générale / Pitch --}}
                @if($sae->description)
                    <div class="bg-white rounded-2xl p-6 sm:p-8 border border-slate-200/90 shadow-xs space-y-3">
                        <div class="flex items-center gap-2.5 text-[#2F3284]">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <h2 class="text-lg font-bold text-slate-900">À propos de la structure</h2>
                        </div>
                        <p class="text-sm text-slate-600 leading-relaxed whitespace-pre-line">
                            {{ $sae->description }}
                        </p>
                    </div>
                @endif

                {{-- Grille Vision & Mission (Mise en valeur distincte) --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Carte Vision --}}
                    <div class="bg-gradient-to-br from-[#2F3284]/5 via-white to-white rounded-2xl p-6 sm:p-7 border border-[#2F3284]/20 shadow-xs space-y-3.5 relative overflow-hidden">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-[#2F3284] text-white flex items-center justify-center shrink-0 shadow-sm">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </div>
                            <div>
                                <span class="text-[10px] font-bold uppercase tracking-wider text-[#2F3284]">Perspective & Avenir</span>
                                <h3 class="text-base font-bold text-slate-900">Notre Vision</h3>
                            </div>
                        </div>
                        <div class="text-xs sm:text-sm text-slate-700 leading-relaxed">
                            @if($sae->vision)
                                @if(str_contains($sae->vision, '<'))
                                    <div class="prose prose-sm max-w-none text-slate-700 leading-relaxed">{!! str($sae->vision)->sanitizeHtml() !!}</div>
                                @else
                                    <p class="whitespace-pre-line">{{ $sae->vision }}</p>
                                @endif
                            @else
                                <p class="text-slate-500 italic">
                                    Bâtir un écosystème d'innovation durable et propulser les startups africaines vers l'excellence régionale et internationale.
                                </p>
                            @endif
                        </div>
                    </div>

                    {{-- Carte Mission --}}
                    <div class="bg-gradient-to-br from-[#F8B81D]/10 via-white to-white rounded-2xl p-6 sm:p-7 border border-[#F8B81D]/30 shadow-xs space-y-3.5 relative overflow-hidden">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-[#F8B81D] text-slate-950 flex items-center justify-center shrink-0 shadow-sm">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            </div>
                            <div>
                                <span class="text-[10px] font-bold uppercase tracking-wider text-[#b88006]">Action & Impact</span>
                                <h3 class="text-base font-bold text-slate-900">Notre Mission</h3>
                            </div>
                        </div>
                        <div class="text-xs sm:text-sm text-slate-700 leading-relaxed">
                            @if($sae->mission)
                                @if(str_contains($sae->mission, '<'))
                                    <div class="prose prose-sm max-w-none text-slate-700 leading-relaxed">{!! str($sae->mission)->sanitizeHtml() !!}</div>
                                @else
                                    <p class="whitespace-pre-line">{{ $sae->mission }}</p>
                                @endif
                            @else
                                <p class="text-slate-500 italic">
                                    Accompagner, structurer et accélérer les projets entrepreneuriaux à fort impact à travers un mentorat de proximité et l'accès aux opportunités de marché.
                                </p>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Objectifs stratégiques (si renseignés) --}}
                @if($sae->objectives)
                    <div class="bg-white rounded-2xl p-6 sm:p-8 border border-slate-200/90 shadow-xs space-y-3">
                        <div class="flex items-center gap-2.5 text-emerald-600">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <h2 class="text-lg font-bold text-slate-900">Objectifs &amp; Piliers d'Intervention</h2>
                        </div>
                        <div class="text-xs sm:text-sm text-slate-700 leading-relaxed">
                            @if(str_contains($sae->objectives, '<'))
                                <div class="prose prose-sm max-w-none text-slate-700 leading-relaxed">{!! str($sae->objectives)->sanitizeHtml() !!}</div>
                            @else
                                <p class="whitespace-pre-line">{{ $sae->objectives }}</p>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- Services & Offre d'accompagnement (si renseignés) --}}
                @if($sae->services)
                    <div class="bg-white rounded-2xl p-6 sm:p-8 border border-slate-200/90 shadow-xs space-y-3">
                        <div class="flex items-center gap-2.5 text-[#2F3284]">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                            <h2 class="text-lg font-bold text-slate-900">Services &amp; Offre d'Accompagnement</h2>
                        </div>
                        <div class="text-xs sm:text-sm text-slate-700 leading-relaxed">
                            @if(str_contains($sae->services, '<'))
                                <div class="prose prose-sm max-w-none text-slate-700 leading-relaxed">{!! str($sae->services)->sanitizeHtml() !!}</div>
                            @else
                                <p class="whitespace-pre-line">{{ $sae->services }}</p>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- Programmes déployés --}}
                <div class="space-y-6 pt-2">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-xl sm:text-2xl font-black text-slate-900">
                                Programmes d'Accompagnement
                            </h2>
                            <p class="text-xs text-slate-500 mt-0.5">
                                Formations, incubations et accélérations proposées par cette structure
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @forelse($sae->programs as $p)
                            <div class="bg-white rounded-2xl p-6 border border-slate-200/90 shadow-xs hover:shadow-md transition space-y-4 flex flex-col justify-between">
                                <div class="space-y-2.5">
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="inline-block px-2.5 py-0.5 rounded text-[10px] font-bold uppercase bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            Programme Actif
                                        </span>
                                        @if($p->duration_weeks)
                                            <span class="text-[11px] text-slate-500 font-medium">
                                                {{ $p->duration_weeks }} sem.
                                            </span>
                                        @endif
                                    </div>
                                    <h3 class="text-base font-bold text-slate-900 hover:text-[#2F3284] transition">
                                        <a href="{{ route('public.programme.detail', $p->id) }}">
                                            {{ $p->name }}
                                        </a>
                                    </h3>
                                    <p class="text-xs text-slate-600 line-clamp-3 leading-relaxed">
                                        {{ $p->description }}
                                    </p>
                                </div>
                                <div class="pt-4 border-t border-slate-100 flex items-center justify-between gap-3">
                                    <a href="{{ route('public.programme.detail', $p->id) }}" class="text-xs font-bold text-[#2F3284] hover:underline">
                                        Voir détails &rarr;
                                    </a>
                                    <a href="{{ route('public.programme.candidater', $p->id) }}" class="px-3.5 py-1.5 rounded-lg bg-[#F8B81D] hover:bg-[#e5a510] text-slate-900 text-xs font-bold transition">
                                        Candidater
                                    </a>
                                </div>
                            </div>
                        @empty
                            <div class="col-span-full bg-white rounded-2xl p-8 text-center border border-dashed border-slate-300 text-slate-500 text-xs space-y-1">
                                <p class="font-semibold text-slate-700">Aucun programme actif actuellement répertorié</p>
                                <p class="text-slate-400">Les prochains appels à candidatures de cette structure seront publiés ici.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- Mentors & Coachs associés (si présents) --}}
                @if($sae->coaches->isNotEmpty())
                    <div class="space-y-4 pt-2">
                        <div class="flex items-center justify-between">
                            <h2 class="text-lg font-bold text-slate-900">
                                Mentors & Experts Mobilisés ({{ $sae->coaches->count() }})
                            </h2>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            @foreach($sae->coaches as $coach)
                                <div class="bg-white rounded-xl p-4 border border-slate-200/90 shadow-xs flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-[#2F3284]/10 text-[#2F3284] font-bold text-xs flex items-center justify-center shrink-0">
                                        {{ substr($coach->name, 0, 2) }}
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="text-xs font-bold text-slate-900 truncate">{{ $coach->name }}</div>
                                        @if(!empty($coach->specialties) && is_array($coach->specialties))
                                            <div class="text-[11px] text-slate-500 truncate mt-0.5">
                                                {{ implode(' • ', array_slice($coach->specialties, 0, 2)) }}
                                            </div>
                                        @else
                                            <div class="text-[11px] text-slate-400 truncate mt-0.5">Coach certifié Hub Africa</div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            {{-- Sidebar droite (1/3) --}}
            <div class="space-y-6">
                
                {{-- Carte Coordonnées & Contact --}}
                <div class="bg-white rounded-2xl p-6 sm:p-7 border border-slate-200/90 shadow-xs space-y-5 text-xs">
                    <div class="border-b border-slate-100 pb-3">
                        <h2 class="text-base font-bold text-slate-900">Informations & Contact</h2>
                        <span class="text-[11px] text-slate-400">Coordonnées de l'organisation</span>
                    </div>

                    <div class="space-y-4 text-slate-600">
                        @if($sae->types->isNotEmpty())
                            <div>
                                <span class="text-slate-400 block text-[11px] font-semibold uppercase tracking-wider mb-1.5">Typologie</span>
                                <div class="flex flex-wrap gap-1">
                                    @foreach($sae->types as $t)
                                        <span class="inline-block px-2 py-0.5 rounded text-[11px] font-semibold bg-[#2F3284]/10 text-[#2F3284]">
                                            {{ $t->name }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if($sae->isUniversity() && $sae->university_name)
                            <div class="flex items-start gap-2.5">
                                <svg class="w-4 h-4 text-amber-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/></svg>
                                <div>
                                    <span class="text-slate-400 block text-[10px] font-semibold uppercase">Tutelle Universitaire</span>
                                    <span class="text-slate-800 font-semibold">{{ $sae->university_name }}</span>
                                </div>
                            </div>
                        @endif

                        @if($sae->country || $sae->city)
                            <div class="flex items-start gap-2.5">
                                <svg class="w-4 h-4 text-slate-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <div>
                                    <span class="text-slate-400 block text-[10px] font-semibold uppercase">Provenance / Pays</span>
                                    <span class="text-slate-800 font-semibold">{{ $sae->provenance_label }}</span>
                                </div>
                            </div>
                        @endif

                        @if($sae->email)
                            <div class="flex items-start gap-2.5">
                                <svg class="w-4 h-4 text-slate-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                <div>
                                    <span class="text-slate-400 block text-[10px] font-semibold uppercase">Email officiel</span>
                                    <a href="mailto:{{ $sae->email }}" class="text-[#2F3284] font-semibold hover:underline break-all">{{ $sae->email }}</a>
                                </div>
                            </div>
                        @endif

                        @if($sae->phone)
                            <div class="flex items-start gap-2.5">
                                <svg class="w-4 h-4 text-slate-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                <div>
                                    <span class="text-slate-400 block text-[10px] font-semibold uppercase">Téléphone</span>
                                    <a href="tel:{{ $sae->phone }}" class="text-slate-800 font-semibold hover:underline">{{ $sae->phone }}</a>
                                </div>
                            </div>
                        @endif

                        @if($sae->website)
                            <div class="flex items-start gap-2.5">
                                <svg class="w-4 h-4 text-slate-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
                                <div>
                                    <span class="text-slate-400 block text-[10px] font-semibold uppercase">Site Web</span>
                                    <a href="{{ $sae->website }}" target="_blank" rel="noopener noreferrer" class="text-[#2F3284] font-semibold hover:underline break-all">
                                        {{ preg_replace('#^https?://#', '', rtrim($sae->website, '/')) }} &nearr;
                                    </a>
                                </div>
                            </div>
                        @endif

                        @if($sae->address)
                            <div class="flex items-start gap-2.5">
                                <svg class="w-4 h-4 text-slate-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                <div>
                                    <span class="text-slate-400 block text-[10px] font-semibold uppercase">Adresse physique</span>
                                    <span class="text-slate-800">{{ $sae->address }}</span>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Carte CTA Entrepreneur --}}
                <div class="bg-gradient-to-br from-slate-900 to-slate-950 rounded-2xl p-6 border border-slate-800 text-white space-y-4">
                    <div class="flex items-center gap-2 text-[#F8B81D]">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
                        <h3 class="text-sm font-bold uppercase tracking-wide">Vous êtes entrepreneur ?</h3>
                    </div>
                    <p class="text-xs text-slate-300 leading-relaxed">
                        Rejoignez l'un des programmes portés par {{ $sae->name }} pour bénéficier d'un coaching structurant et faire grandir votre projet.
                    </p>
                    <a href="{{ route('public.programmes') }}" class="w-full inline-flex items-center justify-center px-4 py-2.5 rounded-xl bg-[#F8B81D] hover:bg-[#e5a510] text-slate-950 font-bold text-xs transition">
                        Explorer tous les programmes
                    </a>
                </div>

            </div>
        </div>
    </section>
</x-layouts.public>
