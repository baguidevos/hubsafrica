<div class="w-full border-b border-slate-200/80 dark:border-slate-800/80 backdrop-blur-md sticky top-0 z-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-14 sm:h-16 flex items-center justify-between text-xs">
        {{-- Lien Retour Accueil avec Logo --}}
        <a href="{{ route('home') }}" class="group inline-flex items-center gap-2.5 text-slate-700 dark:text-slate-300 hover:text-[#2F3284] dark:hover:text-[#F8B81D] font-semibold transition">
            <svg class="w-4 h-4 text-[#F8B81D] transition-transform group-hover:-translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            <span class="font-bold">Hubs Africa</span>
            <span class="text-slate-400 dark:text-slate-600 hidden sm:inline">&bull;</span>
            <span class="text-slate-500 dark:text-slate-400 font-normal hidden sm:inline">Accueil du portail</span>
        </a>

        {{-- Badge de réassurance & Sécurité --}}
        <div class="flex items-center gap-3">
            <span class="hidden md:inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-slate-100 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-300 text-[11px] font-medium">
                <span class="relative flex h-2 w-2 shrink-0">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                </span>
                Accès Chiffré &amp; Sécurisé SSL
            </span>

            <a href="{{ route('public.rejoindre') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-[#2F3284]/10 dark:bg-[#F8B81D]/15 text-[#2F3284] dark:text-[#F8B81D] hover:bg-[#2F3284]/20 font-bold text-[11px] transition">
                <span>Choisir son espace</span>
                <span aria-hidden="true">&rarr;</span>
            </a>
        </div>
    </div>
</div>
