<x-emails.layouts.hubafrica :sae="$sae ?? null" badgeText="Sécurité">
    <h2 style="margin: 0 0 16px 0; font-size: 20px; font-weight: 800; color: #0f172a; letter-spacing: -0.3px;">
        Réinitialisation de votre mot de passe 🔐
    </h2>

    <p style="margin: 0 0 16px 0; color: #334155; font-size: 15px; line-height: 1.6;">
        Bonjour <strong>{{ $name }}</strong>,
    </p>

    <p style="margin: 0 0 20px 0; color: #334155; font-size: 15px; line-height: 1.6;">
        Nous avons bien reçu une demande de réinitialisation de mot de passe pour le compte associé à l'adresse 
        <strong style="color: #2F3284;">{{ $user->email }}</strong> sur la plateforme <strong>Hub Africa</strong>.
    </p>

    <!-- Bouton CTA Principal -->
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" style="margin: 28px 0 24px 0;">
        <tr>
            <td align="center" style="border-radius: 12px; background: linear-gradient(135deg, #F8B81D 0%, #e5a510 100%); background-color: #F8B81D; box-shadow: 0 4px 10px rgba(248, 184, 29, 0.35);">
                <a href="{{ $resetUrl }}" target="_blank" style="font-size: 15px; font-weight: 800; color: #0f172a; text-decoration: none; padding: 14px 32px; border-radius: 12px; display: inline-block; letter-spacing: 0.2px;">
                    Réinitialiser mon mot de passe &rarr;
                </a>
            </td>
        </tr>
    </table>

    <!-- Encadré d'information et durée de validité -->
    <div style="background-color: #f8fafc; border-left: 4px solid #F8B81D; border-top: 1px solid #e2e8f0; border-right: 1px solid #e2e8f0; border-bottom: 1px solid #e2e8f0; border-radius: 10px; padding: 16px 20px; margin: 24px 0;">
        <div style="font-size: 13px; font-weight: 700; color: #0f172a; margin-bottom: 4px;">
            ⏳ Durée de validité du lien : {{ $expireMinutes }} minutes
        </div>
        <div style="font-size: 13px; color: #64748b; line-height: 1.5;">
            Passé ce délai, ce lien de sécurité expirera automatiquement. Vous devrez alors soumettre une nouvelle demande depuis la page de connexion.
        </div>
    </div>

    <!-- Note de sécurité -->
    <div style="background-color: #f1f5f9; border-radius: 10px; padding: 14px 18px; margin: 20px 0;">
        <p style="margin: 0; font-size: 12px; color: #475569; line-height: 1.5;">
            🛡️ <strong>Vous n'êtes pas à l'origine de cette demande ?</strong><br>
            Aucune action supplémentaire n'est requise de votre part. Votre mot de passe actuel reste inchangé et votre compte demeure en parfaite sécurité.
        </p>
    </div>

    <!-- Fallback lien texte direct -->
    <p style="margin: 24px 0 8px 0; font-size: 12px; color: #64748b; line-height: 1.5;">
        Si le bouton ci-dessus ne fonctionne pas, copiez et collez l'adresse suivante directement dans la barre d'adresse de votre navigateur :
    </p>
    <div style="background-color: #ffffff; border: 1px dashed #cbd5e1; border-radius: 8px; padding: 10px 14px; font-family: monospace; font-size: 11px; word-break: break-all; color: #2F3284;">
        <a href="{{ $resetUrl }}" style="color: #2F3284; text-decoration: underline;">{{ $resetUrl }}</a>
    </div>
</x-emails.layouts.hubafrica>
