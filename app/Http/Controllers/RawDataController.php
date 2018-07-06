<?php

namespace App\Http\Controllers;

use DB;
use App\Http\Controllers\Controller;


class RawDataController extends Controller {

    public function index() {
        if (isset($_GET['live']) && $_GET['live'] == 1){
            $data = DB::table('gps_live.gps_data')->orderby('datetime', 'desc')->limit(3000)->get();
        }else {
            $data = DB::table('gps_data')->orderby('datetime', 'desc')->limit(3000)->get();
        }
        // print_r($data);
        foreach ($data as &$value) {
            $datetime1 = date_create($value->datetime);
            $datetime2 = date_create($value->created_at);
            $interval = date_diff($datetime1, $datetime2);
            if ($interval->format("%D")>0) {
                $value->delay = "> 1 day";
            }else{
                $value->delay = $interval->format("%H:%I:%S");
            }
        }

        if (isset($_GET['live']) && $_GET['live'] == 1){
            $deviceID = DB::table('gps_live.gps_data')
                        ->select('device_id')
                        ->orderBy('device_id', 'asc')
                        ->groupBy('device_id')
                        ->get();
        }else {
            $deviceID = DB::table('gps_data')
                        ->select('device_id')
                        ->orderBy('device_id', 'asc')
                        ->groupBy('device_id')
                        ->get();
        }
        return view('raw-data')->with(array('data' => $data, 'deviceID' => $deviceID));
    }

    /**
     * Indents a flat JSON string to make it more human-readable.
     *
     * @param string $json The original JSON string to process.
     *
     * @return string Indented version of the original JSON string.
     */
    private function indent($json) {

        $result      = '';
        $pos         = 0;
        $strLen      = strlen($json);
        $indentStr   = '    ';
        $newLine     = "\n";
        $prevChar    = '';
        $outOfQuotes = true;

        for ($i=0; $i<=$strLen; $i++) {

            // Grab the next character in the string.
            $char = substr($json, $i, 1);

            // Are we inside a quoted string?
            if ($char == '"' && $prevChar != '\\') {
                $outOfQuotes = !$outOfQuotes;

            // If this character is the end of an element,
            // output a new line and indent the next line.
            } else if(($char == '}' || $char == ']') && $outOfQuotes) {
                $result .= $newLine;
                $pos --;
                for ($j=0; $j<$pos; $j++) {
                    $result .= $indentStr;
                }
            }

            // Add the character to the result string.
            $result .= $char;

            // If the last character was the beginning of an element,
            // output a new line and indent the next line.
            if (($char == ',' || $char == '{' || $char == '[') && $outOfQuotes) {
                $result .= $newLine;
                if ($char == '{' || $char == '[') {
                    $pos ++;
                }

                for ($j = 0; $j < $pos; $j++) {
                    $result .= $indentStr;
                }
            }

            $prevChar = $char;
        }

        return $result;
    }

    public function exportRawData() {

        $timeFrom =  $_POST['time-from'];
        $timeTo =  $_POST['time-to'];
        $deviceID =  $_POST['deviceID'];

        if (!empty($timeFrom) && !empty($timeTo) && empty($deviceID)){
            $data = DB::table('gps_data')
                ->select('datetime', 'created_at', 'device_id', 'longitude_final', 'latitude_final', 'battery_level')
                ->where('datetime', '>=', $timeFrom)
                ->where('datetime', '<=', $timeTo)
                ->orderby('datetime', 'desc')
                ->orderby('created_at', 'desc')
                ->get();
        }
        if (!empty($timeFrom) && !empty($timeTo) && !empty($deviceID)){
            $data = DB::table('gps_data')
                ->select('datetime', 'created_at', 'device_id', 'longitude_final', 'latitude_final', 'battery_level')
                ->where('datetime', '>=', $timeFrom)
                ->where('datetime', '<=', $timeTo)
                ->where('device_id', $deviceID)
                ->orderby('datetime', 'desc')
                ->orderby('created_at', 'desc')
                ->get();
        }
        if (empty($timeFrom) && empty($timeTo) && !empty($deviceID)){
            $data = DB::table('gps_data')
                ->select('datetime', 'created_at', 'device_id', 'longitude_final', 'latitude_final', 'battery_level')
                ->where('device_id', $deviceID)
                ->orderby('datetime', 'desc')
                ->orderby('created_at', 'desc')
                ->get();
        }


        // echo "<pre>".print_r($timeFrom)."<pre>";
        // echo "<pre>".print_r($timeTo)."<pre>";
        // echo "<pre>".print_r($deviceID)."<pre>";
        // $data = DB::table('gps_data')
        //     ->select('datetime', 'created_at', 'device_id', 'longitude_final', 'latitude_final', 'battery_level')
        //     ->orderby('datetime', 'desc')
        //     ->orderby('created_at', 'desc')
        //     ->limit(3000)
        //     ->get();

        $order = array('datetime', 'created_at', 'delay', 'device_id', 'longitude_final', 'latitude_final', 'battery_level');
        $assoc_array = [];
        foreach ($data as &$value) {
            $out = array();
            $datetime1 = date_create($value->datetime);
            $datetime2 = date_create($value->created_at);
            $interval = date_diff($datetime1, $datetime2);
            if ($interval->format("%D")>0) {
                $value->delay = "> 1 day";
            }else{
                $value->delay = $interval->format("%H:%I:%S");
            }
            foreach ($order as $k) {
                $out[$k] = $value->$k;
            }
            $assoc_array[] = $out;
        }
        // $array = json_decode(json_encode($data), true);

        $colNames = ["Timestamp", "Received At", "Delay", "Device ID", "Longitude", "Latitude", "Battery Level"];
        $sheets[] = ['colNames' => $colNames, 'data' => $assoc_array, 'sheetname' => "raw-gps-data"];

        require_once '../libs/Excel.php';
        exportAsExcel("raw-gps-data ".date("Y-m-d H:i:s", strtotime('+8 hours')), $sheets);
        // echo "<pre>".print_r($assoc_array,1)."</pre>";
    }

}
