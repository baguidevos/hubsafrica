<x-layouts.public>
    <section class="bg-gradient-to-b from-slate-900 to-slate-950 text-white py-12 border-b border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-3">
            <nav class="flex items-center gap-2 text-xs text-slate-400">
                <a href="{{ route('home') }}" class="hover:text-[#F8B81D]">Accueil</a>
                <span>/</span>
                <a href="{{ route('public.programmes') }}" class="hover:text-[#F8B81D]">Programmes</a>
                <span>/</span>
                <a href="{{ route('public.programme.detail', $program->id) }}" class="hover:text-[#F8B81D]">{{ $program->name }}</a>
                <span>/</span>
                <span class="text-slate-200">Candidature</span>
            </nav>

            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-black">
                        Dossier de Candidature en Ligne
                    </h1>
                    <p class="text-xs sm:text-sm text-slate-400 mt-1">
                        Programme : <strong class="text-white">{{ $program->name }}</strong> &bull; Structure : <strong class="text-[#F8B81D]">{{ $program->sae?->name }}</strong>
                    </p>
                </div>
                @if($cohort)
                    <div class="bg-slate-800/80 border border-slate-700 rounded-2xl px-4 py-2.5 text-xs">
                        <span class="text-slate-400 block">Promotion ciblée :</span>
                        <span class="text-[#F8B81D] font-bold">{{ $cohort->name }}</span>
                    </div>
                @endif
            </div>
        </div>
    </section>

    <section class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="bg-white rounded-3xl p-6 sm:p-10 border border-slate-200/90 shadow-xl">
            <div class="mb-8 pb-6 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2 mb-2">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-50 text-[#2F3284] border border-amber-200">
                            {{ $applicationForm->is_standard ? '⭐️ Formulaire Officiel Hub Africa' : '🏛️ Formulaire Personnalisé SAE' }}
                        </span>
                        @if($cohort)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                Promotion : {{ $cohort->name }}
                            </span>
                        @endif
                    </div>
                    <h2 class="text-xl sm:text-2xl font-bold text-slate-900">{{ $applicationForm->name }}</h2>
                    @if($applicationForm->description)
                        <p class="text-xs sm:text-sm text-slate-500 mt-1">{{ $applicationForm->description }}</p>
                    @endif
                </div>
            </div>

            {{-- Rendu natif du Form Builder Packstub --}}
            <livewire:form-builder :form="$applicationForm" :values="$prefillValues" />
        </div>
    </section>
</x-layouts.public>
