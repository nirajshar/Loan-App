<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use JWTAuth;

class LoanApplicationTest extends TestCase
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

    # USER

    # Create one Loan Application for User
    public function testCreateOneLoanApplication()
    {        
        $token = $this->authenticate('user');
        $baseUrl = env('APP_URL') . env('APP_PORT') . '/api/v1/loan_applications';

        $headers['Content-Type'] = 'application/json';
        $headers['Accept'] = 'application/json';
        $headers['Authorization'] = 'Bearer ' . $token;

        $response = $this->json('POST', $baseUrl, [
            "amount" => 100,
            "description" => "Test",
            "repayment_period" => 4,
            "interest_percentage" => 10
        ], $headers);

        $response->assertStatus(200)->assertJsonStructure([
            'status', 
            'message', 
            'data' => [
                'id', 'amount', 'description', 'repayment_period', 'interest_percentage', 'created_at'
            ]
        ]);
    }

    # Create Multiple Loan Application for User
    public function testDisapproveMultipleLoanApplication()
    {        
        $token = $this->authenticate('user');
        $baseUrl = env('APP_URL') . env('APP_PORT') . '/api/v1/loan_applications';

        $headers['Content-Type'] = 'application/json';
        $headers['Accept'] = 'application/json';
        $headers['Authorization'] = 'Bearer ' . $token;

        $response = $this->json('POST', $baseUrl, [
            "amount" => 100,
            "description" => "Test",
            "repayment_period" => 4,
            "interest_percentage" => 10
        ], $headers);

        $response->assertStatus(409)->assertJsonStructure([
            'status', 
            'message', 
            'error'
        ]);
    }

    # Get All Loan Applications for User
    public function testGetAllLoanApplicationsForUser()
    {
        $token = $this->authenticate('user');
        $baseUrl = env('APP_URL') . env('APP_PORT') . '/api/v1/loan_applications';

        $headers['Content-Type'] = 'application/json';
        $headers['Accept'] = 'application/json';
        $headers['Authorization'] = 'Bearer ' . $token;

        $response = $this->json('GET', $baseUrl, [], $headers);

        $response->assertStatus(200)->assertJsonStructure([
            'status', 
            'message', 
            'data' => [
                ['id', 'amount', 'description', 'repayment_period', 'interest_percentage', 'created_at']
            ]
        ]);
    }

    # Get All Loan Applications for User
    public function testGetAllLoanApplicationsForAdmin()
    {
        $token = $this->authenticate('admin');
        $baseUrl = env('APP_URL') . env('APP_PORT') . '/api/v1/loan_applications';

        $headers['Content-Type'] = 'application/json';
        $headers['Accept'] = 'application/json';
        $headers['Authorization'] = 'Bearer ' . $token;

        $response = $this->json('GET', $baseUrl, [], $headers);

        $response->assertStatus(200)->assertJsonStructure([
            'status', 
            'message', 
            'data' => [
                ['id', 'amount', 'description', 'repayment_period', 'interest_percentage', 'created_at']
            ]
        ]);
    }

    # Get One Loan Applications By ID for User
    public function testGetOneLoanApplicationByID()
    {
        $token = $this->authenticate('user');
        $baseUrl = env('APP_URL') . env('APP_PORT') . '/api/v1/loan_applications/1';

        $headers['Content-Type'] = 'application/json';
        $headers['Accept'] = 'application/json';
        $headers['Authorization'] = 'Bearer ' . $token;

        $response = $this->json('GET', $baseUrl, [], $headers);

        $response->assertStatus(200)->assertJsonStructure([
            'status', 
            'message', 
            'data' => [
                'id', 'amount', 'description', 'repayment_period', 'interest_percentage', 'created_at'
            ]
        ]);
    }

    # Update One Loan Applications By ID for User
    public function testUpdateOneLoanApplicationByID()
    {
        $token = $this->authenticate('user');

        $allLoanApplicationsURL = env('APP_URL') . env('APP_PORT') . '/api/v1/loan_applications';

        $headers['Content-Type'] = 'application/json';
        $headers['Accept'] = 'application/json';
        $headers['Authorization'] = 'Bearer ' . $token;

        $response = $this->json('GET', $allLoanApplicationsURL, [], $headers);

        $allLoanAppsData = json_decode($response->getContent(), true)['data'][0];

        $baseUrl = env('APP_URL') . env('APP_PORT') . '/api/v1/loan_applications/'.$allLoanAppsData['id'];       

        $response = $this->json('PUT', $baseUrl, [
            "amount" => 200,
            "description" => "Added 100 More",
            "repayment_period" => 4,
            "interest_percentage" => 10
        ], $headers);

        $response->assertStatus(200)->assertJsonStructure([
            'status', 
            'message', 
            'data' => [
                'id', 'amount', 'description', 'repayment_period', 'interest_percentage', 'created_at'
            ]
        ]);
    }

    # ADMIN

    # Approve / Reject Users Loan Application
    public function testApproveRejectLoanApplication()
    {
        $token = $this->authenticate('admin');

        $allLoanApplicationsURL = env('APP_URL') . env('APP_PORT') . '/api/v1/loan_applications';

        $headers['Content-Type'] = 'application/json';
        $headers['Accept'] = 'application/json';
        $headers['Authorization'] = 'Bearer ' . $token;

        $allLoanApplicationsresponse = $this->json('GET', $allLoanApplicationsURL, [], $headers);

        $allLoanAppsData = json_decode($allLoanApplicationsresponse->getContent(), true)['data'][0];

        $baseUrl = env('APP_URL') . env('APP_PORT') . '/api/v1/loan_applications/'.$allLoanAppsData['id'].'/approve-reject';

        $response = $this->json('POST', $baseUrl, [
            "amount_approved" => 100,
            "repayment_period_approved" => 1,
            "principal_amount" => 100,
            "interest_percentage" => 10,
            "interest_amount" => 10,
            "loan_application_status" => "APPROVED"
        ], $headers);

        $response->assertStatus(200)->assertJsonStructure([
            'status', 
            'message', 
            'data' => [
                'id', 'loan_no', 'amount_asked', 'repayment_period_asked', 
                'amount_approved', 'repayment_period_approved', 'principal_amount', 
                'interest_amount', 'loan_status', 'created_at'
            ]
        ]);
    }

}
