<?php

namespace App\Classes;

class TRKParser {

  var $insideitem  = false;
  var $tag         = "";
  var $ele         = "";
  var $lat         = "";
  var $lon         = "";
  var $time        = "";
  var $date        = "";
  var $sql         = "";
  var $event_id    = "";
  var $array       = [];
  var $temp        = [];

  function startElement($parser, $tagName, $attrs) {
    if ($this->insideitem) {
      $this->tag = $tagName;
    } elseif ($tagName == "TRKPT" || $tagName == "WPT" || $tagName == "RTEPT") {
      $this->insideitem = true;

      $lat = $attrs['LAT'];
      $lon = $attrs['LON'];

      # This will write the first part of INSERT statment
      $this->temp = ['lat' => $lat, 'lon' => $lon];
    }
  }

  function endElement($parser, $tagName) {

    if ($tagName == "TRKPT" || $tagName == "WPT" || $tagName == "RTEPT") {
      $ele      = htmlspecialchars(trim($this->ele));
      $datetime = htmlspecialchars(trim($this->time));
      if ($datetime){
        # This will split date-time into date & time
        list($date,$mytime) = explode("T", $datetime);
        list($time,$null)   = explode("Z", $mytime);
        // echo $date;
        // echo $time;
        // $this->temp['ele'] = $ele;
        // $this->temp['date'] = $date;
        // $this->temp['time'] = $time;
      } else{
        // $this->temp['ele'] = "'$ele', NULL, NULL);\n";
        // $this->temp['ele'] = $ele;
        // $this->temp['date'] = NULL;
        // $this->temp['time'] = NULL;
      }
      $this->array[] = $this->temp;
      # This will write the last part of INSERT statment
      // $this->sql .= "'$ele', '$date', '$time');\n";\
      $this->ele         = "";
      $this->lat         = "";
      $this->lon         = "";
      $this->time        = "";
      $this->insideitem  = false;
    }
  }

  function characterData($parser, $data) {
    if ($this->insideitem) {
      switch ($this->tag) {
        case "ELE":
        $this->ele .= $data;
        break;
        case "TIME":
        $this->time  .= $data;
        break;
      }
    }
  }
}

// $xml_parser = xml_parser_create();
// $rss_parser = new TRKParser();
// xml_set_object($xml_parser, $rss_parser);
// xml_set_element_handler($xml_parser, "startElement", "endElement");
// xml_set_character_data_handler($xml_parser, "characterData");

// $fp = fopen("TRKFile.gpx","r")
//       or die("Error reading Track Point data.");

// while ($data = fread($fp, 4096))
//   xml_parse($xml_parser, $data, feof($fp))
//     or die(sprintf("XML error: %s at line %d",
//       xml_error_string(xml_get_error_code($xml_parser)),
//       xml_get_current_line_number($xml_parser)));

// fclose($fp);

// xml_parser_free($xml_parser);

// echo $rss_parser->sql;

?>
