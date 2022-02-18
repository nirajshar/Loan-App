<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use DB;
use Carbon\Carbon;
use App\Models\LoanApplication;
use App\Models\Loan;
use App\Services\LoanService;
use App\Services\LoanAmortizationService;
use App\Services\LoanApplicationService;
use App\Http\Resources\LoanApplicationResource;


class LoanApplicationController extends Controller
{

    protected $loanApplicationService;

    public function __construct(LoanApplicationService $loanApplicationService)
    {
        $this->loanApplicationService = $loanApplicationService;
    }
   
    public function index()
    {

        $response = [];

        try {
            $response = $this->loanApplicationService->getAll();
        } catch (Exception $e) {
            $response = [
                'status' => 500,
                'message' => 'Something went wrong',
                'error' => 'INTERNAL SERVER ERROR'
            ];
        }

        return response()->json($response, $response['status']);
       
    }

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

        $response = [];

        try {
            $response = $this->loanApplicationService->createOne($request, $user);
        } catch (Exception $e) {
            $response = [
                'status' => 500,
                'message' => 'Something went wrong',
                'error' => 'INTERNAL SERVER ERROR'
            ];
        }

        return response()->json($response, $response['status']);  
    }
   
    public function show($id)
    {
        $response = [];

        try {
            $response = $this->loanApplicationService->showOne($id);
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

        $user = auth()->user();

        if ( $user->role === 'admin' ) {
            return response()->json([
                'status' => 403,
                'message' => 'Fobidden resource for role',
                'error' => 'FORBIDDEN'
            ], 403);
        }      

        $response = [];

        try {
            $response = $this->loanApplicationService->updateOne($request, $id, $user);
        } catch (Exception $e) {
            $response = [
                'status' => 500,
                'message' => 'Something went wrong',
                'error' => 'INTERNAL SERVER ERROR'
            ];
        }

        return response()->json($response, $response['status']);
     
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

        $response = [];

        try {
            $response = $this->loanApplicationService->approveOrRejectLoanApplication($request, $id, $user);
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
