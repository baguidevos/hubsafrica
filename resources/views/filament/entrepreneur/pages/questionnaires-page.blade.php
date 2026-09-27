<x-filament-panels::page>
    <div class="space-y-6">
        @if($unlocks->isEmpty())
            <div class="flex flex-col items-center justify-center rounded-xl border border-dashed border-gray-300 p-12 text-center dark:border-gray-700 bg-white dark:bg-gray-900">
                <x-heroicon-o-clipboard-document-check class="mx-auto h-12 w-12 text-gray-400" />
                <h3 class="mt-4 text-sm font-semibold text-gray-900 dark:text-white">Aucun questionnaire en attente</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Votre incubateur n'a pas encore assigné de formulaires spécifiques à remplir.</p>
            </div>
        @else
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($unlocks as $unlock)
                    <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset {{ $unlock->status === 'SOUMIS' ? 'bg-green-50 text-green-700 ring-green-600/20 dark:bg-green-900/30 dark:text-green-400' : 'bg-amber-50 text-amber-700 ring-amber-600/20 dark:bg-amber-900/30 dark:text-amber-400' }}">
                                    {{ $unlock->status }}
                                </span>
                                <span class="text-xs text-gray-400">{{ $unlock->created_at?->diffForHumans() }}</span>
                            </div>
                            <h4 class="font-semibold text-gray-900 dark:text-white">Formulaire #{{ $unlock->form_id }}</h4>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Échéance : {{ $unlock->deadline ? $unlock->deadline->format('d/m/Y') : 'Non définie' }}</p>
                        </div>
                        <div class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-800 flex justify-end">
                            @if($unlock->status === 'SOUMIS')
                                <span class="text-xs text-emerald-600 font-medium flex items-center gap-1">
                                    <x-heroicon-m-check-circle class="w-4 h-4" /> Complété
                                </span>
                            @else
                                <x-filament::button size="sm" color="primary">
                                    Remplir le formulaire
                                </x-filament::button>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-filament-panels::page>
