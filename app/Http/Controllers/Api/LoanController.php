<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\LoanService;

class LoanController extends Controller
{

    protected $loanService;

    public function __construct(LoanService $loanService)
    {
        $this->loanService = $loanService;
    }
    
    public function index()
    {

        $response = [];

        try {
            $response = $this->loanService->getAll();
        } catch (Exception $e) {
            $response = [
                'status' => 500,
                'message' => 'Something went wrong',
                'error' => 'INTERNAL SERVER ERROR'
            ];
        }

        return response()->json($response, $response['status']);
        
    }

    # DEPRECATED
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

    public function show($id)
    {

        $response = [];

        try {
            $response = $this->loanService->showOne($id);
        } catch (Exception $e) {
            $response = [
                'status' => 500,
                'message' => 'Something went wrong',
                'error' => 'INTERNAL SERVER ERROR'
            ];
        }

        return response()->json($response, $response['status']);
        
    }

    public function update(Request $request, $id)
    {
        $response = [];

        try {
            $response = $this->loanService->updateOne($request, $id);
        } catch (Exception $e) {
            $response = [
                'status' => 500,
                'message' => 'Something went wrong',
                'error' => 'INTERNAL SERVER ERROR'
            ];
        }

        return response()->json($response, $response['status']);
    }

    # USER
    public function submitPayment(Request $request)
    {
        $user = auth()->user();

        $response = [];

        try {
            $response = $this->loanService->submitPayment($request, $user);
        } catch (Exception $e) {
            $response = [
                'status' => 500,
                'message' => 'Something went wrong',
                'error' => 'INTERNAL SERVER ERROR'
            ];
        }

        return response()->json($response, $response['status']);

    }

    
}
