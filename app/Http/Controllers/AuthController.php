<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Company;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use JWTAuth, JWTFactory;
use Hash, Validator;

class AuthController extends Controller
{
    public function authenticate(Request $request) {
      // Get only email and password from request
      $credentials = $request->only('email', 'password');

        $validator = Validator::make($credentials, [
            'password' => 'required',
            'email' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message'   => 'Invalid credentials',
                'errors'        => $validator->errors()->all()
            ], 422);
        }

      // Get user by email
      $company = Company::where('email', $credentials['email'])->first();

      // Validate Company
      if(!$company) {
        return response()->json([
          'error' => 'Invalid credentials'
        ], 401);
      }

      // Validate Password
      if (!Hash::check($credentials['password'], $company->password)) {
          return response()->json([
            'error' => 'Invalid credentials'
          ], 401);
      }

    //   // Generate Token
    //   $token = JWTAuth::fromUser($company, ['foo' => 'bar', 'baz' => 'bob']);
      
    //   // Get expiration time
    //   $objectToken = JWTAuth::setToken($token);
    //   $expiration = JWTAuth::decode($objectToken->getToken())->get('exp');

    //   return response()->json([
    //     'access_token' => $token,
    //     'token_type' => 'bearer',
    //     'expires_in' => $expiration
    //   ]);

        try {
            // Attempt to verify the credentials and create a token for the user
            if (! $token = JWTAuth::attempt($credentials)) {
                return response()->json([
                    'status' => 'error', 
                    'message' => 'We can`t find an account with this credentials.'
                ], 401);
            }
        } catch (JWTException $e) {
            // Something went wrong with JWT Auth.
            return response()->json([
                'status' => 'error', 
                'message' => 'Failed to login, please try again.'
            ], 500);
        }
        // All good so return the token
        return response()->json([
            'status' => 'success', 
            'data'=> [
                'token' => $token
                // You can add more details here as per you requirment. 
            ]
        ]);
    }
}