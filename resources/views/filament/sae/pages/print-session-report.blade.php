<div class="max-w-[780px] mx-auto bg-white text-stone-900 p-6 rounded-lg text-xs leading-relaxed" id="session-printable-sheet">
    <div class="flex items-start justify-between border-b-2 border-stone-800 pb-3 mb-4">
        <div>
            <h1 class="text-xl font-bold tracking-tight text-stone-900">Compte rendu de séance de coaching</h1>
            <p class="text-xs text-stone-600">
                {{ is_object($record->type) && method_exists($record->type, 'getLabel') ? $record->type->getLabel() : $record->type }} &bull; {{ $record->theme ?: 'Séance de travail' }}
            </p>
        </div>
        <div class="text-right text-[11px] text-stone-500">
            <p class="font-bold text-stone-800">{{ $record->sae?->name ?? 'Hubs Africa' }}</p>
            <p>Édité le {{ now()->translatedFormat('d F Y à H:i') }}</p>
        </div>
    </div>

    <!-- Table signalétique -->
    <table class="w-full text-xs mb-4 border-collapse border border-stone-300">
        <tbody>
            <tr>
                <td class="border border-stone-300 bg-stone-50 px-2.5 py-1.5 font-semibold text-stone-700 w-1/4">Date & heure</td>
                <td class="border border-stone-300 px-2.5 py-1.5 w-1/4">{{ $record->date?->format('d/m/Y à H:i') }}</td>
                <td class="border border-stone-300 bg-stone-50 px-2.5 py-1.5 font-semibold text-stone-700 w-1/4">Durée</td>
                <td class="border border-stone-300 px-2.5 py-1.5 w-1/4">{{ $record->duration }} minutes</td>
            </tr>
            <tr>
                <td class="border border-stone-300 bg-stone-50 px-2.5 py-1.5 font-semibold text-stone-700">Coach intervenant</td>
                <td class="border border-stone-300 px-2.5 py-1.5 font-medium">{{ $record->coach?->name ?? '—' }}</td>
                <td class="border border-stone-300 bg-stone-50 px-2.5 py-1.5 font-semibold text-stone-700">Projet concerné</td>
                <td class="border border-stone-300 px-2.5 py-1.5 font-medium">{{ $record->project?->name ?? '—' }}</td>
            </tr>
            <tr>
                <td class="border border-stone-300 bg-stone-50 px-2.5 py-1.5 font-semibold text-stone-700">Entrepreneur</td>
                <td class="border border-stone-300 px-2.5 py-1.5">{{ $record->entrepreneur?->full_name ?? ($record->project?->entrepreneur?->full_name ?? '—') }}</td>
                <td class="border border-stone-300 bg-stone-50 px-2.5 py-1.5 font-semibold text-stone-700">Contact coach</td>
                <td class="border border-stone-300 px-2.5 py-1.5">{{ $record->coach?->email ?? '—' }}</td>
            </tr>
        </tbody>
    </table>

    <!-- Objectifs -->
    <div class="mb-4">
        <h3 class="text-xs font-bold uppercase tracking-wider text-stone-800 mb-1 border-b border-stone-200 pb-1">
            1. Objectifs assignés à la séance
        </h3>
        <div class="p-2.5 rounded bg-stone-50 border border-stone-200 text-stone-700 min-h-[48px] whitespace-pre-line">
            {{ $record->objectives ?: 'Objectifs non spécifiés à l\'avance.' }}
        </div>
    </div>

    <!-- Compte-rendu -->
    <div class="mb-4">
        <h3 class="text-xs font-bold uppercase tracking-wider text-stone-800 mb-1 border-b border-stone-200 pb-1">
            2. Compte-rendu pédagogique & Échanges
        </h3>
        <div class="p-2.5 rounded bg-stone-50 border border-stone-200 text-stone-700 min-h-[80px] whitespace-pre-line">
            {{ $record->report ?: 'Compte-rendu en attente de rédaction par le coach.' }}
        </div>
    </div>

    <!-- Prochaines actions -->
    <div class="mb-4">
        <h3 class="text-xs font-bold uppercase tracking-wider text-stone-800 mb-1 border-b border-stone-200 pb-1">
            3. Prochaines actions convenues (Roadmap)
        </h3>
        <div class="p-2.5 rounded bg-stone-50 border border-stone-200 text-stone-700 min-h-[48px] whitespace-pre-line">
            {{ $record->next_actions ?: 'Aucune action immédiate renseignée.' }}
        </div>
    </div>

    <!-- Signatures -->
    <div class="grid grid-cols-2 gap-8 text-[11px] text-stone-600 mt-8 pt-4 border-t border-stone-200">
        <div>
            <p class="font-semibold mb-8">Le Coach</p>
            <p class="border-t border-stone-300 pt-1 text-[10px] text-stone-400">{{ $record->coach?->name ?? 'Signature' }}</p>
        </div>
        <div>
            <p class="font-semibold mb-8">L'Entrepreneur</p>
            <p class="border-t border-stone-300 pt-1 text-[10px] text-stone-400">{{ $record->entrepreneur?->full_name ?? ($record->project?->entrepreneur?->full_name ?? 'Signature') }}</p>
        </div>
    </div>
</div>
