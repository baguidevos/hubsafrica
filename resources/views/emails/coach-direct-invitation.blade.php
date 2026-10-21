<x-emails.layouts.hubsafrica :sae="$sae" badgeText="Invitation Mentor">
    <h2 style="margin: 0 0 16px 0; font-size: 20px; font-weight: 800; color: #0f172a;">
        Bonjour {{ $invitation->name ?: 'Cher Mentor' }},
    </h2>

    <p style="margin: 0 0 16px 0; color: #334155; font-size: 15px;">
        La structure <strong>{{ $sae?->name ?? 'Hubs Africa' }}</strong> vous invite à rejoindre son réseau d'experts pour accompagner ses cohortes de startups innovantes.
    </p>

    <div style="background-color: #f8fafc; border-left: 4px solid #2F3284; padding: 18px; border-radius: 8px; margin: 20px 0;">
        <p style="margin: 0 0 8px 0; font-size: 14px; font-weight: 700; color: #0f172a;">
            Votre mission :
        </p>
        <p style="margin: 0; font-size: 14px; color: #475569; line-height: 1.5;">
            Guider les entrepreneurs, animer des séances de coaching individuel ou collectif et évaluer les livrables jalons au sein d'un environnement numérique structuré.
        </p>
    </div>

    <p style="margin: 20px 0 24px 0; color: #334155; font-size: 15px;">
        Pour accepter cette invitation et configurer vos accès, cliquez sur le lien sécurisé ci-dessous :
    </p>

    <!-- Bouton CTA -->
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" style="margin: 24px 0;">
        <tr>
            <td align="center" style="border-radius: 12px; background: #2F3284;">
                <a href="{{ $invitationUrl }}" target="_blank" style="font-size: 15px; font-weight: 700; color: #ffffff; text-decoration: none; padding: 14px 28px; border-radius: 12px; display: inline-block; background-color: #2F3284; border: 1px solid #2F3284;">
                    Activer mon profil Coach &rarr;
                </a>
            </td>
        </tr>
    </table>

    <p style="margin: 20px 0 0 0; font-size: 13px; color: #64748b;">
        Ce lien d'invitation est valable pendant 7 jours.
    </p>
</x-emails.layouts.hubsafrica>
