<?php 

namespace App\Services;

use App\Transaction;
use Carbon\Carbon;
use App\Models\LoanAmortization;
use App\Http\Resources\LoanAmortizationResource;

class LoanAmortizationService {

    public function createLoanAmortization( $loanAmortizationData, $loan_id )
    {

        try {           

            for ($i = 1; $i <= $loanAmortizationData->repayment_period_approved; $i++) {

                $loan = LoanAmortization::create([
                    'loan_id' => $loan_id,
                    'amount_to_pay' => ($loanAmortizationData->amount_approved + $loanAmortizationData->interest_amount) / $loanAmortizationData->repayment_period_approved,
                    'interest_amount_to_pay' => $loanAmortizationData->interest_amount / $loanAmortizationData->repayment_period_approved,
                    'principal_amount_to_pay' => $loanAmortizationData->principal_amount / $loanAmortizationData->repayment_period_approved,
                    'due_date' => Carbon::now()->addWeeks($i),
                ]);
                
            }
           
            return [
                'status' => 200,
                'message' => 'Loan Amortization created successfully'                
            ];

        } catch(\Exception $e) {

            return [
                'status' => 417,
                'message' => 'Something went wrong',
                'error' => 'EXPECTATION FAILED'
            ];

        }

    }

    public function deleteAmortizationForLoan($loan)
    {
        try {

            LoanAmortization::where('loan_id', $loan->id)->forceDelete();

        } catch(\Exception $e) {
            return [
                'status' => 417,
                'message' => 'Something went wrong',
                'error' => 'EXPECTATION FAILED'
            ];
        }
    }

}