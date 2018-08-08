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

  Route::post('auth/login', 'AuthController@authenticate');

});

Route::get('/', function () {
    return redirect('api');
});
