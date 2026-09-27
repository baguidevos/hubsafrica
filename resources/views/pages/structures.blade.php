<x-layouts.public>
    <section class="bg-gradient-to-b from-slate-900 to-slate-950 text-white py-16 lg:py-20 border-b border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center max-w-3xl mx-auto space-y-4">
            <span class="inline-flex items-center gap-1.5 px-3.5 py-1 rounded-full bg-emerald-950 text-emerald-300 border border-emerald-800 text-xs font-bold tracking-wider uppercase">
                Écosystème Panafricain
            </span>
            <h1 class="text-3xl sm:text-4xl lg:text-5xl font-black tracking-tight">
                Structures d'<span class="text-emerald-400">Accompagnement (SAEs)</span>
            </h1>
            <p class="text-sm sm:text-base text-slate-300 leading-relaxed font-normal">
                Découvrez le réseau officiel des incubateurs, technopoles et accélérateurs labellisés Hub Africa opérant dans chaque pays membre.
            </p>
        </div>
    </section>

    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @forelse($saes as $sae)
                <article class="bg-white rounded-3xl border border-slate-200/90 shadow-xs hover:shadow-xl transition-all duration-300 flex flex-col justify-between overflow-hidden group hover:-translate-y-1">
                    <div class="p-8 space-y-4">
                        <div class="flex items-center gap-4">
                            <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-emerald-700 to-slate-900 text-white font-black text-2xl flex items-center justify-center shadow-md">
                                {{ substr($sae->name, 0, 2) }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <h3 class="text-lg font-bold text-slate-900 group-hover:text-emerald-700 transition truncate">
                                    {{ $sae->name }}
                                </h3>
                                <p class="text-xs text-slate-500 truncate mt-0.5">
                                    {{ $sae->address ?: 'Siège régional' }}
                                </p>
                            </div>
                        </div>

                        <p class="text-xs text-slate-600 line-clamp-3 leading-relaxed">
                            {{ $sae->description ?: 'Structure d\'incubation partenaire déployant les standards méthodologiques d\'accompagnement et de suivi de l\'innovation Hub Africa.' }}
                        </p>

                        <div class="grid grid-cols-3 gap-2 pt-3 border-t border-slate-100 text-center">
                            <div class="bg-slate-50 p-2 rounded-xl">
                                <span class="block text-sm font-black text-slate-900">{{ $sae->programs_count }}</span>
                                <span class="text-[10px] text-slate-500 uppercase font-semibold">Programmes</span>
                            </div>
                            <div class="bg-slate-50 p-2 rounded-xl">
                                <span class="block text-sm font-black text-slate-900">{{ $sae->projects_count }}</span>
                                <span class="text-[10px] text-slate-500 uppercase font-semibold">Startups</span>
                            </div>
                            <div class="bg-slate-50 p-2 rounded-xl">
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
                <div class="col-span-full bg-white rounded-3xl p-12 text-center border border-dashed border-slate-300">
                    <p class="text-sm text-slate-500">Aucune structure d'accompagnement enregistrée pour le moment.</p>
                </div>
            @endforelse
        </div>

        <div class="pt-8">
            {{ $saes->links() }}
        </div>
    </section>
</x-layouts.public>
