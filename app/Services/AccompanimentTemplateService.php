<?php

namespace App\Services;

use App\AccompanimentTemplates\AccompanimentTemplateInterface;
use App\AccompanimentTemplates\CubeMethodologyTemplate;
use App\AccompanimentTemplates\StartupLoopAfricinnovTemplate;
use App\Models\Cohort;
use App\Models\Program;
use App\Models\Sae;
use InvalidArgumentException;

class AccompanimentTemplateService
{
    /**
     * @var array<string, AccompanimentTemplateInterface>
     */
    protected array $templates = [];

    public function __construct()
    {
        // Enregistrement des modèles disponibles
        $this->register(new StartupLoopAfricinnovTemplate);
        $this->register(new CubeMethodologyTemplate);
    }

    public function register(AccompanimentTemplateInterface $template): self
    {
        $this->templates[$template->getKey()] = $template;

        return $this;
    }

    /**
     * @return array<string, AccompanimentTemplateInterface>
     */
    public function all(): array
    {
        return $this->templates;
    }

    public function get(string $key): ?AccompanimentTemplateInterface
    {
        return $this->templates[$key] ?? null;
    }

    /**
     * @return array<string, string>
     */
    public function asSelectOptions(): array
    {
        $options = [];
        foreach ($this->templates as $key => $template) {
            $options[$key] = "{$template->getTitle()} ({$template->getBadge()})";
        }

        return $options;
    }

    /**
     * Déploie le template sélectionné sur un programme et optionnellement une cohorte.
     */
    public function apply(string $templateKey, Sae $sae, Program $program, ?Cohort $cohort = null): array
    {
        $template = $this->get($templateKey);

        if (! $template) {
            throw new InvalidArgumentException("Le modèle d'accompagnement [{$templateKey}] n'est pas enregistré.");
        }

        return $template->apply($sae, $program, $cohort);
    }
}
