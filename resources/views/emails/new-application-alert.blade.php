<x-emails.layouts.hubafrica :sae="$sae" badgeText="Nouvelle Candidature">
    <h2 style="margin: 0 0 16px 0; font-size: 20px; font-weight: 800; color: #0f172a;">
        Nouveau dossier de candidature soumis 📥
    </h2>

    <p style="margin: 0 0 16px 0; color: #334155; font-size: 15px;">
        Un entrepreneur vient de déposer un dossier complet pour le programme <strong>{{ $program?->name }}</strong> au sein de <strong>{{ $sae?->name }}</strong>.
    </p>

    <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; margin: 20px 0;">
        <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
            <tr>
                <td style="padding-bottom: 8px; font-size: 14px; color: #64748b; width: 140px;"><strong>Start-up / Projet :</strong></td>
                <td style="padding-bottom: 8px; font-size: 14px; color: #0f172a; font-weight: 700;">{{ $project?->name ?? 'Projet non spécifié' }}</td>
            </tr>
            <tr>
                <td style="padding-bottom: 8px; font-size: 14px; color: #64748b;"><strong>Fondateur :</strong></td>
                <td style="padding-bottom: 8px; font-size: 14px; color: #0f172a;">{{ $entrepreneur?->full_name }} ({{ $entrepreneur?->email }})</td>
            </tr>
            @if($cohort)
            <tr>
                <td style="padding-bottom: 8px; font-size: 14px; color: #64748b;"><strong>Promotion visée :</strong></td>
                <td style="padding-bottom: 8px; font-size: 14px; color: #0f172a;">{{ $cohort->name }}</td>
            </tr>
            @endif
            <tr>
                <td style="padding-bottom: 8px; font-size: 14px; color: #64748b;"><strong>Secteur :</strong></td>
                <td style="padding-bottom: 8px; font-size: 14px; color: #0f172a;">{{ $project?->sector ?? 'Innovation' }}</td>
            </tr>
            <tr>
                <td style="padding-bottom: 4px; font-size: 14px; color: #64748b;"><strong>Déposé le :</strong></td>
                <td style="padding-bottom: 4px; font-size: 14px; color: #0f172a;">{{ $application->submitted_at?->format('d/m/Y à H:i') ?? now()->format('d/m/Y à H:i') }}</td>
            </tr>
        </table>
    </div>

    <!-- Bouton CTA -->
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" style="margin: 28px 0;">
        <tr>
            <td align="center" style="border-radius: 12px; background: #2F3284;">
                <a href="{{ $reviewUrl }}" target="_blank" style="font-size: 15px; font-weight: 700; color: #ffffff; text-decoration: none; padding: 14px 28px; border-radius: 12px; display: inline-block; background-color: #2F3284; border: 1px solid #2F3284;">
                    Examiner le dossier dans l'administration &rarr;
                </a>
            </td>
        </tr>
    </table>
</x-emails.layouts.hubafrica>
