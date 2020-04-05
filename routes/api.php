<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['middleware' => 'api-header'], function () {
    /* 
    **   These routes do not need the JWT middleware as these generate tokens.
    */

    Route::post('/login', 'UserController@login');
    Route::post('/register', 'UserController@register');
});

Route::group(['middleware' => ['jwt.auth','api-header']], function () {
    /*
    **  Routes that require the use of tokens are defined here.
    */

    Route::get('users/list', function(){
        $users = App\User::all();
        
        $response = [
            'success'=>true, 
            'data'=>$users
        ];
        return response()->json($response, 201);
    });
});