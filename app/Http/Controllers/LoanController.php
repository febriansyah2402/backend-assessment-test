<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use Illuminate\Http\Request;

class LoanController extends Controller
{
    public function createLoan(Request $request)
    {
        $loan = Loan::create([
            'user_id' => $request->user()->id,
            'amount' => $request->amount,
            'terms' => $request->terms,
            'outstanding_amount' => $request->amount,
            'currency_code' => $request->currency_code,
            'processed_at' => now(),
            'status' => 'active'
        ]);

        $monthlyAmount = $loan->amount / $loan->terms;
        for ($i = 1; $i <= $loan->terms; $i++) {
            $loan->scheduledRepayments()->create([
                'amount' => $monthlyAmount,
                'due_date' => now()->addMonths($i)
            ]);
        }

        return response()->json($loan, 201);
    }

    public function repayLoan(Request $request, Loan $loan)
    {
        $amount = $request->amount;

        foreach ($loan->scheduledRepayments as $repayment) {
            if ($amount <= 0) {
                break;
            }

            $remaining = $repayment->amount - $repayment->receivedRepayments->sum('amount');
            $paymentAmount = min($amount, $remaining);

            $repayment->receivedRepayments()->create([
                'amount' => $paymentAmount,
                'payment_date' => now()
            ]);

            $amount -= $paymentAmount;
        }

        $loan->outstanding_amount -= $request->amount;
        if ($loan->outstanding_amount <= 0) {
            $loan->status = 'closed';
        }
        $loan->save();

        return response()->json($loan);
    }
}
