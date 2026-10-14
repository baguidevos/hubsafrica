<x-emails.layouts.hubafrica :sae="$sae" badgeText="Accès Responsable SAE">
    <h2 style="margin: 0 0 16px 0; font-size: 20px; font-weight: 800; color: #0f172a;">
        Bienvenue {{ $user->name }} ! 🏛️
    </h2>

    <p style="margin: 0 0 16px 0; color: #334155; font-size: 15px;">
        Votre accès en tant que <strong>Responsable / Gestionnaire de la structure {{ $sae?->name ?? 'SAE' }}</strong> a été configuré avec succès sur la plateforme Hub Africa.
    </p>

    <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; margin: 24px 0;">
        <p style="margin: 0 0 12px 0; font-size: 14px; font-weight: 700; color: #0f172a;">
            Vos prérogatives de gestion :
        </p>
        <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
            <tr>
                <td style="padding-bottom: 8px; font-size: 14px; color: #475569;">
                    &bull; <strong>Programmes &amp; Cohortes :</strong> Créez des appels à candidatures et orchestrez vos promotions.
                </td>
            </tr>
            <tr>
                <td style="padding-bottom: 8px; font-size: 14px; color: #475569;">
                    &bull; <strong>Comité de sélection :</strong> Évaluez les candidatures, attribuez des scores et validez les admissions.
                </td>
            </tr>
            <tr>
                <td style="padding-bottom: 8px; font-size: 14px; color: #475569;">
                    &bull; <strong>Gestion des coachs :</strong> Invitez des mentors, validez les candidatures et affectez-les aux startups.
                </td>
            </tr>
            <tr>
                <td style="padding-bottom: 8px; font-size: 14px; color: #475569;">
                    &bull; <strong>Rapports d'impact :</strong> Générez vos bilans d'activité et indicateurs de performance en 1 clic.
                </td>
            </tr>
        </table>
    </div>

    <!-- Identifiants -->
    <div style="background-color: #f1f5f9; padding: 14px 18px; border-radius: 8px; margin: 20px 0;">
        <span style="font-size: 13px; color: #64748b;">Identifiant de connexion :</span>
        <div style="font-size: 15px; font-weight: 700; color: #0f172a; margin-top: 2px;">{{ $user->email }}</div>
    </div>

    <!-- Bouton CTA -->
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" style="margin: 28px 0;">
        <tr>
            <td align="center" style="border-radius: 12px; background: #2F3284;">
                <a href="{{ $saeUrl }}" target="_blank" style="font-size: 15px; font-weight: 700; color: #ffffff; text-decoration: none; padding: 14px 28px; border-radius: 12px; display: inline-block; background-color: #2F3284; border: 1px solid #2F3284;">
                    Accéder au Tableau de Bord SAE &rarr;
                </a>
            </td>
        </tr>
    </table>
</x-emails.layouts.hubafrica>
