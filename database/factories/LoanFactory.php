<?php

namespace Database\Factories;

use App\Models\Loan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class LoanFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Loan::class;

    public function definition()
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'amount' => $this->faker->numberBetween(1000, 10000),
            'terms' => $this->faker->randomElement([3, 6]),
            'currency_code' => 'VND',
            'processed_at' => now(),
            'outstanding_amount' => $this->faker->numberBetween(1000, 10000),
            'status' => 'DUE',
        ];
    }
}
