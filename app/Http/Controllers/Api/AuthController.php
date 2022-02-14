<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Validator;
use App\Http\Resources\UserResource;

class AuthController extends Controller
{
    public function __construct() {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    public function register(Request $request) 
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|between:2,100',
            'email' => 'required|string|email|max:100|unique:users',
            'password' => 'required|string|confirmed|min:6',
        ]);

        if ( $validator->fails() ) {
            return response()->json([
                'status' => 400,
                'message' => 'Validation failed',
                'error' => 'BAD REQUEST',
                'errors' => $validator->errors()
            ], 400);
        }

        $data = [
            'name' => $validator->safe()->name,
            'email' => $validator->safe()->email,
            'password' => bcrypt($request->password),
            'role' => env('TEST_USER_ROLE')
        ];

        try {

            $user = User::create( $data );

            return response()->json([
                'status' => 201,
                'message' => 'User successfully registered'
            ], 201);

        } catch(\Exception $e) {

            return response()->json([
                'status' => 417,
                'message' => 'Something went wrong'.$e,
                'error' => 'EXPECTATION FAILED'
            ], 417);

        }   
        
    }

    public function login ( Request $request ) 
    {
    	$validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ( $validator->fails() ) {
            return response()->json([
                'status' => 400,
                'message' => 'Validation failed',
                'error' => 'BAD REQUEST',
                'errors' => $validator->errors()
            ], 400);
        }

        if ( ! $token = auth()->attempt( $validator->validated() ) ) {
            return response()->json([
                'status' => 401,
                'message' => 'Email / Password invalid',
                'error' => 'UNAUTHORIZED'
            ], 401);
        }

        return response()->json([
            'status' => 200,
            'message' => 'Authorization successful',
            'data' => $this->createNewToken($token)
        ], 200);
    }  
    
    public function logout() 
    {
        try {

            auth()->logout();

            return response()->json([
                'status' => 200,
                'message' => 'User signed out successfully'
            ], 200);

        } catch(\Exception $e) {

            return response()->json([
                'status' => 417,
                'message' => 'Something went wrong. Please try again',
                'error' => 'EXPECTATION FAILED'
            ], 417);
        }
        
    }
   
    public function refresh() 
    {

        try {
            $refresh_token =  $this->createNewToken(auth()->refresh());

            return response()->json([
                'status' => 200,
                'message' => 'Re-Authorization successful',
                'data' => $refresh_token
            ], 200);

        } catch(\Exception $e) {

            return response()->json([
                'status' => 417,
                'message' => 'Something went wrong. Please try again',
                'error' => 'EXPECTATION FAILED'
            ], 417);

        }
    }
    
    public function userProfile() 
    {
        try {
            $user = auth()->user();

            return response()->json([
                'status' => 200,
                'message' => 'User found successfully',
                'data' => new UserResource($user)
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'status' => 417,
                'message' => 'Something went wrong. Please try again',
                'error' => 'EXPECTATION FAILED'
            ], 417);

        }
    }
   
    protected function createNewToken ( $token )
    {
        return [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ];
    }
}
