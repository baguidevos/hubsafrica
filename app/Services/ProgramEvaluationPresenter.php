<?php

namespace App\Services;

final class ProgramEvaluationPresenter
{
    public static function determineScoreBadgeColor(?int $score, int $admissionThreshold = 75, int $waitlistThreshold = 50): string
    {
        if ($score === null) {
            return 'gray';
        }

        if ($score >= $admissionThreshold) {
            return 'success';
        }

        if ($score >= $waitlistThreshold) {
            return 'warning';
        }

        return 'danger';
    }

    public static function formatScoreLabel(?int $score): string
    {
        if ($score === null) {
            return 'Non notée';
        }

        return "{$score} / 100";
    }

    public static function formatOccupancyRate(int $enrolledCount, ?int $capacity): string
    {
        if ($capacity === null || $capacity <= 0) {
            return "{$enrolledCount} startup(s)";
        }

        $percentage = (int) round(($enrolledCount / $capacity) * 100);

        return "{$enrolledCount} / {$capacity} ({$percentage}%)";
    }

    public static function determineOccupancyColor(int $enrolledCount, ?int $capacity): string
    {
        if ($capacity === null || $capacity <= 0) {
            return 'info';
        }

        if ($enrolledCount >= $capacity) {
            return 'success';
        }

        if ($enrolledCount > 0) {
            return 'primary';
        }

        return 'gray';
    }
}
