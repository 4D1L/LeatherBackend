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

Route::group(['middleware' => 'api'], function () {
    /* 
    **   These routes do not need the JWT middleware as these generate tokens.
    */

    Route::post('/login', 'AuthController@login')->name('login');
    Route::post('/register', 'AuthController@register');

    Route::get('me', 'UserController@me');

    // TODO: REMOVE ONE DAY
    Route::get('roles/give/admin', function() {
        $user = App\User::where('id', 1)->first();
        $user->addRole(\App\Role\UserRole::ROLE_ADMIN);
        $user->save();

        return response()->json(["message" => "Assigned"]);
    });
});

Route::group(['prefix' => 'news',  'middleware' => 'api'], function() {
    Route::get('show', 'NewsFeedController@show');
    Route::post('create', 'NewsFeedController@create');
});

Route::group(['prefix' => 'currency',  'middleware' => 'api'], function() {
    Route::get('all', 'CurrencyController@index');
    Route::get('get/{name}', 'CurrencyController@show');
});

Route::group(['prefix' => 'admin',  'middleware' => 'api', 'check_user_role:' . \App\Role\UserRole::ROLE_ADMIN], function() {
    Route::get('users/list', 'AdminController@getAllUsers');
    Route::get('users/get/{userid}', 'AdminController@getUser');
});
