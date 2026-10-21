<x-filament-panels::page>
    @php
        $application = $this->record;
        $status = $application->status;
        $statusValue = $status instanceof \App\Enums\ApplicationStatus ? $status->value : ($status ?? 'BROUILLON');
        $statusLabel = $status instanceof \App\Enums\ApplicationStatus ? $status->getLabel() : ($status ?? 'Brouillon');
        $statusIcon = $status instanceof \App\Enums\ApplicationStatus ? $status->getIcon() : 'heroicon-m-document';

        $statusThemes = [
            'BROUILLON'  => [
                'badge_bg' => 'bg-gray-100 dark:bg-gray-800',
                'badge_text' => 'text-gray-700 dark:text-gray-300',
                'border' => 'border-gray-200 dark:border-gray-700',
                'gradient' => 'from-gray-500 via-gray-600 to-slate-700',
                'callout_bg' => 'bg-gray-50 dark:bg-gray-800/60',
                'callout_border' => 'border-gray-200 dark:border-gray-700',
                'callout_text' => 'text-gray-800 dark:text-gray-200',
            ],
            'SOUMISE' => [
                'badge_bg' => 'bg-amber-100 dark:bg-amber-900/40',
                'badge_text' => 'text-amber-800 dark:text-amber-300',
                'border' => 'border-amber-200 dark:border-amber-800',
                'gradient' => 'from-amber-500 via-amber-600 to-orange-600',
                'callout_bg' => 'bg-amber-50 dark:bg-amber-950/30',
                'callout_border' => 'border-amber-200 dark:border-amber-800/60',
                'callout_text' => 'text-amber-900 dark:text-amber-200',
            ],
            'EN_EXAMEN' => [
                'badge_bg' => 'bg-sky-100 dark:bg-sky-900/40',
                'badge_text' => 'text-sky-800 dark:text-sky-300',
                'border' => 'border-sky-200 dark:border-sky-800',
                'gradient' => 'from-sky-500 via-blue-600 to-indigo-700',
                'callout_bg' => 'bg-sky-50 dark:bg-sky-950/30',
                'callout_border' => 'border-sky-200 dark:border-sky-800/60',
                'callout_text' => 'text-sky-900 dark:text-sky-200',
            ],
            'ACCEPTEE' => [
                'badge_bg' => 'bg-emerald-100 dark:bg-emerald-900/40',
                'badge_text' => 'text-emerald-800 dark:text-emerald-300',
                'border' => 'border-emerald-200 dark:border-emerald-800',
                'gradient' => 'from-emerald-500 via-teal-600 to-emerald-700',
                'callout_bg' => 'bg-emerald-50 dark:bg-emerald-950/30',
                'callout_border' => 'border-emerald-200 dark:border-emerald-800/60',
                'callout_text' => 'text-emerald-900 dark:text-emerald-200',
            ],
            'REFUSEE' => [
                'badge_bg' => 'bg-rose-100 dark:bg-rose-900/40',
                'badge_text' => 'text-rose-800 dark:text-rose-300',
                'border' => 'border-rose-200 dark:border-rose-800',
                'gradient' => 'from-rose-500 via-red-600 to-rose-700',
                'callout_bg' => 'bg-rose-50 dark:bg-rose-950/30',
                'callout_border' => 'border-rose-200 dark:border-rose-800/60',
                'callout_text' => 'text-rose-900 dark:text-rose-200',
            ],
            'REORIENTEE' => [
                'badge_bg' => 'bg-orange-100 dark:bg-orange-900/40',
                'badge_text' => 'text-orange-800 dark:text-orange-300',
                'border' => 'border-orange-200 dark:border-orange-800',
                'gradient' => 'from-orange-500 via-amber-600 to-yellow-600',
                'callout_bg' => 'bg-orange-50 dark:bg-orange-950/30',
                'callout_border' => 'border-orange-200 dark:border-orange-800/60',
                'callout_text' => 'text-orange-900 dark:text-orange-200',
            ],
            'EN_ATTENTE' => [
                'badge_bg' => 'bg-purple-100 dark:bg-purple-900/40',
                'badge_text' => 'text-purple-800 dark:text-purple-300',
                'border' => 'border-purple-200 dark:border-purple-800',
                'gradient' => 'from-purple-500 via-violet-600 to-indigo-700',
                'callout_bg' => 'bg-purple-50 dark:bg-purple-950/30',
                'callout_border' => 'border-purple-200 dark:border-purple-800/60',
                'callout_text' => 'text-purple-900 dark:text-purple-200',
            ],
            'RETIREE' => [
                'badge_bg' => 'bg-gray-100 dark:bg-gray-800',
                'badge_text' => 'text-gray-700 dark:text-gray-300',
                'border' => 'border-gray-200 dark:border-gray-700',
                'gradient' => 'from-gray-500 via-gray-600 to-slate-700',
                'callout_bg' => 'bg-gray-50 dark:bg-gray-800/60',
                'callout_border' => 'border-gray-200 dark:border-gray-700',
                'callout_text' => 'text-gray-800 dark:text-gray-200',
            ],
        ];

        $theme = $statusThemes[$statusValue] ?? $statusThemes['BROUILLON'];

        // Extraction et séparation des sections de $application->comment si formatées par le wizard
        $rawComment = (string) ($application->comment ?? '');
        $motivation = null;
        $needs = null;
        $specificAnswersFromComment = null;

        if (str_contains($rawComment, 'Motivation du candidat :')) {
            $parts = explode('--- Questions spécifiques de la structure ---', $rawComment);
            $mainPart = $parts[0] ?? '';
            $specificAnswersFromComment = $parts[1] ?? null;

            if (preg_match('/Motivation du candidat\s*:\s*(.*?)(?=\n\nBesoins identifiés|\nPitch deck déposé|$)/s', $mainPart, $matches)) {
                $motivation = trim($matches[1] ?? '');
            }
            if (preg_match('/Besoins identifiés\s*:\s*(.*?)(?=\nPitch deck déposé|$)/s', $mainPart, $matches)) {
                $needs = trim($matches[1] ?? '');
            }
        } else {
            $motivation = $rawComment ?: null;
        }

        $project = $application->project;
    @endphp

    <div class="space-y-6">

        {{-- Top Hero Card --}}
        <div class="relative overflow-hidden rounded-2xl border border-gray-200/80 dark:border-white/10 shadow-sm dark:bg-gray-900">
            <div class="absolute inset-x-0 top-0 h-32 bg-gradient-to-r {{ $theme['gradient'] }}"></div>
            <div class="relative px-6 pb-6 pt-16 sm:px-8">
                <div class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
                    <div class="min-w-0 flex-1">
                        {{-- Badges row --}}
                        <div class="flex flex-wrap items-center gap-2.5 mb-2">
                            <span class="inline-flex items-center gap-x-1.5 rounded-full px-3 py-1 text-xs font-semibold {{ $theme['badge_bg'] }} {{ $theme['badge_text'] }} shadow-xs">
                                <x-dynamic-component :component="$statusIcon" class="h-3.5 w-3.5" />
                                {{ $statusLabel }}
                            </span>

                            @if($application->program?->sae)
                                <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-medium text-gray-700 shadow-xs ring-1 ring-gray-200 backdrop-blur-xs dark:bg-gray-800 dark:text-gray-300 dark:ring-gray-700">
                                    <x-heroicon-m-building-office-2 class="h-3.5 w-3.5 text-emerald-500" />
                                    {{ $application->program->sae->name }}
                                </span>
                            @endif

                            @if($application->cohort)
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-blue-50 px-3 py-1 text-xs font-medium text-blue-700 shadow-xs ring-1 ring-blue-200 dark:bg-blue-900/40 dark:text-blue-300 dark:ring-blue-800">
                                    <x-heroicon-m-user-group class="h-3.5 w-3.5 text-blue-500" />
                                    {{ $application->cohort->name }}
                                </span>
                            @endif
                        </div>

                        {{-- Title --}}
                        <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-3xl">
                            Candidature au programme {{ $application->program?->name ?? 'Programme Hubs Africa' }}
                        </h1>

                        <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400 flex flex-wrap items-center gap-x-3 gap-y-1">
                            @if($application->submitted_at)
                                <span>Déposée le <strong>{{ $application->submitted_at->format('d/m/Y à H:i') }}</strong></span>
                            @else
                                <span>Créée le <strong>{{ $application->created_at?->format('d/m/Y') }}</strong></span>
                            @endif

                            @if($application->project)
                                <span>• Projet : <strong class="text-emerald-600 dark:text-emerald-400">{{ $application->project->name }}</strong></span>
                            @endif

                            @if($application->form)
                                <span>• Formulaire : <span class="text-gray-600 dark:text-gray-300">{{ $application->form->name }}</span></span>
                            @endif
                        </p>
                    </div>

                    {{-- Score Jury Badge --}}
                    @if($application->score !== null)
                        <div class="flex-shrink-0">
                            <div @class([
                                'flex flex-col items-center justify-center rounded-2xl px-5 py-3 shadow-sm border ring-2 ring-white/50 dark:ring-gray-800',
                                'bg-emerald-50 border-emerald-200 dark:bg-emerald-950/40 dark:border-emerald-800' => $application->score >= 75,
                                'bg-amber-50 border-amber-200 dark:bg-amber-950/40 dark:border-amber-800' => $application->score >= 50 && $application->score < 75,
                                'bg-rose-50 border-rose-200 dark:bg-rose-950/40 dark:border-rose-800' => $application->score < 50,
                            ])>
                                <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Note Jury</span>
                                <div class="flex items-baseline gap-1 mt-0.5">
                                    <span @class([
                                        'text-3xl font-extrabold tracking-tight',
                                        'text-emerald-700 dark:text-emerald-400' => $application->score >= 75,
                                        'text-amber-700 dark:text-amber-400' => $application->score >= 50 && $application->score < 75,
                                        'text-rose-700 dark:text-rose-400' => $application->score < 50,
                                    ])>{{ $application->score }}</span>
                                    <span class="text-xs font-medium text-gray-400">/ 100</span>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Visual Stepper --}}
        <div class="rounded-xl border border-gray-200/80 dark:border-white/10 p-6 shadow-sm dark:bg-gray-900">
            @php
                $steps = [
                    [
                        'key' => 'SOUMISSION',
                        'label' => '1. Soumission',
                        'desc' => 'Dossier déposé',
                        'icon' => 'heroicon-m-paper-airplane',
                    ],
                    [
                        'key' => 'EXAMEN',
                        'label' => '2. Éligibilité',
                        'desc' => 'Contrôle dossier',
                        'icon' => 'heroicon-m-clipboard-document-check',
                    ],
                    [
                        'key' => 'EVALUATION',
                        'label' => '3. Jury',
                        'desc' => 'Évaluation critères',
                        'icon' => 'heroicon-m-academic-cap',
                    ],
                    [
                        'key' => 'DECISION',
                        'label' => '4. Délibération',
                        'desc' => match($statusValue) {
                            'ACCEPTEE' => 'Acceptée',
                            'REFUSEE' => 'Non retenue',
                            'EN_ATTENTE' => 'Liste d\'attente',
                            'REORIENTEE' => 'Réorientée',
                            default => 'Décision finale',
                        },
                        'icon' => match($statusValue) {
                            'ACCEPTEE' => 'heroicon-m-check-badge',
                            'REFUSEE' => 'heroicon-m-x-circle',
                            'EN_ATTENTE' => 'heroicon-m-clock',
                            default => 'heroicon-m-check-circle',
                        },
                    ],
                ];

                $stepIndex = match($statusValue) {
                    'BROUILLON' => 0,
                    'SOUMISE' => 1,
                    'EN_EXAMEN' => 2,
                    'ACCEPTEE', 'REFUSEE', 'REORIENTEE', 'EN_ATTENTE', 'RETIREE' => 3,
                    default => 1,
                };
            @endphp

            <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
                @foreach($steps as $idx => $st)
                    @php
                        $isCompleted = $idx < $stepIndex || ($idx === 3 && in_array($statusValue, ['ACCEPTEE', 'REFUSEE', 'EN_ATTENTE', 'REORIENTEE']));
                        $isCurrent = $idx === $stepIndex && !in_array($statusValue, ['ACCEPTEE', 'REFUSEE', 'EN_ATTENTE', 'REORIENTEE']);
                        $isUpcoming = $idx > $stepIndex;
                    @endphp

                    <div @class([
                        'flex items-center gap-3.5 rounded-xl p-3 transition',
                        'bg-emerald-50/70 border border-emerald-200 dark:bg-emerald-950/20 dark:border-emerald-800/60' => $isCompleted && $statusValue !== 'REFUSEE',
                        'bg-rose-50/70 border border-rose-200 dark:bg-rose-950/20 dark:border-rose-800/60' => $idx === 3 && $statusValue === 'REFUSEE',
                        'bg-sky-50/80 border-2 border-sky-400 dark:bg-sky-950/30 dark:border-sky-600' => $isCurrent,
                        'bg-gray-50 border border-gray-100 opacity-60 dark:bg-gray-800/40 dark:border-gray-800' => $isUpcoming,
                    ])>
                        <div @class([
                            'flex h-10 w-10 shrink-0 items-center justify-center rounded-xl shadow-xs',
                            'bg-emerald-500 text-white dark:bg-emerald-600' => $isCompleted && $statusValue !== 'REFUSEE',
                            'bg-rose-500 text-white dark:bg-rose-600' => $idx === 3 && $statusValue === 'REFUSEE',
                            'bg-sky-600 text-white animate-pulse' => $isCurrent,
                            'bg-gray-200 text-gray-500 dark:bg-gray-700 dark:text-gray-400' => $isUpcoming,
                        ])>
                            @if($isCompleted && $statusValue !== 'REFUSEE')
                                <x-heroicon-m-check class="h-5 w-5" />
                            @elseif($idx === 3 && $statusValue === 'REFUSEE')
                                <x-heroicon-m-x-mark class="h-5 w-5" />
                            @else
                                <x-dynamic-component :component="$st['icon']" class="h-5 w-5" />
                            @endif
                        </div>
                        <div class="min-w-0">
                            <p @class([
                                'text-xs font-semibold',
                                'text-emerald-900 dark:text-emerald-200' => $isCompleted && $statusValue !== 'REFUSEE',
                                'text-rose-900 dark:text-rose-200' => $idx === 3 && $statusValue === 'REFUSEE',
                                'text-sky-900 dark:text-sky-200 font-bold' => $isCurrent,
                                'text-gray-500 dark:text-gray-400' => $isUpcoming,
                            ])>
                                {{ $st['label'] }}
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ $st['desc'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Dynamic Status Callout Message --}}
        <div class="rounded-xl border {{ $theme['callout_border'] }} {{ $theme['callout_bg'] }} p-5 shadow-xs">
            <div class="flex items-start gap-4">
                <div class="shrink-0 mt-0.5">
                    @if($statusValue === 'ACCEPTEE')
                        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-500 text-white shadow-xs">
                            <x-heroicon-m-check-badge class="h-5 w-5" />
                        </div>
                    @elseif($statusValue === 'REFUSEE')
                        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-rose-500 text-white shadow-xs">
                            <x-heroicon-m-x-circle class="h-5 w-5" />
                        </div>
                    @elseif($statusValue === 'EN_ATTENTE')
                        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-purple-500 text-white shadow-xs">
                            <x-heroicon-m-clock class="h-5 w-5" />
                        </div>
                    @elseif($statusValue === 'EN_EXAMEN')
                        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-sky-500 text-white shadow-xs">
                            <x-heroicon-m-magnifying-glass class="h-5 w-5" />
                        </div>
                    @else
                        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-500 text-white shadow-xs">
                            <x-heroicon-m-paper-airplane class="h-5 w-5" />
                        </div>
                    @endif
                </div>

                <div class="min-w-0 flex-1">
                    <h3 class="text-sm font-bold {{ $theme['callout_text'] }}">
                        @if($statusValue === 'ACCEPTEE')
                            🎉 Félicitations ! Votre candidature est acceptée.
                        @elseif($statusValue === 'REFUSEE')
                            Dossier non retenu pour cette promotion
                        @elseif($statusValue === 'EN_ATTENTE')
                            Votre candidature est sur liste d'attente
                        @elseif($statusValue === 'EN_EXAMEN')
                            Votre dossier est actuellement en cours d'évaluation
                        @elseif($statusValue === 'SOUMISE')
                            Votre candidature a bien été enregistrée
                        @else
                            Statut du dossier : {{ $statusLabel }}
                        @endif
                    </h3>

                    <p class="mt-1 text-xs leading-relaxed {{ $theme['callout_text'] }} opacity-90">
                        @if($statusValue === 'ACCEPTEE')
                            Le comité de sélection de {{ $application->program?->sae?->name ?? 'la structure' }} a validé votre intégration au programme
                            <strong>{{ $application->program?->name }}</strong>{{ $application->cohort ? ' au sein de la promotion « ' . $application->cohort->name . ' »' : '' }}.
                            Votre projet a été enrôlé et vos prochains rendez-vous de coaching et ateliers seront bientôt planifiés.
                        @elseif($statusValue === 'REFUSEE')
                            Le comité a finalisé l'examen de votre dossier et a décidé de ne pas le retenir pour cette session.
                            @if($application->rejection_reason)
                                <span class="block mt-2 font-semibold">Motif renseigné : « {{ $application->rejection_reason }} »</span>
                            @endif
                            Vous pouvez postuler à de futurs programmes ou solliciter un débriefing auprès de votre structure d'accompagnement.
                        @elseif($statusValue === 'EN_ATTENTE')
                            Votre candidature a franchi avec succès les premières étapes de sélection. En raison du nombre limité de places, votre dossier est positionné sur la liste d'attente.
                            L'équipe de {{ $application->program?->sae?->name ?? 'la structure' }} vous contactera si un désistement ou une ouverture supplémentaire se présente.
                        @elseif($statusValue === 'EN_EXAMEN')
                            Les membres du jury et chargés d'accompagnement examinent attentivement votre proposition de valeur, votre équipe et votre modèle économique.
                            Vous serez notifié dès la fin des délibérations.
                        @elseif($statusValue === 'SOUMISE')
                            Votre dossier a été transmis avec succès aux équipes de {{ $application->program?->sae?->name ?? 'la structure' }}.
                            Il sera prochainement assigné à un jury pour instruction et notation.
                        @else
                            {{ $application->decision ?: 'Suivez l\'évolution de votre démarche depuis cet espace.' }}
                        @endif
                    </p>

                    @if($application->comment && !empty($application->comment) && $statusValue !== 'SOUMISE' && !str_contains($rawComment, 'Motivation du candidat :'))
                        <div class="mt-3 rounded-lg dark:ring-white/10">
                            <p class="text-xs font-semibold text-gray-700 dark:text-gray-300">Appréciation du comité :</p>
                            <p class="text-xs italic text-gray-600 dark:text-gray-400 mt-0.5">« {{ $application->comment }} »</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Two-column main layout --}}
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

            {{-- Left column: dossier details (2/3) --}}
            <div class="lg:col-span-2 space-y-6">

                {{-- Project Snapshot Card --}}
                @if($project)
                    <div class="rounded-xl border border-gray-200/80 dark:border-white/10 p-6 shadow-sm dark:bg-gray-900">
                        <div class="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-gray-800">
                            <div class="flex items-center gap-2.5">
                                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400">
                                    <x-heroicon-m-light-bulb class="h-4 w-4" />
                                </div>
                                <h3 class="text-base font-bold text-gray-900 dark:text-white">Projet d'entreprise candidat</h3>
                            </div>

                            <a href="{{ \App\Filament\Entrepreneur\Resources\Projects\ProjectResource::getUrl('view', ['record' => $project]) }}"
                               class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-semibold text-gray-700 shadow-xs hover:bg-gray-50 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-white/5">
                                <span>Fiche complète</span>
                                <x-heroicon-m-arrow-top-right-on-square class="h-3.5 w-3.5 text-gray-400" />
                            </a>
                        </div>

                        <div class="mt-4 flex flex-col sm:flex-row items-start sm:items-center gap-4">
                            @if($project->logo)
                                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($project->logo) }}"
                                     alt="{{ $project->name }}"
                                     class="h-16 w-16 shrink-0 rounded-xl border border-gray-200 object-cover shadow-xs dark:border-gray-700">
                            @else
                                <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-100 to-teal-100 text-emerald-700 dark:from-emerald-900/40 dark:to-teal-900/40 dark:text-emerald-300 font-bold text-xl shadow-xs">
                                    {{ mb_strtoupper(mb_substr($project->name, 0, 2)) }}
                                </div>
                            @endif

                            <div class="min-w-0 flex-1">
                                <h4 class="text-lg font-bold text-gray-900 dark:text-white">{{ $project->name }}</h4>
                                <div class="flex flex-wrap items-center gap-2 mt-1">
                                    @if($project->sector)
                                        <span class="inline-flex items-center rounded-md bg-emerald-50 px-2 py-0.5 text-xs font-medium text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300">
                                            {{ $project->sector }}
                                        </span>
                                    @endif
                                    @if($project->territory)
                                        <span class="inline-flex items-center gap-1 text-xs text-gray-500 dark:text-gray-400">
                                            <x-heroicon-m-map-pin class="h-3.5 w-3.5 text-gray-400" />
                                            {{ $project->territory }}
                                        </span>
                                    @endif
                                    @if($project->team_size)
                                        <span class="inline-flex items-center gap-1 text-xs text-gray-500 dark:text-gray-400">
                                            <x-heroicon-m-users class="h-3.5 w-3.5 text-gray-400" />
                                            {{ $project->team_size }} pers.
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        @if($project->pitch)
                            <div class="mt-4 rounded-xl bg-gray-50 p-4 dark:bg-gray-800/50">
                                <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1">Pitch court</p>
                                <p class="text-sm italic text-gray-700 dark:text-gray-300 leading-relaxed">« {{ $project->pitch }} »</p>
                            </div>
                        @endif
                    </div>
                @endif

                {{-- Motivation & Candidate Declaration --}}
                @if($motivation || $needs)
                    <div class="rounded-xl border border-gray-200/80 dark:border-white/10 p-6 shadow-sm dark:bg-gray-900">
                        <div class="flex items-center gap-2.5 pb-4 border-b border-gray-100 dark:border-gray-800">
                            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-teal-100 text-teal-600 dark:bg-teal-900/30 dark:text-teal-400">
                                <x-heroicon-m-chat-bubble-bottom-center-text class="h-4 w-4" />
                            </div>
                            <h3 class="text-base font-bold text-gray-900 dark:text-white">Motivation & Attentes de l'entrepreneur</h3>
                        </div>

                        @if($motivation)
                            <div class="mt-4">
                                <h4 class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-2">Pourquoi rejoindre ce programme ?</h4>
                                <div class="rounded-xl bg-gray-50 p-4 dark:bg-gray-800/50 text-sm leading-relaxed text-gray-800 dark:text-gray-200 whitespace-pre-line">
                                    {{ $motivation }}
                                </div>
                            </div>
                        @endif

                        @if($needs)
                            <div class="mt-4">
                                <h4 class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-2">Besoins prioritaires formulés</h4>
                                <div class="rounded-xl border border-gray-200/80 dark:border-white/10 p-3.5 dark:bg-gray-800/70">
                                    <p class="text-sm text-gray-800 dark:text-gray-200">{{ $needs }}</p>
                                </div>
                            </div>
                        @endif
                    </div>
                @endif

                {{-- Questionnaire Answers (Form Builder Responses) --}}
                @if(!empty($application->answers) && is_array($application->answers))
                    <div class="rounded-xl border border-gray-200/80 dark:border-white/10 p-6 shadow-sm dark:bg-gray-900">
                        <div class="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-gray-800">
                            <div class="flex items-center gap-2.5">
                                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400">
                                    <x-heroicon-m-clipboard-document-list class="h-4 w-4" />
                                </div>
                                <div>
                                    <h3 class="text-base font-bold text-gray-900 dark:text-white">Réponses au formulaire de sélection</h3>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Questions spécifiques configurées pour cette cohorte</p>
                                </div>
                            </div>

                            <span class="rounded-full bg-blue-50 px-2.5 py-0.5 text-xs font-semibold text-blue-700 dark:bg-blue-950/40 dark:text-blue-300">
                                {{ count($application->answers) }} réponse(s)
                            </span>
                        </div>

                        <dl class="mt-4 space-y-3.5">
                            @foreach($application->answers as $questionKey => $answerValue)
                                @php
                                    $displayLabel = ucfirst(str_replace(['_', '-'], ' ', (string) $questionKey));
                                @endphp
                                <div class="rounded-xl border border-gray-100 bg-gray-50/70 p-4 dark:border-gray-800 dark:bg-gray-800/40">
                                    <dt class="text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5 flex items-center gap-2">
                                        <x-heroicon-m-question-mark-circle class="h-4 w-4 text-blue-500 shrink-0" />
                                        <span>{{ $displayLabel }}</span>
                                    </dt>
                                    <dd class="text-sm font-medium text-gray-900 dark:text-white pl-6 whitespace-pre-line">
                                        @if(is_array($answerValue))
                                            {{ implode(', ', $answerValue) }}
                                        @elseif(is_bool($answerValue))
                                            {{ $answerValue ? 'Oui' : 'Non' }}
                                        @elseif(empty($answerValue))
                                            <span class="text-gray-400 italic">Non renseigné</span>
                                        @else
                                            {{ $answerValue }}
                                        @endif
                                    </dd>
                                </div>
                            @endforeach
                        </dl>
                    </div>
                @endif

                {{-- Jury Criteria Breakdown (if evaluated with scores) --}}
                @if($application->evaluations->isNotEmpty())
                    <div class="rounded-xl border border-gray-200/80 dark:border-white/10 p-6 shadow-sm dark:bg-gray-900">
                        <div class="flex items-center justify-between pb-4 border-b border-gray-100 dark:border-gray-800">
                            <div class="flex items-center gap-2.5">
                                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400">
                                    <x-heroicon-m-chart-bar class="h-4 w-4" />
                                </div>
                                <div>
                                    <h3 class="text-base font-bold text-gray-900 dark:text-white">Évaluations & Notes du jury</h3>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Grille de notation pondérée multicritères</p>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 space-y-5">
                            @foreach($application->evaluations as $evaluation)
                                <div class="rounded-xl border border-gray-200 p-4.5 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/30">
                                    <div class="flex items-center justify-between mb-3">
                                        <div class="flex items-center gap-2">
                                            <div class="flex h-7 w-7 items-center justify-center rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300 font-semibold text-xs">
                                                J
                                            </div>
                                            <span class="text-xs font-semibold text-gray-900 dark:text-white">
                                                Membre du jury / Évaluateur
                                            </span>
                                            @if($evaluation->submitted_at)
                                                <span class="text-xs text-gray-400">• {{ $evaluation->submitted_at->format('d/m/Y') }}</span>
                                            @endif
                                        </div>

                                        @if($evaluation->total_score !== null)
                                            <span class="inline-flex items-center rounded-lg bg-emerald-100 px-2.5 py-1 text-xs font-bold text-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-300">
                                                {{ (float) $evaluation->total_score }} / 100
                                            </span>
                                        @endif
                                    </div>

                                    @if($evaluation->scores->isNotEmpty())
                                        <div class="space-y-3 mt-3 pt-3 border-t border-gray-200 dark:border-gray-700">
                                            @foreach($evaluation->scores as $sc)
                                                @php
                                                    $criterion = $sc->criterion;
                                                    $maxScore = $criterion?->max_score ?? 100;
                                                    $percentage = $maxScore > 0 ? min(100, round(((float) $sc->score / $maxScore) * 100)) : 0;
                                                @endphp
                                                <div>
                                                    <div class="flex items-center justify-between text-xs mb-1">
                                                        <span class="font-medium text-gray-700 dark:text-gray-300">
                                                            {{ $criterion?->name ?? 'Critère' }}
                                                            @if($criterion?->weight)
                                                                <span class="text-gray-400 font-normal">(coeff {{ $criterion->weight }}x)</span>
                                                            @endif
                                                        </span>
                                                        <span class="font-bold text-gray-900 dark:text-white">
                                                            {{ (float) $sc->score }} / {{ $maxScore }}
                                                        </span>
                                                    </div>
                                                    <div class="h-2 w-full rounded-full bg-gray-200 dark:bg-gray-700 overflow-hidden">
                                                        <div class="h-full rounded-full bg-emerald-500 dark:bg-emerald-400 transition-all duration-300"
                                                             style="width: {{ $percentage }}%"></div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif

                                    @if($evaluation->comment)
                                        <div class="mt-3 pt-3 border-t border-gray-200 dark:border-gray-700 text-xs italic text-gray-600 dark:text-gray-400">
                                            « {{ $evaluation->comment }} »
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Attached Documents --}}
                @if($application->documents->isNotEmpty())
                    <div class="rounded-xl border border-gray-200/80 dark:border-white/10 p-6 shadow-sm dark:bg-gray-900">
                        <div class="flex items-center gap-2.5 pb-4 border-b border-gray-100 dark:border-gray-800">
                            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-purple-100 text-purple-600 dark:bg-purple-900/30 dark:text-purple-400">
                                <x-heroicon-m-paper-clip class="h-4 w-4" />
                            </div>
                            <h3 class="text-base font-bold text-gray-900 dark:text-white">Documents & Pièces jointes</h3>
                        </div>

                        <ul class="mt-4 divide-y divide-gray-100 dark:divide-gray-800">
                            @foreach($application->documents as $doc)
                                <li class="py-3 flex items-center justify-between gap-3">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-gray-100 dark:bg-gray-800 text-gray-500">
                                            <x-heroicon-m-document-text class="h-5 w-5" />
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">
                                                {{ $doc->name ?? $doc->original_name ?? 'Pièce jointe' }}
                                            </p>
                                            <p class="text-xs text-gray-400">
                                                {{ $doc->type ?? 'Document' }}
                                                @if($doc->created_at)
                                                    • Ajouté le {{ $doc->created_at->format('d/m/Y') }}
                                                @endif
                                            </p>
                                        </div>
                                    </div>

                                    @if($doc->path)
                                        <a href="{{ \Illuminate\Support\Facades\Storage::url($doc->path) }}"
                                           target="_blank"
                                           class="inline-flex items-center gap-1 rounded-lg bg-gray-50 px-2.5 py-1.5 text-xs font-semibold text-emerald-600 hover:bg-emerald-50 dark:bg-gray-800 dark:text-emerald-400 dark:hover:bg-emerald-950/30">
                                            <x-heroicon-m-arrow-down-tray class="h-3.5 w-3.5" />
                                            <span>Ouvrir</span>
                                        </a>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>

            {{-- Right column: sidebar details (1/3) --}}
            <div class="space-y-6">

                {{-- Program & Cohort Card --}}
                <div class="rounded-xl border border-gray-200/80 dark:border-white/10 p-6 shadow-sm dark:bg-gray-900">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-4">
                        Programme visé
                    </h3>

                    <div class="space-y-4">
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Programme</p>
                            <p class="text-sm font-bold text-gray-900 dark:text-white mt-0.5">
                                {{ $application->program?->name ?? '—' }}
                            </p>
                            @if($application->program?->description)
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 line-clamp-3 leading-relaxed">
                                    {{ $application->program->description }}
                                </p>
                            @endif
                        </div>

                        <div class="pt-3 border-t border-gray-100 dark:border-gray-800">
                            <p class="text-xs text-gray-500 dark:text-gray-400">Structure d'accompagnement (SAE)</p>
                            <p class="text-sm font-semibold text-emerald-600 dark:text-emerald-400 mt-0.5 flex items-center gap-1.5">
                                <x-heroicon-m-building-office-2 class="h-4 w-4 shrink-0" />
                                {{ $application->program?->sae?->name ?? 'Structure Hubs Africa' }}
                            </p>
                        </div>

                        @if($application->cohort)
                            <div class="pt-3 border-t border-gray-100 dark:border-gray-800">
                                <p class="text-xs text-gray-500 dark:text-gray-400">Promotion / Cohorte</p>
                                <span class="inline-flex items-center gap-1 rounded-md bg-blue-50 px-2 py-1 text-xs font-semibold text-blue-700 dark:bg-blue-950/40 dark:text-blue-300 mt-1">
                                    <x-heroicon-m-academic-cap class="h-3.5 w-3.5" />
                                    {{ $application->cohort->name }}
                                </span>

                                @if($application->cohort->start_date || $application->cohort->end_date)
                                    <p class="text-xs text-gray-400 mt-1">
                                        {{ $application->cohort->start_date ? $application->cohort->start_date->format('d/m/Y') : '?' }}
                                        au
                                        {{ $application->cohort->end_date ? $application->cohort->end_date->format('d/m/Y') : '?' }}
                                    </p>
                                @endif
                            </div>
                        @endif

                        @if($application->program?->target_level || $application->program?->level)
                            <div class="pt-3 border-t border-gray-100 dark:border-gray-800">
                                <p class="text-xs text-gray-500 dark:text-gray-400">Jalon d'accompagnement</p>
                                <p class="text-xs font-semibold text-gray-800 dark:text-gray-200 mt-0.5">
                                    {{ $application->program->level?->name ?? $application->program->target_level }}
                                </p>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Timeline Milestones --}}
                <div class="rounded-xl border border-gray-200/80 dark:border-white/10 p-6 shadow-sm dark:bg-gray-900">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-4">
                        Chronologie du dossier
                    </h3>

                    <ol class="relative border-l-2 border-gray-200 dark:border-gray-700 ml-3 space-y-6">
                        {{-- Déposée --}}
                        @if($application->submitted_at)
                            <li class="ml-6">
                                <span class="absolute -left-2.5 flex h-5 w-5 items-center justify-center rounded-full bg-amber-500 text-white shadow-xs">
                                    <x-heroicon-m-paper-airplane class="h-3 w-3" />
                                </span>
                                <p class="text-xs font-bold text-gray-900 dark:text-white">Candidature transmise</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ $application->submitted_at->format('d/m/Y à H:i') }}</p>
                            </li>
                        @endif

                        {{-- Examinée / Reviewée --}}
                        @if($application->reviewed_at)
                            <li class="ml-6">
                                <span @class([
                                    'absolute -left-2.5 flex h-5 w-5 items-center justify-center rounded-full text-white shadow-xs',
                                    'bg-emerald-500' => $statusValue === 'ACCEPTEE',
                                    'bg-rose-500' => $statusValue === 'REFUSEE',
                                    'bg-blue-500' => !in_array($statusValue, ['ACCEPTEE', 'REFUSEE']),
                                ])>
                                    @if($statusValue === 'ACCEPTEE')
                                        <x-heroicon-m-check class="h-3 w-3" />
                                    @elseif($statusValue === 'REFUSEE')
                                        <x-heroicon-m-x-mark class="h-3 w-3" />
                                    @else
                                        <x-heroicon-m-eye class="h-3 w-3" />
                                    @endif
                                </span>
                                <p class="text-xs font-bold text-gray-900 dark:text-white">Délibération du comité</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ $application->reviewed_at->format('d/m/Y à H:i') }}</p>
                                @if($application->reviewer)
                                    <p class="text-xs text-gray-400 mt-0.5">Par : {{ $application->reviewer->name }}</p>
                                @endif
                            </li>
                        @endif

                        {{-- Initial Creation --}}
                        <li class="ml-6">
                            <span class="absolute -left-2.5 flex h-5 w-5 items-center justify-center rounded-full bg-gray-400 text-white shadow-xs">
                                <x-heroicon-m-plus class="h-3 w-3" />
                            </span>
                            <p class="text-xs font-semibold text-gray-700 dark:text-gray-300">Création initiale</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ $application->created_at?->format('d/m/Y à H:i') }}</p>
                        </li>
                    </ol>
                </div>

                {{-- Contact & Support Card --}}
                <div class="rounded-xl border border-gray-200 bg-gradient-to-br from-emerald-50 to-teal-50 p-6 shadow-sm dark:border-gray-800 dark:from-emerald-950/20 dark:to-teal-950/20">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-500 text-white shadow-xs">
                            <x-heroicon-m-question-mark-circle class="h-5 w-5" />
                        </div>
                        <h4 class="text-sm font-bold text-gray-900 dark:text-white">Besoin d'aide ?</h4>
                    </div>

                    <p class="text-xs text-gray-600 dark:text-gray-300 leading-relaxed">
                        Pour toute question relative aux critères de sélection ou aux modalités d'admission, vous pouvez contacter les responsables de votre structure.
                    </p>

                    <div class="mt-4 pt-3 border-t border-emerald-200/60 dark:border-emerald-800/40">
                        <p class="text-xs font-bold text-emerald-800 dark:text-emerald-300">
                            {{ $application->program?->sae?->name ?? 'Hubs Africa V2' }}
                        </p>
                        @if($application->program?->sae?->email)
                            <a href="mailto:{{ $application->program->sae->email }}" class="text-xs text-emerald-600 hover:underline dark:text-emerald-400 block mt-0.5">
                                {{ $application->program->sae->email }}
                            </a>
                        @endif
                    </div>
                </div>

            </div>
        </div>

    </div>
</x-filament-panels::page>
