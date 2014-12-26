<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the Closure to execute when that URI is requested.
|
*/

Route::get('/', function()
{
	return View::make('hello');
});
//

// Confide routes
Route::group(['prefix' => 'auth'], function ()
{
    // Get Requests
    Route::get('login', ['as' => 'auth.login', 'uses' => 'UsersController@login']);
    Route::get('confirm/{code}', ['as' => 'auth.confirm', 'uses' => 'UsersController@confirm']);
    Route::get('forgot_password', ['as' => 'auth.forgotPassword', 'uses' => 'UsersController@forgotPassword']);
    Route::get('reset_password/{token}', ['as' => 'auth.resetPassword', 'uses' => 'UsersController@resetPassword']);
    Route::get('logout', ['as' => 'auth.logout', 'uses' => 'UsersController@logout']);

    // Form Posts
    Route::group(['before' => 'csrf'], function ()
    {
        Route::post('login', ['as' => 'auth.doLogin', 'uses' => 'UsersController@doLogin']);
        Route::post('forgot_password', ['as' => 'auth.doForgotPassword', 'uses' => 'UsersController@doForgotPassword']);
        Route::post('reset_password', ['as' => 'auth.doResetPassword', 'uses' => 'UsersController@doResetPassword']);
    });
});

