<?php 

namespace App\Services;

use App\Transaction;
use Carbon\Carbon;
use App\Models\Loan;
use App\Http\Resources\LoanResource;

class LoanService {

    public function createLoan( $loanData, $loan_application )
    {

        try {

            $loan = Loan::create([
                'loan_no' => uniqid(),
                'user_id' => $loan_application->user->id,
                'loan_application_id' => $loan_application->id, 
                'amount_asked' => $loan_application->amount, 
                'repayment_period_asked' => $loan_application->repayment_period, 
                'amount_approved' => $loanData->amount_approved, 
                'repayment_period_approved' => $loanData->repayment_period_approved, 
                'principal_amount' => $loanData->principal_amount, 
                'interest_percentage' => $loanData->interest_percentage,
                'interest_amount' => $loanData->interest_amount,                 
                'loan_status' => 'ACTIVE', 
                'balance_amount' => $loanData->principal_amount + $loanData->interest_amount, 
            ]);
            
            
            return [
                'status' => 200,
                'message' => 'Application of Loan approved. Loan created successfully',
                'data' => new LoanResource($loan)
            ];

        } catch(\Exception $e) {

            return [
                'status' => 417,
                'message' => 'Something went wrong',
                'error' => 'EXPECTATION FAILED'
            ];

        }

    }

}