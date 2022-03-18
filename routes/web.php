<?php

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


Auth::routes();
Route::post('/otp', 'Auth\RegisterController@OTP');
Route::get('auth/facebook', 'Auth\SocialLoginController@redirectToFaceBook');
Route::get('auth/facebook/callback', 'Auth\SocialLoginController@handleFacebookCallback');
Route::get('auth/google', 'Auth\SocialLoginController@redirectToGoogle');
Route::get('auth/google/callback', 'Auth\SocialLoginController@handleGoogleCallback');
Route::post('account/kit', 'HomeController@account_kit')->name('account.kit');

Route::group(['prefix' => 'provider'], function () {
    
    Route::get('auth/facebook', 'Auth\SocialLoginController@providerToFaceBook');
    Route::get('auth/google', 'Auth\SocialLoginController@providerToGoogle');

    Route::get('/login', 'ProviderAuth\LoginController@showLoginForm');
    Route::post('/login', 'ProviderAuth\LoginController@login');
    Route::post('/logout', 'ProviderAuth\LoginController@logout');

    Route::get('/register', 'ProviderAuth\RegisterController@showRegistrationForm');
    Route::post('/register', 'ProviderAuth\RegisterController@register');

    Route::post('/password/email', 'ProviderAuth\ForgotPasswordController@sendResetLinkEmail');
    Route::post('/password/reset', 'ProviderAuth\ResetPasswordController@reset');
    Route::get('/password/reset', 'ProviderAuth\ForgotPasswordController@showLinkRequestForm');
    Route::get('/password/reset/{token}', 'ProviderAuth\ResetPasswordController@showResetForm');
    Route::post('/otp', 'ProviderAuth\RegisterController@OTP');
});

Route::group(['prefix' => 'admin'], function () {
    Route::get('/login', 'AdminAuth\LoginController@showLoginForm');
    Route::post('/login', 'AdminAuth\LoginController@login');
    Route::post('/logout', 'AdminAuth\LoginController@logout');

    Route::post('/password/email', 'AdminAuth\ForgotPasswordController@sendResetLinkEmail');
    Route::post('/password/reset', 'AdminAuth\ResetPasswordController@reset');
    Route::get('/password/reset', 'AdminAuth\ForgotPasswordController@showLinkRequestForm');
    Route::get('/password/reset/{token}', 'AdminAuth\ResetPasswordController@showResetForm');
});

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('index');
});

Route::get('/ride', function () {
    return view('ride');
});

Route::get('/drive', function () {
    return view('drive');
});

Route::get('privacy', function () {
    $page = 'page_privacy';
    $title = 'Privacy Policy';
    return view('static', compact('page', 'title'));
});

/*
|--------------------------------------------------------------------------
| User Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/dashboard', 'HomeController@index');

//User chat 
Route::get('/chat/save' , 'ChatController@save');
Route::get('/chat/message', 'RideController@chat_history');

// user profiles
Route::get('/profile',      'HomeController@profile');
Route::get('/edit/profile', 'HomeController@edit_profile');
Route::post('/profile',     'HomeController@update_profile');

//wayforpay
Route::post('/paisa_wallet','HomeController@paisa_wallet_form');
Route::post('/wayforpay/wallet/success','PaymentController@wayWalletSuccess');
Route::post('/wayforpay/payment/success','PaymentController@wayPaymentSuccess');
Route::post('/wayforpay/payment','PaymentController@wayforpay_payment_form');

// update password
Route::get('/change/password',  'HomeController@change_password');
Route::post('/change/password', 'HomeController@update_password');

// ride
Route::get('/confirm/ride', 'RideController@confirm_ride');
Route::post('/create/ride', 'RideController@create_ride');
Route::post('/cancel/ride', 'RideController@cancel_ride');
Route::get('/onride',       'RideController@onride');
Route::post('/payment',     'PaymentController@payment');
Route::post('/rate',        'RideController@rate');

// status check
Route::get('/status', 'RideController@status');

// trips
Route::get('/trips', 'HomeController@trips');

// wallet
Route::get('/wallet',       'HomeController@wallet');
Route::post('/payment_mode',       'UserApiController@payment_mode');
Route::post('/add/money',   'PaymentController@add_money');

// payment
Route::get('/payment', 'HomeController@payment');

// card
Route::resource('card', 'Resource\CardResource');

// promotions
Route::get('/promotion',        'HomeController@promotion');
Route::post('/add/promocode',   'HomeController@add_promocode');

// upcoming
Route::get('/upcoming/trips', 'HomeController@upcoming_trips');

// send push notification
Route::get('/send/push',
    function(){
        $data = PushNotification::app('IOSUser')
        ->to('163e4c0ca9fe084aabeb89372cf3f664790ffc660c8b97260004478aec61212c')
        ->send('Hello World, i`m a push message');
            dd($data);
    });
