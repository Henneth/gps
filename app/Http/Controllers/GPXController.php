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

        // echo $rss_parser->array;
        $route = json_encode($rss_parser->array);

        DrawRoute_Model::drawRouteUpdate($event_id, $route);
        // print_r($route);
        // DB::table('routes')->insert(
        //     ['event_id'=> $event_id, 'route' =>$route]
        // );
        // DB::unprepared($rss_parser->sql);
        return redirect('event/'.$event_id.'/draw-route')->with('success', 'Excel file imported.');
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
}
