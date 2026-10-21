<x-emails.layouts.hubsafrica :sae="$sae" badgeText="Candidature Coach">
    <h2 style="margin: 0 0 16px 0; font-size: 20px; font-weight: 800; color: #0f172a;">
        Nouveau profil Coach / Mentor candidat 🎯
    </h2>

    <p style="margin: 0 0 16px 0; color: #334155; font-size: 15px;">
        Un professionnel expert souhaite rejoindre votre structure d'accompagnement <strong>{{ $sae?->name }}</strong> en tant que Coach / Mentor.
    </p>

    <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; padding: 20px; border-radius: 12px; margin: 20px 0;">
        <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
            <tr>
                <td style="padding-bottom: 10px; font-size: 14px; color: #64748b; width: 140px;"><strong>Nom complet :</strong></td>
                <td style="padding-bottom: 10px; font-size: 14px; color: #0f172a; font-weight: 600;">{{ $joinRequest->name }}</td>
            </tr>
            <tr>
                <td style="padding-bottom: 10px; font-size: 14px; color: #64748b;"><strong>Email :</strong></td>
                <td style="padding-bottom: 10px; font-size: 14px; color: #0f172a;">{{ $joinRequest->email }}</td>
            </tr>
            @if($joinRequest->phone)
            <tr>
                <td style="padding-bottom: 10px; font-size: 14px; color: #64748b;"><strong>Téléphone :</strong></td>
                <td style="padding-bottom: 10px; font-size: 14px; color: #0f172a;">{{ $joinRequest->phone }}</td>
            </tr>
            @endif
            @if($joinRequest->linkedin_url)
            <tr>
                <td style="padding-bottom: 10px; font-size: 14px; color: #64748b;"><strong>Profil LinkedIn :</strong></td>
                <td style="padding-bottom: 10px; font-size: 14px; color: #2F3284;">
                    <a href="{{ $joinRequest->linkedin_url }}" target="_blank" style="color: #2F3284; text-decoration: underline;">Consulter le profil</a>
                </td>
            </tr>
            @endif
            @if(!empty($joinRequest->specialties))
            <tr>
                <td style="padding-bottom: 10px; font-size: 14px; color: #64748b; vertical-align: top;"><strong>Expertises :</strong></td>
                <td style="padding-bottom: 10px; font-size: 14px; color: #0f172a;">
                    {{ is_array($joinRequest->specialties) ? implode(', ', $joinRequest->specialties) : $joinRequest->specialties }}
                </td>
            </tr>
            @endif
            @if($joinRequest->bio)
            <tr>
                <td style="padding-bottom: 10px; font-size: 14px; color: #64748b; vertical-align: top;"><strong>Présentation :</strong></td>
                <td style="padding-bottom: 10px; font-size: 14px; color: #334155; font-style: italic;">
                    "{{ $joinRequest->bio }}"
                </td>
            </tr>
            @endif
        </table>
    </div>

    <p style="margin: 20px 0 24px 0; color: #334155; font-size: 15px;">
        Vous pouvez examiner cette demande et valider l'invitation directement depuis votre espace d'administration :
    </p>

    <!-- Bouton CTA -->
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" style="margin: 24px 0;">
        <tr>
            <td align="center" style="border-radius: 12px; background: #2F3284;">
                <a href="{{ url('/sae/' . ($sae?->slug ?? 'default') . '/coach-join-requests') }}" target="_blank" style="font-size: 15px; font-weight: 700; color: #ffffff; text-decoration: none; padding: 14px 28px; border-radius: 12px; display: inline-block; background-color: #2F3284; border: 1px solid #2F3284;">
                    Gérer les candidatures Coachs &rarr;
                </a>
            </td>
        </tr>
    </table>
</x-emails.layouts.hubsafrica>
