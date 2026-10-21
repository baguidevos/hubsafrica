<?php

namespace App\Models;

use App\Enums\EntrepreneurInvitationStatus;
use App\Models\Concerns\BelongsToTenant;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class EntrepreneurInvitation extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'sae_id',
        'email',
        'first_name',
        'last_name',
        'phone',
        'project_name',
        'cohort_id',
        'token',
        'status',
        'message',
        'expires_at',
        'accepted_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'status' => EntrepreneurInvitationStatus::class,
            'expires_at' => 'datetime',
            'accepted_at' => 'datetime',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (EntrepreneurInvitation $invitation) {
            if (empty($invitation->token)) {
                $invitation->token = Str::random(48);
            }
            if (empty($invitation->expires_at)) {
                $invitation->expires_at = now()->addDays(14);
            }
            if (empty($invitation->created_by) && auth()->check()) {
                $invitation->created_by = auth()->id();
            }
        });
    }

    public function getFullNameAttribute(): string
    {
        $fullName = trim("{$this->first_name} {$this->last_name}");

        return $fullName !== '' ? $fullName : $this->email;
    }

    public function cohort(): BelongsTo
    {
        return $this->belongsTo(Cohort::class, 'cohort_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isExpired(): bool
    {
        $statusVal = is_object($this->status) && isset($this->status->value) ? $this->status->value : (string) $this->status;

        return ($this->expires_at && $this->expires_at->isPast()) || $statusVal === 'EXPIRED';
    }

    public function isPending(): bool
    {
        $statusVal = is_object($this->status) && isset($this->status->value) ? $this->status->value : (string) $this->status;

        return $statusVal === 'PENDING' && ! $this->isExpired();
    }

    public function getInvitationUrl(): string
    {
        return route('entrepreneur.invite', ['token' => $this->token]);
    }

    public function makeInvitationNotification(): Notification
    {
        $url = $this->getInvitationUrl();
        $targetName = $this->full_name;

        $copyJs = "const url = '{$url}'; if (navigator.clipboard && navigator.clipboard.writeText) { navigator.clipboard.writeText(url); } else { const ta = document.createElement('textarea'); ta.value = url; document.body.appendChild(ta); ta.select(); document.execCommand('copy'); document.body.removeChild(ta); } const btn = \$el; const orig = btn.innerText; btn.innerText = '✓ Lien copié !'; setTimeout(() => { btn.innerText = orig; }, 2500);";

        return Notification::make()
            ->title('Lien d\'invitation généré')
            ->body(new HtmlString('Invitation pour <strong>'.e($targetName)."</strong> :<br><span class='text-xs font-mono select-all text-slate-600 dark:text-slate-300 break-all'>".e($url).'</span>'))
            ->icon('heroicon-o-link')
            ->success()
            ->actions([
                Action::make('copy_invitation_url')
                    ->label('Copier le lien')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->button()
                    ->color('primary')
                    ->alpineClickHandler($copyJs),
                Action::make('open_invitation_url')
                    ->label('Ouvrir')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url($url, shouldOpenInNewTab: true)
                    ->color('gray'),
            ])
            ->persistent();
    }
}
