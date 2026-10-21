<div class="max-w-[780px] mx-auto bg-white text-stone-900 p-6 rounded-lg text-xs leading-relaxed" id="project-printable-sheet">
    <div class="flex items-start justify-between border-b-2 border-stone-800 pb-3 mb-4">
        <div>
            <h1 class="text-xl font-bold tracking-tight text-stone-900">Fiche projet &mdash; SIGAE</h1>
            <p class="text-xs text-stone-600">Dossier d'accompagnement entrepreneurial &bull; {{ $record->name }}</p>
        </div>
        <div class="text-right text-[11px] text-stone-500">
            <p class="font-bold text-stone-800">{{ $record->sae?->name ?? 'Hubs Africa' }}</p>
            <p>Édité le {{ now()->translatedFormat('d F Y à H:i') }}</p>
        </div>
    </div>

    <!-- Synthèse -->
    <div class="mb-4">
        <h2 class="text-base font-bold text-stone-900">{{ $record->name }}</h2>
        <p class="text-[11px] text-stone-500 mt-0.5">
            Statut : <b class="text-teal-700">{{ is_object($record->status) && method_exists($record->status, 'getLabel') ? $record->status->getLabel() : $record->status }}</b> &bull;
            Niveau actuel : <b>{{ $record->currentLevel?->name ?? 'Sans niveau' }}</b> &bull;
            Porteur : <b>{{ $record->entrepreneur?->full_name ?? '—' }}</b> &bull;
            Créé le {{ $record->created_at?->format('d/m/Y') }}
        </p>
    </div>

    <!-- Table signalétique -->
    <table class="w-full text-xs mb-4 border-collapse border border-stone-300">
        <tbody>
            <tr>
                <td class="border border-stone-300 bg-stone-50 px-2.5 py-1.5 font-semibold text-stone-700 w-1/4">Secteur</td>
                <td class="border border-stone-300 px-2.5 py-1.5 w-1/4">{{ $record->sector ?? '—' }}</td>
                <td class="border border-stone-300 bg-stone-50 px-2.5 py-1.5 font-semibold text-stone-700 w-1/4">Territoire</td>
                <td class="border border-stone-300 px-2.5 py-1.5 w-1/4">{{ $record->territory ?? '—' }}</td>
            </tr>
            <tr>
                <td class="border border-stone-300 bg-stone-50 px-2.5 py-1.5 font-semibold text-stone-700">Taille de l'équipe</td>
                <td class="border border-stone-300 px-2.5 py-1.5">{{ $record->team_size ?? 1 }} personne(s)</td>
                <td class="border border-stone-300 bg-stone-50 px-2.5 py-1.5 font-semibold text-stone-700">Coach(s) référent(s)</td>
                <td class="border border-stone-300 px-2.5 py-1.5">
                    {{ $record->assignments->pluck('coach.name')->filter()->join(', ') ?: 'Aucun' }}
                </td>
            </tr>
            <tr>
                <td class="border border-stone-300 bg-stone-50 px-2.5 py-1.5 font-semibold text-stone-700">Email porteur</td>
                <td class="border border-stone-300 px-2.5 py-1.5">{{ $record->entrepreneur?->email ?? '—' }}</td>
                <td class="border border-stone-300 bg-stone-50 px-2.5 py-1.5 font-semibold text-stone-700">Téléphone porteur</td>
                <td class="border border-stone-300 px-2.5 py-1.5">{{ $record->entrepreneur?->phone ?? '—' }}</td>
            </tr>
        </tbody>
    </table>

    <!-- Fiche descriptive du modèle -->
    <h3 class="text-xs font-bold uppercase tracking-wider text-stone-800 mb-2 border-b border-stone-200 pb-1">
        Descriptif du modèle & proposition de valeur
    </h3>
    <table class="w-full text-xs mb-4 border-collapse border border-stone-300">
        <tbody>
            @if($record->pitch)
            <tr>
                <td class="border border-stone-300 bg-stone-50 px-2.5 py-1.5 font-semibold text-stone-700 w-1/4 align-top">Pitch</td>
                <td class="border border-stone-300 px-2.5 py-1.5 whitespace-pre-line">{{ $record->pitch }}</td>
            </tr>
            @endif
            @if($record->problem)
            <tr>
                <td class="border border-stone-300 bg-stone-50 px-2.5 py-1.5 font-semibold text-stone-700 w-1/4 align-top">Problème adressé</td>
                <td class="border border-stone-300 px-2.5 py-1.5 whitespace-pre-line">{{ $record->problem }}</td>
            </tr>
            @endif
            @if($record->solution)
            <tr>
                <td class="border border-stone-300 bg-stone-50 px-2.5 py-1.5 font-semibold text-stone-700 w-1/4 align-top">Solution proposée</td>
                <td class="border border-stone-300 px-2.5 py-1.5 whitespace-pre-line">{{ $record->solution }}</td>
            </tr>
            @endif
            @if($record->product_service)
            <tr>
                <td class="border border-stone-300 bg-stone-50 px-2.5 py-1.5 font-semibold text-stone-700 w-1/4 align-top">Produit / Service</td>
                <td class="border border-stone-300 px-2.5 py-1.5 whitespace-pre-line">{{ $record->product_service }}</td>
            </tr>
            @endif
            @if($record->value_proposition)
            <tr>
                <td class="border border-stone-300 bg-stone-50 px-2.5 py-1.5 font-semibold text-stone-700 w-1/4 align-top">Proposition de valeur</td>
                <td class="border border-stone-300 px-2.5 py-1.5 whitespace-pre-line">{{ $record->value_proposition }}</td>
            </tr>
            @endif
            @if($record->target)
            <tr>
                <td class="border border-stone-300 bg-stone-50 px-2.5 py-1.5 font-semibold text-stone-700 w-1/4 align-top">Cible clientèle</td>
                <td class="border border-stone-300 px-2.5 py-1.5 whitespace-pre-line">{{ $record->target }}</td>
            </tr>
            @endif
            @if($record->market)
            <tr>
                <td class="border border-stone-300 bg-stone-50 px-2.5 py-1.5 font-semibold text-stone-700 w-1/4 align-top">Marché visé</td>
                <td class="border border-stone-300 px-2.5 py-1.5 whitespace-pre-line">{{ $record->market }}</td>
            </tr>
            @endif
        </tbody>
    </table>

    <!-- Synthèse financière -->
    @php
        $recherche = (float) $record->funding->sum('amount_sought');
        $obtenu = (float) $record->funding->sum('amount_obtained');
        $investi = (float) $record->funding->sum('amount_invested');
    @endphp
    <div class="grid grid-cols-3 gap-3 mb-4">
        <div class="border border-stone-300 p-2.5 rounded bg-stone-50 text-center">
            <span class="block text-[10px] font-bold uppercase tracking-wider text-stone-500">Recherché</span>
            <span class="text-xs font-bold text-stone-800">{{ number_format($recherche, 0, ',', ' ') }} FCFA</span>
        </div>
        <div class="border border-stone-300 p-2.5 rounded bg-emerald-50 text-center">
            <span class="block text-[10px] font-bold uppercase tracking-wider text-emerald-700">Obtenu</span>
            <span class="text-xs font-bold text-emerald-800">{{ number_format($obtenu, 0, ',', ' ') }} FCFA</span>
        </div>
        <div class="border border-stone-300 p-2.5 rounded bg-teal-50 text-center">
            <span class="block text-[10px] font-bold uppercase tracking-wider text-teal-700">Investi</span>
            <span class="text-xs font-bold text-teal-800">{{ number_format($investi, 0, ',', ' ') }} FCFA</span>
        </div>
    </div>

    <!-- Signatures -->
    <div class="grid grid-cols-2 gap-8 text-[11px] text-stone-600 mt-8 pt-4 border-t border-stone-200">
        <div>
            <p class="font-semibold mb-8">Visa du Coach / Responsable SAE</p>
            <p class="border-t border-stone-300 pt-1 text-[10px] text-stone-400">Signature & date</p>
        </div>
        <div>
            <p class="font-semibold mb-8">Visa de l'Entrepreneur porteur</p>
            <p class="border-t border-stone-300 pt-1 text-[10px] text-stone-400">Signature & date</p>
        </div>
    </div>

    <div class="mt-4 pt-2 border-t border-stone-100 text-center text-[10px] text-stone-400 print:block">
        Système Intégré de Gestion de l'Accompagnement Entrepreneurial (SIGAE) &bull; Hubs Africa &bull; Confidentiel
    </div>
</div>
