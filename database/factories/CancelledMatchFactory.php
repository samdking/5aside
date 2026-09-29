<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CancelledMatchFactory extends Factory
{
    public function definition(): array
    {
        return [
            'date' => $this->faker->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
        ];
    }
}
