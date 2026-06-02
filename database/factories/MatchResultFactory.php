<?php

namespace Database\Factories;

use App\Venue;
use Illuminate\Database\Eloquent\Factories\Factory;

class MatchResultFactory extends Factory
{
    public function definition(): array
    {
        return [
            'date'     => $this->faker->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
            'venue_id' => Venue::factory(),
            'is_short' => false,
            'is_void'  => false,
        ];
    }
}
