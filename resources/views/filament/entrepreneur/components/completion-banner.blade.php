@php
    $tenant = \Filament\Facades\Filament::getTenant();
    $user = auth()->user();
    $status = $tenant instanceof \App\Models\Sae
        ? \App\Support\EntrepreneurCompletionChecker::forUserInSae($user, $tenant)
        : null;
@endphp

@if ($status && ! $status['is_complete'])
    @php
        $profileUrl = \App\Filament\Entrepreneur\Pages\ProfilePage::getUrl(['tenant' => $tenant]);
        $projectUrl = $status['project_id']
            ? \App\Filament\Entrepreneur\Resources\Projects\ProjectResource::getUrl('edit', ['tenant' => $tenant, 'record' => $status['project_id']])
            : \App\Filament\Entrepreneur\Resources\Projects\ProjectResource::getUrl('create', ['tenant' => $tenant]);

        $missingParts = [];
        if (! $status['profile_complete']) {
            $missingParts[] = 'votre profil (' . count($status['missing_profile_fields']) . ' champ' . (count($status['missing_profile_fields']) > 1 ? 's' : '') . ')';
        }
        if (! $status['project_complete']) {
            $missingParts[] = $status['has_project']
                ? 'votre projet (' . count($status['missing_project_fields']) . ' champ' . (count($status['missing_project_fields']) > 1 ? 's' : '') . ')'
                : 'la création de votre projet';
        }

        $previewMissing = array_slice(
            array_merge($status['missing_profile_fields'], $status['missing_project_fields']),
            0,
            4
        );
        $totalMissing = count($status['missing_profile_fields']) + count($status['missing_project_fields']);
    @endphp

    <div class="w-full border-b border-amber-500/30 bg-amber-500/10 px-4 py-2.5 sm:px-6 lg:px-8">
        <div class="mx-auto flex flex-col gap-2.5 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-start gap-3 min-w-0">
                <x-filament::badge color="warning" class="shrink-0 mt-0.5">
                    {{ $status['percentage'] }}%
                </x-filament::badge>

                <div class="min-w-0 text-xs sm:text-sm">
                    <p class="font-semibold text-amber-900 dark:text-amber-200">
                        Complétez {{ implode(' et ', $missingParts) }} pour finaliser votre dossier.
                    </p>
                    <p class="text-xs text-amber-800/80 dark:text-amber-300/80 truncate">
                        Manquant : {{ implode(', ', $previewMissing) }}@if ($totalMissing > 4) et {{ $totalMissing - 4 }} autre(s)@endif.
                    </p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2 shrink-0">
                @if (! $status['profile_complete'])
                    <x-filament::button
                        tag="a"
                        href="{{ $profileUrl }}"
                        size="xs"
                        color="warning"
                        icon="heroicon-m-user"
                    >
                        Compléter mon profil
                    </x-filament::button>
                @endif

                @if (! $status['project_complete'])
                    <x-filament::button
                        tag="a"
                        href="{{ $projectUrl }}"
                        size="xs"
                        color="primary"
                        icon="heroicon-m-rocket-launch"
                    >
                        {{ $status['has_project'] ? 'Compléter mon projet' : 'Créer mon projet' }}
                    </x-filament::button>
                @endif
            </div>
        </div>
    </div>
@endif
