<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use Carbon\Carbon;
use App\Models\LoanAmortization;
use App\Models\Loan;
use App\Models\LoanApplication;

class LoanAmortizationController extends Controller
{
    public function submitPayment(Request $request)
    {
        $user = auth()->user();

        $validator = Validator::make($request->all(),[           
            'loan_no' => 'required|string',
            'amount_paid' => 'required|numeric'
        ]);

        if ( $validator->fails() ) {
            return response()->json([
                'status' => 400,
                'message' => 'Validation failed',
                'error' => 'BAD REQUEST',
                'errors' => $validator->errors()
            ]);       
        }

        $loan = Loan::where('loan_no', $validator->safe()->loan_no)
                    ->where('user_id', $user->id)
                    ->first();


        if ( is_null( $loan ) ) {
            return response()->json([
                'status' => 404,
                'messsage' => 'Loan not found',
                'error' => 'NOT FOUND'
            ], 404);
        }

        if ($loan->loan_status !== 'ACTIVE') {
            return response()->json([
                'status' => 409,
                'message' => 'Loan not Active',
                'error' => 'CONFLICT'
            ], 409);
        }

        $loan_amortization = LoanAmortization::where('loan_id', $loan->id)
                                                ->whereNull('amount_paid')
                                                ->first();

        if ( is_null( $loan_amortization ) ) {
            return response()->json([
                'status' => 404,
                'message' => 'Loan Amortization not found',
                'error' => 'NOT FOUND'
            ], 404);
        }

        try {

            $loan_amortization->amount_paid = $validator->safe()->amount_paid;
            $loan_amortization->paid_date = Carbon::now();
            $loan_amortization->save();

            $loan->balance_amount = $loan->balance_amount - $loan_amortization->amount_paid;
            $loan->save();

            if ( $loan->balance_amount === 0.00 ) {
                $loan->loan_status = 'CLOSED';
                $loan->save();
            }

            return response()->json([
                'status' => 200,
                'message' => 'Loan EWI paid successfully'
            ], 200);
        } catch(\Exception $e) {
            return response()->json([
                'status' => 417,
                'message' => 'Something went wrong'.$e,
                'error' => 'EXPECTATION FAILED'
            ], 417);
        }


    }
}
