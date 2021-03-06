<!-- Start -->
<?php
// This script is for calculating athletes' distance progresses

// $before = microtime(true);

require_once('../setEnv.php');

$host = '127.0.0.1';
$user = 'root';
$pass = ($env == 'server') ? 'rts123' : 'root';
$charset = 'utf8mb4';
if ( !empty($argv[1]) && ($argv[1] == 'replay') && is_numeric($argv[2]) ){
    $db = 'gps';
} else{
    $db = 'gps_live';
}

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$opt = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];
$pdo = new PDO($dsn, $user, $pass, $opt);

if ( !empty($argv[1]) && ($argv[1] == 'replay') && is_numeric($argv[2]) ){
    $event['event_id'] = $argv[2];
    $events = [$event];
    print_r($events);
} else {
    // Event IDs
    $events = $pdo->query('SELECT event_id FROM events WHERE current = 1')->fetchAll();
    if(empty($events)){
        echo "No live events";
        return;
    }
}




foreach ($events as $event) {
    //define event id
    $event_id = $event['event_id'];
    echo $event_id . "\n";

    // get route data
    $eventTimeRange_stmt = $pdo->prepare('SELECT datetime_from, datetime_to FROM events WHERE event_id = :event_id');
    $eventTimeRange_stmt->execute(array(':event_id' => $event_id));
    $eventTimeRange = $eventTimeRange_stmt->fetchAll();
    if(empty($eventTimeRange)){
        echo "empty event time range\n";
        continue;
    }

    // get route
    $route_stmt = $pdo->prepare('SELECT route FROM routes WHERE event_id = :event_id');
    $route_stmt->execute(array(':event_id' => $event_id));
    $route = $route_stmt->fetchAll();
    if ($route){
        $array = json_decode($route[0]['route'], 1);
    } else {
        echo "empty route\n";
        continue;
    }

    // get checkpoint data relevant
    $checkpointData_stmt = $pdo->prepare('SELECT route_index, min_time, checkpoint FROM route_distances WHERE event_id = :event_id AND is_checkpoint = 1 ORDER BY route_index');
    $checkpointData_stmt->execute(array(':event_id' => $event_id));
    $checkpointData = $checkpointData_stmt->fetchAll();
    array_unshift($checkpointData, array("route_index" => 0));
    // echo"<pre>".print_r($checkpointData,1)."</pre>";

    // get gps data
    $gps_data_stmt = $pdo->prepare('SELECT * FROM gps_data WHERE :datetime_from <= gps_data.datetime AND gps_data.datetime <= :datetime_to');
    $gps_data_stmt->execute(array(':datetime_from' => $eventTimeRange[0]['datetime_from'], ':datetime_to' => $eventTimeRange[0]['datetime_to']));
    $gps_data = $gps_data_stmt->fetchAll();

    // copy the last ID from gps_data to lastID
    // $importLastID = $pdo->prepare('REPLACE INTO last_id (last_id, event_id) SELECT MAX(id), device_mapping.event_id FROM gps_data INNER JOIN device_mapping ON device_mapping.device_id = gps_data.device_id WHERE device_mapping.event_id = :event_id GROUP BY device_mapping.event_id ');
    // $importLastID->execute(array(':event_id' => $event_id));

    $gps_data_by_device_id = group_by($gps_data, "device_id");
    // echo"<pre>".print_r($gps_data_by_device_id,1)."</pre>";


    $cpArray = [];
    // looping by each device
    foreach ($gps_data_by_device_id as $device_id => $gps_row) {
        // get the largest route progress's index
        $lastReachedPoint_stmt = $pdo->prepare('SELECT MAX(route_index) FROM route_progress WHERE route_progress.event_id = :event_id AND route_progress.device_id = :device_id GROUP BY route_progress.device_id');
        $lastReachedPoint_stmt->execute(array(':device_id' => $device_id, ':event_id' => $event_id));
        $lastReachedPoint_raw = $lastReachedPoint_stmt->fetchAll();

        // initialize
        $reachedCheckpoint = -1;
        $lastCheckpointLeft = false;
        $finished = false;
        $checkpointTimes[0] = $eventTimeRange[0]['datetime_from'];

        if ($lastReachedPoint_raw){
            $lastReachedPoint = $lastReachedPoint_raw[0]['MAX(route_index)'];
            $reachedCheckpoint = getCurrentCheckpoint($lastReachedPoint, $checkpointData);
        } else {
            $lastReachedPoint = -1;
        }
        // echo"<pre>".print_r($lastReachedPoint,1)."</pre>";

        // looping by each gps data row
        foreach ($gps_row as $key2 => $datum) {
            $lat2 = $datum['latitude_final'];
            $lon2 = $datum['longitude_final'];

            // looping by each route point
            foreach ($array as $key => $routePoint) {
                // echo $key.'<br/>';
                $lat1 = $routePoint['lat'];
                $lon1 = $routePoint['lon'];
                // echo $datum['latitude_final'].'<br/>';
                $result = distanceUnder100m($lat1, $lon1, $lat2, $lon2);

                if ($result && !$finished) {
                    if ($lastCheckpointLeft) {
                        if ($key > $lastReachedPoint && $key <= $checkpointData[$reachedCheckpoint+1]['route_index']){
                            if ($key == $checkpointData[$reachedCheckpoint+1]['route_index']) {
                                if (!empty($checkpointData[$reachedCheckpoint+1]['min_time']) && !checkMinTime($checkpointData[$reachedCheckpoint+1]['min_time'], $checkpointTimes[$reachedCheckpoint], $datum['datetime'])) {
                                    $finished = true;
                                    break;
                                }
                                $reachedCheckpoint++;
                                // echo"<pre>".print_r($reachedCheckpoint,1)."</pre>";
                                $finished = true;
                            }

                            $tempArray['event_id'] = $event_id;
                            $tempArray['route_index'] = $key;
                            $tempArray['device_id'] = $device_id;
                            $tempArray['reached_at'] = $datum['datetime'];
                            $lastReachedPoint = $key;
                            $cpArray[] = $tempArray;
                            // echo"<pre>".print_r($tempArray,1)."</pre>";
                        }
                    } else {
                        if ($key > $lastReachedPoint && $key < $checkpointData[$reachedCheckpoint+2]['route_index']){
                            if ($key >= $checkpointData[$reachedCheckpoint+1]['route_index']) {
                                if (!empty($checkpointData[$reachedCheckpoint+1]['min_time']) && !checkMinTime($checkpointData[$reachedCheckpoint+1]['min_time'], $checkpointTimes[$reachedCheckpoint], $datum['datetime'])) {
                                    $finished = true;
                                    break;
                                }
                                $reachedCheckpoint++;
                                // echo"<pre>".print_r($reachedCheckpoint,1)."</pre>";
                                if ($reachedCheckpoint == sizeof($checkpointData)-2) {
                                    $lastCheckpointLeft = true;
                                }
                            }

                            if ($key == $checkpointData[$reachedCheckpoint]['route_index']) {
                                $checkpointTimes[$reachedCheckpoint] = $datum['datetime'];
                            } else {
                                $checkpointTimes[$reachedCheckpoint+1] = $datum['datetime'];
                            }

                            $tempArray['event_id'] = $event_id;
                            $tempArray['route_index'] = $key;
                            $tempArray['device_id'] = $device_id;
                            $tempArray['reached_at'] = $datum['datetime'];
                            $lastReachedPoint = $key;
                            $cpArray[] = $tempArray;
                            // echo"<pre>".print_r($tempArray,1)."</pre>";
                        }
                    }
                }

            }
        }
        // echo"<pre>".print_r($cpArray,1)."</pre>";
        // insert into DB
        if ($cpArray){
            pdoMultiInsert('route_progress', $cpArray, $pdo);
        }

        $cpArray = [];
    }
}


// $after = microtime(true);
// echo ($after-$before) . " sec\n";

function checkMinTime($min_time, $prev_time, $current_time) {
    $timeFirst  = strtotime($prev_time);
    $timeSecond = strtotime($current_time);
    $differenceInSeconds = $timeSecond - $timeFirst;
    // echo $differenceInSeconds.' ';

    $str_time = $min_time;
    $str_time = preg_replace("/^([\d]{1,2})\:([\d]{2})$/", "00:$1:$2", $str_time);
    sscanf($str_time, "%d:%d:%d", $hours, $minutes, $seconds);
    $time_seconds = $hours * 3600 + $minutes * 60 + $seconds;
    // echo $time_seconds;

    return $differenceInSeconds >= $time_seconds;
}

function getCurrentCheckpoint($currentRouteIndex, $checkpointData) {
    foreach ($checkpointData as $key => $value) {
        if ($currentRouteIndex > $value['route_index']) {
            $lastReachedCheckpoint = !empty($value['checkpoint']) ? $value['checkpoint'] : 0;
        } else {
            break;
        }
    }
    return $lastReachedCheckpoint;
}

// group as an array by key
function group_by($array, $key) {
    $return = array();
    foreach($array as $val) {
        $return[$val[$key]][] = $val;
    }
    return $return;
}

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
/*::  are available at https://www.geodatasource.com                         :*/
/*::                                                                         :*/
/*::  For enquiries, please contact sales@geodatasource.com                  :*/
/*::                                                                         :*/
/*::  Official Web site: https://www.geodatasource.com                       :*/
/*::                                                                         :*/
/*::         GeoDataSource.com (C) All Rights Reserved 2017		   		     :*/
/*::                                                                         :*/
/*::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::*/
//https://www.geodatasource.com/developers/php
// function distance_decap($lat1, $lon1, $lat2, $lon2, $unit) {
//
//     $theta = $lon1 - $lon2;
//     $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
//     $dist = acos($dist);
//     $dist = rad2deg($dist);
//     $miles = $dist * 60 * 1.1515;
//     $unit = strtoupper($unit);
//
//     if ($unit == "K") {
//         return ($miles * 1.609344);
//     } else if ($unit == "N") {
//         return ($miles * 0.8684);
//     } else {
//         return $miles;
//     }
// }
function distanceUnder100m($lat1, $lon1, $lat2, $lon2) {
    $theta = $lon1 - $lon2;
    $alpha = $lat1 - $lat2;
    $dist = pow(deg2rad($theta),2) + pow(deg2rad($alpha),2) <= 0.000000000246368;
    return $dist;
}


/**
* A custom function that automatically constructs a multi insert statement.
*
* @param string $tableName Name of the table we are inserting into.
* @param array $data An "array of arrays" containing our row data.
* @param PDO $pdoObject Our PDO object.
* @return boolean TRUE on success. FALSE on failure.
*/
function pdoMultiInsert($tableName, $data, $pdoObject){

    //Will contain SQL snippets.
    $rowsSQL = array();

    //Will contain the values that we need to bind.
    $toBind = array();

    //Get a list of column names to use in the SQL statement.
    $columnNames = array_keys($data[0]);

    //Loop through our $data array.
    foreach($data as $arrayIndex => $row){
        $params = array();
        foreach($row as $columnName => $columnValue){
            $param = ":" . $columnName . $arrayIndex;
            $params[] = $param;
            $toBind[$param] = $columnValue;
        }
        $rowsSQL[] = "(" . implode(", ", $params) . ")";
    }

    //Construct our SQL statement
    $sql = "INSERT INTO `$tableName` (" . implode(", ", $columnNames) . ") VALUES " . implode(", ", $rowsSQL);

    //Prepare our PDO statement.
    $pdoStatement = $pdoObject->prepare($sql);

    //Bind our values.
    foreach($toBind as $param => $val){
        $pdoStatement->bindValue($param, $val);
    }

    //Execute our statement (i.e. insert the data).
    return $pdoStatement->execute();
}

?>
<!-- End -->
