<x-filament-widgets::widget>
    @if($pendingUnlocks->isNotEmpty())
        @php
            $tenant = \Filament\Facades\Filament::getTenant();
            $tenantSlug = $tenant?->slug ?? auth()->user()?->sae_id;
        @endphp
        <div class="rounded-2xl border border-amber-200 bg-gradient-to-r from-amber-50 via-orange-50 to-amber-50/50 p-6 shadow-sm dark:border-amber-900/50 dark:from-amber-950/40 dark:via-orange-950/20 dark:to-gray-900">
            <div class="flex items-start justify-between gap-4">
                <div class="flex items-start gap-4">
                    <div class="rounded-xl bg-amber-500/10 p-2.5 text-amber-600 dark:bg-amber-400/10 dark:text-amber-400">
                        <x-heroicon-o-clipboard-document-check class="h-7 w-7" />
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-base font-bold text-gray-900 dark:text-white">
                                Questionnaire(s) d'évaluation en attente de réponse ({{ $pendingUnlocks->count() }})
                            </h3>
                            <span class="inline-flex items-center rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-semibold text-amber-800 dark:bg-amber-900/50 dark:text-amber-300">
                                Action requise
                            </span>
                        </div>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                            Votre structure d'accompagnement vous a assigné ces formulaires pour évaluer l'avancement et la maturité de votre projet.
                        </p>
                    </div>
                </div>

                <a href="{{ route('filament.entrepreneur.pages.questionnaires-page', ['tenant' => $tenantSlug]) }}"
                   class="hidden shrink-0 text-sm font-semibold text-amber-700 hover:text-amber-800 dark:text-amber-400 sm:inline-flex sm:items-center sm:gap-1">
                    Voir tout <x-heroicon-m-arrow-right class="h-4 w-4" />
                </a>
            </div>

            <div class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($pendingUnlocks as $unlock)
                    @php
                        $form = $unlock->form;
                        $isOverdue = $unlock->isOverdue();
                    @endphp
                    <div class="flex flex-col justify-between rounded-xl border border-amber-200/80 bg-white/90 p-4 shadow-sm backdrop-blur transition hover:border-amber-400 dark:border-gray-800 dark:bg-gray-900/80">
                        <div>
                            <div class="flex items-center justify-between gap-2 mb-2">
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

                            <h4 class="font-bold text-gray-900 line-clamp-1 dark:text-white">
                                {{ $form?->name ?? 'Questionnaire #' . $unlock->form_id }}
                            </h4>

                            @if($form?->program)
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                    Programme : {{ $form->program->name }}
                                </p>
                            @endif

                            @if($unlock->note)
                                <p class="mt-2 text-xs italic text-gray-600 line-clamp-2 dark:text-gray-400 bg-amber-50/60 p-1.5 rounded dark:bg-gray-800/60">
                                    « {{ $unlock->note }} »
                                </p>
                            @endif
                        </div>

                        <div class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-800 flex items-center justify-between">
                            <span class="text-xs text-amber-700 font-medium dark:text-amber-400">
                                À compléter
                            </span>
                            @if($form)
                                <a href="{{ route('filament.entrepreneur.pages.fill-questionnaire', ['tenant' => $tenantSlug, 'slug' => $form->slug]) }}"
                                   class="inline-flex items-center gap-1.5 rounded-lg bg-amber-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-amber-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-amber-600 transition">
                                    Remplir
                                    <x-heroicon-m-arrow-right class="h-3.5 w-3.5" />
                                </a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</x-filament-widgets::widget>
