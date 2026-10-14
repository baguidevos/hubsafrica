<x-emails.layouts.hubafrica :sae="$sae" :badgeText="$isValidated ? 'Livrable Validé' : 'Révision Demandée'">
    <h2 style="margin: 0 0 16px 0; font-size: 20px; font-weight: 800; color: #0f172a;">
        Bonjour {{ $entrepreneur?->first_name ?? '' }},
    </h2>

    <p style="margin: 0 0 16px 0; color: #334155; font-size: 15px;">
        Votre coach / l'équipe pédagogique a procédé à l'évaluation du livrable <strong>"{{ $definition?->title }}"</strong> soumis pour la start-up <strong>"{{ $project?->name }}"</strong>.
    </p>

    @if($isValidated)
    <div style="background-color: #f0fdf4; border-left: 4px solid #16a34a; padding: 18px; border-radius: 8px; margin: 20px 0;">
        <p style="margin: 0 0 6px 0; font-size: 15px; font-weight: 700; color: #166534;">
            Félicitations, ce livrable est validé ! ✅
        </p>
        <p style="margin: 0; font-size: 14px; color: #15803d;">
            Votre travail répond aux exigences méthodologiques de cette phase. Cette étape validée vous rapproche du franchissement du palier supérieur.
        </p>
    </div>
    @else
    <div style="background-color: #fefce8; border-left: 4px solid #F8B81D; padding: 18px; border-radius: 8px; margin: 20px 0;">
        <p style="margin: 0 0 6px 0; font-size: 15px; font-weight: 700; color: #854d0e;">
            Des ajustements sont requis sur ce livrable 📝
        </p>
        <p style="margin: 0; font-size: 14px; color: #713f12;">
            Veuillez consulter les retours de votre évaluateur ci-dessous et déposer une nouvelle version corrigée sur votre portail.
        </p>
    </div>
    @endif

    @if($submission->comment)
    <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; padding: 16px; border-radius: 8px; margin: 20px 0; font-size: 14px; color: #475569;">
        <strong style="color: #0f172a;">Commentaires &amp; Remarques du coach :</strong><br>
        <p style="margin: 6px 0 0 0; font-style: italic;">
            "{{ $submission->comment }}"
        </p>
    </div>
    @endif

    <!-- Bouton CTA -->
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" style="margin: 28px 0;">
        <tr>
            <td align="center" style="border-radius: 12px; background: #2F3284;">
                <a href="{{ $portalUrl }}" target="_blank" style="font-size: 15px; font-weight: 700; color: #ffffff; text-decoration: none; padding: 14px 28px; border-radius: 12px; display: inline-block; background-color: #2F3284; border: 1px solid #2F3284;">
                    Accéder à mon espace livrables &rarr;
                </a>
            </td>
        </tr>
    </table>
</x-emails.layouts.hubafrica>
