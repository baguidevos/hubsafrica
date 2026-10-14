<x-emails.layouts.hubafrica :sae="$sae" badgeText="Invitation Coach">
    <h2 style="margin: 0 0 16px 0; font-size: 20px; font-weight: 800; color: #0f172a;">
        Félicitations {{ $joinRequest->name }} ! 🎉
    </h2>

    <p style="margin: 0 0 16px 0; color: #334155; font-size: 15px;">
        Le responsable de la structure <strong>{{ $sae?->name ?? 'la SAE' }}</strong> a examiné avec attention votre candidature de mentor / coach expert et a le plaisir de vous accueillir au sein de son équipe d'accompagnement.
    </p>

    <div style="background-color: #f8fafc; border-left: 4px solid #F8B81D; padding: 16px; border-radius: 8px; margin: 20px 0;">
        <p style="margin: 0 0 6px 0; font-size: 14px; font-weight: 700; color: #0f172a;">
            Détails de votre intégration :
        </p>
        <ul style="margin: 0; padding-left: 20px; color: #475569; font-size: 14px;">
            <li><strong>Structure d'accueil :</strong> {{ $sae?->name }}</li>
            <li><strong>Email de connexion :</strong> {{ $joinRequest->email }}</li>
            <li><strong>Rôle :</strong> Coach Référent / Mentor Thématique</li>
            <li><strong>Validité du lien :</strong> 7 jours</li>
        </ul>
    </div>

    <p style="margin: 20px 0 24px 0; color: #334155; font-size: 15px;">
        Pour finaliser votre adhésion, définir votre mot de passe et accéder à votre espace mentor, cliquez sur le bouton ci-dessous :
    </p>

    <!-- Bouton CTA -->
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" style="margin: 24px 0;">
        <tr>
            <td align="center" style="border-radius: 12px; background: #2F3284;">
                <a href="{{ $invitationUrl }}" target="_blank" style="font-size: 15px; font-weight: 700; color: #ffffff; text-decoration: none; padding: 14px 28px; border-radius: 12px; display: inline-block; background-color: #2F3284; border: 1px solid #2F3284;">
                    Activer mon compte Coach &rarr;
                </a>
            </td>
        </tr>
    </table>

    <p style="margin: 24px 0 0 0; font-size: 13px; color: #64748b; line-height: 1.5;">
        Si le bouton ci-dessus ne fonctionne pas, copiez-collez l'adresse suivante dans votre navigateur :<br>
        <span style="word-break: break-all; color: #2F3284; font-family: monospace;">{{ $invitationUrl }}</span>
    </p>
</x-emails.layouts.hubafrica>
