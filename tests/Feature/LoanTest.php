<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use JWTAuth;

class LoanTest extends TestCase
{

    # SHARED 
    protected function authenticate($as = 'user')
    {
        if ( $as === 'admin' ) {

            $user = User::where('email', env('TEST_ADMIN_EMAIL'))->first();
            $token = JWTAuth::fromUser($user);

        } else {

            $user = User::where('email', env('TEST_USER_EMAIL'))->first();
            $token = JWTAuth::fromUser($user);

        }
       
        return $token;
    }

    # ADMIN

    # Get All Loans for User 
    public function testGetAllLoans()
    {
        $token = $this->authenticate('admin');

        $baseUrl = env('APP_URL') . env('APP_PORT') . '/api/v1/loans';

        $headers['Content-Type'] = 'application/json';
        $headers['Accept'] = 'application/json';
        $headers['Authorization'] = 'Bearer ' . $token;

        $response = $this->json('GET', $baseUrl, [], $headers);

        $response->assertStatus(200)->assertJsonStructure([
            'status', 
            'message', 
            'data' => [
                [
                    'id', 'loan_no', 'amount_asked', 'repayment_period_asked', 'amount_approved', 'repayment_period_approved',
                    'principal_amount', 'interest_amount', 'loan_status', 'created_at'
                ]
            ]
        ]);
    }

    # Get One Loan for User By ID
    public function testGetOneLoanByID()
    {
        $token = $this->authenticate('admin');

        $baseUrl = env('APP_URL') . env('APP_PORT') . '/api/v1/loans/1';

        $headers['Content-Type'] = 'application/json';
        $headers['Accept'] = 'application/json';
        $headers['Authorization'] = 'Bearer ' . $token;

        $response = $this->json('GET', $baseUrl, [], $headers);

        $response->assertStatus(200)->assertJsonStructure([
            'status', 
            'message', 
            'data' => [
                'id', 'loan_no', 'amount_asked', 'repayment_period_asked', 'amount_approved', 'repayment_period_approved',
                'principal_amount', 'interest_amount', 'loan_status', 'created_at'
            ]
        ]);
    }

    # Update One Loan By ID for User
    public function testUpdateOneLoanApplicationByID()
    {
        $token = $this->authenticate('admin');

        $baseUrl = env('APP_URL') . env('APP_PORT') . '/api/v1/loans/1';

        $headers['Content-Type'] = 'application/json';
        $headers['Accept'] = 'application/json';
        $headers['Authorization'] = 'Bearer ' . $token;

        $response = $this->json('PUT', $baseUrl, [
            "amount_approved" => 100,
            "repayment_period_approved" => 2,
            "principal_amount" => 100,
            "interest_percentage" => 10,
            "interest_amount" => 10,
            "loan_status" => "ACTIVE"
        ], $headers);

        $response->assertStatus(200)->assertJsonStructure([
            'status', 
            'message', 
            'data' => [
                'id', 'loan_no', 'amount_asked', 'repayment_period_asked', 'amount_approved', 'repayment_period_approved',
                'principal_amount', 'interest_amount', 'loan_status', 'created_at'
            ]
        ]);
    }

    # USER

    # Pay One Loan By Loan No 
    public function testPayEWI()
    {

        # Get All Loans 
        $adminToken = $this->authenticate('admin');
        $allLoansEndpoint = env('APP_URL') . env('APP_PORT') . '/api/v1/loans';

        $adminHeaders['Content-Type'] = 'application/json';
        $adminHeaders['Accept'] = 'application/json';
        $adminHeaders['Authorization'] = 'Bearer ' . $adminToken;       

        $adminResponse = $this->json('GET', $allLoansEndpoint, [], $adminHeaders);
        $allLoansData = json_decode($adminResponse->getContent(), true);   

        # Pay for Loan
        $userToken = $this->authenticate('user');
        $payURL = env('APP_URL') . env('APP_PORT') . '/api/v1/ewi/pay';
        
        $userHeaders['Content-Type'] = 'application/json';
        $userHeaders['Accept'] = 'application/json';
        $userHeaders['Authorization'] = 'Bearer ' . $userToken;       

        $userResponse = $this->json('POST', $payURL, [
            "loan_no" => $allLoansData['data'][0]['loan_no'],
            "amount_paid" => 55
        ], $userHeaders);

        $userResponse->assertStatus(200)->assertJsonStructure([
            'status', 
            'message'
        ]);
    }

    # Restrict to Only User for making Payment
    public function testDisapprovePayEWIForAdmin()
    {
        # Get All Loans 
        $adminToken = $this->authenticate('admin');
        $allLoansEndpoint = env('APP_URL') . env('APP_PORT') . '/api/v1/loans';

        $adminHeaders['Content-Type'] = 'application/json';
        $adminHeaders['Accept'] = 'application/json';
        $adminHeaders['Authorization'] = 'Bearer ' . $adminToken;       

        $adminResponse = $this->json('GET', $allLoansEndpoint, [], $adminHeaders);
        $allLoansData = json_decode($adminResponse->getContent(), true);   

        
        $payURL = env('APP_URL') . env('APP_PORT') . '/api/v1/ewi/pay';
        
        $userHeaders['Content-Type'] = 'application/json';
        $userHeaders['Accept'] = 'application/json';
        $userHeaders['Authorization'] = 'Bearer ' . $adminToken;       

        $userResponse = $this->json('POST', $payURL, [
            "loan_no" => $allLoansData['data'][0]['loan_no'],
            "amount_paid" => 55
        ], $userHeaders);

        $userResponse->assertStatus(403)->assertJsonStructure([
            'status', 
            'message',
            'error'
        ]);
    }
}
