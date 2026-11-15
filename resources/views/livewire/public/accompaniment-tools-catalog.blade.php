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
                    $accessBadgeClasses = match ($tool->access_level) {
                        \App\Enums\AccompanimentToolAccessLevel::FREE => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                        \App\Enums\AccompanimentToolAccessLevel::ACCOUNT_REQUIRED => 'bg-sky-50 text-sky-700 border-sky-200',
                        \App\Enums\AccompanimentToolAccessLevel::ON_REQUEST => 'bg-amber-50 text-amber-800 border-amber-200',
                        \App\Enums\AccompanimentToolAccessLevel::PAID => 'bg-indigo-50 text-[#2F3284] border-indigo-200',
                    };
                @endphp

                <div class="p-6 rounded-3xl bg-white border border-slate-200 shadow-xs flex flex-col justify-between gap-5">
                    <div class="space-y-3">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <span class="px-2.5 py-1 rounded-full text-[11px] font-bold border {{ $accessBadgeClasses }}">
                                {{ $tool->access_level->getLabel() }}
                            </span>
                            <span class="px-2.5 py-1 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-700">
                                {{ $tool->format === \App\Enums\AccompanimentToolFormat::DRIVE_LINK ? 'Lien Drive' : 'Document' }}
                            </span>
                        </div>

                        <div>
                            <div class="text-[11px] font-semibold text-slate-500">
                                {{ $tool->sae?->name }}
                                @if ($tool->category)
                                    &bull; {{ $tool->category }}
                                @endif
                            </div>
                            <h3 class="text-base font-bold text-slate-900 mt-1">{{ $tool->title }}</h3>
                        </div>

                        @if ($tool->description)
                            <p class="text-xs text-slate-600 leading-relaxed line-clamp-3">{{ $tool->description }}</p>
                        @endif

                        @if ($tool->access_level === \App\Enums\AccompanimentToolAccessLevel::PAID && $tool->price)
                            <div class="text-sm font-black text-[#2F3284]">
                                {{ $tool->formattedPrice() }}
                            </div>
                        @endif
                    </div>

                    <div class="pt-4 border-t border-slate-100">
                        @if ($decision->canAccess)
                            <a
                                href="{{ route('public.outils.access', ['slug' => $tool->slug]) }}"
                                @if ($tool->format === \App\Enums\AccompanimentToolFormat::DRIVE_LINK) target="_blank" rel="noopener" @endif
                                class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition"
                            >
                                <span>{{ $tool->format === \App\Enums\AccompanimentToolFormat::DRIVE_LINK ? 'Ouvrir le lien Drive' : 'Télécharger le document' }}</span>
                            </a>
                        @elseif ($decision->code === 'AUTHENTICATION_REQUIRED')
                            <a
                                href="{{ route('public.rejoindre') }}"
                                class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold transition"
                            >
                                <span>Se connecter / Créer un compte</span>
                            </a>
                        @elseif ($decision->code === 'REQUEST_PENDING')
                            <div class="w-full text-center px-4 py-2.5 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-xs font-semibold">
                                Demande en attente de validation
                            </div>
                        @elseif ($decision->code === 'REQUEST_REQUIRED' || $decision->code === 'REQUEST_REJECTED')
                            <form method="POST" action="{{ route('public.outils.request', ['slug' => $tool->slug]) }}" class="space-y-2">
                                @csrf
                                <input
                                    type="text"
                                    name="request_message"
                                    placeholder="Motif de la demande (optionnel)"
                                    class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-slate-50 text-xs text-slate-900"
                                />
                                <button
                                    type="submit"
                                    class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-[#F8B81D] hover:bg-[#e5a510] text-slate-950 text-xs font-bold transition cursor-pointer"
                                >
                                    <span>{{ $decision->code === 'REQUEST_REJECTED' ? 'Renouveler la demande d\'accès' : 'Demander l\'accès' }}</span>
                                </button>
                            </form>
                        @elseif ($decision->code === 'PAYMENT_REQUIRED')
                            <form method="POST" action="{{ route('public.outils.pay', ['slug' => $tool->slug]) }}">
                                @csrf
                                <button
                                    type="submit"
                                    class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-[#2F3284] hover:bg-[#24266b] text-white text-xs font-bold transition cursor-pointer"
                                >
                                    <span>Payer {{ $tool->formattedPrice() }} pour débloquer</span>
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <div class="pt-4">
            {{ $tools->links() }}
        </div>
    @endif
</div>
