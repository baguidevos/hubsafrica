<x-emails.layouts.hubafrica :sae="$sae" badgeText="Nouveau Projet Assigné">
    <h2 style="margin: 0 0 16px 0; font-size: 20px; font-weight: 800; color: #0f172a;">
        Bonjour {{ $coach?->name }},
    </h2>

    <p style="margin: 0 0 16px 0; color: #334155; font-size: 15px;">
        La coordination pédagogique de <strong>{{ $sae?->name ?? 'Hub Africa' }}</strong> vient de vous confier l'accompagnement d'un nouveau projet au sein de votre portefeuille.
    </p>

    <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; margin: 20px 0;">
        <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
            <tr>
                <td style="padding-bottom: 8px; font-size: 14px; color: #64748b; width: 140px;"><strong>Start-up :</strong></td>
                <td style="padding-bottom: 8px; font-size: 15px; color: #0f172a; font-weight: 700;">{{ $project?->name }}</td>
            </tr>
            <tr>
                <td style="padding-bottom: 8px; font-size: 14px; color: #64748b;"><strong>Fondateur :</strong></td>
                <td style="padding-bottom: 8px; font-size: 14px; color: #0f172a;">{{ $entrepreneur?->full_name }}</td>
            </tr>
            @if($entrepreneur?->email)
            <tr>
                <td style="padding-bottom: 8px; font-size: 14px; color: #64748b;"><strong>Email du porteur :</strong></td>
                <td style="padding-bottom: 8px; font-size: 14px; color: #2F3284;">
                    <a href="mailto:{{ $entrepreneur->email }}" style="color: #2F3284; text-decoration: underline;">{{ $entrepreneur->email }}</a>
                </td>
            </tr>
            @endif
            @if($entrepreneur?->phone)
            <tr>
                <td style="padding-bottom: 8px; font-size: 14px; color: #64748b;"><strong>Téléphone :</strong></td>
                <td style="padding-bottom: 8px; font-size: 14px; color: #0f172a;">{{ $entrepreneur->phone }}</td>
            </tr>
            @endif
            @if($project?->sector)
            <tr>
                <td style="padding-bottom: 8px; font-size: 14px; color: #64748b;"><strong>Secteur :</strong></td>
                <td style="padding-bottom: 8px; font-size: 14px; color: #0f172a;">{{ $project->sector }}</td>
            </tr>
            @endif
            @if($assignment->cohort)
            <tr>
                <td style="padding-bottom: 8px; font-size: 14px; color: #64748b;"><strong>Cohorte :</strong></td>
                <td style="padding-bottom: 8px; font-size: 14px; color: #0f172a;">{{ $assignment->cohort->name }}</td>
            </tr>
            @endif
        </table>
    </div>

    <p style="margin: 0 0 16px 0; color: #334155; font-size: 15px;">
        Vous pouvez dès à présent consulter la fiche du projet, examiner ses documents et planifier votre première session de cadrage :
    </p>

    <!-- Bouton CTA -->
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" style="margin: 28px 0;">
        <tr>
            <td align="center" style="border-radius: 12px; background: #2F3284;">
                <a href="{{ $coachUrl }}" target="_blank" style="font-size: 15px; font-weight: 700; color: #ffffff; text-decoration: none; padding: 14px 28px; border-radius: 12px; display: inline-block; background-color: #2F3284; border: 1px solid #2F3284;">
                    Ouvrir la fiche de la startup &rarr;
                </a>
            </td>
        </tr>
    </table>
</x-emails.layouts.hubafrica>
