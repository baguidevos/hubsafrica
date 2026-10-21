<x-emails.layouts.hubsafrica badgeText="Espace Entrepreneur">
    <h2 style="margin: 0 0 16px 0; font-size: 20px; font-weight: 800; color: #0f172a;">
        Bienvenue dans la communauté Hubs Africa, {{ $user->name }} ! 🚀
    </h2>

    <p style="margin: 0 0 16px 0; color: #334155; font-size: 15px;">
        Votre compte entrepreneur a été créé avec succès sur <strong>Hubs Africa</strong>, la plateforme panafricaine dédiée à la croissance et à la structuration des pépites technologiques et innovantes du continent.
    </p>

    <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; margin: 24px 0;">
        <p style="margin: 0 0 12px 0; font-size: 14px; font-weight: 700; color: #0f172a;">
            Ce que vous pouvez accomplir depuis votre espace :
        </p>
        <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
            <tr>
                <td style="padding-bottom: 8px; font-size: 14px; color: #475569;">
                    &bull; <strong>Dossiers de candidature :</strong> Soumettez vos projets aux appels à projets des meilleures structures d'accompagnement (SAEs).
                </td>
            </tr>
            <tr>
                <td style="padding-bottom: 8px; font-size: 14px; color: #475569;">
                    &bull; <strong>Data Room Investisseurs :</strong> Déposez et organisez vos documents stratégiques (Business Plan, Pitch Deck, modèle financier).
                </td>
            </tr>
            <tr>
                <td style="padding-bottom: 8px; font-size: 14px; color: #475569;">
                    &bull; <strong>Suivi de coaching :</strong> Planifiez vos séances avec vos mentors et suivez vos livrables de progression.
                </td>
            </tr>
        </table>
    </div>

    <!-- Identifiants -->
    <div style="background-color: #f1f5f9; padding: 14px 18px; border-radius: 8px; margin: 20px 0;">
        <span style="font-size: 13px; color: #64748b;">Votre identifiant de connexion :</span>
        <div style="font-size: 15px; font-weight: 700; color: #0f172a; margin-top: 2px;">{{ $user->email }}</div>
    </div>

    <!-- Bouton CTA -->
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" style="margin: 28px 0;">
        <tr>
            <td align="center" style="border-radius: 12px; background: #2F3284;">
                <a href="{{ $portalUrl }}" target="_blank" style="font-size: 15px; font-weight: 700; color: #ffffff; text-decoration: none; padding: 14px 28px; border-radius: 12px; display: inline-block; background-color: #2F3284; border: 1px solid #2F3284;">
                    Accéder à mon Espace Entrepreneur &rarr;
                </a>
            </td>
        </tr>
    </table>
</x-emails.layouts.hubsafrica>
