<x-emails.layouts.hubafrica :sae="$sae" badgeText="Admission Confirmée">
    <h2 style="margin: 0 0 16px 0; font-size: 22px; font-weight: 800; color: #0f172a;">
        Excellente nouvelle {{ $entrepreneur?->first_name ?? '' }} ! 🎉
    </h2>

    <p style="margin: 0 0 16px 0; color: #334155; font-size: 15px;">
        Le comité de sélection de <strong>{{ $sae?->name ?? 'Hub Africa' }}</strong> a le plaisir de vous annoncer que votre projet <strong>"{{ $project?->name }}"</strong> a été <strong>officiellement retenu pour admission</strong> !
    </p>

    <div style="background-color: #f8fafc; border-left: 4px solid #F8B81D; padding: 20px; border-radius: 8px; margin: 24px 0;">
        <p style="margin: 0 0 12px 0; font-size: 15px; font-weight: 700; color: #0f172a;">
            Détails de votre accompagnement :
        </p>
        <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
            <tr>
                <td style="padding-bottom: 8px; font-size: 14px; color: #64748b; width: 140px;"><strong>Programme :</strong></td>
                <td style="padding-bottom: 8px; font-size: 14px; color: #0f172a; font-weight: 700;">{{ $program?->name }}</td>
            </tr>
            @if($cohort)
            <tr>
                <td style="padding-bottom: 8px; font-size: 14px; color: #64748b;"><strong>Promotion / Cohorte :</strong></td>
                <td style="padding-bottom: 8px; font-size: 14px; color: #0f172a; font-weight: 600;">{{ $cohort->name }}</td>
            </tr>
            @endif
            @if($sae)
            <tr>
                <td style="padding-bottom: 8px; font-size: 14px; color: #64748b;"><strong>Structure Hôte :</strong></td>
                <td style="padding-bottom: 8px; font-size: 14px; color: #0f172a;">{{ $sae->name }}</td>
            </tr>
            @endif
            @if($application->score)
            <tr>
                <td style="padding-bottom: 8px; font-size: 14px; color: #64748b;"><strong>Score d'évaluation :</strong></td>
                <td style="padding-bottom: 8px; font-size: 14px; color: #2F3284; font-weight: 700;">{{ $application->score }}/100</td>
            </tr>
            @endif
        </table>
    </div>

    @if($application->comment)
    <div style="background-color: #f1f5f9; padding: 16px; border-radius: 8px; margin: 20px 0; font-size: 14px; color: #475569;">
        <strong style="color: #0f172a;">Avis du jury :</strong><br>
        <em>"{{ $application->comment }}"</em>
    </div>
    @endif

    <p style="margin: 0 0 16px 0; color: #334155; font-size: 15px;">
        <strong>Prochaines étapes pour démarrer :</strong><br>
        Votre projet a été automatiquement enrôlé dans le cursus d'accompagnement. Vous allez prochainement recevoir l'assignation de votre mentor référent et les dates de vos premières séances d'immersion.
    </p>

    <!-- Bouton CTA -->
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" style="margin: 28px 0;">
        <tr>
            <td align="center" style="border-radius: 12px; background: #2F3284;">
                <a href="{{ $portalUrl }}" target="_blank" style="font-size: 15px; font-weight: 700; color: #ffffff; text-decoration: none; padding: 14px 28px; border-radius: 12px; display: inline-block; background-color: #2F3284; border: 1px solid #2F3284;">
                    Rejoindre mon espace &amp; Démarrer l'accompagnement &rarr;
                </a>
            </td>
        </tr>
    </table>
</x-emails.layouts.hubafrica>
