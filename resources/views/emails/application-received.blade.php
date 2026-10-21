<x-emails.layouts.hubsafrica :sae="$sae" badgeText="Accusé de réception">
    <h2 style="margin: 0 0 16px 0; font-size: 20px; font-weight: 800; color: #0f172a;">
        Dossier de candidature bien reçu ! 📋
    </h2>

    <p style="margin: 0 0 16px 0; color: #334155; font-size: 15px;">
        Bonjour {{ $entrepreneur?->first_name ?? 'Cher entrepreneur' }}, votre candidature pour le projet <strong>"{{ $project?->name ?? 'Votre projet' }}"</strong> a été enregistrée avec succès.
    </p>

    <div style="background-color: #f8fafc; border-left: 4px solid #2F3284; padding: 18px; border-radius: 8px; margin: 24px 0;">
        <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
            <tr>
                <td style="padding-bottom: 8px; font-size: 14px; color: #64748b; width: 140px;"><strong>Programme :</strong></td>
                <td style="padding-bottom: 8px; font-size: 14px; color: #0f172a; font-weight: 700;">{{ $program?->name }}</td>
            </tr>
            @if($cohort)
            <tr>
                <td style="padding-bottom: 8px; font-size: 14px; color: #64748b;"><strong>Promotion / Cohorte :</strong></td>
                <td style="padding-bottom: 8px; font-size: 14px; color: #0f172a;">{{ $cohort->name }}</td>
            </tr>
            @endif
            @if($sae)
            <tr>
                <td style="padding-bottom: 8px; font-size: 14px; color: #64748b;"><strong>Structure d'accueil :</strong></td>
                <td style="padding-bottom: 8px; font-size: 14px; color: #0f172a;">{{ $sae->name }}</td>
            </tr>
            @endif
            <tr>
                <td style="padding-bottom: 8px; font-size: 14px; color: #64748b;"><strong>Numéro de dossier :</strong></td>
                <td style="padding-bottom: 8px; font-size: 14px; color: #2F3284; font-family: monospace; font-weight: 700;">#APP-{{ str_pad($application->id, 5, '0', STR_PAD_LEFT) }}</td>
            </tr>
            <tr>
                <td style="padding-bottom: 4px; font-size: 14px; color: #64748b;"><strong>Date de dépôt :</strong></td>
                <td style="padding-bottom: 4px; font-size: 14px; color: #0f172a;">{{ $application->submitted_at?->format('d/m/Y à H:i') ?? now()->format('d/m/Y à H:i') }}</td>
            </tr>
        </table>
    </div>

    <p style="margin: 0 0 16px 0; color: #334155; font-size: 15px;">
        <strong>Quelles sont les prochaines étapes ?</strong><br>
        Le comité de sélection procède à l'instruction de votre dossier. Vous serez notifié par email et sur votre espace dès que l'évaluation aura été effectuée.
    </p>

    <!-- Bouton CTA -->
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" style="margin: 28px 0;">
        <tr>
            <td align="center" style="border-radius: 12px; background: #2F3284;">
                <a href="{{ $portalUrl }}" target="_blank" style="font-size: 15px; font-weight: 700; color: #ffffff; text-decoration: none; padding: 14px 28px; border-radius: 12px; display: inline-block; background-color: #2F3284; border: 1px solid #2F3284;">
                    Suivre ma candidature sur le portail &rarr;
                </a>
            </td>
        </tr>
    </table>
</x-emails.layouts.hubsafrica>
