<x-filament-panels::page>
    @php
        $template = $this->template;
        $service = app(\App\Services\AccompanimentTemplateService::class);
        $allTemplates = $service->all();
        $guide = $template->getDetailedGuide();
        $officialDrives = $template->getOfficialDriveResources();
        $customDrives = $this->customDriveResources;
    @endphp

    <div class="space-y-6 pb-12">
        {{-- En-tête / Hero Banner --}}
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-slate-900 via-teal-950 to-slate-900 p-6 lg:p-8 text-white shadow-xl border border-teal-800/40">
            <div class="relative z-10 space-y-4">
                {{-- Barre supérieure d'actions & Retour --}}
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-white/10 pb-4">
                    <a
                        href="{{ \App\Filament\Sae\Pages\AccompanimentTemplatesPage::getUrl() }}"
                        class="inline-flex items-center gap-1.5 text-xs font-semibold text-teal-300 hover:text-white transition"
                    >
                        <x-heroicon-m-arrow-left class="w-4 h-4" />
                        <span>Retour à la bibliothèque des modèles</span>
                    </a>

                    <div class="flex items-center gap-2">
                        <x-filament::badge color="success" icon="heroicon-m-check-badge">
                            {{ $template->getBadge() }}
                        </x-filament::badge>
                        <span class="text-xs font-bold text-gray-300 bg-white/10 px-2.5 py-1 rounded-full backdrop-blur-sm">
                            ⏱️ {{ $template->getDurationWeeks() }} semaines (6 mois)
                        </span>
                    </div>
                </div>

                {{-- Titre & Présentation --}}
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                    <div class="space-y-1.5 max-w-3xl">
                        <h1 class="text-2xl lg:text-3xl font-extrabold tracking-tight text-white flex items-center gap-2.5">
                            <span>{{ $template->getTitle() }}</span>
                        </h1>
                        <p class="text-sm font-semibold text-teal-300">
                            {{ $template->getSubtitle() }}
                        </p>
                        <p class="text-xs lg:text-sm text-gray-300/90 leading-relaxed pt-1">
                            {{ $template->getDescription() }}
                        </p>
                    </div>

                    <div class="flex flex-wrap items-center gap-3 shrink-0">
                        <x-filament::button
                            wire:click="mountAction('deploy', { template_key: '{{ $this->templateKey }}', template_title: '{{ addslashes($template->getTitle()) }}' })"
                            color="success"
                            icon="heroicon-o-rocket-launch"
                            size="md"
                        >
                            Déployer ce modèle
                        </x-filament::button>

                        <x-filament::button
                            wire:click="$set('activeTab', 'drive')"
                            color="gray"
                            icon="heroicon-o-cloud"
                            size="md"
                        >
                            Dossiers Drive ({{ count($officialDrives) + $customDrives->count() }})
                        </x-filament::button>
                    </div>
                </div>

                {{-- Sélecteur rapide entre modèles --}}
                <div class="pt-2 flex flex-wrap items-center gap-2 text-xs">
                    <span class="text-gray-400 font-medium">Changer de modèle :</span>
                    @foreach($allTemplates as $k => $tpl)
                        <a
                            href="{{ \App\Filament\Sae\Pages\AccompanimentTemplateDetailPage::getUrl(['templateKey' => $k]) }}"
                            class="px-3 py-1 rounded-lg text-xs font-medium transition {{ $k === $this->templateKey ? 'bg-teal-500 text-white font-bold shadow' : 'bg-white/10 text-gray-300 hover:bg-white/20' }}"
                        >
                            {{ $tpl->getTitle() }}
                        </a>
                    @endforeach
                </div>
            </div>

            <div class="absolute -right-16 -bottom-16 w-72 h-72 rounded-full bg-teal-500/10 blur-3xl pointer-events-none"></div>
        </div>

        {{-- Barre d'Onglets Principale --}}
        <div class="rounded-xl border border-gray-200/80 dark:border-white/10 p-1.5 shadow-sm">
            <div class="flex flex-wrap gap-1 text-xs font-semibold">
                <button
                    type="button"
                    wire:click="$set('activeTab', 'overview')"
                    class="px-3.5 py-2 rounded-lg flex items-center gap-2 transition {{ $activeTab === 'overview' ? 'bg-primary-600 text-white shadow' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-white/5' }}"
                >
                    <x-heroicon-m-information-circle class="w-4 h-4" />
                    <span>Vision &amp; Piliers</span>
                </button>

                <button
                    type="button"
                    wire:click="$set('activeTab', 'phases')"
                    class="px-3.5 py-2 rounded-lg flex items-center gap-2 transition {{ $activeTab === 'phases' ? 'bg-primary-600 text-white shadow' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-white/5' }}"
                >
                    <x-heroicon-m-squares-2x2 class="w-4 h-4" />
                    <span>Les 6 Phases &amp; Jalons ({{ count($template->getPhases()) }})</span>
                </button>

                <button
                    type="button"
                    wire:click="$set('activeTab', 'startups')"
                    class="px-3.5 py-2 rounded-lg flex items-center gap-2 transition {{ $activeTab === 'startups' ? 'bg-primary-600 text-white shadow' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-white/5' }}"
                >
                    <x-heroicon-m-document-text class="w-4 h-4" />
                    <span>Livrables Startups ({{ count($template->getStartupDeliverables()) }})</span>
                </button>

                <button
                    type="button"
                    wire:click="$set('activeTab', 'programme')"
                    class="px-3.5 py-2 rounded-lg flex items-center gap-2 transition {{ $activeTab === 'programme' ? 'bg-primary-600 text-white shadow' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-white/5' }}"
                >
                    <x-heroicon-m-clipboard-document-check class="w-4 h-4" />
                    <span>Livrables SAEI &amp; Gouvernance ({{ count($template->getProgrammeDeliverables()) }})</span>
                </button>

                <button
                    type="button"
                    wire:click="$set('activeTab', 'evaluation')"
                    class="px-3.5 py-2 rounded-lg flex items-center gap-2 transition {{ $activeTab === 'evaluation' ? 'bg-primary-600 text-white shadow' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-white/5' }}"
                >
                    <x-heroicon-m-chart-bar class="w-4 h-4" />
                    <span>Jury &amp; Indicateurs M&amp;E ({{ count($template->getMneIndicators()) }})</span>
                </button>

                <button
                    type="button"
                    wire:click="$set('activeTab', 'drive')"
                    class="px-3.5 py-2 rounded-lg flex items-center gap-2 transition {{ $activeTab === 'drive' ? 'bg-emerald-600 text-white shadow' : 'text-emerald-700 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/40 hover:bg-emerald-100' }}"
                >
                    <x-heroicon-m-cloud class="w-4 h-4" />
                    <span>📁 Dossiers Drive &amp; Liens Cloud ({{ count($officialDrives) + $customDrives->count() }})</span>
                </button>
            </div>
        </div>

        {{-- CONTENU ONGLET 1 : VISION & PILIERS --}}
        @if($activeTab === 'overview')
            <div class="space-y-6">
                <x-filament::section>
                    <x-slot name="heading">
                        <div class="flex items-center gap-2 text-base font-bold text-gray-950 dark:text-white">
                            <x-heroicon-o-sparkles class="w-5 h-5 text-primary-500" />
                            <span>Piliers Stratégiques &amp; Philosophie de la Méthodologie</span>
                        </div>
                    </x-slot>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs leading-relaxed">
                        @if(isset($guide['overview']))
                            <div class="p-4 rounded-xl border border-gray-200/70 dark:border-white/10 bg-gray-50/50 dark:bg-white/5 space-y-2">
                                <h4 class="font-bold text-primary-600 dark:text-primary-400 uppercase tracking-wider text-[11px]">
                                    Organisation &amp; Origine
                                </h4>
                                <ul class="space-y-2 text-gray-700 dark:text-gray-300">
                                    @foreach($guide['overview'] as $label => $val)
                                        <li>• <strong>{{ ucfirst(str_replace('_', ' ', $label)) }} :</strong> {{ $val }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @if(isset($guide['valeurs']))
                            <div class="p-4 rounded-xl border border-gray-200/70 dark:border-white/10 bg-gray-50/50 dark:bg-white/5 space-y-2">
                                <h4 class="font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider text-[11px]">
                                    Valeurs Fondamentales
                                </h4>
                                <ul class="space-y-2 text-gray-700 dark:text-gray-300">
                                    @foreach($guide['valeurs'] as $valName => $valDesc)
                                        <li>• <strong>{{ $valName }} :</strong> {{ $valDesc }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @elseif(isset($guide['rules']))
                            <div class="p-4 rounded-xl border border-gray-200/70 dark:border-white/10 bg-gray-50/50 dark:bg-white/5 space-y-2">
                                <h4 class="font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider text-[11px]">
                                    Règles d'Or &amp; Engagements
                                </h4>
                                <ul class="space-y-2 text-gray-700 dark:text-gray-300">
                                    @foreach($guide['rules'] as $ruleName => $ruleDesc)
                                        <li>• <strong>{{ $ruleName }} :</strong> {{ $ruleDesc }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>

                    @if(isset($guide['objectifs_strategiques']))
                        <div class="mt-4 p-4 rounded-xl border border-gray-200/70 dark:border-white/10 bg-primary-50/30 dark:bg-primary-950/20 space-y-2 text-xs">
                            <h4 class="font-bold text-primary-700 dark:text-primary-300 uppercase tracking-wider text-[11px]">
                                🎯 Objectifs Stratégiques Officiels (Horizon 2028)
                            </h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 pt-1">
                                @foreach($guide['objectifs_strategiques'] as $objTitle => $objDesc)
                                    <div class="p-2.5 rounded-lg border border-primary-200/50 dark:border-white/10">
                                        <strong class="text-gray-900 dark:text-white block">{{ $objTitle }}</strong>
                                        <p class="text-gray-600 dark:text-gray-400 mt-1 text-[11px] leading-relaxed">{{ $objDesc }}</p>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </x-filament::section>
            </div>
        @endif

        {{-- CONTENU ONGLET 2 : LES 6 PHASES & JALONS --}}
        @if($activeTab === 'phases')
            <div class="space-y-4">
                <div class="border-b border-gray-200 dark:border-white/10 pb-2">
                    <h3 class="text-lg font-bold text-gray-950 dark:text-white flex items-center gap-2">
                        <x-heroicon-o-squares-2x2 class="w-5 h-5 text-primary-500" />
                        <span>Déroulement Chronologique des 6 Phases Pédagogiques</span>
                    </h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                        Chaque jalon est formalisé avec ses objectifs clés, sa durée prévisionnelle, ses critères d'évaluation et ses livrables exigibles.
                    </p>
                </div>

                <div class="space-y-4">
                    @foreach($template->getPhases() as $phase)
                        @php
                            $phaseDetail = collect($guide['pedagogical_phases'] ?? [])->firstWhere('phase', $phase['name']);
                            $startupCount = collect($template->getStartupDeliverables())->where('phase', $phase['position'])->count();
                            $progCount = collect($template->getProgrammeDeliverables())->where('phase', $phase['position'])->count();
                        @endphp
                        <x-filament::section>
                            <div class="space-y-3 text-xs">
                                <div class="flex flex-wrap items-center justify-between gap-2 border-b border-gray-100 dark:border-white/10 pb-2.5">
                                    <div class="flex items-center gap-2.5">
                                        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-primary-600 text-white font-black text-xs shadow-sm">
                                            {{ $phase['position'] }}
                                        </span>
                                        <h4 class="text-sm font-bold text-gray-950 dark:text-white">
                                            {{ $phase['name'] }}
                                        </h4>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <x-filament::badge color="info">
                                            ⏱️ Durée : {{ $phase['duration_weeks'] }} semaines
                                        </x-filament::badge>
                                        <x-filament::badge color="gray">
                                            {{ $startupCount }} livrables startups
                                        </x-filament::badge>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 pt-1">
                                    <div class="space-y-1.5 lg:col-span-2">
                                        <strong class="text-gray-900 dark:text-gray-200 block">Description &amp; Contenu :</strong>
                                        <p class="text-gray-600 dark:text-gray-400 leading-relaxed">
                                            {{ $phase['description'] }}
                                        </p>
                                        @if($phaseDetail && isset($phaseDetail['detail']))
                                            <p class="text-gray-500 dark:text-gray-400 text-[11px] bg-gray-50 dark:bg-white/5 p-2.5 rounded-lg border border-gray-200/50 dark:border-white/5 mt-2">
                                                <strong>Démarche terrain :</strong> {{ $phaseDetail['detail'] }}
                                            </p>
                                        @endif
                                    </div>

                                    <div class="space-y-2 p-3 rounded-xl bg-gray-50/60 dark:bg-white/5 border border-gray-200/50 dark:border-white/5">
                                        <div>
                                            <span class="text-gray-500 dark:text-gray-400 font-medium block text-[10px] uppercase tracking-wider">Objectif Clé :</span>
                                            <strong class="text-gray-900 dark:text-white block">{{ $phase['objectives'] }}</strong>
                                        </div>
                                        <div class="pt-1.5 border-t border-gray-200/40 dark:border-white/5">
                                            <span class="text-gray-500 dark:text-gray-400 font-medium block text-[10px] uppercase tracking-wider">Critère de passage :</span>
                                            <span class="text-emerald-700 dark:text-emerald-400 font-semibold">{{ $phase['passing_criteria'] }}</span>
                                        </div>
                                        @if($phaseDetail && isset($phaseDetail['coach_tips']))
                                            <div class="pt-1.5 border-t border-gray-200/40 dark:border-white/5 text-[11px] text-amber-700 dark:text-amber-400 font-medium">
                                                💡 <em>Conseil Coach :</em> {{ $phaseDetail['coach_tips'] }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </x-filament::section>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- CONTENU ONGLET 3 : LIVRABLES STARTUPS --}}
        @if($activeTab === 'startups')
            <x-filament::section>
                <x-slot name="heading">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2 text-base font-bold text-gray-950 dark:text-white">
                            <x-heroicon-o-document-text class="w-5 h-5 text-emerald-500" />
                            <span>Référentiel des {{ count($template->getStartupDeliverables()) }} Livrables Startups</span>
                        </div>
                        <span class="text-xs text-gray-400">Attribués au rôle : Fondateur / Équipe Startup</span>
                    </div>
                </x-slot>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="border-b border-gray-200 dark:border-white/10 text-gray-400 uppercase text-[10px] tracking-wider bg-gray-50/50 dark:bg-white/5">
                            <tr>
                                <th class="p-3"># Seq</th>
                                <th class="p-3">Phase associée</th>
                                <th class="p-3">Intitulé du livrable startup</th>
                                <th class="p-3">Description &amp; Objectif attendu</th>
                                <th class="p-3 text-right">Note de passage</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                            @foreach($template->getStartupDeliverables() as $item)
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-white/5 transition">
                                    <td class="p-3 font-bold text-gray-950 dark:text-white">
                                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300 font-bold text-[11px]">
                                            {{ $item['seq'] }}
                                        </span>
                                    </td>
                                    <td class="p-3 whitespace-nowrap">
                                        <x-filament::badge color="info">
                                            Phase {{ $item['phase'] }}
                                        </x-filament::badge>
                                    </td>
                                    <td class="p-3 font-bold text-gray-900 dark:text-white">
                                        {{ $item['name'] }}
                                    </td>
                                    <td class="p-3 text-gray-600 dark:text-gray-400">
                                        {{ $item['desc'] }}
                                    </td>
                                    <td class="p-3 text-right font-semibold text-emerald-600 dark:text-emerald-400">
                                        70 / 100
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-filament::section>
        @endif

        {{-- CONTENU ONGLET 4 : LIVRABLES SAEI & GOUVERNANCE --}}
        @if($activeTab === 'programme')
            <x-filament::section>
                <x-slot name="heading">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2 text-base font-bold text-gray-950 dark:text-white">
                            <x-heroicon-o-clipboard-document-check class="w-5 h-5 text-indigo-500" />
                            <span>Référentiel des {{ count($template->getProgrammeDeliverables()) }} Livrables de Gouvernance SAEI</span>
                        </div>
                        <span class="text-xs text-gray-400">Responsabilité partagée de l'équipe incubateur</span>
                    </div>
                </x-slot>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="border-b border-gray-200 dark:border-white/10 text-gray-400 uppercase text-[10px] tracking-wider bg-gray-50/50 dark:bg-white/5">
                            <tr>
                                <th class="p-3"># Seq</th>
                                <th class="p-3">Phase</th>
                                <th class="p-3">Responsable désigné</th>
                                <th class="p-3">Intitulé du livrable institutionnel</th>
                                <th class="p-3 text-right">Statut exigé</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                            @foreach($template->getProgrammeDeliverables() as $item)
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-white/5 transition">
                                    <td class="p-3 font-bold text-gray-950 dark:text-white">
                                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-indigo-100 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300 font-bold text-[11px]">
                                            {{ $item['seq'] }}
                                        </span>
                                    </td>
                                    <td class="p-3 whitespace-nowrap">
                                        <x-filament::badge color="gray">
                                            Phase {{ $item['phase'] }}
                                        </x-filament::badge>
                                    </td>
                                    <td class="p-3 whitespace-nowrap">
                                        <x-filament::badge :color="$item['role']->getColor()">
                                            {{ $item['role']->getLabel() }}
                                        </x-filament::badge>
                                    </td>
                                    <td class="p-3 font-semibold text-gray-900 dark:text-white">
                                        {{ $item['name'] }}
                                    </td>
                                    <td class="p-3 text-right font-medium text-gray-400">
                                        Obligatoire
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-filament::section>
        @endif

        {{-- CONTENU ONGLET 5 : JURY & INDICATEURS M&E --}}
        @if($activeTab === 'evaluation')
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- Critères Jury --}}
                <x-filament::section>
                    <x-slot name="heading">
                        <div class="flex items-center gap-2 text-base font-bold text-gray-950 dark:text-white">
                            <x-heroicon-o-academic-cap class="w-5 h-5 text-amber-500" />
                            <span>Grille de Notation Jury ({{ count($template->getEvaluationCriteria()) }} Critères)</span>
                        </div>
                    </x-slot>

                    <div class="space-y-3 text-xs">
                        @foreach($template->getEvaluationCriteria() as $crit)
                            <div class="p-3 rounded-xl border border-gray-200/70 dark:border-white/10 bg-gray-50/40 dark:bg-white/5 space-y-1.5">
                                <div class="flex items-center justify-between gap-2">
                                    <h5 class="font-bold text-gray-950 dark:text-white">
                                        {{ $crit['name'] }}
                                    </h5>
                                    <span class="rounded bg-amber-100 dark:bg-amber-950 px-2 py-0.5 text-[10px] font-bold text-amber-800 dark:text-amber-300 shrink-0">
                                        Coeff. {{ $crit['weight'] }} &middot; Note / {{ $crit['max_score'] }}
                                    </span>
                                </div>
                                <p class="text-gray-600 dark:text-gray-400 text-[11px] leading-relaxed">
                                    {{ $crit['description'] }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                </x-filament::section>

                {{-- Indicateurs M&E --}}
                <x-filament::section>
                    <x-slot name="heading">
                        <div class="flex items-center gap-2 text-base font-bold text-gray-950 dark:text-white">
                            <x-heroicon-o-chart-pie class="w-5 h-5 text-sky-500" />
                            <span>Indicateurs de Suivi &amp; Évaluation ({{ count($template->getMneIndicators()) }} KPIs)</span>
                        </div>
                    </x-slot>

                    <div class="space-y-3 text-xs">
                        @foreach($template->getMneIndicators() as $ind)
                            <div class="p-3 rounded-xl border border-gray-200/70 dark:border-white/10 bg-gray-50/40 dark:bg-white/5 space-y-1.5">
                                <div class="flex items-center justify-between gap-2">
                                    <h5 class="font-bold text-gray-950 dark:text-white">
                                        {{ $ind['name'] }}
                                    </h5>
                                    <x-filament::badge color="success">
                                        Cible : {{ $ind['target'] }} {{ $ind['unit'] }}
                                    </x-filament::badge>
                                </div>
                                <p class="text-gray-600 dark:text-gray-400 text-[11px]">
                                    <strong>Définition :</strong> {{ $ind['definition'] }}
                                </p>
                                <p class="text-gray-500 dark:text-gray-400 text-[10px]">
                                    <em>Formule :</em> {{ $ind['formula'] }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                </x-filament::section>
            </div>
        @endif

        {{-- CONTENU ONGLET 6 : RESSOURCES GOOGLE DRIVE & LIENS CLOUD --}}
        @if($activeTab === 'drive')
            <div class="space-y-6">
                {{-- Bannière Explicative Drive --}}
                <x-filament::section>
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                        <div class="space-y-1">
                            <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800">
                                <x-heroicon-m-cloud class="w-3.5 h-3.5" />
                                <span>Ressources Cloud Partagées &amp; Google Drive</span>
                            </div>
                            <h3 class="text-lg font-bold text-gray-950 dark:text-white">
                                Boîte à Outils, Formulaires &amp; Dossiers Drive du Modèle
                            </h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 max-w-2xl">
                                Retrouvez ci-dessous les liens officiels Google Drive issus du document méthodologique, ainsi que vos propres dossiers partagés configurés pour votre incubateur.
                            </p>
                        </div>

                        <x-filament::button
                            wire:click="mountAction('addDriveLink')"
                            color="primary"
                            icon="heroicon-m-plus-circle"
                            size="sm"
                        >
                            Ajouter un lien Google Drive
                        </x-filament::button>
                    </div>
                </x-filament::section>

                {{-- 1. Ressources Officielles Google Drive fournies par le modèle --}}
                <x-filament::section>
                    <x-slot name="heading">
                        <div class="flex items-center gap-2 text-sm font-bold text-gray-950 dark:text-white">
                            <x-heroicon-m-check-badge class="w-5 h-5 text-emerald-500" />
                            <span>Dossiers &amp; Feuilles de Calcul Officiels Google Drive ({{ count($officialDrives) }})</span>
                        </div>
                    </x-slot>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                        @foreach($officialDrives as $res)
                            <div class="p-4 rounded-xl border border-gray-200/80 dark:border-white/10 bg-gray-50/50 dark:bg-white/5 flex flex-col justify-between hover:border-emerald-500/50 transition">
                                <div class="space-y-2">
                                    <div class="flex items-start justify-between gap-2">
                                        <div class="flex items-center gap-2 font-bold text-gray-900 dark:text-white">
                                            @if($res['type'] === 'SPREADSHEET')
                                                <div class="p-1.5 rounded-lg bg-emerald-600 text-white shrink-0">
                                                    <x-heroicon-m-table-cells class="w-4 h-4" />
                                                </div>
                                            @elseif($res['type'] === 'DOCUMENT')
                                                <div class="p-1.5 rounded-lg bg-indigo-600 text-white shrink-0">
                                                    <x-heroicon-m-document-text class="w-4 h-4" />
                                                </div>
                                            @else
                                                <div class="p-1.5 rounded-lg bg-amber-600 text-white shrink-0">
                                                    <x-heroicon-m-folder class="w-4 h-4" />
                                                </div>
                                            @endif
                                            <span>{{ $res['title'] }}</span>
                                        </div>
                                        <x-filament::badge color="success" size="xs">
                                            Officiel
                                        </x-filament::badge>
                                    </div>

                                    <p class="text-gray-600 dark:text-gray-400 text-[11px] leading-relaxed">
                                        {{ $res['description'] }}
                                    </p>
                                </div>

                                <div class="mt-4 pt-3 border-t border-gray-200/50 dark:border-white/5 flex items-center justify-between">
                                    <span class="text-[10px] text-gray-400 truncate max-w-[200px]" title="{{ $res['url'] }}">
                                        {{ parse_url($res['url'], PHP_URL_HOST) ?? 'drive.google.com' }}
                                    </span>
                                    <a
                                        href="{{ $res['url'] }}"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-emerald-50 text-emerald-700 hover:bg-emerald-100 dark:bg-emerald-950/60 dark:text-emerald-300 dark:hover:bg-emerald-900 border border-emerald-200 dark:border-emerald-800 transition"
                                    >
                                        <x-heroicon-m-arrow-top-right-on-square class="w-3.5 h-3.5" />
                                        <span>Ouvrir sur Drive</span>
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </x-filament::section>

                {{-- 2. Liens Drive Personnalisés Ajoutés par l'Équipe SAE --}}
                <x-filament::section>
                    <x-slot name="heading">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2 text-sm font-bold text-gray-950 dark:text-white">
                                <x-heroicon-m-folder-plus class="w-5 h-5 text-primary-500" />
                                <span>Vos Liens Google Drive Personnalisés ({{ $customDrives->count() }})</span>
                            </div>
                            <span class="text-xs text-gray-400">Spécifiques à votre SAE</span>
                        </div>
                    </x-slot>

                    @if($customDrives->isEmpty())
                        <div class="text-center py-6 border-2 border-dashed border-gray-200 dark:border-white/10 rounded-xl">
                            <x-heroicon-o-cloud-arrow-up class="w-10 h-10 text-gray-300 dark:text-gray-600 mx-auto mb-2" />
                            <p class="text-xs font-semibold text-gray-700 dark:text-gray-300">
                                Aucun lien Google Drive personnalisé n'a encore été rattaché à ce modèle.
                            </p>
                            <p class="text-[11px] text-gray-400 mt-1 max-w-md mx-auto">
                                Utilisez le formulaire ci-dessous ou le bouton en haut pour associer le dossier Drive partagé de votre structure (canevas adaptés, dossiers des promotions, etc.).
                            </p>
                        </div>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                            @foreach($customDrives as $customRes)
                                <div class="p-4 rounded-xl border border-gray-200/80 dark:border-white/10 flex flex-col justify-between hover:border-primary-500/50 transition">
                                    <div class="space-y-1.5">
                                        <div class="flex items-start justify-between gap-2">
                                            <h4 class="font-bold text-gray-950 dark:text-white flex items-center gap-1.5">
                                                <x-heroicon-m-link class="w-4 h-4 text-primary-500" />
                                                <span>{{ $customRes->title }}</span>
                                            </h4>
                                            <button
                                                type="button"
                                                wire:click="deleteCustomDriveLink({{ $customRes->id }})"
                                                wire:confirm="Êtes-vous sûr de vouloir supprimer ce lien Google Drive ?"
                                                class="text-gray-400 hover:text-red-500 transition p-1"
                                                title="Supprimer ce lien"
                                            >
                                                <x-heroicon-m-trash class="w-4 h-4" />
                                            </button>
                                        </div>

                                        @if($customRes->description)
                                            <p class="text-gray-600 dark:text-gray-400 text-[11px] leading-relaxed">
                                                {{ $customRes->description }}
                                            </p>
                                        @endif
                                    </div>

                                    <div class="mt-4 pt-3 border-t border-gray-100 dark:border-white/5 flex items-center justify-between text-[11px]">
                                        <span class="text-gray-400">
                                            Ajouté le {{ $customRes->created_at?->format('d/m/Y') }}
                                        </span>
                                        <a
                                            href="{{ $customRes->url }}"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="inline-flex items-center gap-1 px-3 py-1 rounded-lg font-semibold bg-primary-50 text-primary-700 hover:bg-primary-100 dark:bg-primary-950 dark:text-primary-300 border border-primary-200 dark:border-primary-800 transition"
                                        >
                                            <x-heroicon-m-arrow-top-right-on-square class="w-3.5 h-3.5" />
                                            <span>Consulter le Drive</span>
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    {{-- Formulaire d'ajout rapide inline --}}
                    <div class="mt-6 pt-5 border-t border-gray-200/80 dark:border-white/10">
                        <div class="bg-gray-50/70 dark:bg-white/5 p-4 rounded-xl border border-gray-200/60 dark:border-white/10 space-y-3">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 flex items-center gap-1.5">
                                <x-heroicon-m-plus-circle class="w-4 h-4 text-primary-600 dark:text-primary-400" />
                                <span>Ajouter un nouveau lien Google Drive pour votre structure</span>
                            </h4>

                            <form wire:submit="saveInlineDriveLink" class="space-y-3 text-xs">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-gray-700 dark:text-gray-300 font-semibold mb-1">Titre de la ressource *</label>
                                        <input
                                            type="text"
                                            wire:model="newDriveTitle"
                                            placeholder="Ex: Dossier Drive Partagé — Cohorte 2026"
                                            class="w-full rounded-lg border border-gray-300 dark:border-white/10 bg-transparent px-3 py-2 text-xs focus:ring-2 focus:ring-primary-500 focus:outline-none"
                                        />
                                        @error('newDriveTitle') <span class="text-red-500 text-[10px] mt-0.5 block">{{ $message }}</span> @enderror
                                    </div>

                                    <div>
                                        <label class="block text-gray-700 dark:text-gray-300 font-semibold mb-1">Lien Google Drive / Cloud (URL) *</label>
                                        <input
                                            type="url"
                                            wire:model="newDriveUrl"
                                            placeholder="https://drive.google.com/drive/folders/..."
                                            class="w-full rounded-lg border border-gray-300 dark:border-white/10 bg-transparent px-3 py-2 text-xs focus:ring-2 focus:ring-primary-500 focus:outline-none"
                                        />
                                        @error('newDriveUrl') <span class="text-red-500 text-[10px] mt-0.5 block">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-gray-700 dark:text-gray-300 font-semibold mb-1">Description &amp; Consignes pour les coachs (Optionnel)</label>
                                    <input
                                        type="text"
                                        wire:model="newDriveDescription"
                                        placeholder="Ex: Répertoire partagé contenant les canevas adaptés et dossiers de promotion."
                                        class="w-full rounded-lg border border-gray-300 dark:border-white/10 bg-transparent px-3 py-2 text-xs focus:ring-2 focus:ring-primary-500 focus:outline-none"
                                    />
                                    @error('newDriveDescription') <span class="text-red-500 text-[10px] mt-0.5 block">{{ $message }}</span> @enderror
                                </div>

                                <div class="flex justify-end pt-1">
                                    <x-filament::button type="submit" color="primary" size="sm" icon="heroicon-m-check">
                                        Enregistrer ce lien Drive
                                    </x-filament::button>
                                </div>
                            </form>
                        </div>
                    </div>
                </x-filament::section>
            </div>
        @endif

        <x-filament-actions::modals />
    </div>
</x-filament-panels::page>
