<?php

namespace App\AccompanimentTemplates;

use App\Models\Cohort;
use App\Models\Program;
use App\Models\Sae;

interface AccompanimentTemplateInterface
{
    public function getKey(): string;

    public function getTitle(): string;

    public function getSubtitle(): string;

    public function getDescription(): string;

    public function getBadge(): string;

    public function getDurationWeeks(): int;

    public function getPhases(): array;

    public function getStartupDeliverables(): array;

    public function getProgrammeDeliverables(): array;

    public function getEvaluationCriteria(): array;

    public function getMneIndicators(): array;

    /**
     * @return array<int, array{title: string, description: string, url: string, type: string, is_official: bool}>
     */
    public function getOfficialDriveResources(): array;

    /**
     * @return array<string, mixed>
     */
    public function getDetailedGuide(): array;

    /**
     * Déploie l'ensemble du pack méthodologique sur la SAE et le programme/cohorte cible.
     *
     * @return array<string, int> Résumé des entités créées/mises à jour.
     */
    public function apply(Sae $sae, Program $program, ?Cohort $cohort = null): array;
}
