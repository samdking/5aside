<?php

namespace Database\Factories;

use App\MatchResult;
use Illuminate\Database\Eloquent\Factories\Factory;

class TeamFactory extends Factory
{
    public function definition(): array
    {
        return [
            'match_id' => MatchResult::factory(),
            'scored'   => $this->faker->numberBetween(0, 5),
            'winners'  => 0,
            'draw'     => 0,
            'handicap' => 0,
        ];
    }

    public function winners(): static
    {
        return $this->state(['winners' => 1, 'draw' => 0]);
    }

    public function draw(): static
    {
        return $this->state(['winners' => 0, 'draw' => 1]);
    }
}
