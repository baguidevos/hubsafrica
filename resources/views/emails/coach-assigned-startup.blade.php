<x-emails.layouts.hubsafrica :sae="$sae" badgeText="Assignation Coach">
    <h2 style="margin: 0 0 16px 0; font-size: 20px; font-weight: 800; color: #0f172a;">
        Votre coach référent est prêt à vous accompagner ! 🤝
    </h2>

    <p style="margin: 0 0 16px 0; color: #334155; font-size: 15px;">
        Bonjour {{ $entrepreneur?->first_name ?? '' }}, l'équipe de coordination de <strong>{{ $sae?->name ?? 'Hubs Africa' }}</strong> a le plaisir de vous présenter le coach qui vous guidera tout au long de votre cycle d'accompagnement pour <strong>"{{ $project?->name }}"</strong>.
    </p>

    <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; margin: 20px 0;">
        <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
            <tr>
                <td style="padding-bottom: 8px; font-size: 14px; color: #64748b; width: 140px;"><strong>Nom du coach :</strong></td>
                <td style="padding-bottom: 8px; font-size: 15px; color: #0f172a; font-weight: 700;">{{ $coach?->name }}</td>
            </tr>
            @if($coach?->email)
            <tr>
                <td style="padding-bottom: 8px; font-size: 14px; color: #64748b;"><strong>Email de contact :</strong></td>
                <td style="padding-bottom: 8px; font-size: 14px; color: #2F3284;">
                    <a href="mailto:{{ $coach->email }}" style="color: #2F3284; text-decoration: underline;">{{ $coach->email }}</a>
                </td>
            </tr>
            @endif
            @if($coach?->phone)
            <tr>
                <td style="padding-bottom: 8px; font-size: 14px; color: #64748b;"><strong>Téléphone :</strong></td>
                <td style="padding-bottom: 8px; font-size: 14px; color: #0f172a;">{{ $coach->phone }}</td>
            </tr>
            @endif
            @if(!empty($coach?->specialties))
            <tr>
                <td style="padding-bottom: 8px; font-size: 14px; color: #64748b; vertical-align: top;"><strong>Expertises :</strong></td>
                <td style="padding-bottom: 8px; font-size: 14px; color: #0f172a;">
                    {{ is_array($coach->specialties) ? implode(', ', $coach->specialties) : $coach->specialties }}
                </td>
            </tr>
            @endif
        </table>
    </div>

    <p style="margin: 0 0 16px 0; color: #334155; font-size: 15px;">
        Votre mentor va prochainement prendre contact avec vous ou planifier votre première séance de cadrage méthodologique sur la plateforme.
    </p>

    <!-- Bouton CTA -->
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" style="margin: 28px 0;">
        <tr>
            <td align="center" style="border-radius: 12px; background: #2F3284;">
                <a href="{{ $portalUrl }}" target="_blank" style="font-size: 15px; font-weight: 700; color: #ffffff; text-decoration: none; padding: 14px 28px; border-radius: 12px; display: inline-block; background-color: #2F3284; border: 1px solid #2F3284;">
                    Accéder à mon espace &amp; Échanger &rarr;
                </a>
            </td>
        </tr>
    </table>
</x-emails.layouts.hubsafrica>
