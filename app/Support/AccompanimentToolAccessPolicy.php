<?php

namespace App\Support;

use App\Enums\AccompanimentToolAccessLevel;
use App\Enums\AccompanimentToolRequestStatus;

final class AccompanimentToolAccessPolicy
{
    /**
     * Évalue de façon pure si un utilisateur peut accéder à un outil d'accompagnement.
     */
    public static function evaluate(
        AccompanimentToolAccessLevel $accessLevel,
        bool $isPublished,
        bool $isAuthenticated,
        ?AccompanimentToolRequestStatus $requestStatus = null,
        bool $hasPaidAccess = false,
    ): AccompanimentToolAccessDecision {
        if (! $isPublished) {
            return AccompanimentToolAccessDecision::denied(
                code: 'TOOL_UNPUBLISHED',
                message: 'Cet outil d\'accompagnement n\'est pas encore publié.',
            );
        }

        if ($accessLevel === AccompanimentToolAccessLevel::FREE) {
            return AccompanimentToolAccessDecision::granted();
        }

        if (! $isAuthenticated) {
            return AccompanimentToolAccessDecision::denied(
                code: 'AUTHENTICATION_REQUIRED',
                message: 'Veuillez créer un compte ou vous connecter pour accéder à cet outil.',
            );
        }

        if ($accessLevel === AccompanimentToolAccessLevel::ACCOUNT_REQUIRED) {
            return AccompanimentToolAccessDecision::granted();
        }

        if ($accessLevel === AccompanimentToolAccessLevel::ON_REQUEST) {
            return self::evaluateOnRequest($requestStatus);
        }

        if ($hasPaidAccess) {
            return AccompanimentToolAccessDecision::granted();
        }

        return AccompanimentToolAccessDecision::denied(
            code: 'PAYMENT_REQUIRED',
            message: 'Un paiement est requis pour débloquer l\'accès à cet outil.',
        );
    }

    private static function evaluateOnRequest(?AccompanimentToolRequestStatus $requestStatus): AccompanimentToolAccessDecision
    {
        if ($requestStatus === AccompanimentToolRequestStatus::APPROVED) {
            return AccompanimentToolAccessDecision::granted();
        }

        if ($requestStatus === AccompanimentToolRequestStatus::PENDING) {
            return AccompanimentToolAccessDecision::denied(
                code: 'REQUEST_PENDING',
                message: 'Votre demande d\'accès est en cours de validation par la structure.',
            );
        }

        if ($requestStatus === AccompanimentToolRequestStatus::REJECTED) {
            return AccompanimentToolAccessDecision::denied(
                code: 'REQUEST_REJECTED',
                message: 'Votre demande d\'accès précédente a été refusée.',
            );
        }

        return AccompanimentToolAccessDecision::denied(
            code: 'REQUEST_REQUIRED',
            message: 'Cet outil est accessible sur demande auprès de la structure.',
        );
    }

    /**
     * Détermine de façon pure si un outil ciblant certains types de SAE correspond aux types d'une structure.
     *
     * @param  array<int, int>  $toolTypeIds
     * @param  array<int, int>  $saeTypeIds
     */
    public static function matchesSaeTypes(array $toolTypeIds, array $saeTypeIds): bool
    {
        if ($toolTypeIds === []) {
            return true;
        }

        if ($saeTypeIds === []) {
            return false;
        }

        return array_intersect($toolTypeIds, $saeTypeIds) !== [];
    }
}
