<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Hash, Validator;

class UserController extends Controller
{
    public function __construct() {
        $this->middleware('jwt.auth', ['except' => ['store']]);
    }
    
    public function index()
    {
        $users = User::all();
        return response()->json($users);
    }

    public function show($id)
    {
        $user = User::find($id);

        if(!$user) {
            return response()->json([
                'message'   => 'Record not found',
            ], 404);
        }

        return response()->json($user);
    }

    public function store(Request $request)
    {
        $data = $request->all();

        $validator = Validator::make($data, [
            'phone' => 'required|regex:/\+?\d{2}?\s?[(]?\d{2,3}[)]?\s?[9]\s?\d{4}[-]?\s?\d{4}/|unique:user',
            'password' => 'required',
        ]);

        if($validator->fails()) {
            return response()->json([
                'message'   => 'Validation Failed',
                'errors'    => $validator->errors()->all()
            ], 422);
        }

        $user = new User();
        $user->fill($data);
        $password = $request->password;
        $user->password = Hash::make($password);
        $user->save();

        return response()->json($user, 201);
    }

    public function update(Request $request, $id)
    {
        $user = Company::find($id);
        $data = $request->all();

        if(!$user) {
            return response()->json([
                'message'   => 'Record not found',
            ], 404);
        }

        if (array_key_exists('phone', $data) && $user->phone == $data['phone']) {
            unset($data['phone']);
        }

        $validator = Validator::make($data, [
            'name' => 'max:100',
            'phone' => 'regex:\+?\d{2}?\s?[(]?\d{2,3}[)]?\s?[9]\s?\d{4}[-]?\s?\d{4}|unique:user',
        ]);

        if($validator->fails()) {
            return response()->json([
                'message'   => 'Validation Failed',
                'errors'    => $validator->errors()->all()
            ], 422);
        }

        $user->fill($request->all());

        // Verify if exists a new password on the request
        if (array_key_exists('password', $data)) {
            $user->password = Hash::make($data['password']);
        }

        if(\Auth::user()->id != $user->id) {
            return response()->json([
                'message'   => 'You haven\'t permission to edit this entry',
            ], 401);
        }

        $user->save();

        return response()->json($company);
    }

    public function destroy($id)
    {
        $user = Company::find($id);

        if(!$user) {
            return response()->json([
                'message'   => 'Record not found',
            ], 404);
        }

        if(\Auth::user()->id != $user->id) {
            return response()->json([
                'message'   => 'You haven\'t permission to delete this entry',
            ], 401);
        }

        $user->delete();
    }
}
