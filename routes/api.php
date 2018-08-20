<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::group(array('prefix' => 'api'), function()
{

  Route::get('/', function () {
      return response()->json(['message' => 'Jobs API', 'status' => 'Connected']);;
  });

  Route::resource('jobs', 'JobsController');
  Route::resource('companies', 'CompaniesController');
  Route::resource('user', 'UserController');

  Route::post('auth/login', 'AuthController@authenticate');
  Route::post('auth/loginuser', 'AuthController@authenticateUser');
  Route::get('auth/logout', 'AuthController@logout');
  Route::get('auth/logoutuser', 'AuthController@logoutUser');
  Route::get('auth/me', 'AuthController@me');
});

Route::get('/', function () {
    return redirect('api');
});
