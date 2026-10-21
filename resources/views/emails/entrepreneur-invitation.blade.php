<x-emails.layouts.hubsafrica :sae="$sae" badgeText="Invitation Entrepreneur">
    <h2 style="margin: 0 0 16px 0; font-size: 20px; font-weight: 800; color: #0f172a;">
        Bonjour {{ $invitation->first_name ?: 'Cher(e) Entrepreneur(e)' }},
    </h2>

    <p style="margin: 0 0 16px 0; color: #334155; font-size: 15px; line-height: 1.6;">
        La structure <strong>{{ $sae?->name ?? 'Hub Africa' }}</strong> vous invite à créer votre espace entrepreneur sur la plateforme <strong>Hub Africa</strong>.
    </p>

    @if ($cohort)
        <div style="background-color: #f0fdf4; border-left: 4px solid #16a34a; padding: 18px; border-radius: 8px; margin: 20px 0;">
            <p style="margin: 0 0 6px 0; font-size: 14px; font-weight: 700; color: #166534;">
                🎓 Rattachement à une promotion active :
            </p>
            <p style="margin: 0; font-size: 14px; color: #15803d; line-height: 1.5;">
                Vous êtes directement intégré(e) à la cohorte <strong>{{ $cohort->name }}</strong>
                @if ($cohort->program)
                    du programme <em>{{ $cohort->program->name }}</em>
                @endif
                dans le cadre de votre accompagnement.
            </p>
        </div>
    @endif

    @if ($invitation->message)
        <div style="background-color: #f8fafc; border-left: 4px solid #2F3284; padding: 18px; border-radius: 8px; margin: 20px 0;">
            <p style="margin: 0 0 8px 0; font-size: 13px; font-weight: 700; color: #0f172a; text-transform: uppercase; letter-spacing: 0.05em;">
                Message de l'équipe :
            </p>
            <p style="margin: 0; font-size: 14px; color: #475569; font-style: italic; line-height: 1.6;">
                « {{ $invitation->message }} »
            </p>
        </div>
    @endif

    <div style="background-color: #f8fafc; border-radius: 8px; padding: 16px; margin: 20px 0;">
        <p style="margin: 0 0 8px 0; font-size: 14px; font-weight: 700; color: #0f172a;">
            Sur Hub Africa, vous pourrez :
        </p>
        <ul style="margin: 0; padding-left: 20px; font-size: 14px; color: #475569; line-height: 1.6;">
            <li>Compléter votre fiche projet et vos informations d'activité</li>
            <li>Accéder à vos sessions de coaching et livrables d'accompagnement</li>
            <li>Suivre vos indicateurs clés de progression et structurer votre dataroom</li>
        </ul>
    </div>

    <p style="margin: 20px 0 24px 0; color: #334155; font-size: 15px;">
        Pour finaliser votre inscription et activer votre compte, cliquez sur le bouton sécurisé ci-dessous :
    </p>

    <!-- Bouton CTA -->
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" style="margin: 24px 0;">
        <tr>
            <td align="center" style="border-radius: 12px; background: #0d9488;">
                <a href="{{ $invitationUrl }}" target="_blank" style="font-size: 15px; font-weight: 700; color: #ffffff; text-decoration: none; padding: 14px 28px; border-radius: 12px; display: inline-block; background-color: #0d9488; border: 1px solid #0d9488;">
                    Activer mon compte Entrepreneur &rarr;
                </a>
            </td>
        </tr>
    </table>

    <p style="margin: 20px 0 0 0; font-size: 13px; color: #64748b;">
        Ce lien d'invitation sécurisé est valable pendant 14 jours.
    </p>
</x-emails.layouts.hubsafrica>
