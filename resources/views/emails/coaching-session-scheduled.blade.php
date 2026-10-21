<x-emails.layouts.hubsafrica :sae="$sae" badgeText="Séance Planifiée">
    <h2 style="margin: 0 0 16px 0; font-size: 20px; font-weight: 800; color: #0f172a;">
        Séance d'accompagnement programmée 📅
    </h2>

    <p style="margin: 0 0 16px 0; color: #334155; font-size: 15px;">
        @if($recipientRole === 'COACH')
            Bonjour {{ $coach?->name }}, votre session de mentorat avec le projet <strong>"{{ $project?->name }}"</strong> a été planifiée au calendrier.
        @else
            Bonjour {{ $entrepreneur?->first_name ?? '' }}, une nouvelle séance d'accompagnement a été fixée avec votre coach <strong>{{ $coach?->name }}</strong>.
        @endif
    </p>

    <div style="background-color: #f8fafc; border-left: 4px solid #2F3284; padding: 20px; border-radius: 8px; margin: 24px 0;">
        <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
            <tr>
                <td style="padding-bottom: 8px; font-size: 14px; color: #64748b; width: 140px;"><strong>Date &amp; Heure :</strong></td>
                <td style="padding-bottom: 8px; font-size: 15px; color: #0f172a; font-weight: 700;">
                    {{ $session->date ? $session->date->format('d/m/Y à H:i') : 'À confirmer' }}
                </td>
            </tr>
            @if($session->duration)
            <tr>
                <td style="padding-bottom: 8px; font-size: 14px; color: #64748b;"><strong>Durée prévue :</strong></td>
                <td style="padding-bottom: 8px; font-size: 14px; color: #0f172a;">{{ $session->duration }} minutes</td>
            </tr>
            @endif
            @if($session->theme)
            <tr>
                <td style="padding-bottom: 8px; font-size: 14px; color: #64748b;"><strong>Thème / Sujet :</strong></td>
                <td style="padding-bottom: 8px; font-size: 14px; color: #2F3284; font-weight: 600;">{{ $session->theme }}</td>
            </tr>
            @endif
            @if($session->type)
            <tr>
                <td style="padding-bottom: 8px; font-size: 14px; color: #64748b;"><strong>Format :</strong></td>
                <td style="padding-bottom: 8px; font-size: 14px; color: #0f172a;">{{ is_object($session->type) && method_exists($session->type, 'getLabel') ? $session->type->getLabel() : $session->type }}</td>
            </tr>
            @endif
            @if($session->objectives)
            <tr>
                <td style="padding-bottom: 8px; font-size: 14px; color: #64748b; vertical-align: top;"><strong>Objectifs visés :</strong></td>
                <td style="padding-bottom: 8px; font-size: 14px; color: #334155;">{{ $session->objectives }}</td>
            </tr>
            @endif
        </table>
    </div>

    <p style="margin: 0 0 16px 0; color: #334155; font-size: 15px;">
        Pensez à préparer vos questions et à actualiser vos livrables méthodologiques en amont de cet échange.
    </p>

    <!-- Bouton CTA -->
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" style="margin: 28px 0;">
        <tr>
            <td align="center" style="border-radius: 12px; background: #2F3284;">
                <a href="{{ $targetUrl }}" target="_blank" style="font-size: 15px; font-weight: 700; color: #ffffff; text-decoration: none; padding: 14px 28px; border-radius: 12px; display: inline-block; background-color: #2F3284; border: 1px solid #2F3284;">
                    Consulter la séance sur la plateforme &rarr;
                </a>
            </td>
        </tr>
    </table>
</x-emails.layouts.hubsafrica>
