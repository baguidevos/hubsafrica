<?php

namespace Database\Factories;

use App\Models\SaeType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<SaeType>
 */
class SaeTypeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'name' => ucfirst($name),
            'slug' => Str::slug($name),
            'description' => fake()->sentence(),
            'color' => 'primary',
            'icon' => 'heroicon-o-building-office-2',
            'is_active' => true,
        ];
    }
}
