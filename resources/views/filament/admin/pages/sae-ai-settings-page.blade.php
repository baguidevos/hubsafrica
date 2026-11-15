<x-filament-panels::page>
    @php
        $saes = $this->saes;
        $selectedSae = $this->selectedSae;
        $categoryLabels = \App\Support\SaeAiFeatures::categoryLabels();
        $isAiEnabled = (bool) ($this->features['ai_enabled'] ?? false);
        $isMcpServerEnabled = (bool) ($this->features['mcp_server_enabled'] ?? false);
    @endphp

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        {{-- Colonne gauche : Liste des SAE (4 cols) --}}
        <div class="lg:col-span-4 rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-white/10 p-4 sm:p-5 shadow-xs space-y-4">
            <div>
                <h2 class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wide">
                    Structures (SAE)
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                    Sélectionnez une structure pour configurer ses accès IA.
                </p>
            </div>

            <div>
                <input
                    type="search"
                    wire:model.live.debounce.250ms="search"
                    placeholder="Rechercher une SAE..."
                    class="w-full rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 px-3.5 py-2 text-xs text-gray-900 dark:text-white placeholder:text-gray-400 focus:border-primary-500 focus:ring-primary-500"
                >
            </div>

            <div class="space-y-2 max-h-[540px] overflow-y-auto pr-1">
                @forelse($saes as $sae)
                    @php
                        $isSelected = $selectedSae && $selectedSae->id === $sae->id;
                        $saeAiActive = $sae->isAiFeatureEnabled('ai_enabled');
                    @endphp
                    <button
                        type="button"
                        wire:key="sae-item-{{ $sae->id }}"
                        wire:click="selectSae({{ $sae->id }})"
                        @class([
                            'w-full text-left p-3.5 rounded-xl border transition flex items-center justify-between gap-3',
                            'border-primary-500 bg-primary-50/70 dark:bg-primary-950/30 dark:border-primary-500/60 shadow-xs' => $isSelected,
                            'border-gray-200 dark:border-white/10 hover:bg-gray-50 dark:hover:bg-white/5' => ! $isSelected,
                        ])
                    >
                        <div class="min-w-0 flex-1">
                            <div class="text-xs sm:text-sm font-bold text-gray-900 dark:text-white truncate">
                                {{ $sae->name }}
                            </div>
                            <div class="text-[11px] text-gray-500 dark:text-gray-400 truncate">
                                {{ $sae->city ? $sae->city.', ' : '' }}{{ $sae->country ?: 'Réseau Hubs Africa' }}
                            </div>
                        </div>

                        <span @class([
                            'shrink-0 inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold',
                            'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300' => $saeAiActive,
                            'bg-gray-100 text-gray-600 dark:bg-white/10 dark:text-gray-400' => ! $saeAiActive,
                        ])>
                            <span @class([
                                'w-1.5 h-1.5 rounded-full',
                                'bg-emerald-500' => $saeAiActive,
                                'bg-gray-400' => ! $saeAiActive,
                            ])></span>
                            {{ $saeAiActive ? 'IA Active' : 'Inactive' }}
                        </span>
                    </button>
                @empty
                    <p class="text-xs text-gray-500 dark:text-gray-400 text-center py-6">
                        Aucune SAE trouvée.
                    </p>
                @endforelse
            </div>
        </div>

        {{-- Colonne droite : Panneau de configuration granulaire (8 cols) --}}
        <div class="lg:col-span-8 space-y-6">
            @if($selectedSae)
                {{-- En-tête de la SAE sélectionnée & Préréglages --}}
                <div class="rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-white/10 p-5 sm:p-6 shadow-xs space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div>
                            <span class="text-[11px] font-semibold uppercase tracking-wider text-gray-400">
                                Configuration IA &amp; MCP
                            </span>
                            <h2 class="text-xl font-extrabold text-gray-900 dark:text-white">
                                {{ $selectedSae->name }}
                            </h2>
                        </div>

                        <div class="flex flex-wrap items-center gap-2">
                            <x-filament::button
                                type="button"
                                size="xs"
                                color="success"
                                icon="heroicon-m-check-circle"
                                wire:click="applyPreset('all')"
                            >
                                Tout activer
                            </x-filament::button>

                            <x-filament::button
                                type="button"
                                size="xs"
                                color="info"
                                icon="heroicon-m-eye"
                                wire:click="applyPreset('readonly')"
                            >
                                Lecture seule IA
                            </x-filament::button>

                            <x-filament::button
                                type="button"
                                size="xs"
                                color="gray"
                                icon="heroicon-m-no-symbol"
                                wire:click="applyPreset('none')"
                            >
                                Tout désactiver
                            </x-filament::button>
                        </div>
                    </div>

                    {{-- Interrupteur Maître --}}
                    <label class="flex items-center justify-between gap-4 p-4 rounded-xl border-2 transition cursor-pointer {{ $isAiEnabled ? 'border-emerald-400/70 bg-emerald-50/50 dark:bg-emerald-950/20' : 'border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5' }}">
                        <div class="space-y-0.5">
                            <div class="text-sm font-extrabold text-gray-900 dark:text-white">
                                Activer les fonctionnalités IA pour cette SAE
                            </div>
                            <p class="text-xs text-gray-600 dark:text-gray-400">
                                Interrupteur principal. Lorsqu'il est désactivé, aucun module IA ni connexion MCP ne fonctionne pour cette structure.
                            </p>
                        </div>
                        <input
                            type="checkbox"
                            wire:model.live="features.ai_enabled"
                            class="h-5 w-5 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500"
                        >
                    </label>
                </div>

                {{-- Section 1 : Modules Back-Office & Serveur MCP --}}
                <div class="rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-white/10 p-5 sm:p-6 shadow-xs space-y-4 {{ ! $isAiEnabled ? 'opacity-60' : '' }}">
                    <div>
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wide">
                            1. Modules &amp; Interfaces disponibles
                        </h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                            Choisissez précisément quelles interfaces et points d'accès sont exposés à cette SAE.
                        </p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <label class="p-4 rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50/60 dark:bg-white/5 flex items-start justify-between gap-3 cursor-pointer">
                            <div class="space-y-1">
                                <span class="block text-xs font-bold text-gray-900 dark:text-white">Copilote IA (UI)</span>
                                <span class="block text-[11px] text-gray-500 dark:text-gray-400">Affiche la page Copilote IA dans le panel SAE.</span>
                            </div>
                            <input
                                type="checkbox"
                                wire:model.live="features.copilot_enabled"
                                @disabled(! $isAiEnabled)
                                class="mt-0.5 h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500"
                            >
                        </label>

                        <label class="p-4 rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50/60 dark:bg-white/5 flex items-start justify-between gap-3 cursor-pointer">
                            <div class="space-y-1">
                                <span class="block text-xs font-bold text-gray-900 dark:text-white">Clés MCP (UI)</span>
                                <span class="block text-[11px] text-gray-500 dark:text-gray-400">Permet à la SAE de générer ses clés API MCP.</span>
                            </div>
                            <input
                                type="checkbox"
                                wire:model.live="features.mcp_keys_enabled"
                                @disabled(! $isAiEnabled)
                                class="mt-0.5 h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500"
                            >
                        </label>

                        <label class="p-4 rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50/60 dark:bg-white/5 flex items-start justify-between gap-3 cursor-pointer">
                            <div class="space-y-1">
                                <span class="block text-xs font-bold text-gray-900 dark:text-white">Serveur MCP &amp; API</span>
                                <span class="block text-[11px] text-gray-500 dark:text-gray-400">Autorise les connexions externes (Streamable HTTP / API).</span>
                            </div>
                            <input
                                type="checkbox"
                                wire:model.live="features.mcp_server_enabled"
                                @disabled(! $isAiEnabled)
                                class="mt-0.5 h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500"
                            >
                        </label>
                    </div>
                </div>

                {{-- Section 2 : Permissions granulaires par domaine d'outils MCP --}}
                <div class="rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-white/10 p-5 sm:p-6 shadow-xs space-y-4 {{ (! $isAiEnabled || ! $isMcpServerEnabled) ? 'opacity-60' : '' }}">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <div>
                            <h3 class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wide">
                                2. Permissions granulaires des outils MCP
                            </h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                Contrôlez domaine par domaine ce que les agents IA connectés via MCP peuvent consulter ou modifier.
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        @foreach($categoryLabels as $key => $label)
                            <label class="p-3.5 rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50/60 dark:bg-white/5 flex items-center justify-between gap-3 cursor-pointer">
                                <span class="text-xs font-bold text-gray-900 dark:text-white">{{ $label }}</span>
                                <input
                                    type="checkbox"
                                    wire:model.live="features.mcp_tools.{{ $key }}"
                                    @disabled(! $isAiEnabled || ! $isMcpServerEnabled)
                                    class="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500"
                                >
                            </label>
                        @endforeach
                    </div>

                    {{-- Garde-fou Écriture --}}
                    <label class="flex items-center justify-between gap-4 p-4 rounded-xl border border-amber-300/70 bg-amber-50/60 dark:bg-amber-950/20 dark:border-amber-700/40 cursor-pointer">
                        <div class="space-y-0.5">
                            <div class="text-xs font-extrabold text-amber-900 dark:text-amber-200">
                                Autoriser les actions d'écriture par l'IA (Création, Notation, Statuts)
                            </div>
                            <p class="text-[11px] text-amber-800/80 dark:text-amber-300/80">
                                Si décoché, l'agent IA reste strictement en lecture seule sur tous les domaines autorisés.
                            </p>
                        </div>
                        <input
                            type="checkbox"
                            wire:model.live="features.mcp_tools.write_actions"
                            @disabled(! $isAiEnabled || ! $isMcpServerEnabled)
                            class="h-4 w-4 rounded border-amber-400 text-amber-600 focus:ring-amber-500"
                        >
                    </label>
                </div>

                {{-- Barre d'enregistrement --}}
                <div class="flex items-center justify-end gap-3">
                    <x-filament::button
                        type="button"
                        color="primary"
                        icon="heroicon-o-check"
                        wire:click="save"
                    >
                        Enregistrer la configuration IA
                    </x-filament::button>
                </div>
            @else
                <div class="rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-white/10 p-10 text-center text-sm text-gray-500 dark:text-gray-400">
                    Aucune SAE disponible à configurer.
                </div>
            @endif
        </div>
    </div>
</x-filament-panels::page>
