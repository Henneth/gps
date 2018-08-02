<?php

// Read JSON file
$json = file_get_contents('./gps_data.json');

//Decode JSON
$json_data = json_decode($json,true);

//Print data
echo '<pre>' . print_r($json_data, true) . '</pre>';

$host = '127.0.0.1';
$user = 'root';
$pass = 'root';
$charset = 'utf8mb4';
$db = 'gps_live';


$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$opt = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];
$pdo = new PDO($dsn, $user, $pass, $opt);
// set default timezone
date_default_timezone_set('Asia/Hong_Kong');

// e.g. php insertSQL.php '2018-07-23 09:00:00' '2018-07-23 18:00:00'
// read args from terminal line
if ( !empty($argv[1])  && !empty($argv[2]) ){
   $startTimeTemp = date_create($argv[1]);
   $endTimeTemp = date_create($argv[2]);
   $startTimeStr = date_format($startTimeTemp, 'Y-m-d H:i:s');
   $endTimeStr = date_format($endTimeTemp, 'Y-m-d H:i:s');
   // strtotime 
   $startTime = strtotime($startTimeStr);
   $endTime = strtotime($endTimeStr);
}
$percentage = ($endTime - $startTime) / 20;

// echo '<pre>'.print_r($startTimeStr,1).'</pre>'."\n";
// echo($startTime."\n");
// echo '<pre>'.print_r($endTimeStr,1).'</pre>'."\n";
// echo($endTime."\n");
// echo('testing: '.($endTime - $startTime) / 20)."\n";
$array_filtered = [];
foreach ($json_data as $value) {
    $arrayDataTimeTemp= date_create($value['datetime']);
    $dataTimeTemp = date_format($arrayDataTimeTemp, 'Y-m-d H:i:s');
    $dataTime = strtotime($dataTimeTemp);
    if ($startTime <= $dataTime && $dataTime <= $endTime){
        $array_filtered[] = $value;
    }
}


// get the current date and time
$initTimeStr = date('Y-m-d H:i:s', time());
$initTime = strtotime($initTimeStr);
// echo 'Current Time: '. $initTimeStr ."\n";
while ($endTime >= $newTime) {
	$currentTimeStr = date('Y-m-d H:i:s', time());
	$currentTime = strtotime($currentTimeStr);
	// echo $currentTime - $initTime;
	$newTime = ($currentTime - $initTime) * $percentage + $startTime;
  $time = date("Y-m-d H:i:s",$newTime);

  foreach ($array_filtered as $key => $value) {
    $arrayDataTimeTemp2= date_create($value['datetime']);
    $dataTimeTemp2 = date_format($arrayDataTimeTemp2, 'Y-m-d H:i:s');
    $dataTime2 = strtotime($dataTimeTemp2);
    if($dataTime2 < $newTime){
        $stmt = $pdo->prepare('INSERT INTO `gps_dd` (`id`, `device_id`, `latitude`, `latitude_logo`, `latitude_final`, `longitude`, `longitude_logo`, `longitude_final`, `elevation`, `battery_level`, `is_valid`, `datetime`, `created_at`) 
          VALUES (NULL, :device_id, :latitude, :latitude_logo, :latitude_final, :longitude, :longitude_logo, :longitude_final, :elevation, :battery_level, :is_valid, :timeStr, NULL)');
        $stmt->bindParam(':device_id', $value['device_id']);
        $stmt->bindParam(':latitude', $value['latitude']);
        $stmt->bindParam(':latitude_logo', $value['latitude_logo']);
        $stmt->bindParam(':latitude_final', $value['latitude_final']);
        $stmt->bindParam(':longitude', $value['longitude']);
        $stmt->bindParam(':longitude_logo', $value['longitude_logo']);
        $stmt->bindParam(':longitude_final', $value['longitude_final']);
        $stmt->bindParam(':elevation', $value['elevation']);
        $stmt->bindParam(':battery_level', $value['battery_level']);
        $stmt->bindParam(':is_valid', $value['is_valid']);
        $stmt->bindParam(':timeStr', $value['datetime']);
        $stmt->execute();
        unset($array_filtered[$key]);
    }
  }
	echo ($time)."\n";
	sleep(1);
}
