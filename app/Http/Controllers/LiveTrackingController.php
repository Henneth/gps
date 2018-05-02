<?php

namespace App\Http\Controllers;

use DB;
use App\Http\Controllers\Controller;
use App\LiveTracking_Model as LiveTracking_Model;
use App\DeviceMapping_Model as DeviceMapping_Model;
use Auth;

class LiveTrackingController extends Controller {

    public function index($event_id) {
        $event = DB::table('events')->where('event_id', $event_id)->first();


        $route = DB::table('routes')
            ->where('event_id',$event_id)
            ->select('route')
            ->first();

        if (Auth::check()) {
            $data = LiveTracking_Model::getLatestLocations($event_id, $event->datetime_from, $event->datetime_to);
            $jsonData = json_encode($data);
            $profile = DeviceMapping_Model::getAthletesProfile($event_id);
        }else{
            $data = LiveTracking_Model::getLatestLocations2($event_id, $event->datetime_from, $event->datetime_to);
            $jsonData = json_encode($data);
            $profile = DeviceMapping_Model::getAthletesProfile2($event_id);
        }

        $jsonProfile = json_encode($profile);
        return view('live-tracking')->with(array('data' => $jsonData, 'event' => $event, 'event_id' => $event_id, 'route' => $route, 'profile' => $profile, 'jsonProfile' => $jsonProfile));
    }
    public function poll($event_id) {
        $event = DB::table('events')->where('event_id', $event_id)->first();
        $data = LiveTracking_Model::getLatestLocations($event_id, $event->datetime_from, $event->datetime_to);
        return response()->json($data);
    }


}
