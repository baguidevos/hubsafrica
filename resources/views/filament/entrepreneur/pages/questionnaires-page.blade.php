<x-filament-panels::page>
    @php
        $tenant = \Filament\Facades\Filament::getTenant();
        $tenantSlug = $tenant?->slug ?? auth()->user()?->sae_id;
    @endphp

    <div class="space-y-8">
        {{-- En-tête explicatif & statistiques rapides --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900 flex items-center justify-between">
                <div>
                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Total formulaires assignés</span>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $totalCount }}</h3>
                </div>
                <div class="rounded-xl bg-blue-500/10 p-3 text-blue-600 dark:bg-blue-400/10 dark:text-blue-400">
                    <x-heroicon-o-clipboard-document-list class="w-6 h-6" />
                </div>
            </div>

            <div class="rounded-2xl border border-amber-200 bg-gradient-to-br from-amber-50 to-white p-5 shadow-sm dark:border-amber-900/40 dark:from-gray-900 dark:to-gray-900 flex items-center justify-between">
                <div>
                    <span class="text-xs font-semibold text-amber-700 dark:text-amber-400">À compléter</span>
                    <h3 class="text-2xl font-bold text-amber-800 dark:text-amber-300 mt-1">{{ $pendingCount }}</h3>
                </div>
                <div class="rounded-xl bg-amber-500/10 p-3 text-amber-600 dark:bg-amber-400/10 dark:text-amber-400">
                    <x-heroicon-o-clock class="w-6 h-6" />
                </div>
            </div>

            <div class="rounded-2xl border border-emerald-200 bg-gradient-to-br from-emerald-50 to-white p-5 shadow-sm dark:border-emerald-900/40 dark:from-gray-900 dark:to-gray-900 flex items-center justify-between">
                <div>
                    <span class="text-xs font-semibold text-emerald-700 dark:text-emerald-400">Complétés</span>
                    <h3 class="text-2xl font-bold text-emerald-800 dark:text-emerald-300 mt-1">{{ $completedCount }}</h3>
                </div>
                <div class="rounded-xl bg-emerald-500/10 p-3 text-emerald-600 dark:bg-emerald-400/10 dark:text-emerald-400">
                    <x-heroicon-o-check-circle class="w-6 h-6" />
                </div>
            </div>
        </div>

        {{-- Section 1 : Formulaires à remplir --}}
        <div>
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <x-heroicon-o-exclamation-circle class="w-5 h-5 text-amber-500" />
                        Questionnaires en attente de réponse ({{ $pendingCount }})
                    </h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                        Formulaires requis par votre incubateur pour le suivi et l'évaluation de vos jalons.
                    </p>
                </div>
            </div>

            @if($pendingUnlocks->isEmpty())
                <div class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-gray-300 p-10 text-center dark:border-gray-800 bg-white dark:bg-gray-900/50">
                    <div class="rounded-full bg-emerald-50 p-3 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400">
                        <x-heroicon-o-check-badge class="h-8 w-8" />
                    </div>
                    <h3 class="mt-3 text-sm font-bold text-gray-900 dark:text-white">Aucun questionnaire en attente</h3>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Félicitations ! Vous êtes complètement à jour dans vos évaluations et collectes de données.
                    </p>
                </div>
            @else
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($pendingUnlocks as $unlock)
                        @php
                            $form = $unlock->form;
                            $isOverdue = $unlock->isOverdue();
                        @endphp
                        <div class="flex flex-col justify-between rounded-2xl border border-gray-200 bg-white p-5 shadow-sm transition hover:shadow-md dark:border-gray-800 dark:bg-gray-900">
                            <div>
                                <div class="flex items-center justify-between gap-2 mb-3">
                                    @if($form?->type)
                                        <span class="inline-flex items-center gap-1 rounded-md px-2 py-0.5 text-xs font-semibold ring-1 ring-inset {{ $form->type->getColor() === 'info' ? 'bg-blue-50 text-blue-700 ring-blue-600/20 dark:bg-blue-900/30 dark:text-blue-300' : ($form->type->getColor() === 'warning' ? 'bg-amber-50 text-amber-700 ring-amber-600/20 dark:bg-amber-900/30 dark:text-amber-300' : 'bg-emerald-50 text-emerald-700 ring-emerald-600/20 dark:bg-emerald-900/30 dark:text-emerald-300') }}">
                                            {{ $form->type->getLabel() }}
                                        </span>
                                    @else
                                        <span class="text-xs text-gray-400">Évaluation</span>
                                    @endif

                                    @if($unlock->due_date)
                                        <span class="text-xs font-medium {{ $isOverdue ? 'text-rose-600 font-bold dark:text-rose-400' : 'text-gray-500 dark:text-gray-400' }}">
                                            {{ $isOverdue ? '⚠️ En retard' : 'Avant le ' . $unlock->due_date->format('d/m/Y') }}
                                        </span>
                                    @endif
                                </div>

                                <h3 class="font-bold text-base text-gray-900 dark:text-white line-clamp-2">
                                    {{ $form?->name ?? 'Questionnaire #' . $unlock->form_id }}
                                </h3>

                                @if($form?->program)
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                        Programme : <span class="font-medium text-gray-700 dark:text-gray-300">{{ $form->program->name }}</span>
                                    </p>
                                @endif

                                @if($form?->description)
                                    <p class="mt-2 text-xs text-gray-600 line-clamp-2 dark:text-gray-400">
                                        {{ $form->description }}
                                    </p>
                                @endif

                                @if($unlock->note)
                                    <div class="mt-3 rounded-lg bg-amber-50/80 p-2.5 text-xs text-amber-900 dark:bg-amber-950/30 dark:text-amber-300 border border-amber-200/50 dark:border-amber-900/40">
                                        <span class="font-semibold block">Consigne de votre incubateur :</span>
                                        « {{ $unlock->note }} »
                                    </div>
                                @endif
                            </div>

                            <div class="mt-6 pt-4 border-t border-gray-100 dark:border-gray-800 flex items-center justify-between">
                                <span class="inline-flex items-center gap-1 text-xs text-amber-600 font-medium dark:text-amber-400">
                                    <x-heroicon-m-clock class="w-4 h-4" />
                                    En attente
                                </span>

                                @if($form)
                                    <a href="{{ route('filament.entrepreneur.pages.fill-questionnaire', ['tenant' => $tenantSlug, 'slug' => $form->slug]) }}"
                                       class="inline-flex items-center gap-1.5 rounded-xl bg-primary-600 px-4 py-2 text-xs font-bold text-white shadow-sm hover:bg-primary-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600 transition">
                                        Remplir le formulaire
                                        <x-heroicon-m-arrow-right class="h-3.5 w-3.5" />
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Section 2 : Historique des formulaires complétés --}}
        <div class="pt-6 border-t border-gray-200 dark:border-gray-800">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <x-heroicon-o-check-badge class="w-5 h-5 text-emerald-500" />
                        Historique des formulaires complétés ({{ $completedCount }})
                    </h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                        Consultez à tout moment les réponses que vous avez fournies.
                    </p>
                </div>
            </div>

            @if($completedUnlocks->isEmpty())
                <div class="rounded-xl border border-gray-100 bg-gray-50 p-6 text-center dark:border-gray-800 dark:bg-gray-900/30">
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Aucun formulaire n'a encore été complété dans cet historique.
                    </p>
                </div>
            @else
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($completedUnlocks as $unlock)
                        @php
                            $form = $unlock->form;
                        @endphp
                        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900 flex flex-col justify-between">
                            <div>
                                <div class="flex items-center justify-between gap-2 mb-2">
                                    @if($form?->type)
                                        <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-semibold bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                            {{ $form->type->getLabel() }}
                                        </span>
                                    @endif
                                    <span class="text-xs text-gray-400">
                                        {{ $unlock->completed_at ? $unlock->completed_at->format('d/m/Y') : '' }}
                                    </span>
                                </div>

                                <h4 class="font-bold text-gray-900 dark:text-white line-clamp-2">
                                    {{ $form?->name ?? 'Questionnaire #' . $unlock->form_id }}
                                </h4>

                                @if($form?->program)
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                        Programme : {{ $form->program->name }}
                                    </p>
                                @endif
                            </div>

                            <div class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-800 flex items-center justify-between">
                                <span class="inline-flex items-center gap-1 text-xs text-emerald-600 font-semibold dark:text-emerald-400">
                                    <x-heroicon-m-check-circle class="w-4 h-4" />
                                    Complété
                                </span>

                                @if($form)
                                    <a href="{{ route('filament.entrepreneur.pages.fill-questionnaire', ['tenant' => $tenantSlug, 'slug' => $form->slug]) }}"
                                       class="inline-flex items-center gap-1 rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition">
                                        <x-heroicon-o-eye class="w-3.5 h-3.5" />
                                        Voir mes réponses
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-filament-panels::page>
