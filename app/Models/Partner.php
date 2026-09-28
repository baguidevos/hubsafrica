<?php

namespace App\Models;

use App\Enums\PartnerType;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Partner extends Model
{
    use BelongsToTenant, HasFactory, SoftDeletes;

    protected $fillable = [
        'sae_id',
        'name',
        'type',
        'website',
        'contact_name',
        'contact_email',
        'contact_phone',
        'logo',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'type' => PartnerType::class,
        ];
    }

    public function programs(): BelongsToMany
    {
        return $this->belongsToMany(Program::class, 'partner_program')
            ->withPivot(['sae_id', 'role', 'contribution_amount', 'currency'])
            ->withTimestamps();
    }
}
