<?php

namespace App\Http\Controllers;

use DB;
use App\Http\Controllers\Controller;
use App\Classes\TRKParser;

class GPXController extends Controller {
    public function index($event_id) {
        $xml_parser = xml_parser_create();
        $rss_parser = new TRKParser();
        $rss_parser->event_id = $event_id;

        xml_set_object($xml_parser, $rss_parser);
        xml_set_element_handler($xml_parser, "startElement", "endElement");
        xml_set_character_data_handler($xml_parser, "characterData");
         
        $fp = fopen("TRKFile.gpx","r")
              or die("Error reading Track Point data.");

        while ($data = fread($fp, 4096))
          xml_parse($xml_parser, $data, feof($fp))
            or die(sprintf("XML error: %s at line %d",
              xml_error_string(xml_get_error_code($xml_parser)),
              xml_get_current_line_number($xml_parser)));
         
        fclose($fp);
         
        xml_parser_free($xml_parser);

        echo $rss_parser->sql;
        DB::unprepared($rss_parser->sql);
    }
}
