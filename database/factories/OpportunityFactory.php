<?php

namespace Database\Factories;

use App\Models\Opportunity;
use App\Models\Sae;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Opportunity>
 */
class OpportunityFactory extends Factory
{
    protected $model = Opportunity::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->sentence(4);

        return [
            'sae_id' => Sae::factory(),
            'title' => $title,
            'slug' => Str::slug($title).'-'.Str::lower(Str::random(6)),
            'sectors' => fake()->randomElements(['AgriTech', 'FinTech', 'EdTech', 'Santé', 'Énergie & Climat', 'E-commerce', 'Industrie'], rand(1, 3)),
            'target_zones' => fake()->randomElements(['Panafricain', 'Afrique de l\'Ouest', 'Sénégal', 'Côte d\'Ivoire', 'Togo', 'Cameroun'], rand(1, 2)),
            'details' => fake()->paragraphs(3, true),
            'learn_more_url' => fake()->url(),
            'apply_url' => fake()->url(),
            'deadline' => now()->addDays(rand(10, 60)),
            'is_active' => true,
        ];
    }

    /**
     * État opportunité clôturée par date limite échue.
     */
    public function closed(): static
    {
        return $this->state(fn (array $attributes) => [
            'deadline' => now()->subDays(rand(2, 20)),
            'is_active' => true,
        ]);
    }

    /**
     * État opportunité désactivée.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
