<div class="space-y-8">
    {{-- Barre de recherche et filtres --}}
    <div class="p-5 rounded-3xl bg-white border border-slate-200 shadow-xs grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-3 items-end">
        <div class="lg:col-span-2">
            <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">Rechercher un outil</label>
            <input
                type="text"
                wire:model.live.debounce.300ms="search"
                placeholder="Ex: Business Model Canvas, Prévisionnel financier..."
                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#2F3284]/30"
            />
        </div>

        <div>
            <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">Structure SAE</label>
            <select wire:model.live="selectedSaeId" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-xs text-slate-900">
                <option value="">Toutes les structures</option>
                @foreach ($saes as $sae)
                    <option value="{{ $sae->id }}">{{ $sae->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">Format</label>
            <select wire:model.live="selectedFormat" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-xs text-slate-900">
                <option value="">Tous les formats</option>
                @foreach ($formats as $format)
                    <option value="{{ $format->value }}">{{ $format->getLabel() }}</option>
                @endforeach
            </select>
        </div>

        <div class="flex items-end gap-2">
            <div class="flex-1">
                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">Accès</label>
                <select wire:model.live="selectedAccessLevel" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-xs text-slate-900">
                    <option value="">Tous les niveaux</option>
                    @foreach ($accessLevels as $level)
                        <option value="{{ $level->value }}">{{ $level->getLabel() }}</option>
                    @endforeach
                </select>
            </div>
            @if ($search || $selectedSaeId || $selectedFormat || $selectedAccessLevel)
                <button
                    type="button"
                    wire:click="resetFilters"
                    class="px-3 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition shrink-0"
                >
                    Réinitialiser
                </button>
            @endif
        </div>
    </div>

    {{-- Grille des outils --}}
    @if ($tools->isEmpty())
        <div class="p-12 rounded-3xl bg-white border border-slate-200 text-center space-y-2">
            <p class="text-base font-bold text-slate-900">Aucun outil d'accompagnement trouvé</p>
            <p class="text-xs text-slate-500">Modifiez vos filtres ou revenez plus tard pour découvrir de nouvelles ressources.</p>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach ($tools as $tool)
                @php
                    $decision = $tool->evaluateAccessFor($currentUser);
                    $isDriveLink = $tool->format === \App\Enums\AccompanimentToolFormat::DRIVE_LINK;
                    $fileType = $tool->fileTypeBadge();
                    $saeName = $tool->sae?->name ?? 'SAE';
                    $saeLogoUrl = $tool->sae?->logo_url;
                    $saeInitials = mb_strtoupper(mb_substr(preg_replace('/\s+/', '', $saeName), 0, 2));

                    $accentBarClass = match ($tool->access_level) {
                        \App\Enums\AccompanimentToolAccessLevel::FREE => 'bg-emerald-500',
                        \App\Enums\AccompanimentToolAccessLevel::ACCOUNT_REQUIRED => 'bg-sky-500',
                        \App\Enums\AccompanimentToolAccessLevel::ON_REQUEST => 'bg-[#F8B81D]',
                        \App\Enums\AccompanimentToolAccessLevel::PAID => 'bg-[#2F3284]',
                    };

                    $accessBadgeClasses = match ($tool->access_level) {
                        \App\Enums\AccompanimentToolAccessLevel::FREE => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                        \App\Enums\AccompanimentToolAccessLevel::ACCOUNT_REQUIRED => 'bg-sky-50 text-sky-700 border-sky-200',
                        \App\Enums\AccompanimentToolAccessLevel::ON_REQUEST => 'bg-amber-50 text-amber-800 border-amber-200',
                        \App\Enums\AccompanimentToolAccessLevel::PAID => 'bg-indigo-50 text-[#2F3284] border-indigo-200',
                    };
                @endphp

                <article class="group relative overflow-hidden rounded-2xl bg-white border border-slate-200/90 shadow-xs hover:shadow-md hover:-translate-y-0.5 hover:border-slate-300 transition-all duration-200 flex flex-col justify-between">
                    <div class="h-1 w-full {{ $accentBarClass }}"></div>

                    <div class="p-5 flex-1 flex flex-col justify-between gap-5">
                        <div class="space-y-4">
                            {{-- En-tête : logo SAE + nom/catégorie + badge d'accès --}}
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex items-center gap-3 min-w-0">
                                    @if ($saeLogoUrl)
                                        <div class="w-10 h-10 rounded-xl bg-white border border-slate-200/90 flex items-center justify-center shrink-0 overflow-hidden shadow-xs p-1">
                                            <img
                                                src="{{ $saeLogoUrl }}"
                                                alt="Logo {{ $saeName }}"
                                                loading="lazy"
                                                class="w-full h-full object-contain"
                                            />
                                        </div>
                                    @else
                                        <div class="w-10 h-10 rounded-xl bg-slate-100 ring-1 ring-slate-200 text-slate-700 font-black text-xs flex items-center justify-center shrink-0">
                                            {{ $saeInitials }}
                                        </div>
                                    @endif

                                    <div class="min-w-0">
                                        <p class="text-xs font-bold uppercase tracking-wider text-slate-700 truncate">
                                            {{ $saeName }}
                                        </p>
                                        @if ($tool->category)
                                            <p class="text-[11px] text-slate-400 truncate">{{ $tool->category }}</p>
                                        @endif
                                    </div>
                                </div>

                                <span class="shrink-0 px-2.5 py-0.5 rounded-md text-[11px] font-semibold border {{ $accessBadgeClasses }}">
                                    {{ $tool->access_level->getLabel() }}
                                </span>
                            </div>

                            {{-- Titre & description --}}
                            <div class="space-y-1.5">
                                <h3 class="text-base font-bold text-slate-900 leading-snug group-hover:text-slate-700 transition-colors">
                                    {{ $tool->title }}
                                </h3>
                                @if ($tool->description)
                                    <p class="text-xs text-slate-500 leading-relaxed line-clamp-3">{{ $tool->description }}</p>
                                @endif
                            </div>
                        </div>

                        {{-- Pied de carte : badge type de fichier/lien + CTA --}}
                        <div class="pt-3.5 border-t border-slate-100">
                            @php
                                $fileTypeBadgeHtml = view('components.accompaniment-tool-file-badge', ['fileType' => $fileType])->render();
                            @endphp

                            @if ($decision->canAccess)
                                <div class="flex items-center justify-between gap-3">
                                    <div class="flex items-center gap-2 min-w-0">
                                        {!! $fileTypeBadgeHtml !!}
                                        @if ($tool->access_level === \App\Enums\AccompanimentToolAccessLevel::PAID && $tool->price)
                                            <span class="text-xs font-bold text-[#2F3284] truncate">{{ $tool->formattedPrice() }}</span>
                                        @endif
                                    </div>

                                    <a
                                        href="{{ route('public.outils.access', ['slug' => $tool->slug]) }}"
                                        @if ($isDriveLink) target="_blank" rel="noopener" @endif
                                        class="shrink-0 inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-xs transition"
                                    >
                                        @if ($isDriveLink)
                                            <span>{{ $fileType['key'] === 'google_drive' ? 'Ouvrir Drive' : 'Ouvrir le lien' }}</span>
                                            <svg class="w-3.5 h-3.5 transition-transform group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                                            </svg>
                                        @else
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                                            </svg>
                                            <span>Télécharger</span>
                                        @endif
                                    </a>
                                </div>
                            @elseif ($decision->code === 'AUTHENTICATION_REQUIRED')
                                <div class="flex items-center justify-between gap-3">
                                    {!! $fileTypeBadgeHtml !!}
                                    <a
                                        href="{{ route('public.rejoindre') }}"
                                        class="shrink-0 inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold transition"
                                    >
                                        <span>Se connecter</span>
                                        <svg class="w-3.5 h-3.5 transition-transform group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                                        </svg>
                                    </a>
                                </div>
                            @elseif ($decision->code === 'REQUEST_PENDING')
                                <div class="flex items-center justify-between gap-3">
                                    {!! $fileTypeBadgeHtml !!}
                                    <div class="px-3 py-1.5 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-[11px] font-semibold">
                                        En attente de validation
                                    </div>
                                </div>
                            @elseif ($decision->code === 'REQUEST_REQUIRED' || $decision->code === 'REQUEST_REJECTED')
                                <form method="POST" action="{{ route('public.outils.request', ['slug' => $tool->slug]) }}" class="space-y-2">
                                    @csrf
                                    <input
                                        type="text"
                                        name="request_message"
                                        placeholder="Motif de la demande (optionnel)"
                                        class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-slate-50 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#F8B81D]/40"
                                    />
                                    <div class="flex items-center justify-between gap-3">
                                        {!! $fileTypeBadgeHtml !!}
                                        <button
                                            type="submit"
                                            class="shrink-0 inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-[#F8B81D] hover:bg-[#e5a510] text-slate-950 text-xs font-bold transition cursor-pointer"
                                        >
                                            <span>{{ $decision->code === 'REQUEST_REJECTED' ? 'Renouveler' : 'Demander l\'accès' }}</span>
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                                            </svg>
                                        </button>
                                    </div>
                                </form>
                            @elseif ($decision->code === 'PAYMENT_REQUIRED')
                                <form method="POST" action="{{ route('public.outils.pay', ['slug' => $tool->slug]) }}" class="flex items-center justify-between gap-3">
                                    @csrf
                                    <div class="flex items-center gap-2 min-w-0">
                                        {!! $fileTypeBadgeHtml !!}
                                        <span class="text-xs font-black text-[#2F3284] truncate">
                                            {{ $tool->formattedPrice() }}
                                        </span>
                                    </div>
                                    <button
                                        type="submit"
                                        class="shrink-0 inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-[#2F3284] hover:bg-[#24266b] text-white text-xs font-bold transition cursor-pointer"
                                    >
                                        <span>Débloquer</span>
                                        <svg class="w-3.5 h-3.5 transition-transform group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                                        </svg>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </article>
            @endforeach
        </div>

        <div class="pt-4">
            {{ $tools->links() }}
        </div>
    @endif
</div>
