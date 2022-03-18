<?php

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

// Authentication
Route::get('/initsetup', function () {
    return Setting::all();
});
Route::post('/register' ,       'ProviderAuth\TokenController@register');
Route::post('/oauth/token' ,    'ProviderAuth\TokenController@authenticate');
Route::post('/verify' ,         'ProviderAuth\TokenController@verify');
Route::post('/checkemail' ,     'ProviderAuth\TokenController@verifyemail');
Route::post('/checkmobile' ,     'ProviderAuth\TokenController@verifymobile');
Route::post('/register/otp' , 'ProviderAuth\TokenController@OTP');

Route::post('/auth/facebook','ProviderAuth\TokenController@facebookViaAPI');
Route::post('/auth/google',  'ProviderAuth\TokenController@googleViaAPI');
Route::post('/auth/apple','ProviderAuth\TokenController@appleViaAPI');

Route::post('/forgot/password',     'ProviderAuth\TokenController@forgot_password');
Route::post('/reset/password',      'ProviderAuth\TokenController@reset_password');

 Route::post('/subscription/pay', 'SubscriptionController@subscription_api')->name('subscription.pay');
 Route::get('/subscription/wayforpay', 'SubscriptionController@sub_wayforpau');

Route::group(['middleware' => ['provider.api']], function () {

    Route::get('document', 'ProviderResources\DocumentController@index');

    Route::post('/update/document', 'ProviderResources\DocumentController@updateApi');

    Route::group(['prefix' => 'profile'], function () {

        Route::get ('/' ,           'ProviderResources\ProfileController@index');
        Route::post('/' ,           'ProviderResources\ProfileController@update');
        Route::post('/password' ,   'ProviderResources\ProfileController@password');
        Route::post('/location' ,   'ProviderResources\ProfileController@location');
        Route::post('/available' ,  'ProviderResources\ProfileController@available');

    });

    Route::get('/target' , 'ProviderApiController@target');
    
    Route::resource('providercard', 'Resource\ProviderCardResource');
    Route::get('/subscription', 'SubscriptionController@subscription_plans');
   

    Route::get ('/services' ,    'ProviderApiController@services');
    Route::get ('/user' ,    'ProviderApiController@user');
    Route::post ('/update/service' ,    'ProviderApiController@update_services');
    Route::post('/logout' , 'ProviderAuth\TokenController@logout');
    Route::resource('trip', 'ProviderResources\TripController');
    Route::post('cancel', 'ProviderResources\TripController@cancel');
    Route::get('summary', 'ProviderResources\TripController@summary');
    Route::get('help', 'ProviderResources\TripController@help_details');

    Route::group(['prefix' => 'trip'], function () {

        Route::post('{id}',             'ProviderResources\TripController@accept');
        Route::post('{id}/rate',        'ProviderResources\TripController@rate');
        Route::post('{id}/message' ,    'ProviderResources\TripController@message');

    });

    //chat
    Route::get('/chat' , 'ProviderResources\TripController@chat_histroy');

    Route::group(['prefix' => 'requests'], function () {

        Route::get('/upcoming' , 'ProviderApiController@upcoming_request');
        Route::get('/history',          'ProviderResources\TripController@history');
        Route::get('/history/details',  'ProviderResources\TripController@history_details');
        Route::get('/upcoming/details', 'ProviderResources\TripController@upcoming_details');

    });

});