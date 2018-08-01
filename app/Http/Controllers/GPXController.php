<?php

namespace App\Http\Controllers;

use DB;
use App\Http\Controllers\Controller;
use App\Classes\TRKParser;
use App\DrawRoute_Model as DrawRoute_Model;

class GPXController extends Controller {
    public function index($event_id) {
        $target_dir = storage_path('app/GPX/');
        $target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
        $uploadOk = 1;
        $gpxFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));

        // Check if file already exists
        if (file_exists($target_file)) {
            return redirect('event/'.$event_id.'/draw-route')->with('error', 'GPX file with the same name has been uploaded.');
        }

        // Allow certain file formats
        if ($gpxFileType != "gpx") {
            return redirect('event/'.$event_id.'/draw-route')->with('error', 'Sorry, only gpx file is allowed.');
        }

        // if everything is ok, try to upload file
        if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {

        } else {
            $error_msg = "Sorry, there was an error uploading your file.";
            return redirect('event/'.$event_id.'/draw-route')->with('error', $error_msg);
        }


        $xml_parser = xml_parser_create();
        $rss_parser = new TRKParser();
        $rss_parser->event_id = $event_id;

        xml_set_object($xml_parser, $rss_parser);
        xml_set_element_handler($xml_parser, "startElement", "endElement");
        xml_set_character_data_handler($xml_parser, "characterData");

        $fp = fopen($target_file,"r")
              or die("Error reading Track Point data.");

        while ($data = fread($fp, 4096))
          xml_parse($xml_parser, $data, feof($fp))
            or die(sprintf("XML error: %s at line %d",
              xml_error_string(xml_get_error_code($xml_parser)),
              xml_get_current_line_number($xml_parser)));

        fclose($fp);

        xml_parser_free($xml_parser);


        $gpxArray = $rss_parser->array;

        $distanceArray = [];
        foreach ($gpxArray as $key => $value) {
            if ($key == 0) {
                $lat2 = $value['lat'];
                $lon2 = $value['lon'];
                $distanceArray[] = array("latitude" => $lat2, "longitude" => $lon2, "distance_from_last_point" => 0, "distance_from_start" => 0);

            } else {
                $lat2 = $value['lat'];
                $lon2 = $value['lon'];
                // the distance between two points, if it greater than "50m", will be seperate to cut into multiple parts
                $point_distance = $this->distance($lat1, $lon1, $lat2, $lon2);

                // below "2" means two point that used to calculate the distance, should ignore them
                $number_of_sections = ceil($point_distance / 50);

                if ($number_of_sections > 1){
                    $x = 1;
                    do {
                        $other = $number_of_sections - $x;
                        $add_lat = ( ($lat2 * $x) + ($lat1 * $other) ) / $number_of_sections;
                        $add_lon = ( ($lon2 * $x) + ($lon1 * $other) ) / $number_of_sections;
                        $distanceArray[] = array("latitude" => $add_lat, "longitude" => $add_lon, "distance_from_last_point" => 0, "distance_from_start" => 0);


                        // echo "add lat ".$add_lat;
                        // echo "add lat ".$add_lon;
                        $x++;
                    } while ($x <= $number_of_sections - 1);

                }

                $distanceArray[] = array("latitude" => $lat2, "longitude" => $lon2, "distance_from_last_point" => 0, "distance_from_start" => 0);

            }

            // store the value of former checkpoint
            $lat1 = $lat2;
            $lon1 = $lon2;
        }

        // echo '<pre>'.print_r($rss_parser->array,1).'</pre>';
        // echo '<pre>'.print_r($distanceArray,1).'</pre>';
        // $route = json_encode($distanceArray);

        DB::table('gps_live_'.$event_id.'.map_point')->truncate();
        DB::table('gps_live_'.$event_id.'.map_point')
            ->insert($distanceArray);

        // DrawRoute_Model::drawRouteUpdate($event_id, $route);
        // // print_r($route);
        // // DB::table('routes')->insert(
        // //     ['event_id'=> $event_id, 'route' =>$route]
        // // );
        // // DB::unprepared($rss_parser->sql);
        return redirect('event/'.$event_id.'/draw-route?gpx=1')->with('success', 'Excel file imported.');
    }


    // public function gpxRoute($event_id){
    //     $gpxData = DB::table('routes')
    //         ->where('event_id', $event_id)
    //         ->select('latitude','longitude')
    //         ->get();
    //     $gpxData = json_encode($gpxData);
    //     // print_r($gpxData);
    //     return view('gpx-route')->with(array('event_id' => $event_id, 'gpxData'=>$gpxData));
    // }


/*::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::*/
/*::                                                                         :*/
/*::  This routine calculates the distance between two points (given the     :*/
/*::  latitude/longitude of those points). It is being used to calculate     :*/
/*::  the distance between two locations using GeoDataSource(TM) Products    :*/
/*::                                                                         :*/
/*::  Definitions:                                                           :*/
/*::    South latitudes are negative, east longitudes are positive           :*/
/*::                                                                         :*/
/*::  Passed to function:                                                    :*/
/*::    lat1, lon1 = Latitude and Longitude of point 1 (in decimal degrees)  :*/
/*::    lat2, lon2 = Latitude and Longitude of point 2 (in decimal degrees)  :*/
/*::    unit = the unit you desire for results                               :*/
/*::           where: 'M' is statute miles (default)                         :*/
/*::                  'K' is kilometers                                      :*/
/*::                  'N' is nautical miles                                  :*/
/*::  Worldwide cities and other features databases with latitude longitude  :*/
/*::  are available at https://www.geodatasource.com                          :*/
/*::                                                                         :*/
/*::  For enquiries, please contact sales@geodatasource.com                  :*/
/*::                                                                         :*/
/*::  Official Web site: https://www.geodatasource.com                        :*/
/*::                                                                         :*/
/*::         GeoDataSource.com (C) All Rights Reserved 2017		   		     :*/
/*::                                                                         :*/
/*::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::*/
// https://www.geodatasource.com/developers/php
    public function distance($lat1, $lon1, $lat2, $lon2) {

        $theta = $lon1 - $lon2;
        $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
        $dist = acos($dist);
        $dist = rad2deg($dist);
        $miles = $dist * 60 * 1.1515;

        return $miles * 1609.344;
        // if ($unit == "K") {
        //     return ($miles * 1.609344);
        // } else if ($unit == "N") {
        //     return ($miles * 0.8684);
        // } else {
        //     return $miles;
        // }
    }

}
