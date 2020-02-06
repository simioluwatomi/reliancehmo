<?php

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
    return view('welcome');
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

Route::get('{user}/subscription', 'SubscriptionController@create')->name('subscription.create')->middleware('auth');

Route::post('{user}/subscription', 'SubscriptionController@store')->name('subscription.pay')->middleware('auth');

Route::get('/payment/callback', 'SubscriptionController@handleGatewayCallback');
