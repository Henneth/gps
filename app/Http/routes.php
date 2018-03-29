<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

// Public
Route::get('/', function () {
    return redirect('view-all-events');
});
Route::get('view-all-events', function () {
    return view('view-all-events');
});
Route::get('event/{event_id}', function ($event_id) {
    return redirect('event/'.$event_id.'/live-tracking');
});
Route::get('event/{event_id}/live-tracking', 'LiveTrackingController@index');
Route::get('event/{event_id}/live-tracking/poll', 'LiveTrackingController@poll');
Route::get('event/{event_id}/replay-tracking', function () {
    return view('replay-tracking');
});

// Admins
Route::group(['middleware' => 'auth'], function () {

    Route::get('home', function () {
        return view('home');
    });
    Route::get('raw-data', 'RawDataController@index');
    Route::get('create-new-event', function () {
        return view('create-new-event');
    });
    Route::get('event/{event_id}/device-mapping', 'DeviceMappingController@index');
    Route::get('event/{event_id}/draw-route', function () {
        return view('draw-route');
    });

    // Other Configs
    Route::get('event/{event_id}/other-configurations', 'OtherConfigsController@index');
    Route::post('event/{event_id}/other-configurations/post', 'OtherConfigsController@postOtherConfigs');

    // Playground
    Route::get('/playground', function () {
        return view('playground');
    });

});

// Data import
Route::post('data-import', 'DataImportController@import');



// Authentication routes...
Route::get('auth/login', 'Auth\AuthController@getLogin');
Route::post('auth/login', 'Auth\AuthController@postLogin');
Route::get('auth/logout', 'Auth\AuthController@getLogout');

// Registration routes...
Route::get('auth/register', 'Auth\AuthController@getRegister');
Route::post('auth/register', 'Auth\AuthController@postRegister');

// Password reset link request routes...
Route::get('password/email', 'Auth\PasswordController@getEmail');
Route::post('password/email', 'Auth\PasswordController@postEmail');

// Password reset routes...
Route::get('password/reset/{token}', 'Auth\PasswordController@getReset');
Route::post('password/reset', 'Auth\PasswordController@postReset');

// Logout
Route::get('logout', function() {
	Auth::logout();
	return redirect('/');
});
