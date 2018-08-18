<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Company;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use JWTAuth, JWTFactory;
use Tymon\JWTAuth\Exceptions\JWTException;
use Hash, Validator, Auth;

class AuthController extends Controller
{
    public function __construct() {
        $this->middleware('jwt.auth', ['except' => ['authenticate']]);
    }

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

        // Generate Token
        $token = JWTAuth::fromUser($company, ['foo' => 'bar', 'baz' => 'bob']);
        header('Authorization: bearer ' . $token);
        
        // Get expiration time
        $objectToken = JWTAuth::setToken($token);
        $expiration = JWTAuth::decode($objectToken->getToken())->get('exp');

        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => date( 'Y-m-d H:i:s', $expiration)
        ]);

        // try {
        //     // Attempt to verify the credentials and create a token for the user
        //     if (! $token = JWTAuth::attempt($credentials)) {
        //         return response()->json([
        //             'status' => 'error', 
        //             'message' => 'We can`t find an account with this credentials.'
        //         ], 401);
        //     }
        // } catch (JWTException $e) {
        //     // Something went wrong with JWT Auth.
        //     return response()->json([
        //         'status' => 'error', 
        //         'message' => 'Failed to login, please try again.'
        //     ], 500);
        // }
        // // All good so return the token
        // header('Authorization: Bearer ' . $token);
        // return response()->json([
        //     'status' => 'success', 
        //     'data'=> [
        //         'token' => $token
        //         // You can add more details here as per you requirment. 
        //     ]
        // ]);
    }

    public function logout(Request $request)
    {
        $output = new \Symfony\Component\Console\Output\ConsoleOutput();
        $output->writeln("token:".$request);
        // $this->validate($request, [
        //     'token' => 'required'
        // ]);
        // $output = new \Symfony\Component\Console\Output\ConsoleOutput();
        // $output->writeln("token:".$request);
        try {
            JWTAuth::invalidate($request->token);
 
            return response()->json([
                'success' => true,
                'message' => 'User logged out successfully'
            ]);
        } catch (JWTException $exception) {
            return response()->json([
                'success' => false,
                'message' => 'Sorry, the user cannot be logged out'
            ], 500);
        }
    }

    public function me()
    {   
        $token = JWTAuth::getToken()->get();

        $user = Auth::user();
        $user['token'] = $token;

        $objectToken = JWTAuth::setToken($token);
        $expiration = JWTAuth::decode($objectToken->getToken())->get('exp');

        $user['expire_at'] = $expiration;

        return response()->json($user);
    }
}