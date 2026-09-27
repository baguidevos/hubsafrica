<x-layouts.public>
    <section class="bg-gradient-to-b from-slate-900 to-slate-950 text-white py-12 border-b border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-3">
            <nav class="flex items-center gap-2 text-xs text-slate-400">
                <a href="{{ route('home') }}" class="hover:text-emerald-400">Accueil</a>
                <span>/</span>
                <a href="{{ route('public.programmes') }}" class="hover:text-emerald-400">Programmes</a>
                <span>/</span>
                <a href="{{ route('public.programme.detail', $program->id) }}" class="hover:text-emerald-400">{{ $program->name }}</a>
                <span>/</span>
                <span class="text-slate-200">Candidature</span>
            </nav>

            <h1 class="text-2xl sm:text-3xl font-black">
                Dossier de Candidature en Ligne
            </h1>
            <p class="text-xs sm:text-sm text-slate-400">
                Programme : <strong class="text-white">{{ $program->name }}</strong> &bull; Structure : <strong class="text-emerald-400">{{ $program->sae?->name }}</strong>
            </p>
        </div>
    </section>

    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <livewire:public.program-application-wizard :program="$program" />
    </section>
</x-layouts.public>
