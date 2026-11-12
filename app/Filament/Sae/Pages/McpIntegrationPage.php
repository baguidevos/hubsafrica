<?php

namespace App\Filament\Sae\Pages;

use App\Models\McpApiKey;
use App\Models\McpOAuthClient;
use App\Models\Sae;
use BackedEnum;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class McpIntegrationPage extends Page
{
    protected string $view = 'filament.sae.pages.mcp-integration-page';

    protected static string|\UnitEnum|null $navigationGroup = 'Système';

    protected static ?string $navigationLabel = 'Intégration IA & Clés MCP';

    protected static ?string $title = 'Intégration IA — Serveur MCP & Clés d\'API';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-cpu-chip';

    protected static ?int $navigationSort = 10;

    /**
     * Jeton en clair affiché temporairement lors de la génération.
     */
    public ?string $newlyCreatedPlainToken = null;

    public ?string $newlyCreatedKeyName = null;

    public function getActiveTenantProperty(): ?Sae
    {
        return Filament::getTenant();
    }

    public function getActiveTenant(): ?Sae
    {
        return $this->activeTenant;
    }

    /**
     * @return Collection<int, McpApiKey>
     */
    public function getApiKeysProperty(): Collection
    {
        $tenant = $this->getActiveTenant();

        if (! $tenant) {
            return new Collection;
        }

        return McpApiKey::query()
            ->where('sae_id', $tenant->id)
            ->with('user:id,name,email')
            ->latest()
            ->get();
    }

    public function getMcpEndpointUrlProperty(): string
    {
        return url('/mcp/sae');
    }

    public function getOpenApiSpecUrlProperty(): string
    {
        return url('/api/mcp/openapi.json');
    }

    public function getAuthorizeUrlProperty(): string
    {
        return url('/oauth/authorize');
    }

    public function getTokenUrlProperty(): string
    {
        return url('/oauth/token');
    }

    public function getOAuthClientProperty(): ?McpOAuthClient
    {
        $tenant = $this->getActiveTenant();

        if (! $tenant) {
            return null;
        }

        return McpOAuthClient::findOrCreateForSae($tenant);
    }

    public function getTestConnectUrlProperty(): string
    {
        $client = $this->oAuthClient;

        return url('/oauth/authorize').'?'.http_build_query([
            'response_type' => 'code',
            'client_id' => $client?->client_id,
            'redirect_uri' => url('/api/oauth/userinfo'),
            'state' => 'test_1click_'.uniqid(),
            'scope' => 'mcp:sae',
        ]);
    }

    public function regenerateOAuthSecret(): void
    {
        $client = $this->oAuthClient;

        if (! $client) {
            return;
        }

        $client->update([
            'client_secret' => 'ha_sec_'.Str::random(40),
        ]);

        Notification::make()
            ->title('Secret OAuth régénéré')
            ->body('Le secret client OAuth a été mis à jour avec succès.')
            ->success()
            ->send();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('createKey')
                ->label('Générer une nouvelle clé MCP')
                ->icon('heroicon-m-key')
                ->color('primary')
                ->modalHeading('Générer une clé API MCP')
                ->modalDescription('Cette clé permettra à Claude, ChatGPT ou tout client MCP de piloter les données de votre structure.')
                ->modalSubmitActionLabel('Créer la clé')
                ->form([
                    TextInput::make('name')
                        ->label('Libellé / Usage')
                        ->placeholder('ex: Claude Desktop Bureau, Agent GPT Candidatures')
                        ->required()
                        ->maxLength(100),
                    DateTimePicker::make('expires_at')
                        ->label('Date d\'expiration (optionnel)')
                        ->helperText('Laissez vide pour une clé sans date de fin de validité.')
                        ->minDate(now()),
                ])
                ->action(function (array $data): void {
                    $tenant = $this->getActiveTenant();
                    $user = auth()->user();

                    if (! $tenant || ! $user) {
                        Notification::make()
                            ->title('Action impossible')
                            ->body('Structure ou utilisateur non identifié.')
                            ->danger()
                            ->send();

                        return;
                    }

                    $expiresAt = ! empty($data['expires_at']) ? Carbon::parse($data['expires_at']) : null;
                    $result = McpApiKey::createToken($tenant, $user, $data['name'], $expiresAt);

                    $this->newlyCreatedPlainToken = $result['plainTextToken'];
                    $this->newlyCreatedKeyName = $data['name'];

                    Notification::make()
                        ->title('Clé API MCP générée avec succès')
                        ->body('Copiez immédiatement votre clé ci-dessous. Elle ne sera plus affichée.')
                        ->success()
                        ->persistent()
                        ->send();
                }),
        ];
    }

    public function revokeKey(int $id): void
    {
        $tenant = $this->getActiveTenant();

        if (! $tenant) {
            return;
        }

        $key = McpApiKey::where('sae_id', $tenant->id)->where('id', $id)->first();

        if ($key) {
            $keyName = $key->name;
            $key->delete();

            Notification::make()
                ->title('Clé révoquée')
                ->body("La clé « {$keyName} » a été révoquée et ne peut plus être utilisée.")
                ->warning()
                ->send();
        }
    }

    public function dismissNewKeyBanner(): void
    {
        $this->newlyCreatedPlainToken = null;
        $this->newlyCreatedKeyName = null;
    }
}
