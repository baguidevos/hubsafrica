<?php

namespace App\Services;

use App\Models\Application;
use App\Models\EvaluationCriterion;
use Illuminate\Support\Collection;

class ApplicationGridEvaluationService
{
    /**
     * Calcule de façon pure le score pondéré ramené sur 100 à partir des notes par critère.
     *
     * @param  array<int, array{score: float|int, max_score: int, weight: int}>  $criteriaScores
     */
    public function calculateWeightedScore(array $criteriaScores): ?float
    {
        $totalWeightedScore = 0.0;
        $totalWeight = 0;

        foreach ($criteriaScores as $item) {
            $maxScore = (int) ($item['max_score'] ?? 0);
            $weight = (int) ($item['weight'] ?? 0);

            if ($maxScore <= 0 || $weight <= 0) {
                continue;
            }

            $clampedScore = max(0.0, min((float) $item['score'], (float) $maxScore));
            $normalizedOn100 = ($clampedScore / $maxScore) * 100.0;

            $totalWeightedScore += $normalizedOn100 * $weight;
            $totalWeight += $weight;
        }

        if ($totalWeight === 0) {
            return null;
        }

        return round($totalWeightedScore / $totalWeight, 2);
    }

    /**
     * Détermine de façon pure la recommandation indicative (sans jamais appliquer de décision finale).
     *
     * @return array{code: string, label: string, rank_priority: int}
     */
    public function recommendOutcome(?float $score, int $admissionThreshold, int $waitlistThreshold): array
    {
        if ($score === null) {
            return [
                'code' => 'A_EVALUER',
                'label' => 'À évaluer',
                'rank_priority' => 4,
            ];
        }

        if ($score >= $admissionThreshold) {
            return [
                'code' => 'REUSSI',
                'label' => 'Réussi (Admissible)',
                'rank_priority' => 1,
            ];
        }

        if ($score >= $waitlistThreshold) {
            return [
                'code' => 'LISTE_ATTENTE',
                'label' => 'Liste d\'attente',
                'rank_priority' => 2,
            ];
        }

        return [
            'code' => 'RECALE',
            'label' => 'Recalé',
            'rank_priority' => 3,
        ];
    }

    /**
     * Construit le classement structuré des candidatures pour revue et validation humaine.
     *
     * @param  Collection<int, Application>  $applications
     * @param  Collection<int, EvaluationCriterion>  $criteria
     * @return array<int, array<string, mixed>>
     */
    public function buildRankedRows(
        Collection $applications,
        Collection $criteria,
        int $admissionThreshold,
        int $waitlistThreshold,
    ): array {
        $rows = [];

        foreach ($applications as $application) {
            $latestEvaluation = $application->evaluations->sortByDesc('updated_at')->first();
            $score = $latestEvaluation?->total_score !== null
                ? (float) $latestEvaluation->total_score
                : ($application->score !== null ? (float) $application->score : null);

            $recommendation = $this->recommendOutcome($score, $admissionThreshold, $waitlistThreshold);

            $scoresByCriterionId = $latestEvaluation
                ? $latestEvaluation->scores->keyBy('criterion_id')
                : collect();

            $criteriaBreakdown = [];
            $criteriaReasons = [];

            foreach ($criteria as $criterion) {
                $criterionScore = $scoresByCriterionId->get($criterion->id);
                $numericScore = $criterionScore?->score !== null ? (float) $criterionScore->score : null;
                $criterionComment = trim((string) ($criterionScore?->comment ?? ''));

                $criteriaBreakdown[] = [
                    'criterion_id' => $criterion->id,
                    'name' => $criterion->name,
                    'weight' => $criterion->weight,
                    'max_score' => $criterion->max_score,
                    'score' => $numericScore,
                    'comment' => $criterionComment !== '' ? $criterionComment : null,
                ];

                if ($criterionComment !== '') {
                    $scoreText = $numericScore !== null ? "{$numericScore}/{$criterion->max_score}" : '—';
                    $criteriaReasons[] = "{$criterion->name} ({$scoreText}) : {$criterionComment}";
                }
            }

            $summaryComment = trim((string) ($latestEvaluation?->comment ?? ''));
            $combinedReasons = collect([$summaryComment, ...$criteriaReasons])
                ->filter(fn (string $part): bool => $part !== '')
                ->implode(' | ');

            $candidateName = $application->entrepreneur?->full_name
                ?: $application->entrepreneur?->user?->name
                ?: 'Candidat';

            $rows[] = [
                'application_id' => $application->id,
                'code' => $application->code,
                'candidate_name' => trim($candidateName),
                'candidate_email' => $application->entrepreneur?->email ?? $application->entrepreneur?->user?->email,
                'project_name' => $application->project?->name ?? 'Projet non renseigné',
                'sector' => $application->project?->sector,
                'cohort_name' => $application->cohort?->name,
                'current_status' => $application->status?->value ?? (string) $application->status,
                'score' => $score,
                'recommendation_code' => $recommendation['code'],
                'recommendation_label' => $recommendation['label'],
                'rank_priority' => $recommendation['rank_priority'],
                'summary_comment' => $summaryComment !== '' ? $summaryComment : null,
                'reasons_and_comments' => $combinedReasons !== '' ? $combinedReasons : null,
                'criteria_breakdown' => $criteriaBreakdown,
            ];
        }

        usort($rows, function (array $left, array $right): int {
            if ($left['rank_priority'] !== $right['rank_priority']) {
                return $left['rank_priority'] <=> $right['rank_priority'];
            }

            $leftScore = $left['score'] ?? -1.0;
            $rightScore = $right['score'] ?? -1.0;

            if ($leftScore !== $rightScore) {
                return $rightScore <=> $leftScore;
            }

            return $left['application_id'] <=> $right['application_id'];
        });

        foreach ($rows as $index => &$row) {
            $row['rank'] = $index + 1;
            unset($row['rank_priority']);
        }
        unset($row);

        return $rows;
    }

    /**
     * Génère le contenu CSV compatible Excel (UTF-8 BOM, séparateur point-virgule) classé par recommandation.
     *
     * @param  array<int, array<string, mixed>>  $rankedRows
     * @param  Collection<int, EvaluationCriterion>  $criteria
     */
    public function generateExcelCsv(array $rankedRows, Collection $criteria): string
    {
        $handle = fopen('php://temp', 'r+');

        if ($handle === false) {
            return '';
        }

        fwrite($handle, "\xEF\xBB\xBF");

        $headers = [
            'Rang',
            'Recommandation (Indicative)',
            'Note /100',
            'Statut actuel (Validation humaine)',
            'Candidat',
            'Email',
            'Projet',
            'Secteur',
            'Cohorte',
        ];

        foreach ($criteria as $criterion) {
            $headers[] = "{$criterion->name} (/{$criterion->max_score}, poids {$criterion->weight}%)";
        }

        $headers[] = 'Synthèse';
        $headers[] = 'Raisons & Commentaires détaillés';

        fputcsv($handle, $headers, ';');

        foreach ($rankedRows as $row) {
            $line = [
                $row['rank'],
                $row['recommendation_label'],
                $row['score'] !== null ? number_format((float) $row['score'], 2, ',', '') : '—',
                $row['current_status'],
                $row['candidate_name'],
                $row['candidate_email'] ?? '—',
                $row['project_name'],
                $row['sector'] ?? '—',
                $row['cohort_name'] ?? '—',
            ];

            $breakdownById = collect($row['criteria_breakdown'] ?? [])->keyBy('criterion_id');

            foreach ($criteria as $criterion) {
                $entry = $breakdownById->get($criterion->id);
                $line[] = ($entry && $entry['score'] !== null)
                    ? number_format((float) $entry['score'], 2, ',', '')."/{$criterion->max_score}"
                    : '—';
            }

            $line[] = $row['summary_comment'] ?? '—';
            $line[] = $row['reasons_and_comments'] ?? '—';

            fputcsv($handle, $line, ';');
        }

        rewind($handle);
        $csvContent = stream_get_contents($handle) ?: '';
        fclose($handle);

        return $csvContent;
    }
}
