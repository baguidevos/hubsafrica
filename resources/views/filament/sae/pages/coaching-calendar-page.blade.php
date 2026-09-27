<x-filament-panels::page>
    <div class="space-y-4">
        <!-- Barre d'outils du calendrier -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-4 rounded-xl border border-stone-200 dark:border-stone-800 bg-white dark:bg-stone-900 shadow-sm">
            <div class="flex items-center gap-2">
                <button wire:click="previousMonth" type="button" class="p-2 rounded-lg border border-stone-200 dark:border-stone-700 hover:bg-stone-100 dark:hover:bg-stone-800 text-stone-700 dark:text-stone-300">
                    <x-heroicon-m-chevron-left class="w-5 h-5" />
                </button>
                <button wire:click="today" type="button" class="px-3 py-1.5 text-xs font-semibold rounded-lg border border-stone-200 dark:border-stone-700 hover:bg-stone-100 dark:hover:bg-stone-800 text-stone-700 dark:text-stone-300">
                    Aujourd'hui
                </button>
                <button wire:click="nextMonth" type="button" class="p-2 rounded-lg border border-stone-200 dark:border-stone-700 hover:bg-stone-100 dark:hover:bg-stone-800 text-stone-700 dark:text-stone-300">
                    <x-heroicon-m-chevron-right class="w-5 h-5" />
                </button>
                <h2 class="ml-2 text-lg font-bold text-stone-900 dark:text-white capitalize">
                    {{ $this->monthName }}
                </h2>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <div class="flex items-center gap-2">
                    <label class="text-xs font-medium text-stone-500">Coach :</label>
                    <select wire:model.live="selectedCoachId" class="text-xs rounded-lg border-stone-300 dark:border-stone-700 dark:bg-stone-800 text-stone-800 dark:text-stone-200 py-1.5 px-3">
                        <option value="all">Tous les coachs</option>
                        @foreach($this->coaches as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>

                <a href="{{ url(filament()->getCurrentPanel()->getId() . '/coaching-sessions/create') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg bg-teal-600 text-white hover:bg-teal-700 shadow-sm transition">
                    <x-heroicon-m-plus class="w-4 h-4" />
                    <span>Planifier une séance</span>
                </a>
            </div>
        </div>

        <!-- Grille du calendrier -->
        <div class="rounded-xl border border-stone-200 dark:border-stone-800 bg-white dark:bg-stone-900 shadow-sm overflow-hidden">
            <!-- Jours de la semaine -->
            <div class="grid grid-cols-7 border-b border-stone-200 dark:border-stone-800 bg-stone-50 dark:bg-stone-950 text-center text-xs font-semibold text-stone-600 dark:text-stone-400 py-2.5">
                <div>Lun</div>
                <div>Mar</div>
                <div>Mer</div>
                <div>Jeu</div>
                <div>Ven</div>
                <div>Sam</div>
                <div>Dim</div>
            </div>

            <!-- Jours du mois -->
            <div class="grid grid-cols-7 auto-rows-fr divide-x divide-y divide-stone-200 dark:divide-stone-800">
                @foreach($this->days as $d)
                    <div class="min-h-[110px] p-1.5 flex flex-col justify-between transition {{ $d['isCurrentMonth'] ? 'bg-white dark:bg-stone-900' : 'bg-stone-50/60 dark:bg-stone-950/40 text-stone-400' }} {{ $d['isToday'] ? 'ring-2 ring-inset ring-teal-500 bg-teal-50/10' : '' }}">
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-xs font-bold px-1.5 py-0.5 rounded-full {{ $d['isToday'] ? 'bg-teal-600 text-white' : ($d['isCurrentMonth'] ? 'text-stone-800 dark:text-stone-200' : 'text-stone-400') }}">
                                {{ $d['dayNumber'] }}
                            </span>
                            @if($d['sessions']->count() > 0)
                                <span class="text-[10px] font-bold text-teal-600 dark:text-teal-400 bg-teal-50 dark:bg-teal-950/50 px-1 rounded">
                                    {{ $d['sessions']->count() }}
                                </span>
                            @endif
                        </div>

                        <!-- Liste des séances du jour -->
                        <div class="space-y-1 overflow-y-auto max-h-[85px]">
                            @foreach($d['sessions'] as $s)
                                @php
                                    $time = \Carbon\Carbon::parse($s->date)->format('H:i');
                                    $typeVal = is_object($s->type) ? $s->type->value : $s->type;
                                    $typeColor = match($typeVal) {
                                        'COLLECTIF' => 'bg-emerald-50 border-emerald-200 text-emerald-800 dark:bg-emerald-950/40 dark:border-emerald-800 dark:text-emerald-300',
                                        'MENTORAT' => 'bg-rose-50 border-rose-200 text-rose-800 dark:bg-rose-950/40 dark:border-rose-800 dark:text-rose-300',
                                        'ATELIER' => 'bg-amber-50 border-amber-200 text-amber-800 dark:bg-amber-950/40 dark:border-amber-800 dark:text-amber-300',
                                        default => 'bg-teal-50 border-teal-200 text-teal-800 dark:bg-teal-950/40 dark:border-teal-800 dark:text-teal-300',
                                    };
                                @endphp
                                <button
                                    wire:click="openSession({{ $s->id }})"
                                    type="button"
                                    class="w-full text-left p-1 rounded border text-[11px] leading-tight truncate block transition hover:opacity-80 shadow-xs {{ $typeColor }}"
                                    title="{{ $s->theme ?: 'Séance' }} - {{ $s->coach?->name }} ({{ $time }})"
                                >
                                    <div class="flex items-center gap-1 font-semibold truncate">
                                        <span class="tabular-nums">{{ $time }}</span>
                                        <span class="truncate">{{ $s->theme ?: ($s->project?->name ?: 'Séance') }}</span>
                                    </div>
                                    <div class="text-[9px] opacity-75 truncate">
                                        {{ $s->coach?->name }}
                                    </div>
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Modal de détail de séance -->
        @if($this->selectedSession)
            @php
                $sel = $this->selectedSession;
                $dateObj = \Carbon\Carbon::parse($sel->date);
                $googleStart = $dateObj->copy()->setTimezone('UTC')->format('Ymd\THis\Z');
                $googleEnd = $dateObj->copy()->addMinutes($sel->duration)->setTimezone('UTC')->format('Ymd\THis\Z');
                $googleUrl = 'https://calendar.google.com/calendar/render?action=TEMPLATE'
                    . '&text=' . urlencode('Coaching SIGAE: ' . ($sel->theme ?: 'Séance'))
                    . '&dates=' . $googleStart . '/' . $googleEnd
                    . '&details=' . urlencode("Coach: " . ($sel->coach?->name ?? '—') . "\nProjet: " . ($sel->project?->name ?? '—') . "\nObjectifs: " . ($sel->objectives ?? '—'))
                    . '&location=' . urlencode('Hub Africa / En ligne');
            @endphp
            <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-stone-900/60 backdrop-blur-xs" wire:keydown.escape="closeSession">
                <div class="bg-white dark:bg-stone-900 rounded-2xl max-w-xl w-full p-6 shadow-2xl border border-stone-200 dark:border-stone-800 space-y-4 animate-in fade-in zoom-in-95 duration-150">
                    <div class="flex items-start justify-between border-b border-stone-100 dark:border-stone-800 pb-3">
                        <div>
                            <span class="inline-block text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full bg-teal-100 text-teal-800 dark:bg-teal-900 dark:text-teal-200 mb-1">
                                {{ is_object($sel->type) && method_exists($sel->type, 'getLabel') ? $sel->type->getLabel() : $sel->type }} &bull; {{ is_object($sel->status) && method_exists($sel->status, 'getLabel') ? $sel->status->getLabel() : $sel->status }}
                            </span>
                            <h3 class="text-base font-bold text-stone-900 dark:text-white">
                                {{ $sel->theme ?: 'Séance de coaching' }}
                            </h3>
                        </div>
                        <button wire:click="closeSession" type="button" class="text-stone-400 hover:text-stone-600 dark:hover:text-stone-200 p-1">
                            <x-heroicon-m-x-mark class="w-6 h-6" />
                        </button>
                    </div>

                    <div class="grid grid-cols-2 gap-3 text-xs">
                        <div class="p-2.5 rounded-lg bg-stone-50 dark:bg-stone-800/60">
                            <span class="block text-[10px] text-stone-400 font-semibold uppercase">Date & Horaires</span>
                            <span class="font-bold text-stone-800 dark:text-stone-200">{{ $dateObj->translatedFormat('d F Y à H:i') }}</span>
                            <span class="text-stone-500"> ({{ $sel->duration }} min)</span>
                        </div>
                        <div class="p-2.5 rounded-lg bg-stone-50 dark:bg-stone-800/60">
                            <span class="block text-[10px] text-stone-400 font-semibold uppercase">Coach intervenant</span>
                            <span class="font-bold text-stone-800 dark:text-stone-200">{{ $sel->coach?->name ?: '—' }}</span>
                            <span class="block text-[10px] text-stone-400">{{ $sel->coach?->email }}</span>
                        </div>
                        <div class="p-2.5 rounded-lg bg-stone-50 dark:bg-stone-800/60">
                            <span class="block text-[10px] text-stone-400 font-semibold uppercase">Projet</span>
                            <span class="font-bold text-stone-800 dark:text-stone-200">{{ $sel->project?->name ?: 'Non renseigné' }}</span>
                        </div>
                        <div class="p-2.5 rounded-lg bg-stone-50 dark:bg-stone-800/60">
                            <span class="block text-[10px] text-stone-400 font-semibold uppercase">Entrepreneur</span>
                            <span class="font-bold text-stone-800 dark:text-stone-200">{{ $sel->entrepreneur?->full_name ?: ($sel->project?->entrepreneur?->full_name ?: '—') }}</span>
                        </div>
                    </div>

                    @if($sel->objectives)
                        <div class="text-xs space-y-1">
                            <h4 class="font-semibold text-stone-800 dark:text-stone-200">Objectifs de la séance :</h4>
                            <p class="p-2.5 rounded-lg border border-stone-200 dark:border-stone-800 bg-stone-50 dark:bg-stone-800/40 text-stone-700 dark:text-stone-300 whitespace-pre-line">{{ $sel->objectives }}</p>
                        </div>
                    @endif

                    @if($sel->report)
                        <div class="text-xs space-y-1">
                            <h4 class="font-semibold text-stone-800 dark:text-stone-200">Compte-rendu & Échanges :</h4>
                            <p class="p-2.5 rounded-lg border border-stone-200 dark:border-stone-800 bg-stone-50 dark:bg-stone-800/40 text-stone-700 dark:text-stone-300 whitespace-pre-line">{{ $sel->report }}</p>
                        </div>
                    @endif

                    @if($sel->next_actions)
                        <div class="text-xs space-y-1">
                            <h4 class="font-semibold text-stone-800 dark:text-stone-200">Prochaines actions convenues :</h4>
                            <p class="p-2.5 rounded-lg border border-stone-200 dark:border-stone-800 bg-stone-50 dark:bg-stone-800/40 text-stone-700 dark:text-stone-300 whitespace-pre-line">{{ $sel->next_actions }}</p>
                        </div>
                    @endif

                    <div class="flex items-center justify-between pt-3 border-t border-stone-100 dark:border-stone-800">
                        <a href="{{ $googleUrl }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-lg border border-stone-200 dark:border-stone-700 hover:bg-stone-100 dark:hover:bg-stone-800 text-stone-700 dark:text-stone-300">
                            <x-heroicon-o-calendar-days class="w-4 h-4 text-teal-600" />
                            <span>Ajouter à Google Agenda</span>
                        </a>

                        <div class="flex items-center gap-2">
                            <a href="{{ url(filament()->getCurrentPanel()->getId() . '/coaching-sessions/' . $sel->id) }}" class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold rounded-lg bg-stone-100 dark:bg-stone-800 hover:bg-stone-200 dark:hover:bg-stone-700 text-stone-800 dark:text-stone-200">
                                <span>Fiche A4 &rarr;</span>
                            </a>
                            <a href="{{ url(filament()->getCurrentPanel()->getId() . '/coaching-sessions/' . $sel->id . '/edit') }}" class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold rounded-lg bg-teal-600 text-white hover:bg-teal-700">
                                <span>Modifier</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>
