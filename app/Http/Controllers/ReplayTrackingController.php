<?php

namespace App\Http\Controllers;

use DB;
use App\Http\Controllers\Controller;
use App\ReplayTracking_Model as ReplayTracking_Model;
use App\DeviceMapping_Model as DeviceMapping_Model;
use App\LiveTracking_Model as LiveTracking_Model;
use Auth;

class ReplayTrackingController extends Controller {

    public function index($event_id) {
        // check loading time of the page
        // $time_start = microtime(true);
        // $time_end = microtime(true);
        // $execution_time = ($time_end - $time_start);
        // echo $execution_time;

        // run calculation.php
        // shell_exec("php ".public_path()."/calculation.php 'alert' >> ".public_path()."/calculation.log");
        // shell_exec("php ".public_path()."/calculation.php replay ".$event_id);

        $event = DB::table('events')->where('event_id', $event_id)->first();
        if ($event->live == 1){
            $route = DB::table('gps_live_'.$event_id.'.map_point')->get();
            $tempCheckpoint = DB::table('gps_live_'.$event_id.'.checkpoint')->get();
        } else if ($event->live == 2){
            $route = DB::table('archive_map_point')->where('event_id', $event_id)->get();
            $tempCheckpoint = DB::table('archive_checkpoint')->where('event_id', $event_id)->get();
        } else {
            return redirect('event/'.$event_id.'/draw-route');
        }

        $route = json_encode($route);
        $checkpoint = json_encode($tempCheckpoint);

        $timestamp_from = strtotime($event->datetime_from." HKT");
        $timestamp_to = strtotime($event->datetime_to." HKT");

        // echo "<pre>".print_r($tempCheckpoint,1)."</pre>";

        if (!empty($_GET['tab']) && $_GET['tab'] == 2) {
            if (Auth::check()) {
                if ($event->live == 1){
                    $profile = DeviceMapping_Model::getAthletesProfile($event_id, true, false, true); // 2: auth, 3: map (hide hidden athletes) or participants list (show hidden athletes), 4: live or not
                } else {
                    $profile = DeviceMapping_Model::getAthletesProfile($event_id, true, false); // 2: auth, 3: map (hide hidden athletes) or participants list (show hidden athletes), 4: live or not
                }
            }else{
                if ($event->live == 1){
                    $profile = DeviceMapping_Model::getAthletesProfile($event_id, false, false, true); // 2: auth, 3: map (hide hidden athletes) or participants list (show hidden athletes), 4: live or not
                } else {
                    $profile = DeviceMapping_Model::getAthletesProfile($event_id, false, false); // 2: auth, 3: map (hide hidden athletes) or participants list (show hidden athletes), 4: live or not
                }
            }

            return view('replay-tracking-athletes')->with(array('profile' => $profile, 'event_id' => $event_id, 'event'=>$event));
        }

        if (!empty($_GET['tab']) && $_GET['tab'] == 1) {
            return view('replay-tracking-chart')->with(array('event_id' => $event_id, 'timestamp_from' => $timestamp_from, 'timestamp_to' => $timestamp_to, 'event'=>$event, 'route' => $route, 'checkpoint'=>$checkpoint));
        }

        if (!empty($_GET['tab']) && $_GET['tab'] == 3) {
            return view('replay-tracking-checkpoint')->with(array('event_id' => $event_id,  'event'=>$event, 'checkpoint'=>$tempCheckpoint));
        }

        else {
            return view('replay-tracking-map')->with(array('event' => $event, 'event_id' => $event_id, 'timestamp_from' => $timestamp_from, 'timestamp_to' => $timestamp_to, 'route' => $route, 'checkpoint'=>$checkpoint));
        }
    }

    public function poll($event_id){
        // $time_start = microtime(true);
        $event = DB::table('events')->where('event_id', $event_id)->first();

        // $colorArray = ["00FF00","0000FF","FF0000","FFFF00","00FFFF","FF00FF","00FF80","8000FF","FF8000","80FF00","0080FF","FF0080","80FF80","8080FF","FF8080","FFFF80","80FFFF","FF80FF","80FFBF","BF80FF","FFBF80","BFFF80","80BFFF","FF80BF"];
        $colorArray = ["00FF00","0000FF","FF0000","FFFF00","00FFFF","FF00FF","00FF80","8000FF","FF8000","80FF00","0080FF","FF0080","009900","000099","990000","999900","009999","990099","00994D","4D0099","994D00","4D9900","004D99","99004D"];

        // if not empty localstorage
        if ( !empty($_GET['bib_numbers']) ){
            $bib_numbers = json_decode($_GET['bib_numbers'], true);
            sort($bib_numbers);

            $data = [];
            $count = 0; // count index of $colorArray
            foreach ($bib_numbers as $key => $bib_number) {
                if ($event->live == 1){
                    $deviceData = LiveTracking_Model::getLocationsViaBibNumber($event_id, $event->datetime_from, $event->datetime_to, $bib_number, $colorArray[$count], false);
                } else {
                    $deviceData = ReplayTracking_Model::getLocationsViaBibNumber($event_id, $event->datetime_from, $event->datetime_to, $bib_number, $colorArray[$count]);
                }

                $data[$bib_number] = $deviceData;
                $count++;
            }
        } else {
            // get 20 athletes from db
            if (Auth::check()){
                if ($event->live == 1){
                    $athletes = DeviceMapping_Model::getAthletesProfile($event_id, true, true, true); // 2: auth, 3: map (hide hidden athletes) or participants list (show hidden athletes), 4: live or not
                }else {
                    $athletes = DeviceMapping_Model::getAthletesProfile($event_id, true, true); // 2: auth, 3: map (hide hidden athletes) or participants list (show hidden athletes), 4: live or not
                }
            } else {
                if  ($event->live == 1){
                    $athletes = DeviceMapping_Model::getAthletesProfile($event_id, false, true, true); // 2: auth, 3: map (hide hidden athletes) or participants list (show hidden athletes), 4: live or not
                } else{
                    $athletes = DeviceMapping_Model::getAthletesProfile($event_id, false, true); // 2: auth, 3: map (hide hidden athletes) or participants list (show hidden athletes), 4: live or not
                }
            }

            $data = [];
            $count = 0; // count index of $colorArray
            foreach ($athletes as $key => $athlete) {
                if  ($event->live == 1){
                    $deviceData = LiveTracking_Model::getLocationsViaBibNumber($event_id, $event->datetime_from, $event->datetime_to, $athlete->bib_number, $colorArray[$count], false);
                } else {
                    $deviceData = ReplayTracking_Model::getLocationsViaBibNumber($event_id, $event->datetime_from, $event->datetime_to, $athlete->bib_number, $colorArray[$count]);
                }
                $data[$athlete->bib_number] = $deviceData;
                $count++;
            }
        }

        // $time_end = microtime(true);
        // $execution_time = ($time_end - $time_start);
        // echo $execution_time;

        return response()->json($data);
    }

    // poll checkpoint table related data
    public function checkpointTable($event_id){
        // $time_start = microtime(true);
        $event = DB::table('events')->where('event_id', $event_id)->first();

        // if not empty localstorage
        if ( !empty($_GET['bib_numbers']) ){
            $bib_numbers = json_decode($_GET['bib_numbers']);
            sort($bib_numbers);

            $data = [];
            foreach ($bib_numbers as $key => $bib_number) {
                if ($event->live == 1){
                    $reachedCheckpointData = LiveTracking_Model::getReachedCheckpointData($event_id, $bib_number);
                } else {
                    $reachedCheckpointData = ReplayTracking_Model::getReachedCheckpointData($event_id, $bib_number);
                }


                $data[$bib_number] = $reachedCheckpointData;
            }
        } else {
            // get 20 athletes from db
            if (Auth::check()){
                if ($event->live == 1){
                    $athletes = DeviceMapping_Model::getAthletesProfile($event_id, true, true, true); // 2: auth, 3: map (hide hidden athletes) or participants list (show hidden athletes), 4: live or not
                }else {
                    $athletes = DeviceMapping_Model::getAthletesProfile($event_id, true, true); // 2: auth, 3: map (hide hidden athletes) or participants list (show hidden athletes), 4: live or not
                }
            } else {
                if  ($event->live == 1){
                    $athletes = DeviceMapping_Model::getAthletesProfile($event_id, false, true, true); // 2: auth, 3: map (hide hidden athletes) or participants list (show hidden athletes), 4: live or not
                } else{
                    $athletes = DeviceMapping_Model::getAthletesProfile($event_id, false, true); // 2: auth, 3: map (hide hidden athletes) or participants list (show hidden athletes), 4: live or not
                }
            }

            $data = [];
            foreach ($athletes as $key => $athlete) {
                if  ($event->live == 1){
                    $reachedCheckpointData = LiveTracking_Model::getReachedCheckpointData($event_id, $athlete->bib_number);
                } else {
                    $reachedCheckpointData = ReplayTracking_Model::getReachedCheckpointData($event_id, $athlete->bib_number);
                }
                $data[$athlete->bib_number] = $reachedCheckpointData;
            }
        }

        usort($data, array($this, "cmp"));
        if ($event->live == 1){
            $tempCheckpoint = DB::table('gps_live_'.$event_id.'.checkpoint')->get();
        } else {
            $tempCheckpoint = DB::table('archive_checkpoint')->where('event_id', $event_id)->get();
        }
        $dataArray = [];
        foreach ($data as $value) {
            $tempArray = [];
            $tempArray[] = $value['name'].' ('.$value['bib_number'].')';

            foreach ($tempCheckpoint as $index => $checkpoint) {
                if( ($index != 0) && ($checkpoint->display == 1) ) {
                    if( !empty($value['data']) ) {
                        $tempDatetime = "";
                        foreach ($value['data'] as $item) {
                            if($item->checkpoint_id == $checkpoint->checkpoint_id){
                                $tempDatetime = $item->datetime;
                            }
                        }
                        $tempArray[] = $tempDatetime;
                    } else {
                        $tempArray[] = "";
                    }
                }
            }
            $dataArray[] = $tempArray;
            // echo '<pre>'.print_r($dataArray, 1).'</pre>';
        }


        return response()->json(array('data' => $dataArray));
    }


    public function cmp($a, $b){
        if ( !empty($a['data'][0]) && !empty($b['data'][0]) ){
            if( $a['data'][0]->checkpoint_id > $b['data'][0]->checkpoint_id) {
                return -1;
            } else if ($a['data'][0]->checkpoint_id == $b['data'][0]->checkpoint_id) {
                if(strtotime($a['data'][0]->datetime) < strtotime($b['data'][0]->datetime)) {
                    return -1;
                } else if ( strtotime($a['data'][0]->datetime) == strtotime($b['data'][0]->datetime) ) {
                    if ($a['bib_number'] < $b['bib_number']) {
                        return -1;
                    } else {
                        return 1;
                    }
                } else {
                    return 1;
                }
            } else {
                return 1;
            }
        } else {
            if ( !empty($a['data'][0]) && empty($b['data'][0]) ) {
                return -1;
            } else if ( empty($a['data'][0]) && !empty($b['data'][0]) ) {
                return 1;
            } else {
                if ($a['bib_number'] < $b['bib_number']) {
                    return -1;
                } else {
                    return 1;
                }
            }
        }
    }




}

    // private function group_by($array, $key) {
    //     $return = array();
    //     foreach($array as $val) {
    //         $return[$val->$key][] = $val;
    //     }
    //     return $return;
    // }
