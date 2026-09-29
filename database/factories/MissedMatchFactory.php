<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class MissedMatchFactory extends Factory
{
    public function definition(): array
    {
        return [
            'date' => $this->faker->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
        ];
    }
}
