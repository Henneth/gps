<?php

namespace App\Http\Controllers;

use DB;
use App\Http\Controllers\Controller;
use App\ReplayTracking_Model as ReplayTracking_Model;
use App\DeviceMapping_Model as DeviceMapping_Model;


use Auth;

class ReplayTrackingController extends Controller {

    public function index($event_id) {
        // check loading time of the page
        $time_start = microtime(true);
        $time_end = microtime(true);
        $execution_time = ($time_end - $time_start);
        // echo $execution_time;


        // run calculation.php
        // shell_exec("php ".public_path()."/calculation.php 'alert' >> ".public_path()."/calculation.log");
        // shell_exec("php ".public_path()."/calculation.php replay ".$event_id);

        $event = DB::table('events')->where('event_id', $event_id)->first();

        $timestamp_from = strtotime($event->datetime_from." HKT");
        $timestamp_to = strtotime($event->datetime_to." HKT");

        $route = DB::table('archive_map_point')
            ->where('event_id', $event_id)
            ->get();
        // echo '<pre>'.print_r($route, 1).'</pre>';
        $route = json_encode($route);

// -------------- old
        // // get checkpoint distances
        // $tempCheckpointDistances = DB::table('gps_live_'.$event_id.'.map_point')->get();
        // $checkpointDistances = json_encode($tempCheckpointDistances);
// --------------


        if (!empty($_GET['tab']) && $_GET['tab'] == 2) {
            if (Auth::check()) {
                $profile = DeviceMapping_Model::getAthletesProfile($event_id, true, false);
            }else{
                $profile = DeviceMapping_Model::getAthletesProfile($event_id, false, false);
                // echo "<pre>".print_r($profile,1)."</pre>";
            }

            return view('replay-tracking-athletes')->with(array('profile' => $profile, 'event_id' => $event_id, 'event'=>$event));
        }

        if (!empty($_GET['tab']) && $_GET['tab'] == 1) {
            return view('replay-tracking-chart')->with(array('event_id' => $event_id, 'timestamp_from' => $timestamp_from, 'timestamp_to' => $timestamp_to, 'route' => $route, 'event'=>$event));
        }

        else {
            return view('replay-tracking-map')->with(array('event_id' => $event_id, 'timestamp_from' => $timestamp_from, 'timestamp_to' => $timestamp_to, 'route' => $route, 'event'=>$event));
        }
    }

    public function poll($event_id){

        // $time_start = microtime(true);
        $event = DB::table('events')->where('event_id', $event_id)->first();
        $colorArray = ["00FF00","0000FF","FF0000","FFFF00","00FFFF","FF00FF","00FF80","8000FF","FF8000","80FF00","0080FF","FF0080","80FF80","8080FF","FF8080","FFFF80","80FFFF","FF80FF","80FFBF","BF80FF","FFBF80","BFFF80","80BFFF","FF80BF"];

        if ( !empty($_GET['bib_numbers']) ){
            $bib_numbers = json_decode($_GET['bib_numbers']);
            $data = [];

            $count = 0; // count index of $colorArray
            foreach ($bib_numbers as $key => $bib_number) {
                $deviceData = ReplayTracking_Model::getLocationsViaBibNumber($event_id, $event->datetime_from, $event->datetime_to, $bib_number, $colorArray[$count]);
                $data[$bib_number] = $deviceData;
                $count++;
            }
        } else {
            // get 20 athletes from db
            if (Auth::check()){
                $athletes = DeviceMapping_Model::getAthletesProfile($event_id, true, true);
            } else {
                $athletes = DeviceMapping_Model::getAthletesProfile($event_id, false, true);
            }
            $data = [];

            $count = 0; // count index of $colorArray
            foreach ($athletes as $key => $athlete) {
                $deviceData = ReplayTracking_Model::getLocationsViaBibNumber($event_id, $event->datetime_from, $event->datetime_to, $athlete->bib_number, $colorArray[$count]);
                $data[$athlete->bib_number] = $deviceData;
                $count++;
            }
        }

        // $time_end = microtime(true);
        // $execution_time = ($time_end - $time_start);
        // echo $execution_time;

        return response()->json($data);
    }

    // private function group_by($array, $key) {
    //     $return = array();
    //     foreach($array as $val) {
    //         $return[$val->$key][] = $val;
    //     }
    //     return $return;
    // }
}
