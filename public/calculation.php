<!-- Start -->
<?php
// This script is for calculating athletes' distance progresses

// $before = microtime(true);

require_once('../setEnv.php');

$host = '127.0.0.1';
$user = 'root';
$pass = ($env == 'server') ? 'rts123' : 'root';
$charset = 'utf8mb4';
$db = 'gps';

// if ( !empty($argv[1]) && ($argv[1] == 'replay') && is_numeric($argv[2]) ){
//     // Event IDs
//     $event['event_id'] = $argv[2];
//     $events = [$event];
// } else{
// }

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$opt = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];
$pdo = new PDO($dsn, $user, $pass, $opt);

// Event IDs
$events = $pdo->query('SELECT event_id FROM gps.events WHERE live = 1')->fetchAll();
if(empty($events)){
    echo "-- No live events --";
    return;
}


foreach ($events as $event) {
    //define event id
    $event_id = $event['event_id'];
    $db = "gps_live_".$event['event_id'];
    echo "== Event ID: " . $event_id . " ==\n";

    // get route data
    $eventTimeRange_stmt = $pdo->prepare('SELECT datetime_from, datetime_to FROM gps.events WHERE event_id = :event_id');
    $eventTimeRange_stmt->execute(array(':event_id' => $event_id));
    $eventTimeRange = $eventTimeRange_stmt->fetchAll();
    if(empty($eventTimeRange)){
        echo "-- No event time range --\n";
        continue;
    }

    // get route
    $route = $pdo->query('SELECT * FROM {$db}.map_point')->fetchAll();
    if (!$route){
        echo "-- No route --\n";
        continue;
    }

    // get bib numbers
    $bib_numbers = $pdo->query('SELECT bib_number FROM {$db}.athletes')->fetchAll();
    if(empty($bib_numbers)){
        echo "-- No athletes --\n";
        continue;
    }

    foreach ($bib_numbers as $bib_number) {

        // get device_id(s) for this athlete
        $device_ids_stmt = $pdo->prepare('SELECT device_id, start_time, end_time FROM {$db}.device_mapping WHERE bib_number = :bib_number');
        $device_ids_stmt->execute(array(':bib_number' => $bib_number));
        $device_ids = $device_ids_stmt->fetchAll();
        if(empty($device_ids)){
            echo "-- No device mapping for this athlete --\n";
            continue;
        }

        // get gps data
        $data_array = [];
        foreach ($device_ids as $device_id) {
            $gps_data_stmt = $pdo->prepare('SELECT * FROM {$db}.raw_data
                WHERE :datetime_from <= gps_data.datetime AND gps_data.datetime <= :datetime_to
                AND :start_time <= gps_data.datetime AND gps_data.datetime <= :end_time
                AND device_id = :device_id');
            $gps_data_stmt->execute(array(
                ':datetime_from' => $eventTimeRange[0]['datetime_from'],
                ':datetime_to' => $eventTimeRange[0]['datetime_to'],
                ':device_id' => $device_id['device_id'],
                ':start_time' => $device_id['start_time'],
                ':end_time' => $device_id['end_time'],
            ));
            $gps_data = $gps_data_stmt->fetchAll();

            $data_array = array_merge($data_array, $gps_data)
        }

        // get the largest distance progress map point order
        $lastReachedPoint_stmt = $pdo->prepare('SELECT MAX(point_order) FROM {$db}.distance_data WHERE device_id = :device_id GROUP BY device_id');
        $lastReachedPoint_stmt->execute(array(':device_id' => $device_id, ':event_id' => $event_id));
        $lastReachedPoint_raw = $lastReachedPoint_stmt->fetchAll();
        $lastReachedPoint = $lastReachedPoint_raw ? $lastReachedPoint_raw[0]['MAX(route_index)'] : -1;

        // get the largest distance progress map point order
        $checkpoints = $pdo->query('SELECT * FROM {$db}.checkpoint')->fetchAll();
        $reachedCheckpoint = $lastReachedPoint != -1 ? getCurrentCheckpoint($lastReachedPoint, $checkpoints) : -1;

        // initialize
        $lastCheckpointLeft = false;
        $finished = false;
        $checkpointTimes[0] = $eventTimeRange[0]['datetime_from'];

        foreach ($data_array as $gps_position) {
            $lat2 = $gps_position['latitude'];
            $lon2 = $gps_position['longitude'];

            foreach ($route as $key => $routePoint) {
                $lat1 = $routePoint['latitude'];
                $lon1 = $routePoint['longitude'];

                if (distanceUnder50m($lat1, $lon1, $lat2, $lon2) && speedCheck($lat2, $lon2, $lat3, $lon3, $previousValidDatetime, $gps_position['datetime'])) {
                    // valid data

                    // Start the transaction. PDO turns autocommit mode off depending on the driver, you don't need to implicitly say you want it off
                    $pdo->beginTransaction();

                    try {
                        // Delete the privileges
                        $stmt = $pdo->prepare('INSERT INTO {$db}.valid_data SELECT * FROM {$db}.raw_data WHERE id = :id');
                        $stmt->bindValue(':id', $gps_position['id'], PDO::PARAM_INT);
                        $stmt->execute();

                        // Delete the group
                        $stmt = $pdo->prepare('DELETE FROM {$db}.raw_data WHERE id = :id');
                        $stmt->bindValue(':id', $gps_position['id'], PDO::PARAM_INT);
                        $stmt->execute();

                        $pdo->commit();
                    } catch(PDOException $e) {
                        $pdo->rollBack();

                        // Report errors
                        echo "=== MYSQL Error. Rollback. ===";
                    }
                } else {
                    // invalid data

                    // Start the transaction. PDO turns autocommit mode off depending on the driver, you don't need to implicitly say you want it off
                    $pdo->beginTransaction();

                    try {
                        // Delete the privileges
                        $stmt = $pdo->prepare('INSERT INTO {$db}.invalid_data SELECT * FROM {$db}.raw_data WHERE id = :id');
                        $stmt->bindValue(':id', $gps_position['id'], PDO::PARAM_INT);
                        $stmt->execute();

                        // Delete the group
                        $stmt = $pdo->prepare('DELETE FROM {$db}.raw_data WHERE id = :id');
                        $stmt->bindValue(':id', $gps_position['id'], PDO::PARAM_INT);
                        $stmt->execute();

                        $pdo->commit();
                    } catch(PDOException $e) {
                        $pdo->rollBack();

                        // Report errors
                        echo "=== MYSQL Error. Rollback. ===";
                    }
                }
            }
        }

    }

    // copy the last ID from gps_data to lastID
    // $importLastID = $pdo->prepare('REPLACE INTO last_id (last_id, event_id) SELECT MAX(id), device_mapping.event_id FROM gps_data INNER JOIN device_mapping ON device_mapping.device_id = gps_data.device_id WHERE device_mapping.event_id = :event_id GROUP BY device_mapping.event_id ');
    // $importLastID->execute(array(':event_id' => $event_id));


    $cpArray = [];
    // looping by each device
    foreach ($gps_data_by_device_id as $device_id => $gps_row) {
        // get the largest route progress's index
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

function getCurrentCheckpoint($currentRouteIndex, $checkpoints) {
    foreach ($checkpoints as $checkpoint) {
        if ($currentRouteIndex > $checkpoint['point_order']) {
            $lastReachedCheckpoint = !empty($checkpoint['checkpoint']) ? $checkpoint['checkpoint'] : 0;
        } else {
            break;
        }
    }
    return $lastReachedCheckpoint;
}

// check speed limit
function exceedSpeedLimit() {

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
