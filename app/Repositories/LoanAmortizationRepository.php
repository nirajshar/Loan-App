<?php

namespace App\Repositories;

# MODELS
use App\Models\LoanAmortization;

# SERVICES
use App\Services\LoanService;

# RESOURCE
use App\Http\Resources\LoanAmortizationResource;

# MISC
use DB;
use Validator;
use Exception;
use Carbon\Carbon;

class LoanAmortizationRepository 
{

    protected $loan_amortization;

    public function __construct(LoanAmortization $loan_amortization)
    {
        $this->loan_amortization = $loan_amortization;
    }

    public function createLoanAmortization( $loanAmortizationData, $loan_id )
    {

        try {           

            for ($i = 1; $i <= $loanAmortizationData->repayment_period_approved; $i++) {

                $loan = $this->loan_amortization->create([
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

        } catch( Exception $e ) {

            return [
                'status' => 500,
                'message' => 'Something went wrong',
                'error' => 'INTERNAL SERVER ERROR'
            ];

        }

    }

    public function deleteLoanAmortization($loan_id)
    {
        try {

            $this->loan_amortization->where('loan_id', $loan_id)->forceDelete();


            return [
                'status' => 200,
                'message' => 'Loan Amortization deleted successfully'
            ];

        } catch ( Exception $e ) {
            return [
                'status' => 500,
                'message' => 'Something went wrong',
                'error' => 'INTERNAL SERVER ERROR'
            ];
        }
    }

    public function payLoanAmortizationIfExists($loan_id, $amount_paid)
    {
        try {

            $loanAmortizationExists = $this->loan_amortization->where('loan_id', $loan_id)->whereNull('amount_paid')->first();

            if ( is_null( $loanAmortizationExists ) ) {
                return [
                    'status' => 404,
                    'message' => 'Loan Amortization not found',
                    'error' => 'NOT FOUND'
                ];
            }

            $loanAmortizationExists->amount_paid = $amount_paid;
            $loanAmortizationExists->paid_date = Carbon::now();
            $loanAmortizationExists->save();

            return [
                'status' => 200,
                'message' => 'Amortization Paid successfully',
                'data' => $loanAmortizationExists
            ];

        } catch ( Exception $e ) {
            return [
                'status' => 500,
                'message' => 'Something went wrong',
                'error' => 'INTERNAL SERVER ERROR'
            ];
        }
    }

}