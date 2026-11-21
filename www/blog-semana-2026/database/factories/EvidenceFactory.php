<?php

namespace Database\Factories;

use App\Models\Evidence;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Evidence>
 */
class EvidenceFactory extends Factory
{
    protected $model = Evidence::class;

    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'event_reference' => fake()->randomElement(Evidence::EVENT_REFERENCES),
            'path' => 'evidences/fake_evidence.jpg',
            'user_id' => User::factory(),
            'description' => fake()->paragraph(),
            'taken_at' => fake()->date(),
        ];
    }
}
