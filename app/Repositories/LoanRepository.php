<?php

namespace App\Repositories;

# MODELS
use App\Models\Loan;

# SERVICES
use App\Services\LoanAmortizationService;

# RESOURCE
use App\Http\Resources\LoanResource;

# MISC
use DB;
use Validator;
use Exception;

class LoanRepository 
{

    protected $loan;
    protected $loanAmortizationService;

    public function __construct(Loan $loan, LoanAmortizationService $loanAmortizationService)
    {
        $this->loan = $loan;
        $this->loanAmortizationService = $loanAmortizationService;
    }

    public function getAll()
    {
        $loanExists = $this->loan->latest()->get();

        if ( $loanExists->isEmpty() ) {
            return [
                'status' => 404,
                'message' => 'Loans not found',
                'error' => 'NOT FOUND'
            ];
        }

        return [
            'status' => 200,
            'message' => 'Loans found',
            'data' => LoanResource::collection($loanExists)
        ];
    }

    public function createOne( $loanData, $loan_application )
    {

        $validator = Validator::make($loanData->all(),[            
            'amount_approved' => 'required|min:0|max:52',
            'repayment_period_approved' => 'required|min:0|max:52',            
            'principal_amount' => 'required|min:0|max:100000',
            'interest_percentage' => 'required|numeric|min:0.1|max:100.0',
            'interest_amount' => 'required|min:0|max:100000',
            'loan_application_status' => 'required|string|in:APPROVED,REJECTED,PROCESSING'
        ]);

        if ( $validator->fails() ) {
            return [
                'status' => 400,
                'message' => 'Validation failed',
                'error' => 'BAD REQUEST',
                'errors' => $validator->errors()
            ];       
        }


        try {

            $new_loan = $this->loan->create([
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

            $loanAmortization = $this->loanAmortizationService->createLoanAmortization($validator->safe(), $new_loan->id);            
            
            return [
                'status' => 200,
                'message' => 'Application of Loan approved. Loan created successfully',
                'data' => new LoanResource($new_loan)
            ];

        } catch( Exception $e ) {

            return [
                'status' => 500,
                'message' => 'Something went wrong',
                'error' => 'INTERNAL SERVER ERROR'
            ];

        }

    }

    public function showOne($id)
    {
        $loanExists = $this->loan->find($id);

        if ( is_null( $loanExists ) ) {
            return [
                'status' => 404,
                'message' => 'Loan not found',
                'error' => 'NOT FOUND'
            ]; 
        }

        return [
            'status' => 200,
            'message' => 'Loan found',
            'data' => new LoanResource($loanExists)
        ];
    }

    public function getLoanForLoanApplicationID($loan_application_id)
    {
        $loanExists = $this->loan->where('loan_application_id',$loan_application_id)->first();

        if ( is_null( $loanExists ) ) {
            return [
                'status' => 404,
                'message' => 'Loan not found',
                'error' => 'NOT FOUND'
            ]; 
        }

        return [
            'status' => 200,
            'message' => 'Loan found',
            'data' => new LoanResource($loanExists)
        ];
    }

    public function updateOne($request, $id)
    {
        $validator = Validator::make($request->all(),[           
            'amount_approved' => 'required|min:0|max:52',
            'repayment_period_approved' => 'required|min:0|max:52',            
            'principal_amount' => 'required|min:0|max:100000',
            'interest_percentage' => 'required|numeric|min:0.1|max:100.0',
            'interest_amount' => 'required|min:0|max:100000',
            'loan_status' => 'required|string|in:ACTIVE,CLOSED,SETTLED'
        ]);

        if ( $validator->fails() ) {
            return response()->json([
                'status' => 400,
                'message' => 'Validation failed',
                'error' => 'BAD REQUEST',
                'errors' => $validator->errors()
            ]);       
        }

        $loanExists = $this->loan->find($id);

        if ( is_null( $loanExists ) ) {
            return [
                'status' => 404,
                'message' => 'Loan not found',
                'error' => 'NOT FOUND'
            ]; 
        }

        try {

            DB::beginTransaction();

            $loanExists->amount_approved = $validator->safe()->amount_approved;
            $loanExists->repayment_period_approved = $validator->safe()->repayment_period_approved;
            $loanExists->principal_amount = $validator->safe()->principal_amount;
            $loanExists->principal_amount = $validator->safe()->interest_percentage;
            $loanExists->interest_amount = $validator->safe()->interest_amount;
            $loanExists->loan_status = $validator->safe()->loan_status;
            $loanExists->save();

            $this->loanAmortizationService->deleteLoanAmortization($loanExists->id);
            $loanAmortization = $this->loanAmortizationService->createLoanAmortization($validator->safe(), $loanExists->id);

            DB::commit();             
            
            return [
                'status' => 200,
                'message' => 'Loan updated successfully', 
                'data' => new LoanResource($loanExists)
            ];
            
        } catch( Exception $e ) {
    
            DB::rollback();

            return [
                'status' => 500,
                'message' => 'Something went wrong',
                'error' => 'INTERNAL SERVER ERROR'
            ];
        }
    }

    public function getLoanByLoanNoWithUserID($loan_no, $user_id)
    {

        if ( trim($loan_no) === '' || trim($user_id) === '') {
            return [
                'status' => 400,
                'message' => 'Loan no / User missing',
                'error' => 'BAD REQUEST'
            ];
        }

        $loanExists = $this->loan->where('loan_no', $loan_no)->where('user_id', $user_id)->first();

        if ( is_null( $loanExists ) ) {
            return [
                'status' => 404,
                'message' => 'Loan not found',
                'error' => 'NOT FOUND'
            ]; 
        }

        return [
            'status' => 200,
            'message' => 'Loan found',
            'data' => new LoanResource($loanExists)
        ];
    }

    public function submitPayment($request, $user)
    {
        $validator = Validator::make($request->all(),[           
            'loan_no' => 'required|string',
            'amount_paid' => 'required|numeric'
        ]);

        if ( $validator->fails() ) {
            return [
                'status' => 400,
                'message' => 'Validation failed',
                'error' => 'BAD REQUEST',
                'errors' => $validator->errors()
            ];       
        }
      
        $loanExists = $this->loan->where('loan_no', $validator->safe()->loan_no)->where('user_id', $user->id)->first();

        if ( is_null( $loanExists ) ) {
            return [
                'status' => 404,
                'messsage' => 'Loan not found',
                'error' => 'NOT FOUND'
            ];
        }

        if ($loanExists->loan_status !== 'ACTIVE') {
            return [
                'status' => 409,
                'message' => 'Loan not Active',
                'error' => 'CONFLICT'
            ];
        }        
        
        
        try {
            
            DB::beginTransaction();
            
            $loanAmortizationExists = $this->loanAmortizationService->payLoanAmortizationIfExists($loanExists->id, $validator->safe()->amount_paid);
            
            if ( $loanAmortizationExists['status'] == 200 ) {

                $loanExists->balance_amount = $loanExists->balance_amount - $validator->safe()->amount_paid;
                $loanExists->save();
    
                if ( $loanExists->balance_amount === 0.00 ) {
                    $loanExists->loan_status = 'CLOSED';
                    $loanExists->save();
                }
                    
                DB::commit();               
                
                return [
                    'status' => 200,
                    'message' => 'Loan EWI paid successfully'
                ];

            } else {
                DB::rollback();

                return [
                    'status' => $loanAmortizationExists['status'] ? $loanAmortizationExists['status'] : 500,
                    'message' => $loanAmortizationExists['message'] ? $loanAmortizationExists['message'] : 'Something went wrong',
                    'error' =>  $loanAmortizationExists['error'] ? $loanAmortizationExists['error'] : 'INTERNAL SERVER ERROR',
                ];
            }

           
        } catch ( Exception $e ) {
            
            DB::rollback();
            
            return [
                'status' => 500,
                'message' => 'Something went wrong',
                'error' => 'INTERNAL SERVER ERROR'
            ];
        }


    }

}