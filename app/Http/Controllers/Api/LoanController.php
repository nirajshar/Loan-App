<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use App\Models\Loan;
use App\Models\LoanApplication;
use App\Http\Resources\LoanResource;
use DB;
use App\Services\LoanAmortizationService;

class LoanController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $loans = Loan::latest()->get();

        if ( $loans->isEmpty() ) {
            return response()->json([
                'status' => 404,
                'message' => 'Loans not found',
                'error' => 'NOT FOUND'
            ], 404);
        }

        return response()->json([
            'status' => 200,
            'message' => 'Loans found',
            'data' => LoanResource::collection($loans)
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'loan_application_id' => 'required|numeric|unique:loans,loan_application_id',
            'amount_asked' => 'required|numeric|min:1|max:100000',
            'repayment_period_asked' => 'required|min:1',
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

        $loan_application = LoanApplication::find($validator->safe()->loan_application_id);

        if ( is_null($loan_application) ) {
            return response()->json([
                'status' => 404,
                'message' => 'Application for Loan not found',
                'error' => 'NOT FOUND'
            ], 404);
        }

        try {

            $loan = Loan::create([
                'loan_no' => uniqid(),
                'loan_application_id' => $validator->safe()->loan_application_id, 
                'amount_asked' => $validator->safe()->amount_asked, 
                'repayment_period_asked' => $validator->safe()->repayment_period_asked, 
                'amount_approved' => $validator->safe()->amount_approved, 
                'repayment_period_approved' => $validator->safe()->repayment_period_approved, 
                'principal_amount' => $validator->safe()->principal_amount, 
                'interest_percentage' => $validator->safe()->interest_percentage,
                'interest_amount' => $validator->safe()->interest_amount, 
                'loan_status' => $validator->safe()->loan_status, 
             ]);
            
            return response()->json([
                'status' => 200,
                'message' => 'Loan created successfully',
                'data' => new LoanResource($loan)
            ], 200);
        } catch(\Exception $e) {
            return response()->json([
                'status' => 417,
                'message' => 'Something went wrong '. $e,
                'error' => 'EXPECTATION FAILED'
            ], 417);
        }

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $loan = Loan::find($id);

        if ( is_null( $loan ) ) {
            return response()->json([
                'status' => 404,
                'message' => 'Loan not found',
                'error' => 'NOT FOUND'
            ], 404); 
        }


        return response()->json([
            'status' => 200,
            'message' => 'Loan found',
            'data' => new LoanResource($loan)
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {

        $loan = Loan::find($id);

        if ( is_null( $loan ) ) {
            return response()->json([
                'status' => 404,
                'message' => 'Loan not found',
                'error' => 'NOT FOUND'
            ], 404); 
        }

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


        try {

            DB::beginTransaction();

            $loan->amount_approved = $validator->safe()->amount_approved;
            $loan->repayment_period_approved = $validator->safe()->repayment_period_approved;
            $loan->principal_amount = $validator->safe()->principal_amount;
            $loan->principal_amount = $validator->safe()->interest_percentage;
            $loan->interest_amount = $validator->safe()->interest_amount;
            $loan->loan_status = $validator->safe()->loan_status;

            $loan->save();

            LoanAmortizationService::deleteAmortizationForLoan($loan);
            $loanAmortization = LoanAmortizationService::createLoanAmortization($validator->safe(), $loan->id);

            DB::commit();             
            
            return response()->json([
                'status' => 200,
                'message' => 'Loan updated successfully', 
                'data' => new LoanResource($loan)
            ], 200);
            
        } catch(\Exception $e) {
    
            DB::rollback();

            return response()->json([
                'status' => 417,
                'message' => 'Something went wrong',
                'error' => 'EXPECTATION FAILED'.$e
            ], 417);
        }
    }

    
}
