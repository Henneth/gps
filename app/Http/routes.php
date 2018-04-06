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
Route::get('view-all-events', 'EventController@viewAllEvents');
Route::get('event/{event_id}', function ($event_id) {
    return redirect('event/'.$event_id.'/live-tracking');
});
Route::get('event/{event_id}/live-tracking', 'LiveTrackingController@index')->name('live-tracking');
Route::get('event/{event_id}/live-tracking/poll', 'LiveTrackingController@poll');
Route::get('event/{event_id}/replay-tracking', 'ReplayTrackingController@index')->name('replay-tracking');


// Admins
Route::group(['middleware' => 'auth'], function () {

    // Home
    Route::get('home', function () {
        $events = DB::table('events')->orderby('event_id', 'desc')->get();
        return view('home')->with(array('events' => $events));
    });

    // Raw Data
    Route::get('raw-data', 'RawDataController@index');

    // Create New Event
    Route::get('create-new-event', 'EventController@createNewEvent');
    Route::post('create-new-event/post', 'EventController@createNewEventPost');

    // Athletes
    Route::get('event/{event_id}/athletes', 'AthletesController@index')->name('athletes');
    Route::post('event/{event_id}/athletes/add', 'AthletesController@addAthlete');
    Route::post('event/{event_id}/athletes/edit', 'AthletesController@editAthlete');

    // Device Mapping
    Route::get('event/{event_id}/device-mapping', 'DeviceMappingController@index')->name('device-mapping');
    Route::post('event/{event_id}/device-mapping/add', 'DeviceMappingController@addDeviceMapping');
    Route::post('event/{event_id}/device-mapping/edit', 'DeviceMappingController@editDeviceMapping');

    // Draw Route
    Route::get('event/{event_id}/draw-route', 'DrawRouteController@index')->name('draw-route');

    // Other Configs
    Route::get('event/{event_id}/other-configurations', 'OtherConfigsController@index')->name('other-configurations');
    Route::post('event/{event_id}/other-configurations/post', 'OtherConfigsController@postOtherConfigs');

    // Playground
    // Route::get('/playground', function () {
    //     return view('playground');
    // });

});

// Data import
Route::post('data-import', 'DataImportController@import');
Route::post('data-import-2', 'DataImportController@import2');


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
