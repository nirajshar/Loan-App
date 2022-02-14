<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use JWTAuth;

class AuthTest extends TestCase
{
    use WithFaker;

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

    # Register User Test
    public function testUserRegister()
    {
        $baseUrl = env('APP_URL') . env('APP_PORT') . '/api/v1/auth/register';       

        $headers['Content-Type'] = 'application/json';
        $headers['Accept'] = 'application/json';

        $password = $this->faker->password;

        $response = $this->json('POST', $baseUrl, [
            'name' => $this->faker->firstName(),
            'email' => $this->faker->email(),
            'password' => $password,
            'password_confirmation' => $password,
        ], $headers);

        // print_r($response->getContent());

        $response->assertStatus(201)->assertExactJson([
            "status" => 201,
            "message" => "User successfully registered"
        ]);
    }

    # Login Test
    public function testUserLogin()
    {
        $baseUrl = env('APP_URL') . env('APP_PORT') . '/api/v1/auth/login';
        $email = env('TEST_USER_EMAIL');
        $password = env('TEST_USER_PASSWORD');

        $headers['Content-Type'] = 'application/json';
        $headers['Accept'] = 'application/json';

        $response = $this->json('POST', $baseUrl, [
            'email' => $email,
            'password' => $password
        ], $headers);

        // print_r($response->getContent());

        $response->assertStatus(200)->assertJsonStructure([
            'status', 
            'message', 
            'data' => [
                'access_token', 'token_type', 'expires_in'
            ]
        ]);

    }

    # Get User Profile Test
    public function testUserUserProfile()
    {
        $token = $this->authenticate('user');
        $baseUrl = env('APP_URL') . env('APP_PORT') . '/api/v1/auth/user-profile';       

        $headers['Content-Type'] = 'application/json';
        $headers['Accept'] = 'application/json';
        $headers['Authorization'] = 'Bearer ' . $token;

        $response = $this->json('GET', $baseUrl, [],$headers);

        // print_r($response->getContent());

        $response->assertStatus(200)->assertJsonStructure([
            'status', 
            'message', 
            'data' => [
                'id', 'name', 'email', 'role', 'created_at', 'updated_at'
            ]
        ]);
    }

    # Refresh Token Test
    public function testUserRefresh()
    {
        $token = $this->authenticate('user');
        $baseUrl = env('APP_URL') . env('APP_PORT') . '/api/v1/auth/refresh';
        $headers['Authorization'] = 'Bearer ' . $token;

        $response = $this->withHeaders($headers)->json('POST', $baseUrl, []);

        // print_r($response->getContent());

        $response->assertStatus(200)->assertJsonStructure([
            'status', 
            'message', 
            'data' => [
                'access_token', 'token_type', 'expires_in'
            ]
        ]);
    }

    # Logout Test
    public function testUserLogout()
    {
        $token = $this->authenticate('user');
        $baseUrl = env('APP_URL') . env('APP_PORT') . '/api/v1/auth/logout';

        $headers['Content-Type'] = 'application/json';
        $headers['Accept'] = 'application/json';
        $headers['Authorization'] = 'Bearer ' . $token;

        $response = $this->withHeaders($headers)->json('POST', $baseUrl, []);

        // print_r($response->getContent());

        $response->assertStatus(200)->assertExactJson([
            "status" =>  200,
            "message" => "User signed out successfully"
        ]);
    }

    # ADMIN

    # Login Test
    public function testAdminLogin()
    {
        $baseUrl = env('APP_URL') . env('APP_PORT') . '/api/v1/auth/login';
        $email = env('TEST_ADMIN_EMAIL');
        $password = env('TEST_ADMIN_PASSWORD');

        $headers['Content-Type'] = 'application/json';
        $headers['Accept'] = 'application/json';

        $response = $this->json('POST', $baseUrl, [
            'email' => $email,
            'password' => $password
        ], $headers);

        // print_r($response->getContent());

        $response->assertStatus(200)->assertJsonStructure([
            'status', 
            'message', 
            'data' => [
                'access_token', 'token_type', 'expires_in'
            ]
        ]);
    }

}
