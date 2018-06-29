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

Route::group(array('prefix' => ''), function() {

    // get current event ID from 'event' table, column name 'current'
    $data = DB::table('events')
    ->select('event_id')
    ->where('current', 1)
    ->first();

    if ($data) {
        define('current_event', $data->event_id);
    }else {
        define('current_event', false);
    }

    // get the lastest event ID from 'event' table, column name 'current'
    $maxEventID = DB::table('events')
    ->max('event_id');

    if ($maxEventID) {
        define('maxEventID', $maxEventID);
    }else {
        define('maxEventID', false);
    }

});

// Public
Route::get('/', function () {
    return redirect('view-all-events');
});

Route::get('view-all-events', 'EventController@viewAllEvents');
Route::get('event/{event_id}', function ($event_id) {
    // get current event ID from 'event' table, column name 'current'
    $data = DB::table('events')
    ->select('event_id')
    ->where('current', 1)
    ->first();

    if ($data && $data->event_id == $event_id){
        $event_id = $data->event_id;
        return redirect('event/'.$event_id.'/live-tracking');
    }else{
        return redirect('event/'.$event_id.'/replay-tracking');
    }
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
    Route::post('raw-data/export-raw-data', 'RawDataController@exportRawData');

    // Create New Event
    Route::get('create-new-event', 'EventController@createNewEvent');
    Route::post('create-new-event/post', 'EventController@createNewEventPost');

    // Athletes
    Route::get('event/{event_id}/athletes', 'AthletesController@index')->name('athletes');
    Route::post('event/{event_id}/athletes/add', 'AthletesController@addAthlete');
    Route::post('event/{event_id}/athletes/edit', 'AthletesController@editAthlete');
    Route::post('event/{event_id}/athletes/import-from-excel', 'AthletesController@importFromExcel');

    // Device Mapping
    Route::get('event/{event_id}/device-mapping', 'DeviceMappingController@index')->name('device-mapping');
    Route::post('event/{event_id}/device-mapping/add', 'DeviceMappingController@addDeviceMapping');
    Route::post('event/{event_id}/device-mapping/edit', 'DeviceMappingController@editDeviceMapping');
    Route::post('event/{event_id}/device-mapping/import-from-excel', 'DeviceMappingController@importFromExcel');

    // Draw Route
    Route::get('event/{event_id}/draw-route', 'DrawRouteController@index')->name('draw-route');
    Route::post('event/{event_id}/save-route', 'DrawRouteController@saveRoute');
    Route::get('event/{event_id}/gpx-route', 'GPXController@gpxRoute');
    Route::post('event/{event_id}/save-minimum-times', 'DrawRouteController@saveMinimumTimes');
    Route::post('event/{event_id}/save-checkpoint-name', 'DrawRouteController@saveCheckpointName');

    // Checkpoint
    Route::get('event/{event_id}/checkpoint', 'CheckpointController@index')->name('checkpoint');
    Route::post('event/{event_id}/save-checkpoint', 'CheckpointController@saveCheckpoint');

    // Edit Event
    Route::get('event/{event_id}/edit-event', 'EditEventController@index')->name('edit-event');
    Route::post('event/{event_id}/edit-event/post', 'EditEventController@postEditEvent');
    Route::post('event/{event_id}/edit-event/gpx-file-upload', 'GPXController@index');
    Route::post('event/{event_id}/edit-event/unset-live', 'EditEventController@unsetLive');

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
