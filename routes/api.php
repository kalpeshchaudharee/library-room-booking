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

Route::post('login', 'API\UserController@login');
Route::post('forgot_password', 'API\UserController@forgotPassword');
Route::post('reset_password', 'API\UserController@resetPassword');
Route::group(['middleware' => 'auth:api'], function(){
    Route::get('rooms', 'API\RoomController@roomList');
    Route::get('room/{id}', 'API\RoomController@roomDetails');
    Route::post('room/booking/{roomId}', 'API\BookingController@createBooking');
    Route::get('bookings', 'API\BookingController@bookingList');
});