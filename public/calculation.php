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
    echo "== Event ID: " . $event_id . " ==<br>";

    // get route data
    $eventInfo_stmt = $pdo->prepare('SELECT datetime_from, datetime_to, event_type FROM gps.events WHERE event_id = :event_id');
    $eventInfo_stmt->execute(array(':event_id' => $event_id));
    $eventInfo = $eventInfo_stmt->fetchAll();
    if(empty($eventInfo)){
        echo "-- No event time range --<br>";
        continue;
    }

    // get route
    $route = $pdo->query("SELECT * FROM {$db}.map_point")->fetchAll();
    if (!$route){
        echo "-- No route --<br>";
        continue;
    }

    // get bib numbers
    $bib_numbers = $pdo->query("SELECT bib_number FROM {$db}.athletes ORDER BY ABS(bib_number)")->fetchAll();
    if(empty($bib_numbers)){
        echo "-- No athletes --<br>";
        continue;
    }

    foreach ($bib_numbers as $bib_number) {

        echo "==== {$bib_number['bib_number']} ====<br>";

        // get device_id(s) for this athlete
        $device_ids_stmt = $pdo->prepare("SELECT device_id, start_time, end_time FROM {$db}.device_mapping WHERE bib_number = :bib_number");
        $device_ids_stmt->execute(array(':bib_number' => $bib_number['bib_number']));
        $device_ids = $device_ids_stmt->fetchAll();
        if(empty($device_ids)){
            echo "-- No device mapping for this athlete --<br>";
            continue;
        }

        // get gps data
        $data_array = [];
        foreach ($device_ids as $device_id) {
            $gps_data_stmt = $pdo->prepare("SELECT * FROM {$db}.raw_data
                WHERE :datetime_from <= datetime
            	-- AND (:start_time1 IS NULL OR (:start_time2 IS NOT NULL AND datetime >= :start_time3))
            	-- AND (:end_time1 IS NULL OR (:end_time2 IS NOT NULL AND datetime <= :end_time3))
                AND device_id = :device_id AND processed = 0 ORDER BY datetime");
            $gps_data_stmt->execute(array(
                ':datetime_from' => $eventInfo[0]['datetime_from'],
                // ':datetime_to' => $eventInfo[0]['datetime_to'],
                ':device_id' => $device_id['device_id'],
                // ':start_time1' => $device_id['start_time'],
                // ':start_time2' => $device_id['start_time'],
                // ':start_time3' => $device_id['start_time'],
                // ':end_time1' => $device_id['end_time'],
                // ':end_time2' => $device_id['end_time'],
                // ':end_time3' => $device_id['end_time'],
            ));
            $gps_data = $gps_data_stmt->fetchAll();

            $data_array = array_merge($data_array, $gps_data);
        }

        if ($eventInfo[0]['event_type'] == 'fixed route') {

            // get checkpoints and last reached checkpoint
            $checkpoints = $pdo->query("SELECT * FROM {$db}.checkpoint")->fetchAll();

            // variables that are from db and will change on the go
            $lastReachedPoint = [];
            $lastReachedPointNo = 0;
            $reachedCkpt = [];
            $reachedCkptNo = 0;
            $reachedCkptID = $reachedCkptNo + 1;
            $finished = false;
            // $nextCkpt
            $accumulated_distance_since_last_ckpt = 0;

            // get the last reached map point
            $lastReachedPoint_stmt = $pdo->prepare("SELECT * FROM {$db}.distance_data WHERE bib_number = :bib_number1 AND point_order = (SELECT MAX(point_order) FROM {$db}.distance_data WHERE bib_number = :bib_number2) LIMIT 1");
            $lastReachedPoint_stmt->execute([
                ':bib_number1' => $bib_number['bib_number'],
                ':bib_number2' => $bib_number['bib_number']
            ]);
            $lastReachedPoint = $lastReachedPoint_stmt->fetchAll();
            $lastReachedPoint = !empty($lastReachedPoint) ? $lastReachedPoint[0] : [];
            $lastReachedPointNo = !empty($lastReachedPoint) ? $lastReachedPoint['point_order'] : 0;
            if (empty($lastReachedPoint)) {
                $lastReachedPoint['distance_from_start'] = 0;
                $lastReachedPoint['datetime'] = $eventInfo[0]['datetime_from'];
            }

            // get the last reached checkpoint
            $reachedCkpt_stmt = $pdo->prepare("SELECT * FROM {$db}.reached_checkpoint WHERE bib_number = :bib_number1 AND checkpoint_id = (SELECT MAX(checkpoint_id) FROM {$db}.reached_checkpoint WHERE bib_number = :bib_number2) LIMIT 1");
            $reachedCkpt_stmt->execute([
                ':bib_number1' => $bib_number['bib_number'],
                ':bib_number2' => $bib_number['bib_number']
            ]);
            $reachedCkpt = $reachedCkpt_stmt->fetchAll();
            $reachedCkpt = !empty($reachedCkpt) ? $reachedCkpt[0] : [];
            $reachedCkptNo = !empty($reachedCkpt) ? $reachedCkpt[0]['checkpoint_id'] - 1 : 0;
            if (empty($reachedCkpt)) {
                $reachedCkpt['datetime'] = $eventInfo[0]['datetime_from'];
            }

            // get accumulated distance
            $nextCkpt_stmt = $pdo->prepare("SELECT * FROM {$db}.next_checkpoint WHERE bib_number = :bib_number LIMIT 1");
            $nextCkpt_stmt->execute(array(':bib_number' => $bib_number['bib_number']));
            $nextCkpt = $nextCkpt_stmt->fetchAll();
            $accumulated_distance_since_last_ckpt = !empty($nextCkpt) ? $nextCkpt[0]['accumulated_distance_since_last_ckpt'] : 0;

            // initialize
            // $lastCheckpointLeft = false;

            foreach ($data_array as $gps_position) {
                $lat2 = $gps_position['latitude'];
                $lon2 = $gps_position['longitude'];

                $validated = false;
                foreach ($route as $point_order => $routePoint) {
                    $lat1 = $routePoint['latitude'];
                    $lon1 = $routePoint['longitude'];

                    $validData = false;
                    if (distanceUnder50m($lat1, $lon1, $lat2, $lon2)) {
                        $distance_covered = $routePoint['distance_from_start'] - $lastReachedPoint['distance_from_start'];
                        $elapsed_time = elapsed_time($gps_position['datetime'], $lastReachedPoint['datetime']);
                        // echo $distance_covered.' ';
                        // echo $elapsed_time.' <br>';
                        if ($distance_covered > 0 && $elapsed_time > 0 && speedCheck($distance_covered, $elapsed_time)) {
                            $validData = true;
                        }
                    }

                    if ($validData && !$validated) {
                        // Start the transaction. PDO turns autocommit mode off depending on the driver, you don't need to implicitly say you want it off
                        $pdo->beginTransaction();

                        try {
                            $table = $validData ? 'valid_data':'invalid_data';

                            // Copy to valid/invalid data table
                            $stmt = $pdo->prepare("INSERT INTO {$db}.{$table} (id, bib_number, latitude, longitude, distance_covered, elapsed_time, datetime) SELECT id, :bib_number, latitude, longitude, :distance_covered, :elapsed_time, datetime FROM {$db}.raw_data WHERE id = :id");
                            $stmt->bindValue(':id', $gps_position['id']);
                            $stmt->bindValue(':bib_number', $bib_number['bib_number']);
                            $stmt->bindValue(':distance_covered', $distance_covered);
                            $stmt->bindValue(':elapsed_time', $elapsed_time);
                            $stmt->execute();

                            // Flag the raw data
                            $stmt = $pdo->prepare("UPDATE {$db}.raw_data SET processed = 1 WHERE id = :id");
                            $stmt->bindValue(':id', $gps_position['id'], PDO::PARAM_INT);
                            $stmt->execute();

                            $pdo->commit();

                            $validated = true;
                        } catch(PDOException $e) {
                            $pdo->rollBack();

                            // Report errors
                            echo "<pre>".print_r($e,1)."</pre>";
                            echo "=== MYSQL Error. Rollback. ===<br>";
                        }
                    }

                    if ($validData){
                        // Check if distance has progress
                        if (!$finished && $point_order > $lastReachedPointNo) {
                            if (getCurrentCheckpoint($point_order, $checkpoints) > $reachedCkptNo + 1) {
                                continue;
                            }

                            // reached map point
                            $lastReachedPointNo = $point_order;
                            echo "last reached: {$lastReachedPointNo}<br>";

                            // update distances
                            $accumulated_distance_since_last_ckpt = $accumulated_distance_since_last_ckpt + $routePoint['distance_from_start'] - $lastReachedPoint['distance_from_start'];
                            echo "current distance: {$routePoint['distance_from_start']}<br>";
                            echo "previous distance: {$lastReachedPoint['distance_from_start']}<br>";
                            // echo "difference: {($routePoint['distance_from_start'] - $lastReachedPoint['distance_from_start'])}<br>";
                            echo "accumulated_distance_since_last_ckpt: {$accumulated_distance_since_last_ckpt}<br>";
                            $distance_to_next_ckpt = $checkpoints[$reachedCkptNo]['distance_to_next_ckpt'] - $accumulated_distance_since_last_ckpt;
                            echo "distance_to_next_ckpt: {$distance_to_next_ckpt}<br>";

                            try {
                                // insert into distance_data
                                $stmt = $pdo->prepare("INSERT INTO {$db}.distance_data (bib_number, point_order, distance_from_start, datetime) VALUES (:bib_number, :point_order, :distance_from_start, :datetime)");
                                $stmt->bindValue(':bib_number', $bib_number['bib_number']);
                                $stmt->bindValue(':point_order', $point_order);
                                $stmt->bindValue(':distance_from_start', $routePoint['distance_from_start']);
                                $stmt->bindValue(':datetime', $gps_position['datetime']);
                                $stmt->execute();
                            } catch(PDOException $e) {
                                // Report errors
                                echo "<pre>".print_r($e,1)."</pre>";
                            }

                            // update $lastReachedPoint
                            $lastReachedPoint['distance_from_start'] = $routePoint['distance_from_start'];
                            $lastReachedPoint['datetime'] = $gps_position['datetime'];

                            // if ($distance_to_next_ckpt <= 100 && checkMinTime($checkpoints[$reachedCkptNo + 1]['min_time'], $reachedCkpt['datetime'], $gps_position['datetime'])) {
                            if ($distance_to_next_ckpt <= 100) {

                                // reached checkpoint
                                $reachedCkptNo = $reachedCkptNo + 1;
                                $reachedCkptID = $reachedCkptNo + 1;
                                echo "reachedCkptID: {$reachedCkptID}<br>";

                                // update distances
                                $accumulated_distance_since_last_ckpt = $distance_to_next_ckpt * -1;
                                echo "accumulated_distance_since_last_ckpt: {$accumulated_distance_since_last_ckpt}<br>";
                                $distance_to_next_ckpt = $checkpoints[$reachedCkptNo]['distance_to_next_ckpt'] - $distance_to_next_ckpt;
                                echo "distance_to_next_ckpt: {$distance_to_next_ckpt}<br>";

                                try {
                                    // insert into reached_checkpoint
                                    $stmt = $pdo->prepare("INSERT INTO {$db}.reached_checkpoint (bib_number, checkpoint_id, datetime, elapsed_time_btwn_ckpts) VALUES (:bib_number, :checkpoint_id, :datetime, :elapsed_time_btwn_ckpts)");
                                    $stmt->bindValue(':bib_number', $bib_number['bib_number']);
                                    $stmt->bindValue(':checkpoint_id', $reachedCkptID);
                                    $stmt->bindValue(':datetime', $gps_position['datetime']);
                                    $stmt->bindValue(':elapsed_time_btwn_ckpts', elapsed_time($gps_position['datetime'], $reachedCkpt['datetime']));
                                    $stmt->execute();
                                } catch(PDOException $e) {
                                    // Report errors
                                    echo "<pre>".print_r($e,1)."</pre>";
                                }

                                // update $reachedCkpt
                                $reachedCkpt['checkpoint_id'] = $reachedCkptID;
                                $reachedCkpt['datetime'] = $gps_position['datetime'];
                                $reachedCkpt['elapsed_time_btwn_ckpts'] = elapsed_time($gps_position['datetime'], $reachedCkpt['datetime']);

                                // check if finished
                                if (sizeof($checkpoints) == $reachedCkptID) {
                                    echo "finished<br>";
                                    // this athlete has finished
                                    $finished = true;

                                    try {
                                        // insert into distance_data
                                        $stmt = $pdo->prepare("INSERT INTO {$db}.distance_data (bib_number, point_order, distance_from_start, datetime) VALUES (:bib_number, :point_order, :distance_from_start, :datetime)");
                                        $stmt->bindValue(':bib_number', $bib_number['bib_number']);
                                        $stmt->bindValue(':point_order', sizeof($route));
                                        $stmt->bindValue(':distance_from_start', $route[sizeof($route) - 1]['distance_from_start']);
                                        $stmt->bindValue(':datetime', $gps_position['datetime']);
                                        $stmt->execute();
                                    } catch(PDOException $e) {
                                        // Report errors
                                        echo "<pre>".print_r($e,1)."</pre>";
                                    }

                                    // end this loop
                                    continue;
                                }

                                try {
                                    // reset next_checkpoint
                                    $stmt = $pdo->prepare("INSERT INTO {$db}.next_checkpoint (bib_number, checkpoint_id, accumulated_distance_since_last_ckpt, accumulated_time_since_last_ckpt, distance_to_next_ckpt) VALUES (:bib_number, :checkpoint_id, :accumulated_distance_since_last_ckpt, :accumulated_time_since_last_ckpt, :distance_to_next_ckpt) ON DUPLICATE KEY UPDATE accumulated_distance_since_last_ckpt = VALUES(accumulated_distance_since_last_ckpt), accumulated_time_since_last_ckpt = VALUES(accumulated_time_since_last_ckpt), distance_to_next_ckpt = VALUES(distance_to_next_ckpt)");
                                    $stmt->bindValue(':bib_number', $bib_number['bib_number']);
                                    $stmt->bindValue(':checkpoint_id', $reachedCkptID + 1);
                                    $stmt->bindValue(':accumulated_distance_since_last_ckpt', $accumulated_distance_since_last_ckpt);
                                    $stmt->bindValue(':accumulated_time_since_last_ckpt', 0);
                                    $stmt->bindValue(':distance_to_next_ckpt', $distance_to_next_ckpt);
                                    $stmt->execute();
                                } catch(PDOException $e) {
                                    // Report errors
                                    echo "<pre>".print_r($e,1)."</pre>";
                                }
                            } else {
                                try {
                                    // update next_checkpoint
                                    $stmt = $pdo->prepare("INSERT INTO {$db}.next_checkpoint (bib_number, checkpoint_id, accumulated_distance_since_last_ckpt, accumulated_time_since_last_ckpt, distance_to_next_ckpt) VALUES (:bib_number, :checkpoint_id, :accumulated_distance_since_last_ckpt, :accumulated_time_since_last_ckpt, :distance_to_next_ckpt) ON DUPLICATE KEY UPDATE accumulated_distance_since_last_ckpt = VALUES(accumulated_distance_since_last_ckpt), accumulated_time_since_last_ckpt = VALUES(accumulated_time_since_last_ckpt), distance_to_next_ckpt = VALUES(distance_to_next_ckpt)");
                                    $stmt->bindValue(':bib_number', $bib_number['bib_number']);
                                    $stmt->bindValue(':checkpoint_id', $reachedCkptID + 1);
                                    $stmt->bindValue(':accumulated_distance_since_last_ckpt', $accumulated_distance_since_last_ckpt);
                                    $stmt->bindValue(':accumulated_time_since_last_ckpt', elapsed_time($gps_position['datetime'], $reachedCkpt['datetime']));
                                    $stmt->bindValue(':distance_to_next_ckpt', $distance_to_next_ckpt);
                                    $stmt->execute();
                                } catch(PDOException $e) {
                                    // Report errors
                                    echo "<pre>".print_r($e,1)."</pre>";
                                }
                            }

                        }
                    }
                }
            }

        } else {

        }

    }
}


// $after = microtime(true);
// echo ($after-$before) . " sec<br>";

// function checkMinTime($min_time, $prev_time, $current_time) {
//     $timeFirst  = strtotime($prev_time);
//     $timeSecond = strtotime($current_time);
//     $differenceInSeconds = $timeSecond - $timeFirst;
//     // echo $differenceInSeconds.' ';
//
//     $str_time = preg_replace("/^([\d]{1,2})\:([\d]{2})$/", "00:$1:$2", $min_time);
//     sscanf($str_time, "%d:%d:%d", $hours, $minutes, $seconds);
//     $time_seconds = $hours * 3600 + $minutes * 60 + $seconds;
//     // echo $time_seconds;
//
//     return $differenceInSeconds >= $time_seconds;
// }

function elapsed_time($new_time, $old_time) {
    $timeFirst  = strtotime($old_time);
    $timeSecond = strtotime($new_time);
    return $timeSecond - $timeFirst;
}

function speedCheck($distance_covered, $elapsed_time) {
    return $distance_covered / $elapsed_time <= 5;
}

function getCurrentCheckpoint($currentPointOrder, $checkpoints) {
    foreach ($checkpoints as $checkpoint) {
        if ($currentPointOrder > $checkpoint['point_order']) {
            $lastReachedCheckpoint = !empty($checkpoint['checkpoint_no']) ? $checkpoint['checkpoint_no'] : 0;
        } else {
            break;
        }
    }
    return $lastReachedCheckpoint;
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

function distanceUnder50m($lat1, $lon1, $lat2, $lon2) {
    $theta = $lon1 - $lon2;
    $alpha = $lat1 - $lat2;
    $dist = pow(deg2rad($theta),2) + pow(deg2rad($alpha),2) <= 6.159206976E-11;
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
