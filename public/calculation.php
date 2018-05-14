<?php
$host = 'localhost';
$db   = 'gps';
$user = 'root';
$pass = 'root';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$opt = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];
$pdo = new PDO($dsn, $user, $pass, $opt);

// get route data
$eventTimeRange = $pdo->query('SELECT datetime_from, datetime_to FROM events WHERE event_id = 7')->fetchAll();
if(empty($eventTimeRange)){
    return;
}

// get last ID
$lastIDArray = $pdo->query('SELECT lastID FROM lastID WHERE event_id = 7 LIMIT 1')->fetchAll();
// if(!empty($lastIDArray)){
//     $lastID = $lastIDArray[0]['lastID'];
// }else{
    $lastID = 0;
// }
// echo"<pre>".print_r($lastID,1)."</pre>";

// get route
$route = $pdo->query('SELECT route FROM routes WHERE event_id = 7')->fetchAll();
if ($route){
    $array = json_decode($route[0]['route'], 1);
} else {
    return;
}

// get gps data
$gps_data_stmt = $pdo->prepare('SELECT * FROM gps_data WHERE :datetime_from <= gps_data.datetime AND gps_data.datetime <= :datetime_to AND id > :lastID');
$gps_data_stmt->execute(array(':datetime_from' => $eventTimeRange[0]['datetime_from'], ':datetime_to' => $eventTimeRange[0]['datetime_to'], ':lastID' => $lastID));
$gps_data = $gps_data_stmt->fetchAll();

// copy the last ID from gps_data to lastID
$importLastID = $pdo->prepare('REPLACE INTO lastID (lastID, event_id) SELECT MAX(id), device_mapping.event_id FROM gps_data INNER JOIN device_mapping ON device_mapping.device_id = gps_data.device_id WHERE device_mapping.event_id = :event_id GROUP BY device_mapping.event_id ');
$importLastID ->execute(array(':event_id' => 7 ));

$gps_data_by_device_id = group_by($gps_data, "device_id");
// echo"<pre>".print_r($gps_data_by_device_id,1)."</pre>";




$cpArray = [];


foreach ($gps_data_by_device_id as $device_id => $gps_row) {
    // get the largest route progress's index
    $getRouteIndex = $pdo->prepare('SELECT MAX(route_index) FROM route_progress WHERE route_progress.event_id = 7 AND route_progress.device_id = :device_id GROUP BY route_progress.device_id');
    $getRouteIndex -> execute(array(':device_id' => $device_id ));
    $getRouteIndex = $getRouteIndex->fetchAll();

    if ($getRouteIndex){
        $lastReachedPoint = $getRouteIndex[0]['MAX(route_index)'];
    } else {
        $lastReachedPoint = -1;
    }
    echo"<pre>".print_r($gps_row,1)."</pre>";
    echo "pass";
    foreach ($gps_row as $key2 => $datum) {
        $lat2 = $datum['latitude_final'];
        $lon2 = $datum['longitude_final'];


        foreach ($array as $key => $routePoint) {
            // echo $key.'<br/>';
            $lat1 = $routePoint['lat'];
            $lon1 = $routePoint['lon'];
            // echo $datum['latitude_final'].'<br/>';
            $result = round(distance($lat1, $lon1, $lat2, $lon2, 'K') * 1000);
            // echo round($result);
            // echo '<br/>';
            // echo"<pre>".print_r($result,1)."</pre>";

            if ($result <= 100 && ($key > $lastReachedPoint && $key < $lastReachedPoint + 80)){
                $tempArray['event_id'] = 7;
                $tempArray['route_index'] = $key;
                $tempArray['device_id'] = $device_id;
                $tempArray['reached_at'] = $datum['datetime'];
                $lastReachedPoint = $key;
                $cpArray[] = $tempArray;

                echo"<pre>".print_r($tempArray['device_id'],1)."</pre>";

            }
        }
    }
    echo"<pre>".print_r($cpArray,1)."</pre>";
    // insert into DB
    if ($cpArray){
        pdoMultiInsert('route_progress', $cpArray, $pdo);
    }
    $cpArray = [];
    // echo"<pre>".print_r($test,1)."</pre>";
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
function distance($lat1, $lon1, $lat2, $lon2, $unit) {

  $theta = $lon1 - $lon2;
  $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
  $dist = acos($dist);
  $dist = rad2deg($dist);
  $miles = $dist * 60 * 1.1515;
  $unit = strtoupper($unit);

  if ($unit == "K") {
    return ($miles * 1.609344);
  } else if ($unit == "N") {
      return ($miles * 0.8684);
    } else {
        return $miles;
      }
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
