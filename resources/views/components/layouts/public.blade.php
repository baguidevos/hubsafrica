<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Package officiel laravel/head pour le SEO institutionnel --}}
    @head

    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">

    {{-- Polices officielles Version 1 (DM Sans & Poppins) --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    {{-- Variables CSS configurables depuis config('hubafrica.theme') --}}
    <style>
        :root {
            --ha-primary: {{ config('hubafrica.theme.primary', '#F8B81D') }};
            --ha-primary-hover: {{ config('hubafrica.theme.primary_hover', '#e5a510') }};
            --ha-primary-light: {{ config('hubafrica.theme.primary_light', '#fef3c7') }};
            --ha-secondary: {{ config('hubafrica.theme.secondary', '#2F3284') }};
            --ha-secondary-light: {{ config('hubafrica.theme.secondary_light', '#4347a8') }};
            --ha-accent: {{ config('hubafrica.theme.accent', '#F9B104') }};
            --ha-accent-light: {{ config('hubafrica.theme.accent_light', '#fbbf24') }};
            --ha-surface: {{ config('hubafrica.theme.surface', '#f8fafc') }};
        }
    </style>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-slate-50 text-slate-900 font-sans antialiased selection:bg-[#F8B81D] selection:text-slate-950 flex flex-col min-h-screen" x-data="{ mobileMenuOpen: false }">

    {{-- 1. Bandeau Institutionnel Supérieur --}}
    <header class="bg-slate-950 text-slate-300 text-xs border-b border-slate-800/80">
        <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 py-2">
            {{-- Version Mobile (< sm) : compacte, aérée, sans retour à la ligne cassé ni artefact visuel --}}
            <div class="flex sm:hidden items-center justify-between gap-2">
                <div class="flex items-center gap-2 min-w-0">
                    <span class="inline-flex items-center gap-1.5 font-bold text-[#F8B81D] whitespace-nowrap text-[11px] shrink-0">
                        <span class="relative flex h-2 w-2 shrink-0">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-[#F8B81D] opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-[#F8B81D]"></span>
                        </span>
                        Portail Officiel
                    </span>
                    <span class="text-slate-700 shrink-0">&bull;</span>
                    <span class="text-slate-400 text-[10.5px] truncate font-medium">
                        Réseau Panafricain SAE
                    </span>
                </div>

                <div class="flex items-center gap-2 shrink-0">
                    <a href="mailto:{{ config('hubafrica.contact.email') }}" class="inline-flex items-center gap-1.5 text-[11px] text-slate-300 hover:text-[#F8B81D] transition py-1 px-2.5 rounded-lg bg-slate-900 border border-slate-800 hover:border-[#F8B81D]/30" title="{{ config('hubafrica.contact.email') }}">
                        <svg class="w-3.5 h-3.5 text-[#F8B81D] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        <span class="font-medium text-[10.5px]">Contact</span>
                    </a>
                </div>
            </div>

            {{-- Version Desktop & Tablette (>= sm) : vue large complète avec coordonnées --}}
            <div class="hidden sm:flex items-center justify-between gap-4">
                <div class="flex items-center gap-3 min-w-0">
                    <span class="inline-flex items-center gap-1.5 font-semibold text-[#F8B81D] whitespace-nowrap shrink-0">
                        <span class="relative flex h-2 w-2 shrink-0">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-[#F8B81D] opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-[#F8B81D]"></span>
                        </span>
                        Portail Officiel
                    </span>
                    <span class="text-slate-600 shrink-0">&bull;</span>
                    <span class="text-slate-400 text-xs truncate lg:overflow-visible">
                        Réseau Panafricain des Structures d'Accompagnement à l'Entrepreneuriat
                    </span>
                </div>

                <div class="flex items-center gap-4 text-xs text-slate-400 shrink-0">
                    <a href="mailto:{{ config('hubafrica.contact.email') }}" class="hover:text-[#F8B81D] transition flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-[#F8B81D] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        <span>{{ config('hubafrica.contact.email') }}</span>
                    </a>
                    @if(config('hubafrica.contact.phone'))
                        <span class="text-slate-700">&bull;</span>
                        <a href="tel:{{ config('hubafrica.contact.phone') }}" class="hover:text-[#F9B104] transition flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-[#F9B104] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            <span>{{ config('hubafrica.contact.phone') }}</span>
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </header>

    {{-- 2. Barre Principale de Navigation --}}
    <nav class="sticky top-0 z-40 bg-white/95 backdrop-blur-md border-b border-slate-200/80 shadow-xs transition">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-18 sm:h-20">
                
                {{-- Logo Officiel Hub-Africa issu de la Version 1 --}}
                <div class="flex items-center gap-3">
                    <a href="{{ route('home') }}" class="group flex items-center">
                        <img src="{{ asset('images/koworkers.png') }}" alt="Hub-Africa" class="h-10 sm:h-12 w-auto object-contain group-hover:opacity-90 transition">
                    </a>
                </div>

                {{-- Liens de Navigation Desktop (Visible à partir de lg: 1024px pour ne pas déborder) --}}
                <div class="hidden lg:flex items-center gap-6 xl:gap-8">
                    <a href="{{ route('home') }}" class="text-xs sm:text-sm font-semibold {{ request()->routeIs('home') ? 'text-[#2F3284] font-bold border-b-2 border-[#F8B81D] pb-1' : 'text-slate-600 hover:text-[#2F3284]' }} transition">
                        Accueil
                    </a>
                    <a href="{{ route('public.programmes') }}" class="text-xs sm:text-sm font-semibold {{ request()->routeIs('public.programmes*') ? 'text-[#2F3284] font-bold border-b-2 border-[#F8B81D] pb-1' : 'text-slate-600 hover:text-[#2F3284]' }} transition">
                        Programmes
                    </a>
                    <a href="{{ route('public.structures') }}" class="text-xs sm:text-sm font-semibold {{ request()->routeIs('public.structures*') ? 'text-[#2F3284] font-bold border-b-2 border-[#F8B81D] pb-1' : 'text-slate-600 hover:text-[#2F3284]' }} transition">
                        Structures SAE
                    </a>
                    <a href="{{ route('home') }}#impact" class="text-xs sm:text-sm font-semibold text-slate-600 hover:text-[#2F3284] transition">
                        À propos
                    </a>
                </div>

                {{-- Boutons d'Action & Menus Déroulants --}}
                <div class="hidden lg:flex items-center gap-3 shrink-0">
                    {{-- Dropdown Espaces Sécurisés --}}
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" @click.away="open = false" type="button" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-50 hover:text-[#2F3284] transition">
                            <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            <span>Espaces Dédiés</span>
                            <svg class="w-3 h-3 text-slate-400 transition" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>

                        <div x-show="open" x-transition.origin.top.right class="absolute right-0 mt-2 w-64 rounded-2xl bg-white border border-slate-200 shadow-xl py-2 z-50 divide-y divide-slate-100" style="display: none;">
                            <div class="px-4 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                                Connexion aux portails
                            </div>
                            <div class="py-1">
                                <a href="/portal" class="flex items-center gap-3 px-4 py-2.5 text-xs text-slate-700 hover:bg-amber-50 hover:text-[#2F3284] transition">
                                    <div class="w-7 h-7 rounded-lg bg-[#F8B81D]/20 text-[#2F3284] flex items-center justify-center font-bold">E</div>
                                    <div>
                                        <div class="font-bold">Espace Entrepreneur</div>
                                        <div class="text-[10px] text-slate-500">Suivi projet, livrables, coaching</div>
                                    </div>
                                </a>
                                <a href="/mentor" class="flex items-center gap-3 px-4 py-2.5 text-xs text-slate-700 hover:bg-indigo-50 hover:text-[#2F3284] transition">
                                    <div class="w-7 h-7 rounded-lg bg-[#2F3284]/15 text-[#2F3284] flex items-center justify-center font-bold">M</div>
                                    <div>
                                        <div class="font-bold">Espace Coach &amp; Mentor</div>
                                        <div class="text-[10px] text-slate-500">Séances, évaluations, agenda</div>
                                    </div>
                                </a>
                                <a href="/sae" class="flex items-center gap-3 px-4 py-2.5 text-xs text-slate-700 hover:bg-slate-100 hover:text-slate-900 transition">
                                    <div class="w-7 h-7 rounded-lg bg-slate-200 text-slate-800 flex items-center justify-center font-bold">S</div>
                                    <div>
                                        <div class="font-bold">Espace Structure (SAE)</div>
                                        <div class="text-[10px] text-slate-500">Pilotage cohorte, promotions</div>
                                    </div>
                                </a>
                            </div>
                            <div class="p-2.5 bg-slate-50 border-t border-slate-100">
                                <a href="{{ route('public.rejoindre') }}" class="flex items-center justify-center gap-1.5 py-2 px-3 rounded-xl bg-[#2F3284]/10 hover:bg-[#2F3284]/15 text-[#2F3284] text-[11px] font-bold transition">
                                    <span>Nouvel utilisateur ? S'orienter &rarr;</span>
                                </a>
                            </div>
                        </div>
                    </div>

                    {{-- CTA Candidater / Rejoindre (Bouton Or V1) --}}
                    <a href="{{ route('public.rejoindre') }}" class="inline-flex items-center gap-1.5 px-4 sm:px-5 py-2 rounded-xl bg-[#F8B81D] hover:bg-[#e5a510] text-slate-950 font-bold text-xs shadow-sm hover:shadow transition transform hover:-translate-y-0.5">
                        <span>Rejoindre</span>
                        <svg class="w-3.5 h-3.5 text-slate-950" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </a>
                </div>

                {{-- Burger Menu Mobile & Tablette (< lg) avec bouton CTA Rejoindre rapide --}}
                <div class="flex lg:hidden items-center gap-2">
                    <a href="{{ route('public.rejoindre') }}" class="inline-flex items-center px-3 py-1.5 rounded-xl bg-[#F8B81D] hover:bg-[#e5a510] text-slate-950 font-bold text-xs shadow-xs transition">
                        Rejoindre
                    </a>
                    <button @click="mobileMenuOpen = !mobileMenuOpen" type="button" class="p-2 rounded-xl text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition" aria-label="Menu principal">
                        <svg x-show="!mobileMenuOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                        <svg x-show="mobileMenuOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>
        </div>

        {{-- Tiroir Mobile & Tablette --}}
        <div x-show="mobileMenuOpen" x-transition class="lg:hidden border-t border-slate-200 bg-white px-4 pt-3 pb-6 space-y-3" style="display: none;">
            <a href="{{ route('home') }}" class="block px-3 py-2 rounded-lg text-sm font-semibold text-slate-800 hover:bg-slate-100 hover:text-[#2F3284]">Accueil</a>
            <a href="{{ route('public.programmes') }}" class="block px-3 py-2 rounded-lg text-sm font-semibold text-slate-800 hover:bg-slate-100 hover:text-[#2F3284]">Programmes</a>
            <a href="{{ route('public.structures') }}" class="block px-3 py-2 rounded-lg text-sm font-semibold text-slate-800 hover:bg-slate-100 hover:text-[#2F3284]">Structures SAE</a>
            <a href="{{ route('home') }}#impact" class="block px-3 py-2 rounded-lg text-sm font-semibold text-slate-800 hover:bg-slate-100 hover:text-[#2F3284]">À propos</a>
            <div class="pt-3 border-t border-slate-100 flex flex-col gap-2">
                <div class="px-2 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Connexion Portails</div>
                <a href="/portal" class="block w-full text-center px-4 py-2.5 rounded-xl border border-slate-300 text-xs font-bold text-slate-700 hover:text-[#2F3284] hover:bg-slate-50 transition">Portail Entrepreneur</a>
                <a href="/mentor" class="block w-full text-center px-4 py-2.5 rounded-xl border border-slate-300 text-xs font-bold text-slate-700 hover:text-[#2F3284] hover:bg-slate-50 transition">Portail Coach &amp; Mentor</a>
                <a href="/sae" class="block w-full text-center px-4 py-2.5 rounded-xl border border-slate-300 text-xs font-bold text-slate-700 hover:text-[#2F3284] hover:bg-slate-50 transition">Portail Structure SAE</a>
                <a href="/admin" class="block w-full text-center px-4 py-2.5 rounded-xl border border-slate-300 text-xs font-bold text-slate-700 hover:text-[#2F3284] hover:bg-slate-50 transition">Portail Super Admin</a>
                <a href="{{ route('public.rejoindre') }}" class="block w-full text-center px-4 py-2.5 rounded-xl bg-[#F8B81D] hover:bg-[#e5a510] text-slate-950 text-xs font-bold shadow transition">Rejoindre / Candidater</a>
            </div>
            
            {{-- Coordonnées institutionnelles en bas de tiroir mobile --}}
            <div class="pt-3 border-t border-slate-100 text-xs text-slate-500 space-y-2 px-1">
                <a href="mailto:{{ config('hubafrica.contact.email') }}" class="flex items-center gap-2 text-slate-600 hover:text-[#2F3284]">
                    <svg class="w-4 h-4 text-[#F8B81D] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    <span>{{ config('hubafrica.contact.email') }}</span>
                </a>
                @if(config('hubafrica.contact.phone'))
                <a href="tel:{{ config('hubafrica.contact.phone') }}" class="flex items-center gap-2 text-slate-600 hover:text-[#2F3284]">
                    <svg class="w-4 h-4 text-[#F9B104] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                    <span>{{ config('hubafrica.contact.phone') }}</span>
                </a>
                @endif
            </div>
        </div>
    </nav>

    {{-- 3. Corps de Page Dynamique --}}
    <main class="flex-grow">
        {{ $slot }}
    </main>

    {{-- 4. Footer Institutionnel & Partenaires --}}
    <footer class="bg-slate-950 text-slate-400 border-t border-slate-800 pt-16 pb-12 mt-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
            
            {{-- En-tête Footer avec Partenaires Institutionnels --}}
            <div class="border-b border-slate-800/80 pb-10">
                <div class="text-xs uppercase font-bold tracking-widest text-slate-500 mb-6 text-center sm:text-left">
                    Partenaires d'Appui &amp; Cadre de Concertation Panafricain
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-4 items-center opacity-85">
                    <div class="bg-slate-900/60 p-3 rounded-xl border border-slate-800 flex items-center justify-center text-xs font-bold text-slate-300">
                        BAD
                    </div>
                    <div class="bg-slate-900/60 p-2.5 rounded-xl border border-slate-800 flex items-center justify-center">
                        <img src="{{ asset('images/iyba-seed.png') }}" alt="IYBA-SEED" class="h-6 w-auto object-contain">
                    </div>
                    <div class="bg-slate-900/60 p-3 rounded-xl border border-slate-800 flex items-center justify-center text-xs font-bold text-slate-300">
                        AFD
                    </div>
                    <div class="bg-slate-900/60 p-3 rounded-xl border border-slate-800 flex items-center justify-center text-xs font-bold text-slate-300">
                        ENABEL
                    </div>
                    <div class="bg-slate-900/60 p-3 rounded-xl border border-slate-800 flex items-center justify-center text-xs font-bold text-slate-300">
                        PROPARCO
                    </div>
                    <div class="bg-slate-900/60 p-3 rounded-xl border border-slate-800 flex items-center justify-center text-xs font-bold text-slate-300">
                        UNION EUROPÉENNE
                    </div>
                </div>
            </div>

            {{-- 4 Colonnes Principales --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                
                {{-- Col 1 : Identité & Mission avec Logo Version 1 --}}
                <div class="space-y-4">
                    <div>
                        <a href="{{ route('home') }}" class="inline-block">
                            <img src="{{ asset('images/koworkers.png') }}" alt="Hub-Africa" class="h-10 sm:h-12 w-auto object-contain brightness-105">
                        </a>
                    </div>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Plateforme panafricaine unifiant les structures d'accompagnement locales, les mentors engagés et les entrepreneurs innovants pour créer des entreprises pérennes et génératrices d'emplois.
                    </p>
                    <div class="text-xs text-[#F8B81D] font-semibold">
                        &bull; Dakar &bull; Lomé &bull; Abidjan &bull; Yaoundé &bull; Cotonou
                    </div>
                </div>

                {{-- Col 2 : Programmes & Candidatures --}}
                <div class="space-y-3">
                    <h4 class="text-xs font-bold text-white uppercase tracking-wider">Opportunités</h4>
                    <ul class="space-y-2 text-xs">
                        <li><a href="{{ route('public.programmes') }}" class="hover:text-[#F8B81D] transition">Tous les programmes d'incubation</a></li>
                        <li><a href="{{ route('public.programmes') }}?status=open" class="hover:text-[#F8B81D] transition">Appels à candidatures ouverts</a></li>
                        <li><a href="{{ route('public.rejoindre') }}" class="hover:text-[#F8B81D] transition">Déposer une candidature</a></li>
                        <li><a href="{{ route('public.structures') }}" class="hover:text-[#F8B81D] transition">Annuaire des structures (SAE)</a></li>
                    </ul>
                </div>

                {{-- Col 3 : Accès Portails --}}
                <div class="space-y-3">
                    <h4 class="text-xs font-bold text-white uppercase tracking-wider">Espaces Utilisateurs</h4>
                    <ul class="space-y-2 text-xs">
                        <li><a href="/portal" class="hover:text-[#F8B81D] transition flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-[#F8B81D]"></span>Portail Entrepreneur</a></li>
                        <li><a href="/mentor" class="hover:text-[#F8B81D] transition flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-[#2F3284]"></span>Portail Coach &amp; Mentor</a></li>
                        <li><a href="/sae" class="hover:text-[#F8B81D] transition flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>Portail Structure SAE</a></li>
                        <li><a href="{{ route('public.rejoindre') }}" class="hover:text-[#F8B81D] transition flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-[#F9B104]"></span>Orientation &amp; Inscription</a></li>
                    </ul>
                </div>

                {{-- Col 4 : Contact & Réseau --}}
                <div class="space-y-3">
                    <h4 class="text-xs font-bold text-white uppercase tracking-wider">Contact &amp; Réseau</h4>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        {{ config('hubafrica.contact.address') }}
                    </p>
                    <div class="text-xs text-slate-400 space-y-1">
                        <div>Email : <a href="mailto:{{ config('hubafrica.contact.email') }}" class="text-[#F8B81D] hover:underline">{{ config('hubafrica.contact.email') }}</a></div>
                        <div>Assistance : <span class="text-slate-300 font-semibold">{{ config('hubafrica.contact.phone') }}</span></div>
                    </div>
                    <div class="pt-2">
                        <span class="inline-block px-3 py-1 bg-slate-900 border border-[#F8B81D]/40 rounded-md text-[10px] font-bold text-[#F8B81D] uppercase">
                            Réseau Labellisé Hub Africa
                        </span>
                    </div>
                </div>
            </div>

            {{-- Mentions Légales & Droits --}}
            <div class="pt-8 border-t border-slate-900 flex flex-col sm:flex-row items-center justify-between gap-4 text-[11px] text-slate-500">
                <p>&copy; {{ date('Y') }} Hub Africa. Tous droits réservés. Plateforme Panafricaine d'Accompagnement à l'Entrepreneuriat.</p>
                <div class="flex items-center gap-6">
                    <a href="#" class="hover:text-slate-400 transition">Politique de confidentialité</a>
                    <a href="#" class="hover:text-slate-400 transition">Conditions générales</a>
                    <a href="#" class="hover:text-slate-400 transition">Charte éthique</a>
                </div>
            </div>
        </div>
    </footer>

    @livewireScripts
</body>
</html>
