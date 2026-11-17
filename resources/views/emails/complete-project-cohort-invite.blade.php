<x-emails.layouts.hubsafrica :sae="$sae" badgeText="Compléter votre projet">
    <h2 style="margin: 0 0 16px 0; font-size: 20px; font-weight: 800; color: #0f172a;">
        Bonjour {{ $entrepreneur->first_name ?: 'Cher(e) Entrepreneur(e)' }},
    </h2>

    <p style="margin: 0 0 16px 0; color: #334155; font-size: 15px; line-height: 1.6;">
        La structure <strong>{{ $sae?->name ?? 'Hub Africa' }}</strong> vous a rattaché(e) à la cohorte
        <strong>{{ $cohort->name }}</strong>
        @if ($cohort->program)
            (programme <em>{{ $cohort->program->name }}</em>)
        @endif
        et vous invite à renseigner votre fiche projet sur la plateforme <strong>Hub Africa</strong>.
    </p>

    @if ($customMessage)
        <div style="background-color: #f8fafc; border-left: 4px solid #2F3284; padding: 18px; border-radius: 8px; margin: 20px 0;">
            <p style="margin: 0 0 8px 0; font-size: 13px; font-weight: 700; color: #0f172a; text-transform: uppercase; letter-spacing: 0.05em;">
                Message de l'équipe :
            </p>
            <p style="margin: 0; font-size: 14px; color: #475569; font-style: italic; line-height: 1.6;">
                « {{ $customMessage }} »
            </p>
        </div>
    @endif

    <div style="margin: 28px 0; text-align: center;">
        <a href="{{ $projectUrl }}" style="display: inline-block; background-color: #0d9488; color: #ffffff; text-decoration: none; font-weight: 700; font-size: 15px; padding: 14px 28px; border-radius: 8px;">
            Compléter mon projet maintenant
        </a>
    </div>

    <p style="margin: 0; font-size: 13px; color: #64748b; line-height: 1.5;">
        Une fois votre fiche projet complétée, votre coach et l'équipe d'accompagnement pourront suivre votre progression et vos livrables.
    </p>
</x-emails.layouts.hubsafrica>
