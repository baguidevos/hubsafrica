<x-filament-panels::page>
    @php
        $tenant = \Filament\Facades\Filament::getTenant();
        $tenantSlug = $tenant?->slug ?? auth()->user()?->sae_id;
    @endphp

    <div class="space-y-6 max-w-4xl mx-auto">
        {{-- En-tête de retour et cadrage --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-gray-200 dark:border-gray-800">
            <div>
                <a href="{{ route('filament.entrepreneur.pages.questionnaires-page', ['tenant' => $tenantSlug]) }}"
                   class="inline-flex items-center gap-1.5 text-xs font-semibold text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition mb-2">
                    <x-heroicon-m-arrow-left class="w-4 h-4" />
                    Retour à mes questionnaires
                </a>
                <div class="flex items-center gap-2.5 flex-wrap">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                        {{ $evaluationForm->name }}
                    </h1>
                    @if($evaluationForm->type)
                        <span class="inline-flex items-center gap-1 rounded-md px-2.5 py-0.5 text-xs font-semibold ring-1 ring-inset {{ $evaluationForm->type->getColor() === 'info' ? 'bg-blue-50 text-blue-700 ring-blue-600/20 dark:bg-blue-900/30 dark:text-blue-300' : ($evaluationForm->type->getColor() === 'warning' ? 'bg-amber-50 text-amber-700 ring-amber-600/20 dark:bg-amber-900/30 dark:text-amber-300' : 'bg-emerald-50 text-emerald-700 ring-emerald-600/20 dark:bg-emerald-900/30 dark:text-emerald-300') }}">
                            {{ $evaluationForm->type->getLabel() }}
                        </span>
                    @endif
                    @if($evaluationForm->is_mandatory)
                        <span class="inline-flex items-center rounded-md bg-rose-50 px-2 py-0.5 text-xs font-medium text-rose-700 ring-1 ring-inset ring-rose-600/20 dark:bg-rose-900/30 dark:text-rose-400">
                            Requis
                        </span>
                    @endif
                </div>

                @if($evaluationForm->program)
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        Programme rattaché : <span class="font-medium text-gray-700 dark:text-gray-300">{{ $evaluationForm->program->name }}</span>
                    </p>
                @endif
            </div>

            @if($unlock->due_date)
                <div class="rounded-xl border border-gray-200 bg-gray-50/80 px-3.5 py-2 text-right dark:border-gray-800 dark:bg-gray-900/50">
                    <span class="block text-xs text-gray-500 dark:text-gray-400">Date limite de réponse</span>
                    <span class="text-sm font-bold {{ $unlock->isOverdue() ? 'text-rose-600 dark:text-rose-400' : 'text-gray-900 dark:text-white' }}">
                        {{ $unlock->due_date->format('d/m/Y') }}
                        @if($unlock->isOverdue())
                            <span class="text-xs font-normal text-rose-500">(En retard)</span>
                        @endif
                    </span>
                </div>
            @endif
        </div>

        {{-- Consigne SAE --}}
        @if($unlock->note)
            <div class="rounded-xl border border-amber-200 bg-amber-50/70 p-4 dark:border-amber-900/40 dark:bg-amber-950/20 flex items-start gap-3">
                <x-heroicon-m-chat-bubble-bottom-center-text class="w-5 h-5 text-amber-600 dark:text-amber-400 shrink-0 mt-0.5" />
                <div>
                    <h4 class="text-xs font-bold text-amber-900 dark:text-amber-200">Consignes de votre structure d'accompagnement :</h4>
                    <p class="mt-0.5 text-xs text-amber-800 dark:text-amber-300">
                        {{ $unlock->note }}
                    </p>
                </div>
            </div>
        @endif

        {{-- Description du formulaire --}}
        @if($evaluationForm->description)
            <div class="prose prose-sm dark:prose-invert max-w-none text-gray-600 dark:text-gray-300">
                <p>{{ $evaluationForm->description }}</p>
            </div>
        @endif

        {{-- État : Déjà soumis vs À compléter --}}
        @if($unlock->isCompleted())
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50/50 p-6 shadow-sm dark:border-emerald-900/50 dark:bg-emerald-950/20">
                <div class="flex items-center gap-3">
                    <div class="rounded-full bg-emerald-500/10 p-2 text-emerald-600 dark:text-emerald-400">
                        <x-heroicon-m-check-circle class="w-8 h-8" />
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-emerald-900 dark:text-emerald-200">
                            Questionnaire complété avec succès
                        </h3>
                        <p class="text-xs text-emerald-700 dark:text-emerald-300 mt-0.5">
                            Vos réponses ont été transmises à l'équipe le {{ $unlock->completed_at?->format('d/m/Y à H:i') ?? 'récemment' }}.
                        </p>
                    </div>
                </div>

                @if($unlock->submission && !empty($unlock->submission->data))
                    <div class="mt-6 border-t border-emerald-200/60 pt-5 dark:border-emerald-900/40">
                        <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">
                            Récapitulatif de vos réponses soumises :
                        </h4>
                        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
                            <dl class="divide-y divide-gray-200 dark:divide-gray-800 text-xs">
                                @foreach($unlock->submission->data as $key => $val)
                                    <div class="px-4 py-3 sm:grid sm:grid-cols-3 sm:gap-4">
                                        <dt class="font-medium text-gray-500 dark:text-gray-400 capitalize">
                                            {{ str_replace(['_', '-'], ' ', $key) }}
                                        </dt>
                                        <dd class="mt-1 text-gray-900 dark:text-white sm:col-span-2 sm:mt-0 font-semibold">
                                            @if(is_array($val))
                                                {{ implode(', ', $val) }}
                                            @elseif(is_bool($val))
                                                {{ $val ? 'Oui' : 'Non' }}
                                            @else
                                                {{ $val ?? '—' }}
                                            @endif
                                        </dd>
                                    </div>
                                @endforeach
                            </dl>
                        </div>
                    </div>
                @endif

                <div class="mt-6 flex justify-end">
                    <a href="{{ route('filament.entrepreneur.pages.questionnaires-page', ['tenant' => $tenantSlug]) }}"
                       class="inline-flex items-center gap-2 rounded-xl bg-white px-4 py-2 text-xs font-semibold text-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-200 dark:ring-gray-700 dark:hover:bg-gray-700 transition">
                        <x-heroicon-m-arrow-left class="w-4 h-4" />
                        Retourner aux questionnaires
                    </a>
                </div>
            </div>
        @else
            {{-- Formulaire interactif Packstub Form Builder --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <livewire:form-builder :form="$evaluationForm" />
            </div>
        @endif
    </div>
</x-filament-panels::page>
