<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class CoachInvitation extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'sae_id',
        'email',
        'name',
        'token',
        'specialties',
        'max_capacity',
        'is_external',
        'status',
        'expires_at',
        'accepted_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'status' => \App\Enums\CoachInvitationStatus::class,
            'specialties' => 'array',
            'is_external' => 'boolean',
            'expires_at' => 'datetime',
            'accepted_at' => 'datetime',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (CoachInvitation $invitation) {
            if (empty($invitation->token)) {
                $invitation->token = Str::random(48);
            }
            if (empty($invitation->expires_at)) {
                $invitation->expires_at = now()->addDays(7);
            }
        });
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isExpired(): bool
    {
        $statusVal = is_object($this->status) && isset($this->status->value) ? $this->status->value : (string) $this->status;
        return $this->expires_at->isPast() || $statusVal === 'EXPIRED';
    }

    public function isPending(): bool
    {
        $statusVal = is_object($this->status) && isset($this->status->value) ? $this->status->value : (string) $this->status;
        return $statusVal === 'PENDING' && ! $this->isExpired();
    }

    public function getInvitationUrl(): string
    {
        return route('coach.invite', ['token' => $this->token]);
    }

    public function makeInvitationNotification(): \Filament\Notifications\Notification
    {
        $url = $this->getInvitationUrl();
        $targetName = $this->name ?: 'le coach';

        $copyJs = "const url = '{$url}'; if (navigator.clipboard && navigator.clipboard.writeText) { navigator.clipboard.writeText(url); } else { const ta = document.createElement('textarea'); ta.value = url; document.body.appendChild(ta); ta.select(); document.execCommand('copy'); document.body.removeChild(ta); } const btn = \$el; const orig = btn.innerText; btn.innerText = '✓ Lien copié !'; setTimeout(() => { btn.innerText = orig; }, 2500);";

        return \Filament\Notifications\Notification::make()
            ->title('Lien d\'invitation généré')
            ->body(new \Illuminate\Support\HtmlString("Invitation pour <strong>" . e($targetName) . "</strong> :<br><span class='text-xs font-mono select-all text-slate-600 dark:text-slate-300 break-all'>" . e($url) . "</span>"))
            ->icon('heroicon-o-link')
            ->success()
            ->actions([
                \Filament\Actions\Action::make('copy_invitation_url')
                    ->label('Copier le lien')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->button()
                    ->color('primary')
                    ->alpineClickHandler($copyJs),
                \Filament\Actions\Action::make('open_invitation_url')
                    ->label('Ouvrir')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url($url, shouldOpenInNewTab: true)
                    ->color('gray'),
            ])
            ->persistent();
    }
}
