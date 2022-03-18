<?php

/*
|--------------------------------------------------------------------------
| Provider Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', 		'ProviderController@index')->name('index');
Route::get('/trips', 	'ProviderResources\TripController@history')->name('trips');

Route::get('/incoming', 			'ProviderController@incoming')->name('incoming');
Route::post('/request/{id}', 		'ProviderController@accept')->name('accept');
Route::patch('/request/{id}', 		'ProviderController@update')->name('update');
Route::post('/request/{id}/rate', 	'ProviderController@rating')->name('rating');
Route::delete('/request/{id}', 		'ProviderController@reject')->name('reject');

Route::get('/earnings', 'ProviderController@earnings')->name('earnings');

Route::resource('documents', 'ProviderResources\DocumentController');

Route::get('/profile', 	'ProviderResources\ProfileController@show')->name('profile.index');
Route::post('/profile', 'ProviderResources\ProfileController@store')->name('profile.update');

Route::get('/location', 	'ProviderController@location_edit')->name('location.index');
Route::post('/location', 	'ProviderController@location_update')->name('location.update');

Route::post('/profile/available', 	'ProviderController@available')->name('available');
Route::get('/profile/password', 'ProviderController@change_password')->name('change.password');
Route::post('/change/password', 'ProviderController@update_password')->name('password.update');

Route::get('/upcoming', 'ProviderController@upcoming_trips')->name('upcoming');
Route::post('/cancel', 'ProviderController@cancel')->name('cancel');

Route::get('/chat/message', 'ProviderController@chat_history')->name('chat.message');
Route::get('/subscription', 'SubscriptionController@index');
Route::post('/subscription/wayforpay', 'SubscriptionController@sub_wayforpau');
Route::post('/subscription/pay', 'SubscriptionController@subscription')->name('subscription.pay');
Route::get('cards', 'ProviderController@cards')->name('cards');
Route::post('card/store', 'Resource\ProviderCardResource@store');
Route::post('card/set', 'Resource\ProviderCardResource@set_default');
Route::delete('card/destroy', 'Resource\ProviderCardResource@destroy');