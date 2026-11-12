<?php

namespace App\Mcp\Concerns;

use App\Models\AuditLog;
use App\Models\McpApiKey;
use App\Models\Sae;
use Illuminate\Validation\ValidationException;
use Laravel\Mcp\Response;

trait InteractsWithSae
{
    /**
     * Récupère la SAE authentifiée via la clé API.
     */
    protected function currentSae(): Sae
    {
        if (app()->has('current_sae')) {
            return app('current_sae');
        }

        abort(401, 'Contexte SAE introuvable.');
    }

    /**
     * Récupère la clé API MCP ayant authentifié la requête courante.
     */
    protected function currentApiKey(): ?McpApiKey
    {
        return app()->has(McpApiKey::class) ? app(McpApiKey::class) : null;
    }

    /**
     * Consigne une trace d'action effectuée par l'agent IA dans les journaux d'audit de la SAE.
     */
    protected function logAudit(string $action, string $entity, ?string $entityId = null, string|array|null $details = null): void
    {
        $sae = $this->currentSae();
        $apiKey = $this->currentApiKey();
        $user = auth()->user() ?? $apiKey?->user;

        AuditLog::create([
            'sae_id' => $sae->id,
            'user_id' => $user?->id,
            'user_name' => $user?->name ? ($user->name.' [IA: '.$apiKey?->name.']') : ('Agent IA ('.$apiKey?->name.')'),
            'action' => $action,
            'entity' => $entity,
            'entity_id' => $entityId !== null ? (string) $entityId : null,
            'details' => is_array($details) ? json_encode($details, JSON_UNESCAPED_UNICODE) : (string) $details,
            'ip_address' => request()->ip(),
        ]);
    }

    /**
     * Helper pour formater les erreurs de validation en réponse MCP conviviale.
     */
    protected function handleValidationException(ValidationException $e): Response
    {
        $errors = collect($e->errors())->flatten()->implode(' ');

        return Response::error("Erreur de validation : {$errors}");
    }
}
