<x-layouts.public>
    <section class="bg-slate-950 text-white py-14 lg:py-18 border-b border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center max-w-3xl mx-auto space-y-3">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-slate-900 text-emerald-400 border border-emerald-800/80 text-xs font-semibold">
                Écosystème &amp; Incubateurs
            </span>
            <h1 class="text-3xl sm:text-4xl lg:text-5xl font-black tracking-tight">
                Structures d'<span class="text-emerald-400">Accompagnement (SAE)</span>
            </h1>
            <p class="text-xs sm:text-sm text-slate-300 leading-relaxed max-w-2xl mx-auto font-normal">
                Découvrez le réseau officiel des incubateurs, technopoles et accélérateurs membres d'Hub Africa à travers le continent.
            </p>
        </div>
    </section>

    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8">
            @forelse($saes as $sae)
                <article class="bg-white rounded-2xl border border-slate-200/90 shadow-xs hover:shadow-lg transition-all duration-200 flex flex-col justify-between overflow-hidden">
                    <div class="p-6 sm:p-7 space-y-4">
                        <div class="flex items-center gap-4">
                            <div class="w-13 h-13 rounded-xl bg-slate-100 text-emerald-900 font-extrabold text-xl flex items-center justify-center border border-slate-200 shrink-0">
                                {{ substr($sae->name, 0, 2) }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <h3 class="text-base font-bold text-slate-900 hover:text-emerald-800 transition truncate">
                                    {{ $sae->name }}
                                </h3>
                                <p class="text-xs text-slate-500 truncate mt-0.5">
                                    {{ $sae->address ?: 'Siège régional' }}
                                </p>
                            </div>
                        </div>

                        <p class="text-xs text-slate-600 line-clamp-3 leading-relaxed">
                            {{ $sae->description ?: 'Structure d\'incubation partenaire déployant les programmes d\'accompagnement et de suivi de l\'innovation Hub Africa.' }}
                        </p>

                        <div class="grid grid-cols-3 gap-2 pt-3 border-t border-slate-100 text-center">
                            <div class="bg-slate-50 p-2 rounded-xl border border-slate-100">
                                <span class="block text-sm font-black text-slate-900">{{ $sae->programs_count }}</span>
                                <span class="text-[10px] text-slate-500 uppercase font-semibold">Programmes</span>
                            </div>
                            <div class="bg-slate-50 p-2 rounded-xl border border-slate-100">
                                <span class="block text-sm font-black text-slate-900">{{ $sae->projects_count }}</span>
                                <span class="text-[10px] text-slate-500 uppercase font-semibold">Startups</span>
                            </div>
                            <div class="bg-slate-50 p-2 rounded-xl border border-slate-100">
                                <span class="block text-sm font-black text-slate-900">{{ $sae->coaches_count }}</span>
                                <span class="text-[10px] text-slate-500 uppercase font-semibold">Mentors</span>
                            </div>
                        </div>
                    </div>

                    <div class="p-6 pt-0 border-t border-slate-100 bg-slate-50/50 flex items-center justify-between">
                        <a
                            href="{{ route('public.structure.detail', $sae->id) }}"
                            class="w-full text-center py-2.5 px-4 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold transition"
                        >
                            Découvrir cette structure &rarr;
                        </a>
                    </div>
                </article>
            @empty
                <div class="col-span-full bg-white rounded-2xl p-12 text-center border border-dashed border-slate-300">
                    <p class="text-sm text-slate-500">Aucune structure d'accompagnement enregistrée pour le moment.</p>
                </div>
            @endforelse
        </div>

        <div class="pt-8">
            {{ $saes->links() }}
        </div>
    </section>
</x-layouts.public>
