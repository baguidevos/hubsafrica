<x-layouts.public>
    <section class="bg-gradient-to-b from-slate-900 to-slate-950 text-white py-12 border-b border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
            <nav class="flex items-center gap-2 text-xs text-slate-400">
                <a href="{{ route('home') }}" class="hover:text-emerald-400">Accueil</a>
                <span>/</span>
                <a href="{{ route('public.structures') }}" class="hover:text-emerald-400">Structures</a>
                <span>/</span>
                <span class="text-slate-200">{{ $sae->name }}</span>
            </nav>

            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 pt-2">
                <div class="flex items-center gap-5">
                    <div class="w-18 h-18 rounded-2xl bg-gradient-to-br from-emerald-600 to-slate-900 text-white font-black text-2xl flex items-center justify-center shadow-lg">
                        {{ substr($sae->name, 0, 2) }}
                    </div>
                    <div>
                        <h1 class="text-2xl sm:text-3xl font-black text-white">
                            {{ $sae->name }}
                        </h1>
                        <p class="text-xs sm:text-sm text-slate-400 mt-1">
                            {{ $sae->address ?: 'Structure d\'Accompagnement labellisée Hub Africa' }}
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-4 text-xs">
                    <div class="bg-slate-800/80 px-4 py-2 rounded-xl text-center border border-slate-700">
                        <span class="block text-emerald-400 font-bold text-sm">{{ $sae->projects_count }}</span>
                        <span class="text-slate-400 text-[10px]">Startups</span>
                    </div>
                    <div class="bg-slate-800/80 px-4 py-2 rounded-xl text-center border border-slate-700">
                        <span class="block text-amber-400 font-bold text-sm">{{ $sae->coaches_count }}</span>
                        <span class="text-slate-400 text-[10px]">Mentors</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 space-y-12">
        
        {{-- Présentation & Coordonnées --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 bg-white rounded-3xl p-8 border border-slate-200 shadow-xs space-y-4">
                <h2 class="text-lg font-bold text-slate-900">À propos de cette structure</h2>
                <p class="text-sm text-slate-600 leading-relaxed whitespace-pre-line">
                    {{ $sae->description ?: 'Cette structure d\'accompagnement est engagée dans l\'écosystème entrepreneurial panafricain pour la formation, l\'accélération et le passage à l\'échelle de startups innovantes.' }}
                </p>
            </div>

            <div class="bg-white rounded-3xl p-8 border border-slate-200 shadow-xs space-y-4 text-xs">
                <h2 class="text-base font-bold text-slate-900">Coordonnées Officielles</h2>
                <div class="space-y-3 text-slate-600">
                    @if($sae->email)
                        <div>
                            <span class="text-slate-400 block text-[11px]">Email :</span>
                            <a href="mailto:{{ $sae->email }}" class="text-emerald-700 font-semibold hover:underline">{{ $sae->email }}</a>
                        </div>
                    @endif
                    @if($sae->phone)
                        <div>
                            <span class="text-slate-400 block text-[11px]">Téléphone :</span>
                            <span class="text-slate-800 font-semibold">{{ $sae->phone }}</span>
                        </div>
                    @endif
                    @if($sae->address)
                        <div>
                            <span class="text-slate-400 block text-[11px]">Localisation :</span>
                            <span class="text-slate-800">{{ $sae->address }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Programmes actifs de la SAE --}}
        <div class="space-y-6">
            <h2 class="text-2xl font-black text-slate-900">
                Programmes Déployés par cette Structure
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($sae->programs as $p)
                    <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-xs hover:shadow-md transition space-y-3 flex flex-col justify-between">
                        <div class="space-y-2">
                            <span class="inline-block px-2.5 py-0.5 rounded text-[10px] font-bold uppercase bg-emerald-50 text-emerald-800">
                                Actif
                            </span>
                            <h3 class="text-base font-bold text-slate-900">
                                {{ $p->name }}
                            </h3>
                            <p class="text-xs text-slate-500 line-clamp-2">
                                {{ $p->description }}
                            </p>
                        </div>
                        <div class="pt-3 border-t border-slate-100 flex items-center justify-between">
                            <a href="{{ route('public.programme.detail', $p->id) }}" class="text-xs font-bold text-emerald-700 hover:underline">
                                Voir détails &rarr;
                            </a>
                            <a href="{{ route('public.programme.candidater', $p->id) }}" class="px-3.5 py-1.5 rounded-lg bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-bold">
                                Candidater
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full bg-white rounded-3xl p-8 text-center border border-dashed border-slate-300 text-slate-500 text-xs">
                        Aucun programme actif répertorié pour cette structure actuellement.
                    </div>
                @endforelse
            </div>
        </div>
    </section>
</x-layouts.public>
