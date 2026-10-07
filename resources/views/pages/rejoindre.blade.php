<x-layouts.public>
    <section class="bg-slate-950 text-white py-14 lg:py-18 border-b border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center max-w-3xl mx-auto space-y-3">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-slate-900 text-[#F8B81D] border border-[#F8B81D]/30 text-xs font-semibold">
                Portail d'Orientation &amp; Accès
            </span>
            <h1 class="text-3xl sm:text-4xl lg:text-5xl font-black tracking-tight">
                Rejoindre la Dynamique <span class="text-[#F8B81D]">Hub Africa</span>
            </h1>
            <p class="text-xs sm:text-sm text-slate-300 leading-relaxed max-w-2xl mx-auto font-normal">
                Sélectionnez votre profil d'acteur pour être orienté vers les opportunités, les appels à candidatures ou votre espace de gestion dédié.
            </p>
        </div>
    </section>

    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14 sm:py-16">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 sm:gap-8">
            
            {{-- Profil 1 : Entrepreneur --}}
            <div class="bg-white rounded-2xl p-7 sm:p-8 border border-slate-200/90 shadow-xs hover:shadow-lg transition-all duration-200 flex flex-col justify-between">
                <div class="space-y-4">
                    <div class="w-12 h-12 rounded-xl bg-amber-50 text-[#F8B81D] flex items-center justify-center border border-amber-200">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-[#2F3284] bg-amber-50 px-2.5 py-1 rounded-md">
                        Startups &amp; Fondateurs
                    </span>
                    <h3 class="text-lg font-black text-slate-900">Je suis Entrepreneur</h3>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Vous développez une entreprise et cherchez un accompagnement méthodologique, des mentors experts et un accès structuré au financement.
                    </p>
                    <ul class="space-y-2 pt-2 text-xs text-slate-600">
                        <li class="flex items-center gap-2 text-[#2F3284] font-medium">✓ Candidater aux programmes ouverts</li>
                        <li class="flex items-center gap-2">✓ Diagnostic de maturité et plan d'action</li>
                        <li class="flex items-center gap-2">✓ Préparation du dossier investisseur</li>
                    </ul>
                </div>

                <div class="pt-8 space-y-3">
                    <a
                        href="{{ route('public.programmes') }}"
                        class="block w-full py-3 rounded-xl bg-[#F8B81D] hover:bg-[#e5a510] text-slate-950 font-bold text-xs text-center shadow-xs transition"
                    >
                        Explorer les programmes &rarr;
                    </a>
                    <a
                        href="/portal"
                        class="block w-full py-2.5 rounded-xl border border-slate-300 hover:bg-slate-50 text-slate-700 font-semibold text-xs text-center transition"
                    >
                        Connexion Espace Entrepreneur
                    </a>
                </div>
            </div>

            {{-- Profil 2 : Mentor & Coach --}}
            <div class="bg-white rounded-2xl p-7 sm:p-8 border border-slate-200/90 shadow-xs hover:shadow-lg transition-all duration-200 flex flex-col justify-between">
                <div class="space-y-4">
                    <div class="w-12 h-12 rounded-xl bg-[#2F3284]/10 text-[#2F3284] flex items-center justify-center border border-[#2F3284]/20">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    </div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-[#2F3284] bg-[#2F3284]/10 px-2.5 py-1 rounded-md">
                        Experts &amp; Mentors Métier
                    </span>
                    <h3 class="text-lg font-black text-slate-900">Je suis Coach / Mentor</h3>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Vous êtes un professionnel expérimenté désireux de transmettre vos compétences et de guider les fondateurs vers la rentabilité économique.
                    </p>
                    <ul class="space-y-2 pt-2 text-xs text-slate-600">
                        <li class="flex items-center gap-2 text-[#2F3284] font-medium">✓ Conduite des séances de coaching</li>
                        <li class="flex items-center gap-2">✓ Évaluation des livrables méthodologiques</li>
                        <li class="flex items-center gap-2">✓ Suivi du portefeuille de projets</li>
                    </ul>
                </div>

                <div class="pt-8 space-y-3">
                    <a
                        href="/mentor"
                        class="block w-full py-3 rounded-xl bg-[#2F3284] hover:bg-[#1e2167] text-white font-bold text-xs text-center shadow-xs transition"
                    >
                        Connexion Espace Mentor &rarr;
                    </a>
                    <div class="p-2.5 rounded-xl bg-slate-50 text-[11px] text-slate-500 text-center">
                        Intégration sur invitation directe d'une structure SAE
                    </div>
                </div>
            </div>

            {{-- Profil 3 : Structure SAE --}}
            <div class="bg-white rounded-2xl p-7 sm:p-8 border border-slate-200/90 shadow-xs hover:shadow-lg transition-all duration-200 flex flex-col justify-between">
                <div class="space-y-4">
                    <div class="w-12 h-12 rounded-xl bg-slate-100 text-slate-800 flex items-center justify-center border border-slate-200">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    </div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-700 bg-slate-100 px-2.5 py-1 rounded-md">
                        Incubateurs &amp; Hubs d'Innovation
                    </span>
                    <h3 class="text-lg font-black text-slate-900">Je représente une SAE</h3>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Vous pilotez une structure d'accompagnement et souhaitez orchestrer vos promotions, affecter vos mentors et suivre vos indicateurs d'impact.
                    </p>
                    <ul class="space-y-2 pt-2 text-xs text-slate-600">
                        <li class="flex items-center gap-2 text-slate-800 font-medium">✓ Gestion des cohortes &amp; inscriptions</li>
                        <li class="flex items-center gap-2">✓ Tableaux de bord de suivi &amp; performance</li>
                        <li class="flex items-center gap-2">✓ Rapports d'avancement pour les partenaires</li>
                    </ul>
                </div>

                <div class="pt-8 space-y-3">
                    <a
                        href="/sae/register"
                        class="block w-full py-3 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs text-center shadow-xs transition"
                    >
                        Enregistrer ma structure SAE &rarr;
                    </a>
                    <a
                        href="/sae"
                        class="block w-full py-2.5 rounded-xl border border-slate-300 hover:bg-slate-50 text-slate-700 font-semibold text-xs text-center transition"
                    >
                        Déjà inscrit ? Connexion Espace SAE
                    </a>
                    <a
                        href="{{ route('public.structures') }}"
                        class="block w-full py-2 rounded-xl text-slate-500 hover:text-slate-800 text-[11px] text-center transition"
                    >
                        Consulter l'annuaire des membres
                    </a>
                </div>
            </div>
        </div>
    </section>
</x-layouts.public>
