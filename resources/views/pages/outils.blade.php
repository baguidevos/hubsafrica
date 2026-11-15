<x-layouts.public>
    {{-- En-tête du Catalogue des Outils d'Accompagnement --}}
    <section class="bg-slate-950 text-white py-14 lg:py-18 border-b border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center space-y-3">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-slate-900 text-[#F8B81D] border border-[#F8B81D]/30 text-xs font-semibold">
                Boîte à outils &amp; Ressources SAE
            </span>
            <h1 class="text-3xl sm:text-4xl lg:text-5xl font-black tracking-tight">
                Outils <span class="text-[#F8B81D]">d'Accompagnement</span>
            </h1>
            <p class="text-xs sm:text-sm text-slate-300 leading-relaxed max-w-2xl mx-auto font-normal">
                Accédez aux modèles, guides méthodologiques, documents pratiques et dossiers Google Drive partagés par les structures d'accompagnement.
            </p>
        </div>
    </section>

    {{-- Messages flash --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-8">
        @if (session('tool_success'))
            <div class="mb-4 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-sm font-medium">
                {{ session('tool_success') }}
            </div>
        @endif

        @if (session('tool_notice'))
            <div class="mb-4 p-4 rounded-2xl bg-amber-50 border border-amber-200 text-amber-900 text-sm font-medium">
                {{ session('tool_notice') }}
            </div>
        @endif

        @if (session('tool_error'))
            <div class="mb-4 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-900 text-sm font-medium">
                {{ session('tool_error') }}
            </div>
        @endif
    </section>

    {{-- Catalogue Livewire --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 pb-16">
        <livewire:public.accompaniment-tools-catalog />
    </section>
</x-layouts.public>
