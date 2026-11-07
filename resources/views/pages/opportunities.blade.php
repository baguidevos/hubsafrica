<x-layouts.public>
    {{-- En-tête du Catalogue des Opportunités --}}
    <section class="bg-slate-950 text-white py-14 lg:py-18 border-b border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center max-w-3xl mx-auto space-y-3">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-slate-900 text-[#F8B81D] border border-[#F8B81D]/30 text-xs font-semibold">
                Financements, Appels à Projets &amp; Partenariats
            </span>
            <h1 class="text-3xl sm:text-4xl lg:text-5xl font-black tracking-tight">
                Opportunités &amp; <span class="text-[#F8B81D]">Appels à Projets</span>
            </h1>
            <p class="text-xs sm:text-sm text-slate-300 leading-relaxed max-w-2xl mx-auto font-normal">
                Explorez les opportunités stratégiques, subventions, concours et défis publiés par les structures d'accompagnement (SAEs) pour propulser votre entreprise en Afrique.
            </p>
        </div>
    </section>

    {{-- Composant Livewire Réactif avec filtres et pagination --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <livewire:public.opportunities-catalog />
    </section>
</x-layouts.public>
