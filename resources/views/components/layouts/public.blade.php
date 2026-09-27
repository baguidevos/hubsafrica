<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Package officiel laravel/head pour le SEO institutionnel --}}
    @head

    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">

    {{-- Variables CSS configurables depuis config('hubafrica.theme') --}}
    <style>
        :root {
            --ha-primary: {{ config('hubafrica.theme.primary', '#064e3b') }};
            --ha-primary-hover: {{ config('hubafrica.theme.primary_hover', '#04382a') }};
            --ha-primary-light: {{ config('hubafrica.theme.primary_light', '#059669') }};
            --ha-secondary: {{ config('hubafrica.theme.secondary', '#0f172a') }};
            --ha-secondary-light: {{ config('hubafrica.theme.secondary_light', '#1e293b') }};
            --ha-accent: {{ config('hubafrica.theme.accent', '#d97706') }};
            --ha-accent-light: {{ config('hubafrica.theme.accent_light', '#f59e0b') }};
            --ha-surface: {{ config('hubafrica.theme.surface', '#f8fafc') }};
        }
    </style>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-slate-50 text-slate-900 font-sans antialiased selection:bg-emerald-600 selection:text-white flex flex-col min-h-screen" x-data="{ mobileMenuOpen: false }">

    {{-- 1. Bandeau Institutionnel Supérieur --}}
    <header class="bg-slate-950 text-slate-300 text-xs border-b border-slate-800/80">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-2 flex flex-col sm:flex-row items-center justify-between gap-2">
            <div class="flex items-center gap-3">
                <span class="inline-flex items-center gap-1.5 font-semibold text-emerald-400">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
                    Portail Officiel
                </span>
                <span class="text-slate-600 hidden sm:inline">&bull;</span>
                <span class="text-slate-400 text-[11px] sm:text-xs">Réseau Panafricain des Structures d'Accompagnement à l'Entrepreneuriat</span>
            </div>

            <div class="flex items-center gap-4 text-[11px] text-slate-400">
                <a href="mailto:{{ config('hubafrica.contact.email') }}" class="hover:text-emerald-400 transition flex items-center gap-1">
                    <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    <span>{{ config('hubafrica.contact.email') }}</span>
                </a>
                <span class="text-slate-700">&bull;</span>
                <span class="flex items-center gap-1">
                    <svg class="w-3.5 h-3.5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                    <span>{{ config('hubafrica.contact.phone') }}</span>
                </span>
            </div>
        </div>
    </header>

    {{-- 2. Barre Principale de Navigation --}}
    <nav class="sticky top-0 z-40 bg-white/95 backdrop-blur-md border-b border-slate-200/80 shadow-xs transition">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20">
                
                {{-- Logo & Identité Institutionnelle --}}
                <div class="flex items-center gap-3">
                    <a href="{{ route('home') }}" class="group flex items-center gap-3">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-emerald-800 to-slate-900 flex items-center justify-center text-white shadow-md shadow-emerald-950/20 group-hover:scale-105 transition-transform duration-200">
                            {{-- Emblème officiel Panafricain --}}
                            <svg class="w-7 h-7 text-amber-400" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
                            </svg>
                        </div>
                        <div class="flex flex-col">
                            <span class="font-extrabold text-xl tracking-tight text-slate-950 font-sans flex items-center gap-1.5">
                                HUB <span class="text-emerald-700">AFRICA</span>
                                <span class="text-[10px] px-1.5 py-0.5 rounded font-black tracking-widest bg-amber-100 text-amber-800 uppercase">v2</span>
                            </span>
                            <span class="text-[10px] uppercase font-bold tracking-widest text-slate-500">
                                Accompagnement & Suivi de l'Innovation
                            </span>
                        </div>
                    </a>
                </div>

                {{-- Liens de Navigation Desktop --}}
                <div class="hidden md:flex items-center gap-8">
                    <a href="{{ route('home') }}" class="text-sm font-semibold {{ request()->routeIs('home') ? 'text-emerald-700 border-b-2 border-emerald-600 pb-1' : 'text-slate-600 hover:text-emerald-700' }} transition">
                        Accueil
                    </a>
                    <a href="{{ route('public.programmes') }}" class="text-sm font-semibold {{ request()->routeIs('public.programmes*') ? 'text-emerald-700 border-b-2 border-emerald-600 pb-1' : 'text-slate-600 hover:text-emerald-700' }} transition">
                        Programmes d'Accompagnement
                    </a>
                    <a href="{{ route('public.structures') }}" class="text-sm font-semibold {{ request()->routeIs('public.structures*') ? 'text-emerald-700 border-b-2 border-emerald-600 pb-1' : 'text-slate-600 hover:text-emerald-700' }} transition">
                        Réseau des SAEs
                    </a>
                    <a href="{{ route('home') }}#cube" class="text-sm font-semibold text-slate-600 hover:text-emerald-700 transition">
                        Cadre CUBE S&amp;E
                    </a>
                </div>

                {{-- Boutons d'Action & Menus Déroulants --}}
                <div class="hidden lg:flex items-center gap-3">
                    {{-- Dropdown Espaces Sécurisés --}}
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" @click.away="open = false" type="button" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-100 transition">
                            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            <span>Espaces Dédiés</span>
                            <svg class="w-3.5 h-3.5 text-slate-400 transition" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>

                        <div x-show="open" x-transition.origin.top.right class="absolute right-0 mt-2 w-64 rounded-xl bg-white border border-slate-200 shadow-xl py-2 z-50 divide-y divide-slate-100" style="display: none;">
                            <div class="px-4 py-2 text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                                Connexion aux portails
                            </div>
                            <div class="py-1">
                                <a href="/portal" class="flex items-center gap-3 px-4 py-2.5 text-xs text-slate-700 hover:bg-emerald-50 hover:text-emerald-800 transition">
                                    <div class="w-7 h-7 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold">E</div>
                                    <div>
                                        <div class="font-bold">Espace Entrepreneur</div>
                                        <div class="text-[10px] text-slate-500">Suivi projet, livrables, coaching</div>
                                    </div>
                                </a>
                                <a href="/mentor" class="flex items-center gap-3 px-4 py-2.5 text-xs text-slate-700 hover:bg-teal-50 hover:text-teal-800 transition">
                                    <div class="w-7 h-7 rounded-lg bg-teal-100 text-teal-700 flex items-center justify-center font-bold">M</div>
                                    <div>
                                        <div class="font-bold">Espace Coach &amp; Mentor</div>
                                        <div class="text-[10px] text-slate-500">Séances, évaluations, agenda</div>
                                    </div>
                                </a>
                                <a href="/sae" class="flex items-center gap-3 px-4 py-2.5 text-xs text-slate-700 hover:bg-slate-100 hover:text-slate-900 transition">
                                    <div class="w-7 h-7 rounded-lg bg-slate-200 text-slate-800 flex items-center justify-center font-bold">S</div>
                                    <div>
                                        <div class="font-bold">Espace Structure (SAE)</div>
                                        <div class="text-[10px] text-slate-500">Pilotage cohorte, KPIs CUBE</div>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>

                    {{-- CTA Candidater / Rejoindre --}}
                    <a href="{{ route('public.rejoindre') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-emerald-700 to-emerald-800 hover:from-emerald-800 hover:to-emerald-900 text-white font-bold text-xs shadow-md shadow-emerald-900/20 hover:shadow-lg transition transform hover:-translate-y-0.5">
                        <span>Candidater / Rejoindre</span>
                        <svg class="w-4 h-4 text-emerald-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </a>
                </div>

                {{-- Burger Menu Mobile --}}
                <div class="flex md:hidden items-center">
                    <button @click="mobileMenuOpen = !mobileMenuOpen" type="button" class="p-2 rounded-lg text-slate-600 hover:text-slate-900 hover:bg-slate-100">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                </div>
            </div>
        </div>

        {{-- Tiroir Mobile --}}
        <div x-show="mobileMenuOpen" x-transition class="md:hidden border-t border-slate-200 bg-white px-4 pt-3 pb-6 space-y-3" style="display: none;">
            <a href="{{ route('home') }}" class="block px-3 py-2 rounded-lg text-sm font-semibold text-slate-800 hover:bg-slate-100">Accueil</a>
            <a href="{{ route('public.programmes') }}" class="block px-3 py-2 rounded-lg text-sm font-semibold text-slate-800 hover:bg-slate-100">Programmes d'Accompagnement</a>
            <a href="{{ route('public.structures') }}" class="block px-3 py-2 rounded-lg text-sm font-semibold text-slate-800 hover:bg-slate-100">Réseau des SAEs</a>
            <a href="{{ route('home') }}#cube" class="block px-3 py-2 rounded-lg text-sm font-semibold text-slate-800 hover:bg-slate-100">Cadre CUBE S&amp;E</a>
            <div class="pt-3 border-t border-slate-100 flex flex-col gap-2">
                <a href="/portal" class="block w-full text-center px-4 py-2.5 rounded-lg border border-slate-300 text-xs font-bold text-slate-700">Portail Entrepreneur</a>
                <a href="/mentor" class="block w-full text-center px-4 py-2.5 rounded-lg border border-slate-300 text-xs font-bold text-slate-700">Portail Mentor / Coach</a>
                <a href="/sae" class="block w-full text-center px-4 py-2.5 rounded-lg border border-slate-300 text-xs font-bold text-slate-700">Portail Structure SAE</a>
                <a href="{{ route('public.rejoindre') }}" class="block w-full text-center px-4 py-2.5 rounded-lg bg-emerald-700 text-white text-xs font-bold shadow">Candidater / Rejoindre</a>
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
            
            {{-- En-tête Footer avec Logos Partenaires Institutionnels --}}
            <div class="border-b border-slate-800/80 pb-10">
                <div class="text-xs uppercase font-bold tracking-widest text-slate-500 mb-6 text-center sm:text-left">
                    Partenaires d'Appui &amp; Cadre de Concertation Panafricain
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 gap-6 items-center opacity-75 grayscale hover:grayscale-0 transition-all duration-300">
                    <div class="bg-slate-900/60 p-3 rounded-xl border border-slate-800 flex items-center justify-center text-xs font-bold text-slate-300">
                        CUBE S&amp;E
                    </div>
                    <div class="bg-slate-900/60 p-3 rounded-xl border border-slate-800 flex items-center justify-center text-xs font-bold text-slate-300">
                        IYBA-SEED
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
                
                {{-- Col 1 : Identité & Mission --}}
                <div class="space-y-4">
                    <div class="flex items-center gap-2 text-white font-extrabold text-lg">
                        <span class="w-8 h-8 rounded-lg bg-emerald-800 flex items-center justify-center text-amber-400">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
                            </svg>
                        </span>
                        <span>HUB AFRICA</span>
                    </div>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Plateforme panafricaine d'accompagnement entrepreneurial unifiant structures d'incubation, mentors accrédités et startups innovantes selon la méthodologie de mesure d'impact CUBE.
                    </p>
                    <div class="text-xs text-emerald-400 font-semibold">
                        &bull; Dakar &bull; Lomé &bull; Abidjan &bull; Yaoundé &bull; Cotonou
                    </div>
                </div>

                {{-- Col 2 : Programmes & Candidatures --}}
                <div class="space-y-3">
                    <h4 class="text-xs font-bold text-white uppercase tracking-wider">Opportunités</h4>
                    <ul class="space-y-2 text-xs">
                        <li><a href="{{ route('public.programmes') }}" class="hover:text-emerald-400 transition">Tous les programmes d'incubation</a></li>
                        <li><a href="{{ route('public.programmes') }}?status=open" class="hover:text-emerald-400 transition">Appels à candidatures ouverts</a></li>
                        <li><a href="{{ route('public.rejoindre') }}" class="hover:text-emerald-400 transition">Déposer une candidature spontanée</a></li>
                        <li><a href="{{ route('public.structures') }}" class="hover:text-emerald-400 transition">Annuaire des incubateurs et SAEs</a></li>
                    </ul>
                </div>

                {{-- Col 3 : Accès Portails --}}
                <div class="space-y-3">
                    <h4 class="text-xs font-bold text-white uppercase tracking-wider">Espaces Utilisateurs</h4>
                    <ul class="space-y-2 text-xs">
                        <li><a href="/portal" class="hover:text-emerald-400 transition flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>Portail Entrepreneur</a></li>
                        <li><a href="/mentor" class="hover:text-emerald-400 transition flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-teal-500"></span>Portail Coach &amp; Mentor</a></li>
                        <li><a href="/sae" class="hover:text-emerald-400 transition flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>Portail Structure SAE</a></li>
                        <li><a href="{{ route('public.rejoindre') }}" class="hover:text-emerald-400 transition flex items-center gap-1.5"><span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>Orientation &amp; Inscription</a></li>
                    </ul>
                </div>

                {{-- Col 4 : Contact & Certifications --}}
                <div class="space-y-3">
                    <h4 class="text-xs font-bold text-white uppercase tracking-wider">Gouvernance &amp; Contact</h4>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        {{ config('hubafrica.contact.address') }}
                    </p>
                    <div class="text-xs text-slate-400 space-y-1">
                        <div>Email : <a href="mailto:{{ config('hubafrica.contact.email') }}" class="text-emerald-400 hover:underline">{{ config('hubafrica.contact.email') }}</a></div>
                        <div>Assistance : <span class="text-slate-300 font-semibold">{{ config('hubafrica.contact.phone') }}</span></div>
                    </div>
                    <div class="pt-2">
                        <span class="inline-block px-3 py-1 bg-emerald-950 border border-emerald-800/80 rounded-md text-[10px] font-bold text-emerald-300 uppercase">
                            Conforme Standards CUBE S&amp;E
                        </span>
                    </div>
                </div>
            </div>

            {{-- Mentions Légales & Droits --}}
            <div class="pt-8 border-t border-slate-900 flex flex-col sm:flex-row items-center justify-between gap-4 text-[11px] text-slate-500">
                <p>&copy; {{ date('Y') }} Hub Africa. Tous droits réservés. Plateforme Panafricaine de Suivi et d'Évaluation.</p>
                <div class="flex items-center gap-6">
                    <a href="#" class="hover:text-slate-400 transition">Politique de confidentialité</a>
                    <a href="#" class="hover:text-slate-400 transition">Conditions générales</a>
                    <a href="#" class="hover:text-slate-400 transition">Cadre éthique CUBE</a>
                </div>
            </div>
        </div>
    </footer>

    @livewireScripts
</body>
</html>
