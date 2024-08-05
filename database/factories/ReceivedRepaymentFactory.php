<?php

namespace Database\Factories;

use App\Models\Loan;
use App\Models\ReceivedRepayment;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReceivedRepaymentFactory extends Factory
{
    protected $model = ReceivedRepayment::class;
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'loan_id' => Loan::factory(),
            'amount' => $this->faker->numberBetween(1000, 5000),
            'payment_date' => $this->faker->date(),
        ];
    }
}
