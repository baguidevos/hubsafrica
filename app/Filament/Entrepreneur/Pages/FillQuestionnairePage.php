<?php

namespace App\Filament\Entrepreneur\Pages;

use App\Models\Form;
use App\Models\FormUnlock;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

class FillQuestionnairePage extends Page
{
    protected static ?string $slug = 'questionnaires/{slug}';

    protected static bool $shouldRegisterNavigation = false;

    protected string $view = 'filament.entrepreneur.pages.fill-questionnaire-page';

    public ?string $formSlug = null;

    public ?Form $evaluationForm = null;

    public ?FormUnlock $unlock = null;

    public function mount(string $slug): void
    {
        $this->formSlug = $slug;

        $form = Form::where('slug', $slug)->firstOrFail();

        $entrepreneur = auth()->user()?->entrepreneurProfile;

        if (! $entrepreneur) {
            abort(403, 'Profil entrepreneur requis.');
        }

        $unlock = FormUnlock::with('submission')
            ->where('form_id', $form->getKey())
            ->where('entrepreneur_id', $entrepreneur->id)
            ->first();

        if (! $unlock) {
            abort(403, 'Ce questionnaire ne vous a pas encore été assigné par votre incubateur.');
        }

        $this->evaluationForm = $form;
        $this->unlock = $unlock;
    }

    public function getTitle(): string|Htmlable
    {
        return $this->evaluationForm?->name ?? 'Questionnaire d\'évaluation';
    }
}
