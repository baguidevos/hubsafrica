<x-layouts.public>
    {{-- En-tête du Catalogue --}}
    <section class="bg-slate-950 text-white py-14 lg:py-18 border-b border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center max-w-3xl mx-auto space-y-3">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-slate-900 text-[#F8B81D] border border-[#F8B81D]/30 text-xs font-semibold">
                Appels à Candidatures &amp; Cohortes
            </span>
            <h1 class="text-3xl sm:text-4xl lg:text-5xl font-black tracking-tight">
                Programmes d'<span class="text-[#F8B81D]">Accompagnement</span>
            </h1>
            <p class="text-xs sm:text-sm text-slate-300 leading-relaxed max-w-2xl mx-auto font-normal">
                Découvrez les programmes d'incubation et d'accélération portés par les structures partenaires et candidatez directement en ligne.
            </p>
        </div>
    </section>

    {{-- Composant Livewire Réactif avec filtres et pagination --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <livewire:public.programmes-catalog />
    </section>
</x-layouts.public>
