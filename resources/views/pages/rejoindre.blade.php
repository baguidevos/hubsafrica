<x-layouts.public>
    <section class="bg-gradient-to-b from-slate-900 to-slate-950 text-white py-16 lg:py-20 border-b border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center max-w-3xl mx-auto space-y-4">
            <span class="inline-flex items-center gap-1.5 px-3.5 py-1 rounded-full bg-emerald-950 text-emerald-300 border border-emerald-800 text-xs font-bold tracking-wider uppercase">
                Portail d'Orientation &amp; Accès
            </span>
            <h1 class="text-3xl sm:text-4xl lg:text-5xl font-black tracking-tight">
                Rejoindre la Dynamique <span class="text-emerald-400">Hub Africa</span>
            </h1>
            <p class="text-sm sm:text-base text-slate-300 leading-relaxed font-normal">
                Sélectionnez votre profil d'acteur pour être orienté vers les opportunités, les appels à candidatures ou votre espace de gestion dédié.
            </p>
        </div>
    </section>

    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            
            {{-- Profil 1 : Entrepreneur --}}
            <div class="bg-white rounded-3xl p-8 border border-slate-200/90 shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col justify-between group hover:-translate-y-1">
                <div class="space-y-4">
                    <div class="w-14 h-14 rounded-2xl bg-emerald-100 text-emerald-800 flex items-center justify-center font-black text-2xl group-hover:scale-105 transition">
                        🚀
                    </div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-md">
                        Startups &amp; Porteurs de Projets
                    </span>
                    <h3 class="text-xl font-black text-slate-900">Je suis Entrepreneur</h3>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Vous développez une solution innovante et cherchez un accompagnement méthodologique de haut niveau, des mentors dédiés et un accès structuré au financement.
                    </p>
                    <ul class="space-y-2 pt-2 text-xs text-slate-500">
                        <li class="flex items-center gap-2">✓ Candidater aux programmes ouverts</li>
                        <li class="flex items-center gap-2">✓ Diagnostic de maturité CUBE</li>
                        <li class="flex items-center gap-2">✓ Préparation de votre Data Room</li>
                    </ul>
                </div>

                <div class="pt-8 space-y-3">
                    <a
                        href="{{ route('public.programmes') }}"
                        class="block w-full py-3 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-xs text-center shadow-md transition"
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
            <div class="bg-white rounded-3xl p-8 border border-slate-200/90 shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col justify-between group hover:-translate-y-1">
                <div class="space-y-4">
                    <div class="w-14 h-14 rounded-2xl bg-teal-100 text-teal-800 flex items-center justify-center font-black text-2xl group-hover:scale-105 transition">
                        🎯
                    </div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-teal-700 bg-teal-50 px-2.5 py-1 rounded-md">
                        Experts &amp; Mentors Métier
                    </span>
                    <h3 class="text-xl font-black text-slate-900">Je suis Coach / Mentor</h3>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Vous êtes un professionnel accrédité ou un chef d'entreprise désireux de transmettre vos compétences et de guider les fondateurs vers la viabilité économique.
                    </p>
                    <ul class="space-y-2 pt-2 text-xs text-slate-500">
                        <li class="flex items-center gap-2">✓ Conduite des séances de coaching</li>
                        <li class="flex items-center gap-2">✓ Notation des livrables méthodologiques</li>
                        <li class="flex items-center gap-2">✓ Suivi de votre portefeuille de projets</li>
                    </ul>
                </div>

                <div class="pt-8 space-y-3">
                    <a
                        href="/mentor"
                        class="block w-full py-3 rounded-xl bg-teal-700 hover:bg-teal-800 text-white font-bold text-xs text-center shadow-md transition"
                    >
                        Connexion Espace Mentor &rarr;
                    </a>
                    <div class="p-2.5 rounded-xl bg-slate-50 text-[11px] text-slate-500 text-center">
                        Intégration sur invitation directe de votre structure (SAE)
                    </div>
                </div>
            </div>

            {{-- Profil 3 : Structure SAE --}}
            <div class="bg-white rounded-3xl p-8 border border-slate-200/90 shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col justify-between group hover:-translate-y-1">
                <div class="space-y-4">
                    <div class="w-14 h-14 rounded-2xl bg-slate-100 text-slate-800 flex items-center justify-center font-black text-2xl group-hover:scale-105 transition">
                        🏛️
                    </div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-700 bg-slate-100 px-2.5 py-1 rounded-md">
                        Incubateurs, Accélérateurs, Hubs
                    </span>
                    <h3 class="text-xl font-black text-slate-900">Je représente une SAE</h3>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Vous pilotez une structure d'accompagnement et souhaitez orchestrer vos promotions, affecter vos mentors et suivre vos indicateurs d'impact CUBE S&amp;E.
                    </p>
                    <ul class="space-y-2 pt-2 text-xs text-slate-500">
                        <li class="flex items-center gap-2">✓ Gestion des cohortes &amp; inscriptions</li>
                        <li class="flex items-center gap-2">✓ Tableaux de bord de suivi &amp; S&amp;E</li>
                        <li class="flex items-center gap-2">✓ Rapports certifiés pour les bailleurs</li>
                    </ul>
                </div>

                <div class="pt-8 space-y-3">
                    <a
                        href="/sae"
                        class="block w-full py-3 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs text-center shadow-md transition"
                    >
                        Connexion Espace SAE &rarr;
                    </a>
                    <a
                        href="{{ route('public.structures') }}"
                        class="block w-full py-2.5 rounded-xl border border-slate-300 hover:bg-slate-50 text-slate-700 font-semibold text-xs text-center transition"
                    >
                        Consulter le réseau des membres
                    </a>
                </div>
            </div>
        </div>
    </section>
</x-layouts.public>
