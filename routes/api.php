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

    Route::get('wallet/create/unsafe', 'WalletController@createUNSAFE');
});

Route::group(['prefix' => 'news',  'middleware' => 'api'], function() {
    Route::get('show', 'NewsFeedController@show');
    Route::post('create', 'NewsFeedController@create');
});

Route::group(['prefix' => 'currency',  'middleware' => 'api'], function() {
    Route::get('all', 'CurrencyController@index');
    Route::get('get/{name}', 'CurrencyController@show');
});

Route::group(['prefix' => 'wallet',  'middleware' => 'api'], function() {

    Route::get('get/user/{currencyid?}', 'WalletController@index');
    Route::post('create', 'WalletController@create');
    Route::get('delete/{walletid}', 'WalletController@delete');
    
});

Route::group(['prefix' => 'transaction',  'middleware' => 'api'], function() {

    Route::post('create/unsafe', 'TransactionController@createUNSAFE');
    Route::post('create/micro/unsafe', 'TransactionController@createMicroUNSAFE');
    
});

Route::group(['prefix' => 'admin',  'middleware' => 'api', 'check_user_role:' . \App\Role\UserRole::ROLE_ADMIN], function() {
    Route::get('users/list', 'AdminController@getAllUsers');
    Route::get('users/get/{userid}', 'AdminController@getUser');

    Route::post('users/edit/{userid}', 'AdminController@editUser');
    Route::post('users/edit/{userid}/role/add', 'AdminController@addRoleToUser');
    Route::post('users/edit/{userid}/role/remove', 'AdminController@removeRoleFromUser');

    Route::get('system/wallet/replenish', 'AdminController@replenishSystemAccount');
});

Route::group(['prefix' => 'support',  'middleware' => 'api'], function() {
    //Route::get('get/{name}', 'CurrencyController@show');

    Route::group(['prefix' => 'ticket'], function() {
        Route::get('index', 'SupportTicketController@index');
        Route::get('get/{ticketid}', 'SupportTicketController@show');
        Route::get('get/author/{userid?}', 'SupportTicketController@showByUser');
        Route::post('create', 'SupportTicketController@create');
        Route::post('create/{ticketid}/message', 'SupportTicketController@addMessage');
        Route::post('update/{ticketid}', 'SupportTicketController@update');
    });
    
});