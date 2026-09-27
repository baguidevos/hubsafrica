<div class="max-w-[780px] mx-auto bg-white text-stone-900 p-6 rounded-lg text-xs leading-relaxed" id="entrepreneur-printable-sheet">
    <div class="flex items-start justify-between border-b-2 border-stone-800 pb-3 mb-4">
        <div>
            <h1 class="text-xl font-bold tracking-tight text-stone-900">Fiche entrepreneur &mdash; SIGAE</h1>
            <p class="text-xs text-stone-600">Dossier individuel d'accompagnement &bull; {{ $record->full_name }}</p>
        </div>
        <div class="text-right text-[11px] text-stone-500">
            <p class="font-bold text-stone-800">{{ $record->sae?->name ?? 'Hub Africa' }}</p>
            <p>Édité le {{ now()->translatedFormat('d F Y à H:i') }}</p>
        </div>
    </div>

    <!-- Synthèse profil -->
    <div class="mb-4">
        <h2 class="text-base font-bold text-stone-900">{{ $record->full_name }}</h2>
        <p class="text-[11px] text-stone-500 mt-0.5">
            Statut du dossier : <b class="text-teal-700">{{ is_object($record->status) && method_exists($record->status, 'getLabel') ? $record->status->getLabel() : $record->status }}</b> &bull;
            Inscrit le {{ $record->created_at?->format('d/m/Y') }} &bull;
            Genre : <b>{{ is_object($record->gender) && method_exists($record->gender, 'getLabel') ? $record->gender->getLabel() : ($record->gender === 'F' ? 'Féminin' : ($record->gender === 'M' ? 'Masculin' : 'Autre')) }}</b>
        </p>
    </div>

    <!-- Table signalétique -->
    <table class="w-full text-xs mb-4 border-collapse border border-stone-300">
        <tbody>
            <tr>
                <td class="border border-stone-300 bg-stone-50 px-2.5 py-1.5 font-semibold text-stone-700 w-1/4">Email</td>
                <td class="border border-stone-300 px-2.5 py-1.5 w-1/4">{{ $record->email ?? '—' }}</td>
                <td class="border border-stone-300 bg-stone-50 px-2.5 py-1.5 font-semibold text-stone-700 w-1/4">Téléphone</td>
                <td class="border border-stone-300 px-2.5 py-1.5 w-1/4">{{ $record->phone ?? '—' }}</td>
            </tr>
            <tr>
                <td class="border border-stone-300 bg-stone-50 px-2.5 py-1.5 font-semibold text-stone-700">Date de naissance</td>
                <td class="border border-stone-300 px-2.5 py-1.5">{{ $record->birth_date ? $record->birth_date->format('d/m/Y') : '—' }}</td>
                <td class="border border-stone-300 bg-stone-50 px-2.5 py-1.5 font-semibold text-stone-700">Territoire / Région</td>
                <td class="border border-stone-300 px-2.5 py-1.5">{{ $record->territory ?? '—' }} ({{ $record->country ?? '—' }})</td>
            </tr>
            <tr>
                <td class="border border-stone-300 bg-stone-50 px-2.5 py-1.5 font-semibold text-stone-700">Niveau d'études</td>
                <td class="border border-stone-300 px-2.5 py-1.5">{{ $record->education_level ?? '—' }}</td>
                <td class="border border-stone-300 bg-stone-50 px-2.5 py-1.5 font-semibold text-stone-700">Profession antérieure</td>
                <td class="border border-stone-300 px-2.5 py-1.5">{{ $record->profession ?? '—' }}</td>
            </tr>
            <tr>
                <td class="border border-stone-300 bg-stone-50 px-2.5 py-1.5 font-semibold text-stone-700">Situation actuelle</td>
                <td class="border border-stone-300 px-2.5 py-1.5">{{ $record->professional_status ?? '—' }}</td>
                <td class="border border-stone-300 bg-stone-50 px-2.5 py-1.5 font-semibold text-stone-700">Statut juridique</td>
                <td class="border border-stone-300 px-2.5 py-1.5">{{ $record->legal_status ?? '—' }}</td>
            </tr>
        </tbody>
    </table>

    <!-- Projets portés -->
    <h3 class="text-xs font-bold uppercase tracking-wider text-stone-800 mb-2 border-b border-stone-200 pb-1">
        Projets portés au sein de la structure ({{ $record->projects->count() }})
    </h3>
    <table class="w-full text-xs mb-4 border-collapse border border-stone-300">
        <thead>
            <tr class="bg-stone-100 text-stone-700">
                <th class="border border-stone-300 px-2 py-1 text-left">Nom du projet</th>
                <th class="border border-stone-300 px-2 py-1 text-left">Secteur</th>
                <th class="border border-stone-300 px-2 py-1 text-left">Niveau</th>
                <th class="border border-stone-300 px-2 py-1 text-left">Statut</th>
            </tr>
        </thead>
        <tbody>
            @forelse($record->projects as $p)
                <tr>
                    <td class="border border-stone-300 px-2 py-1 font-semibold">{{ $p->name }}</td>
                    <td class="border border-stone-300 px-2 py-1">{{ $p->sector ?? '—' }}</td>
                    <td class="border border-stone-300 px-2 py-1">{{ $p->currentLevel?->name ?? 'Initial' }}</td>
                    <td class="border border-stone-300 px-2 py-1">{{ is_object($p->status) && method_exists($p->status, 'getLabel') ? $p->status->getLabel() : $p->status }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="border border-stone-300 px-2 py-2 text-center text-stone-400">Aucun projet rattaché</td></tr>
            @endforelse
        </tbody>
    </table>

    @if($record->notes)
        <div class="mb-4">
            <h3 class="text-xs font-bold uppercase tracking-wider text-stone-800 mb-1 border-b border-stone-200 pb-1">Notes de cadrage & observations</h3>
            <p class="border border-stone-200 bg-stone-50 p-2.5 rounded text-stone-700 whitespace-pre-line">{{ $record->notes }}</p>
        </div>
    @endif

    <!-- Signatures -->
    <div class="grid grid-cols-2 gap-8 text-[11px] text-stone-600 mt-8 pt-4 border-t border-stone-200">
        <div>
            <p class="font-semibold mb-8">Visa de la structure d'accompagnement</p>
            <p class="border-t border-stone-300 pt-1 text-[10px] text-stone-400">Signature & cachet</p>
        </div>
        <div>
            <p class="font-semibold mb-8">Visa de l'entrepreneur</p>
            <p class="border-t border-stone-300 pt-1 text-[10px] text-stone-400">Signature</p>
        </div>
    </div>
</div>
