<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\LoanApplicationResource;
use Validator;
use DB;
use Carbon\Carbon;
use App\Models\LoanApplication;
use App\Models\Loan;
use App\Services\LoanService;
use App\Services\LoanAmortizationService;

class LoanApplicationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = auth()->user();

        if ( $user->role === 'admin' ) {
            $loan_applications = LoanApplication::latest()->get();
        } else {            
            $loan_applications = LoanApplication::where('user_id', $user->id)->latest()->get();
        }

        if ( $loan_applications->isEmpty() ) {
            return response()->json([
                'status' => 404,
                'message' => 'Loan Applications not found',
                'error' => 'NOT FOUND'
            ], 404);
        }

        return response()->json([
            'status' => 200,
            'message' => 'Loan Applications found',
            'data' =>  LoanApplicationResource::collection($loan_applications),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $user = auth()->user();

        if ( $user->role === 'admin' ) {
            return response()->json([
                'status' => 403,
                'message' => 'Fobidden resource for role',
                'error' => 'FORBIDDEN'
            ], 403);
        }

        $loan_application = LoanApplication::where('user_id', $user->id)
                            ->whereIn('loan_application_status', ['SUBMITTED', 'PROCESSING'])
                            ->get();

        if ( $loan_application->isNotEmpty() ) {
            return response()->json([
                'status' => 409,
                'message' => 'Application for Loan already in process',
                'error' => 'CONFLICT'
            ], 409);
        }

        $validator = Validator::make($request->all(),[
            'amount' => 'required|numeric|min:1|max:100000',
            'description' => 'required|string',
            'repayment_period' => 'required|numeric|min:1|max:52',
            'interest_percentage' => 'required|numeric|min:0.1|max:100.0'
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
             
            $loan_application = LoanApplication::create([
                'user_id' => auth()->user()->id,
                'amount' => $validator->safe()->amount,
                'description' => $validator->safe()->description,
                'repayment_period' => $validator->safe()->repayment_period,
                'interest_percentage' => $validator->safe()->interest_percentage,
                'loan_application_status' => 'SUBMITTED'
             ]);
            
            return response()->json([
                'status' => 200,
                'message' => 'Application for Loan created successfully', 
                'data' => new LoanApplicationResource($loan_application)
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 417,
                'message' => 'Something went wrong '.$e,
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
        $user = auth()->user();

        if ( $user->role === 'admin' ) {
            $loan_application = LoanApplication::find($id);
        } else {
            $loan_application = LoanApplication::where('user_id', $user->id)->find($id);
        }

        if ( is_null( $loan_application ) ) {
            return response()->json([
                'status' => 404,
                'message' => 'Application for Loan not found',
                'error' => 'NOT FOUND'
            ], 404);
        }

        return response()->json([
            'status' => 200,
            'message' => 'Application for Loan found',
            'data' => new LoanApplicationResource($loan_application)
        ], 200);
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

        $user = auth()->user();

        if ( $user->role === 'admin' ) {
            return response()->json([
                'status' => 403,
                'message' => 'Fobidden resource for role',
                'error' => 'FORBIDDEN'
            ], 403);
        }

        $loan_application = LoanApplication::where('user_id', $user->id)->find($id);

        if ( is_null( $loan_application ) ) {
            return response()->json([
                'status' => 404,
                'message' => 'Application for Loan not found',
                'error' => 'NOT FOUND'
            ], 404);
        }

        if ( $loan_application->loan_application_status !== 'SUBMITTED' ) {
            return response()->json([
                'status' => 409,
                'message' => 'Application for Loan cannot be updated since status in '. $loan_application->loan_application_status,
                'error' => 'CONFLICT'
            ], 409);
        }

        $validator = Validator::make($request->all(),[
            'amount' => 'required|numeric|min:1|max:100000',
            'description' => 'required|string',
            'repayment_period' => 'required|numeric|min:1|max:52',
            'interest_percentage' => 'required|numeric|min:0.1|max:100.0'
        ]);

        if ( $validator->fails() ) {
            return response()->json([
                'status' => 400,
                'message' => 'Validation failed',
                'error' => 'BAD REQUEST',
                'errors' => $validator->errors()
            ]);       
        }
        
        $loan_application->amount = $validator->safe()->amount;
        $loan_application->description = $validator->safe()->description;
        $loan_application->repayment_period = $validator->safe()->repayment_period;
        $loan_application->interest_percentage = $validator->safe()->interest_percentage;
        $loan_application->save();
        
        return response()->json([
           'status' => 200,
           'message' => 'Application for Loan updated successfully', 
            'data' => new LoanApplicationResource($loan_application)
        ], );
    }


    # ADMIN FUNCTIONS
    
    public function approveRejectLoanApplication(Request $request, $id)
    {
        $user = auth()->user();

        if ( $user->role !== 'admin' ) {
            return response()->json([
                'status' => 403,
                'message' => 'Fobidden resource for role',
                'error' => 'FORBIDDEN'
            ], 403);
        }

        $loan_application = LoanApplication::find($id);

        if ( is_null( $loan_application ) ) {
            return response()->json([
                'status' => 404,
                'message' => 'Application for Loan not found',
                'error' => 'NOT FOUND'
            ], 404);
        }

        $validator = Validator::make($request->all(),[            
            'amount_approved' => 'required|min:0|max:52',
            'repayment_period_approved' => 'required|min:0|max:52',            
            'principal_amount' => 'required|min:0|max:100000',
            'interest_percentage' => 'required|numeric|min:0.1|max:100.0',
            'interest_amount' => 'required|min:0|max:100000',
            'loan_application_status' => 'required|string|in:APPROVED,REJECTED,PROCESSING'
        ]);

        if ( $validator->fails() ) {
            return response()->json([
                'status' => 400,
                'message' => 'Validation failed',
                'error' => 'BAD REQUEST',
                'errors' => $validator->errors()
            ], 400);       
        }

        $loanExistsForApplication = Loan::find($loan_application->id);

        if ( ! is_null( $loanExistsForApplication ) ) {
            return response()->json([
                'status' => 409,
                'message' => 'Loan already exists for Application',
                'error' => 'CONFLICT'
            ], 409);       
        }

        $loan_application->loan_application_status = $validator->safe()->loan_application_status;
        $loan_application->save();

        if ( $validator->safe()->loan_application_status === 'APPROVED' ) {

            try {

                DB::beginTransaction();
                
                $loan = LoanService::createLoan($validator->safe(), $loan_application);

                if ( $loan['status'] === 200 ) {
                    $loanAmortization = LoanAmortizationService::createLoanAmortization($validator->safe(), $loan['data']['id']);
                }

                DB::commit();               

                return response()->json([
                    'status' => 200,
                    'message' => 'Application for Loan approved successfully',
                    'data' => $loan['data']
                ], 200);

            } catch(\Exception $e) {

                DB::rollback();

                return response()->json([
                    'status' => 417,
                    'message' => 'Something went wrong'.$e,
                    'error' => 'EXPECTATION FAILED'
                ], 417);

            }
            
        } else {

            return response()->json([
                'status' => 200,
                'message' => 'Application of Loan rejected',
                'data' => new LoanResource($loan)
            ], 200);


        }   
      
    }
    
}
