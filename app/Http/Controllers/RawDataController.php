<?php

namespace App\Http\Controllers;

use DB;
use App\Http\Controllers\Controller;

class RawDataController extends Controller {

    public function index() {
        // foreach ($data as &$value) {
        //     $datetime1 = date_create($value->datetime);
        //     $datetime2 = date_create($value->created_at);
        //     $interval = date_diff($datetime1, $datetime2);
        //     // echo "<pre>".print_r($interval,1)."</pre>";
        //
        //     if ($interval->format("%D")>0) {
        //         $value->delay = "> 1 day";
        //     } else{
        //         $value->delay = $interval->format("%R%H:%I:%S");
        //     }
        // }

        if (isset($_GET['event_id'])){
            $device_ids = DB::table('gps_live_'.$_GET['event_id'].'.raw_data')
                        ->select('device_id')
                        ->orderBy('device_id', 'asc')
                        ->groupBy('device_id')
                        ->get();
        } else {
            $device_ids = DB::table('raw_data')
                        ->select('device_id')
                        ->orderBy('device_id', 'asc')
                        ->groupBy('device_id')
                        ->get();
        }
        $live_event_ids = DB::table('events')
                    ->select('event_id', 'event_name')
                    ->where('live', 1)
                    ->get();
                    
        return view('raw-data')->with(array('device_ids' => $device_ids, 'live_event_ids' => $live_event_ids));
    }

    public function exportRawData() {

        $timeFrom =  $_POST['time-from'];
        $timeTo =  $_POST['time-to'];
        $deviceID =  $_POST['deviceID'];

        if (!empty($timeFrom) && !empty($timeTo) && empty($deviceID)){
            $data = DB::table('raw_data')
                ->select('datetime', 'created_at', DB::raw('TIMEDIFF(datetime, created_at) AS delay'), 'device_id', 'longitude_final', 'latitude_final', 'battery_level')
                ->where('datetime', '>=', $timeFrom)
                ->where('datetime', '<=', $timeTo)
                ->orderby('datetime', 'desc')
                ->orderby('created_at', 'desc')
                ->get();
        }
        if (!empty($timeFrom) && !empty($timeTo) && !empty($deviceID)){
            $data = DB::table('raw_data')
                ->select('datetime', 'created_at', DB::raw('TIMEDIFF(datetime, created_at) AS delay'), 'device_id', 'longitude_final', 'latitude_final', 'battery_level')
                ->where('datetime', '>=', $timeFrom)
                ->where('datetime', '<=', $timeTo)
                ->where('device_id', $deviceID)
                ->orderby('datetime', 'desc')
                ->orderby('created_at', 'desc')
                ->get();
        }
        if (empty($timeFrom) && empty($timeTo) && !empty($deviceID)){
            $data = DB::table('raw_data')
                ->select('datetime', 'created_at', DB::raw('TIMEDIFF(datetime, created_at) AS delay'), 'device_id', 'longitude_final', 'latitude_final', 'battery_level')
                ->where('device_id', $deviceID)
                ->orderby('datetime', 'desc')
                ->orderby('created_at', 'desc')
                ->get();
        }


        // echo "<pre>".print_r($timeFrom)."<pre>";
        // echo "<pre>".print_r($timeTo)."<pre>";
        // echo "<pre>".print_r($deviceID)."<pre>";
        // $data = DB::table('raw_data')
        //     ->select('datetime', 'created_at', 'device_id', 'longitude_final', 'latitude_final', 'battery_level')
        //     ->orderby('datetime', 'desc')
        //     ->orderby('created_at', 'desc')
        //     ->limit(3000)
        //     ->get();

        // $order = array('datetime', 'created_at', 'delay', 'device_id', 'longitude_final', 'latitude_final', 'battery_level');
        // $assoc_array = [];
        // foreach ($data as &$value) {
        //     $out = array();
        //     $datetime1 = date_create($value->datetime);
        //     $datetime2 = date_create($value->created_at);
        //     $interval = date_diff($datetime1, $datetime2);
        //     if ($interval->format("%D")>0) {
        //         $value->delay = "> 1 day";
        //     }else{
        //         $value->delay = $interval->format("%H:%I:%S");
        //     }
        //     foreach ($order as $k) {
        //         $out[$k] = $value->$k;
        //     }
        //     $assoc_array[] = $out;
        // }
        // $array = json_decode(json_encode($data), true);

        $colNames = ["Timestamp", "Received At", "Delay", "Device ID", "Longitude", "Latitude", "Battery Level"];
        $sheets[] = ['colNames' => $colNames, 'data' => $data, 'sheetname' => "raw-gps-data"];

        require_once '../libs/Excel.php';
        exportAsExcel("raw-gps-data ".date("Y-m-d H:i:s", strtotime('+8 hours')), $sheets);
        // echo "<pre>".print_r($assoc_array,1)."</pre>";
    }

}
