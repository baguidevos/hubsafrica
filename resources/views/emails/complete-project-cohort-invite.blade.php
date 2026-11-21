<x-emails.layouts.hubsafrica :sae="$sae" badgeText="Action requise — Fiche projet">
    <h2 style="margin: 0 0 16px 0; font-size: 20px; font-weight: 800; color: #0f172a;">
        Bonjour {{ $entrepreneur->first_name ?: 'Cher(e) Entrepreneur(e)' }},
    </h2>

    @if ($isCohortEnrollment && $cohort)
        <div style="background-color: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 8px; padding: 14px 16px; margin: 0 0 18px 0;">
            <p style="margin: 0; color: #065f46; font-size: 14px; line-height: 1.5;">
                La structure <strong>{{ $sae?->name ?? 'Hubs Africa' }}</strong> vous a rattaché(e) à la cohorte
                <strong>{{ $cohort->name }}</strong>
                @if ($cohort->program)
                    (programme <em>{{ $cohort->program->name }}</em>)
                @endif
                .
            </p>
        </div>
    @elseif ($cohort)
        <div style="background-color: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; padding: 14px 16px; margin: 0 0 18px 0;">
            <p style="margin: 0; color: #1e40af; font-size: 14px; line-height: 1.5;">
                Concernant votre candidature auprès de <strong>{{ $sae?->name ?? 'Hubs Africa' }}</strong>
                pour la cohorte <strong>{{ $cohort->name }}</strong>
                @if ($cohort->program)
                    (programme <em>{{ $cohort->program->name }}</em>)
                @endif
                :
            </p>
        </div>
    @endif

    @if (filled($customMessage))
        <div style="color: #334155; font-size: 15px; line-height: 1.65; margin: 0 0 20px 0;">
            {!! str($customMessage)->sanitizeHtml() !!}
        </div>
    @else
        <p style="margin: 0 0 16px 0; color: #334155; font-size: 15px; line-height: 1.6;">
            La structure <strong>{{ $sae?->name ?? 'Hubs Africa' }}</strong> vous invite à compléter obligatoirement votre fiche projet sur la plateforme <strong>Hubs Africa</strong> afin de finaliser votre dossier.
        </p>
    @endif

    <div style="margin: 28px 0; text-align: center;">
        <a href="{{ $projectUrl }}" style="display: inline-block; background-color: #0d9488; color: #ffffff; text-decoration: none; font-weight: 700; font-size: 15px; padding: 14px 28px; border-radius: 8px;">
            Compléter mon projet maintenant &rarr;
        </a>
    </div>

    <p style="margin: 0; font-size: 13px; color: #64748b; line-height: 1.5;">
        Une fois votre fiche projet complétée, l'équipe {{ $sae?->name ?? 'Hubs Africa' }} pourra valider votre dossier et assurer votre suivi.
    </p>
</x-emails.layouts.hubsafrica>
