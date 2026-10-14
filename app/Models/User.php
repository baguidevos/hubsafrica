<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Models\Contracts\HasTenants;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser, HasAvatar, HasTenants
{
    use HasFactory, HasRoles, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'avatar_url',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function saes(): BelongsToMany
    {
        return $this->belongsToMany(Sae::class, 'sae_users')
            ->withPivot(['role', 'status'])
            ->withTimestamps();
    }

    public function ownedSae(): HasOne
    {
        return $this->hasOne(Sae::class, 'owner_id');
    }

    /**
     * Règle stricte : Un utilisateur ne peut créer qu'une seule structure SAE.
     */
    public function canCreateTenants(): bool
    {
        return $this->ownedSae()->doesntExist();
    }

    public function coachProfile(): HasOne
    {
        return $this->hasOne(Coach::class, 'user_id');
    }

    public function entrepreneurProfile(): HasOne
    {
        return $this->hasOne(Entrepreneur::class, 'user_id');
    }

    public function entrepreneur(): HasOne
    {
        return $this->entrepreneurProfile();
    }

    public function isSuperAdmin(): bool
    {
        $initialAdmin = env('INITIAL_ADMIN_EMAIL', 'admin@hubafrica.org');

        if ($this->email === 'admin@hubafrica.org' || ($initialAdmin && $this->email === $initialAdmin)) {
            return true;
        }

        return DB::table('model_has_roles')
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->where('model_has_roles.model_id', $this->id)
            ->where('model_has_roles.model_type', static::class)
            ->where('roles.name', 'super_admin')
            ->exists();
    }

    public function isCoach(): bool
    {
        return $this->coachProfile()->exists() || $this->hasRole('coach');
    }

    public function isEntrepreneur(): bool
    {
        return $this->entrepreneurProfile()->exists() || $this->hasRole('entrepreneur');
    }

    public function canAccessPanel(Panel $panel): bool
    {
        $panelId = $panel->getId();

        // 1. Super Admin a accès à tous les panels
        if ($this->isSuperAdmin()) {
            return true;
        }

        // 2. Panel Admin : Réservé aux Super Admins Hub Africa
        if ($panelId === 'admin') {
            return $this->isSuperAdmin();
        }

        // 3. Panel SAE : Responsables et managers de l'incubateur
        if ($panelId === 'sae') {
            return $this->saes()->wherePivot('role', 'RESPONSABLE')->exists()
                || $this->ownedSae()->exists()
                || $this->canCreateTenants(); // Permet l'accès pour enregistrer sa 1ère SAE
        }

        // 4. Panel Coach : Réservé aux coachs / mentors
        if ($panelId === 'coach') {
            return $this->coachProfile()->exists()
                || $this->saes()->wherePivot('role', 'COACH')->exists();
        }

        // 5. Panel Entrepreneur : Réservé aux porteurs de projets
        if ($panelId === 'entrepreneur') {
            return $this->entrepreneurProfile()->exists()
                || $this->saes()->wherePivot('role', 'ENTREPRENEUR')->exists();
        }

        return true;
    }

    public function getTenants(Panel $panel): Collection
    {
        if ($this->isSuperAdmin()) {
            return Sae::all();
        }

        $tenants = collect();
        if ($this->ownedSae) {
            $tenants->push($this->ownedSae);
        }

        return $tenants->merge($this->saes)->unique('id');
    }

    public function canAccessTenant(Model $tenant): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        if ($this->ownedSae && $this->ownedSae->id === $tenant->id) {
            return true;
        }

        if ($this->relationLoaded('saes') && $this->saes->contains('id', $tenant->id)) {
            return true;
        }

        return $this->saes()->where('saes.id', $tenant->id)->exists();
    }

    public function getFilamentAvatarUrl(): ?string
    {
        return $this->avatar_url;
    }
}
