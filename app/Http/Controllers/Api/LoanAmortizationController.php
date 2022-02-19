<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\LoanAmortizationService;

class LoanAmortizationController extends Controller
{

    protected $loanAmortizationService;

    public function __construct(LoanAmortizationService $loanAmortizationService)
    {
        $this->loanAmortizationService = $loanAmortizationService;
    }

    public function submitPayment(Request $request)
    {
        $user = auth()->user();

        $response = [];

        try {
            $response = $this->loanAmortizationService->submitPayment($request, $user);
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
