<?php

namespace Database\Factories;

use App\Models\Loan;
use App\Models\ScheduledRepayment;
use Illuminate\Database\Eloquent\Factories\Factory;

class ScheduledRepaymentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ScheduledRepayment::class;

    public function definition()
    {
        return [
            'loan_id' => \App\Models\Loan::factory(),
            'amount' => $this->faker->numberBetween(1000, 10000),
            'outstanding_amount' => $this->faker->numberBetween(1000, 10000),
            'currency_code' => 'VND',
            'due_date' => now()->addMonth(),
            'status' => 'DUE',
        ];
    }
}
