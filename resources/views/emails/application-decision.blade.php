<x-emails.layouts.hubsafrica :sae="$sae" :badgeText="$isWaitlist ? 'Liste d\'attente' : 'Décision du jury'">
    <h2 style="margin: 0 0 16px 0; font-size: 20px; font-weight: 800; color: #0f172a;">
        Bonjour {{ $entrepreneur?->first_name ?? '' }},
    </h2>

    <p style="margin: 0 0 16px 0; color: #334155; font-size: 15px;">
        Le comité de sélection de <strong>{{ $sae?->name ?? 'Hubs Africa' }}</strong> a attentivement étudié votre candidature pour le projet <strong>"{{ $project?->name }}"</strong> dans le cadre du programme <strong>{{ $program?->name }}</strong>.
    </p>

    @if($isWaitlist)
    <div style="background-color: #fefce8; border-left: 4px solid #F8B81D; padding: 18px; border-radius: 8px; margin: 20px 0;">
        <p style="margin: 0 0 8px 0; font-size: 15px; font-weight: 700; color: #854d0e;">
            Votre dossier est retenu sur liste d'attente
        </p>
        <p style="margin: 0; font-size: 14px; color: #713f12; line-height: 1.5;">
            En raison d'un nombre élevé de dossiers d'excellence et d'une capacité d'accueil limitée, votre projet est placé sur liste d'attente. Nous vous recontacterons sans délai en cas de désistement ou d'ouverture de places supplémentaires.
        </p>
    </div>
    @else
    <div style="background-color: #f8fafc; border-left: 4px solid #64748b; padding: 18px; border-radius: 8px; margin: 20px 0;">
        <p style="margin: 0 0 8px 0; font-size: 15px; font-weight: 700; color: #0f172a;">
            Décision concernant votre candidature
        </p>
        <p style="margin: 0; font-size: 14px; color: #475569; line-height: 1.5;">
            Nous sommes au regret de vous informer que votre projet n'a pas été retenu pour cette promotion. La compétition a été particulièrement exigeante cette saison.
        </p>
    </div>
    @endif

    @if($application->rejection_reason || $application->comment)
    <div style="background-color: #f1f5f9; padding: 16px; border-radius: 8px; margin: 20px 0; font-size: 14px; color: #475569;">
        <strong style="color: #0f172a;">Observations &amp; Recommandations du comité :</strong><br>
        <p style="margin: 6px 0 0 0; font-style: italic;">
            "{{ $application->rejection_reason ?: $application->comment }}"
        </p>
    </div>
    @endif

    <p style="margin: 20px 0 24px 0; color: #334155; font-size: 15px;">
        Nous vous encourageons vivement à poursuivre le développement de votre projet et à postuler à nouveau lors de nos prochains appels à projets.
    </p>

    <!-- Bouton CTA -->
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" style="margin: 24px 0;">
        <tr>
            <td align="center" style="border-radius: 12px; background: #2F3284;">
                <a href="{{ $portalUrl }}" target="_blank" style="font-size: 15px; font-weight: 700; color: #ffffff; text-decoration: none; padding: 14px 28px; border-radius: 12px; display: inline-block; background-color: #2F3284; border: 1px solid #2F3284;">
                    Consulter mon espace &rarr;
                </a>
            </td>
        </tr>
    </table>
</x-emails.layouts.hubsafrica>
