<x-layouts.public>
    {{-- En-tête Institutionnel du Catalogue --}}
    <section class="bg-gradient-to-b from-slate-900 via-slate-900 to-slate-950 text-white py-16 lg:py-20 border-b border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center max-w-3xl mx-auto space-y-4">
            <span class="inline-flex items-center gap-1.5 px-3.5 py-1 rounded-full bg-emerald-950 text-emerald-300 border border-emerald-800 text-xs font-bold tracking-wider uppercase">
                🚀 Opportunités Panafricaines
            </span>
            <h1 class="text-3xl sm:text-4xl lg:text-5xl font-black tracking-tight">
                Programmes d'<span class="text-emerald-400">Accompagnement</span>
            </h1>
            <p class="text-sm sm:text-base text-slate-300 leading-relaxed font-normal">
                Explorez l'ensemble des programmes d'incubation et d'accélération labellisés sur la plateforme Hub Africa et déposez votre candidature directement en ligne.
            </p>
        </div>
    </section>

    {{-- Composant Livewire Réactif avec filtres et pagination --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <livewire:public.programmes-catalog />
    </section>
</x-layouts.public>
