<?php

namespace App\Repositories;

# MODELS
use App\Models\LoanApplication;
use App\Models\Loan;

# SERVICES
use App\Services\LoanService;
use App\Services\LoanAmortizationService;

# RESOURCE
use App\Http\Resources\LoanApplicationResource;

# MISC
use DB;
use Validator;
use Exception;

class LoanApplicationRepository 
{

    protected $loan_application;
    protected $loan;

    public function __construct(LoanApplication $loan_application, Loan $loan)
    {
        $this->loan_application = $loan_application;
        $this->loan = $loan;
    }

    public function getAll()
    {
        $user = auth()->user();

        if ( $user->role === 'admin' ) {
            $loanApplicationExists = $this->loan_application->latest()->get();
        } else {
            $loanApplicationExists = $this->loan_application->where('user_id', $user->id)->latest()->get();
        }

        if ( $loanApplicationExists->isEmpty() ) {
            return [
                'status' => 404,
                'message' => 'Loan Applications not found',
                'error' => 'NOT FOUND'
            ];
        }

        return [
            'status' => 200,
            'message' => 'Loan Applications found',
            'data' =>  LoanApplicationResource::collection($loanApplicationExists),
        ];

    }

    public function createOne($request, $user)
    {

        $validator = Validator::make($request->all(),[
            'amount' => 'required|numeric|min:1|max:100000',
            'description' => 'required|string',
            'repayment_period' => 'required|numeric|min:1|max:52',
            'interest_percentage' => 'required|numeric|min:0.1|max:100.0'
        ]);

        if ( $validator->fails() ) {
            return [
                'status' => 400,
                'message' => 'Validation failed',
                'error' => 'BAD REQUEST',
                'errors' => $validator->errors()
            ];       
        }

        $checkLoanApplicationExists = $this->loan_application->where('user_id', $user->id)
                                    ->whereIn('loan_application_status', ['SUBMITTED', 'PROCESSING'])
                                    ->get();

        if ( $checkLoanApplicationExists->isNotEmpty() ) {
            return [
                'status' => 409,
                'message' => 'Application for Loan already in process',
                'error' => 'CONFLICT'
            ];
        }

        try {
             
            $new_loan_application = LoanApplication::create([
                'user_id' => $user->id,
                'amount' => $validator->safe()->amount,
                'description' => $validator->safe()->description,
                'repayment_period' => $validator->safe()->repayment_period,
                'interest_percentage' => $validator->safe()->interest_percentage,
                'loan_application_status' => 'SUBMITTED'
             ]);
            
            return [
                'status' => 200,
                'message' => 'Application for Loan created successfully', 
                'data' => new LoanApplicationResource($new_loan_application)
            ];

        } catch ( Exception $e ) {
            return [
                'status' => 500,
                'message' => 'Something went wrong',
                'error' => 'INTERNAL SERVER ERROR'
            ];
        }

    }

    public function showOne($id)
    {
        $user = auth()->user();

        if ( $user->role === 'admin' ) {
            $loanApplicationExists = $this->loan_application->find($id);
        } else {
            $loanApplicationExists = $this->loan_application->where('user_id', $user->id)->find($id);
        }

        if ( is_null( $loanApplicationExists ) ) {
            return [
                'status' => 404,
                'message' => 'Application for Loan not found',
                'error' => 'NOT FOUND'
            ];
        }

        return [
            'status' => 200,
            'message' => 'Application for Loan found',
            'data' => new LoanApplicationResource($loanApplicationExists)
        ];
    }

    public function updateOne($request, $id, $user)
    {

        $validator = Validator::make($request->all(),[
            'amount' => 'required|numeric|min:1|max:100000',
            'description' => 'required|string',
            'repayment_period' => 'required|numeric|min:1|max:52',
            'interest_percentage' => 'required|numeric|min:0.1|max:100.0'
        ]);

        if ( $validator->fails() ) {
            return [
                'status' => 400,
                'message' => 'Validation failed',
                'error' => 'BAD REQUEST',
                'errors' => $validator->errors()
            ];       
        }

        $loanApplicationExists = $this->loan_application->where('user_id', $user->id)->find($id);

        if ( is_null( $loanApplicationExists ) ) {
            return [
                'status' => 404,
                'message' => 'Application for Loan not found',
                'error' => 'NOT FOUND'
            ];
        }

        if ( $loanApplicationExists->loan_application_status !== 'SUBMITTED' ) {
            return [
                'status' => 409,
                'message' => 'Application for Loan cannot be updated since status in '. $loanApplicationExists->loan_application_status,
                'error' => 'CONFLICT'
            ];
        }
           
        $loanApplicationExists->amount = $validator->safe()->amount;
        $loanApplicationExists->description = $validator->safe()->description;
        $loanApplicationExists->repayment_period = $validator->safe()->repayment_period;
        $loanApplicationExists->interest_percentage = $validator->safe()->interest_percentage;
        $loanApplicationExists->save();
        
        return [
           'status' => 200,
           'message' => 'Application for Loan updated successfully', 
            'data' => new LoanApplicationResource($loanApplicationExists)
        ];
    }

    public function approveOrRejectLoanApplication($request, $id, $user)
    {
        $validator = Validator::make($request->all(),[            
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

        $loanApplicationExists = $this->loan_application->find($id);

        if ( is_null( $loanApplicationExists ) ) {
            return [
                'status' => 404,
                'message' => 'Application for Loan not found',
                'error' => 'NOT FOUND'
            ];
        }

        $loanExistsForApplication = $this->loan->find($loanApplicationExists->id);

        if ( ! is_null( $loanExistsForApplication ) ) {
            return [
                'status' => 409,
                'message' => 'Loan already exists for Application',
                'error' => 'CONFLICT'
            ];       
        }

        $loanApplicationExists->loan_application_status = $validator->safe()->loan_application_status;
        $loanApplicationExists->save();

        if ( $validator->safe()->loan_application_status === 'APPROVED' ) {

            try {

                DB::beginTransaction();
                
                $new_loan = LoanService::createLoan($validator->safe(), $loanApplicationExists);

                if ( $new_loan['status'] === 200 ) {
                    $loanAmortization = LoanAmortizationService::createLoanAmortization($validator->safe(), $new_loan['data']['id']);
                }

                DB::commit();               

                return [
                    'status' => 200,
                    'message' => 'Application for Loan approved successfully',
                    'data' => $new_loan['data']
                ];

            } catch( Exception $e ) {

                DB::rollback();

                return [
                    'status' => 500,
                    'message' => 'Something went wrong'.$e,
                    'error' => 'EXPECTATION FAILED'
                ];

            }
            
        } else {

            return [
                'status' => 200,
                'message' => 'Application of Loan rejected',
                'data' => new LoanResource($loanExistsForApplication)
            ];

        }   
    }
}