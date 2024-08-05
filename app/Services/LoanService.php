<?php

namespace App\Services;

use App\Models\Loan;
use App\Models\ScheduledRepayment;
use App\Models\ReceivedRepayment;
use Carbon\Carbon;

class LoanService
{
    /**
     * Create a Loan
     *
     * @param  User  $user
     * @param  int  $amount
     * @param  string  $currencyCode
     * @param  int  $terms
     * @param  string  $processedAt
     *
     * @return Loan
     */
    public function createLoan($user, $amount, $currencyCode, $terms, $processedAt)
    {
        $loan = Loan::create([
            'user_id' => $user->id,
            'amount' => $amount,
            'currency_code' => $currencyCode,
            'terms' => $terms,
            'processed_at' => $processedAt,
            'status' => Loan::STATUS_DUE,
            'outstanding_amount' => $amount
        ]);

        $this->createScheduledRepayments($loan, $amount, $terms, $currencyCode, $processedAt);

        return $loan;
    }

    protected function createScheduledRepayments($loan, $amount, $terms, $currencyCode, $processedAt)
    {
        $monthlyRepayment = round($amount / $terms, 2);
        $startDate = Carbon::parse($processedAt)->addMonth();

        for ($i = 0; $i < $terms; $i++) {
            ScheduledRepayment::create([
                'loan_id' => $loan->id,
                'amount' => $monthlyRepayment,
                'outstanding_amount' => $monthlyRepayment,
                'currency_code' => $currencyCode,
                'due_date' => $startDate->copy()->addMonths($i)->toDateString(),
                'status' => ScheduledRepayment::STATUS_DUE,  // Pastikan status sesuai dengan pengujian
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'deleted_at' => null,
            ]);
        }
    }

    public function repayLoan($loan, $receivedRepayment, $currencyCode, $receivedAt)
    {
        $receivedRepayment = (float)$receivedRepayment;

        // Fetch scheduled repayments in order
        $scheduledRepayments = $loan->scheduledRepayments()->where('status', ScheduledRepayment::STATUS_DUE)->orderBy('due_date')->get();

        foreach ($scheduledRepayments as $scheduledRepayment) {
            if ($receivedRepayment <= 0) {
                break;
            }

            $initialOutstandingAmount = $scheduledRepayment->outstanding_amount;

            if ($receivedRepayment >= $scheduledRepayment->outstanding_amount) {
                $receivedRepayment -= $scheduledRepayment->outstanding_amount;
                $scheduledRepayment->update([
                    'status' => ScheduledRepayment::STATUS_REPAID,
                    'outstanding_amount' => 0,
                    'updated_at' => Carbon::now(),
                ]);
            } else {
                $scheduledRepayment->update([
                    'status' => ScheduledRepayment::STATUS_PARTIAL,
                    'outstanding_amount' => $scheduledRepayment->outstanding_amount - $receivedRepayment,
                    'updated_at' => Carbon::now(),
                ]);
                $receivedRepayment = 0;
            }

            ReceivedRepayment::create([
                'loan_id' => $loan->id,
                'amount' => $initialOutstandingAmount - $scheduledRepayment->outstanding_amount,
                'currency_code' => $currencyCode,
                'received_at' => $receivedAt,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }

        $outstandingAmount = $loan->scheduledRepayments()->where('status', '!=', ScheduledRepayment::STATUS_REPAID)->sum('outstanding_amount');
        $loan->update([
            'outstanding_amount' => $outstandingAmount,
            'status' => $outstandingAmount > 0 ? Loan::STATUS_DUE : Loan::STATUS_REPAID,
            'updated_at' => Carbon::now(),
        ]);

        return $loan;
    }
}
