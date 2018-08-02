<?php

function distance1($lat1, $lon1, $lat2, $lon2, $unit = "K") {
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

function distance2($lat1, $lon1, $lat2, $lon2) {
    $theta = $lon1 - $lon2;
    $alpha = $lat1 - $lat2;
    $dist = (pow(deg2rad($theta),2) + pow(deg2rad($alpha),2)) * 40589641;
    return pow($dist, .5);
}

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



// $before = microtime(true);
//
// for ($i=0 ; $i<100000 ; $i++) {
//     distance1(22.245135, 114.245482, 22.245018, 114.245491);
//     distance1(22.253487, 114.245402, 22.253487, 114.244505);
//     distance1(22.254485, 114.243057, 22.253487, 114.244505);
//     distance1(22.254485, 114.243057, 22.253537, 114.245438);
//     distance1(22.242106, 114.245722, 22.229925, 114.251210);
//     distance1(22.257268, 114.236726, 22.229925, 114.251210);
// }
//
// $after = microtime(true);
// echo ($after-$before)/$i . " sec/serialize\n";
//
// $before = microtime(true);
//
// for ($i=0 ; $i<100000 ; $i++) {
//     distanceUnder100m(22.245135, 114.245482, 22.245018, 114.245491);
//     distanceUnder100m(22.253487, 114.245402, 22.253487, 114.244505);
//     distanceUnder100m(22.254485, 114.243057, 22.253487, 114.244505);
//     distanceUnder100m(22.254485, 114.243057, 22.253537, 114.245438);
//     distanceUnder100m(22.242106, 114.245722, 22.229925, 114.251210);
//     distanceUnder100m(22.257268, 114.236726, 22.229925, 114.251210);
// }
//
// $after = microtime(true);
// echo ($after-$before)/$i . " sec/serialize\n";

$arr = [22.245135, 114.245482, 22.245018, 114.245491];
echo distance1($arr[0], $arr[1], $arr[2], $arr[3]).' ';
echo distance2($arr[0], $arr[1], $arr[2], $arr[3]).' ';
echo distanceUnder50m($arr[0], $arr[1], $arr[2], $arr[3]).' <br>';

$arr = [22.253487, 114.244402, 22.253487, 114.244505];
echo distance1($arr[0], $arr[1], $arr[2], $arr[3]).' ';
echo distance2($arr[0], $arr[1], $arr[2], $arr[3]).' ';
echo distanceUnder50m($arr[0], $arr[1], $arr[2], $arr[3]).' <br>';

$arr = [22.253482, 114.244462, 22.253487, 114.244505];
echo distance1($arr[0], $arr[1], $arr[2], $arr[3]).' ';
echo distance2($arr[0], $arr[1], $arr[2], $arr[3]).' ';
echo distanceUnder50m($arr[0], $arr[1], $arr[2], $arr[3]).' <br>';

$arr = [22.253487, 114.245402, 22.253487, 114.245005];
echo distance1($arr[0], $arr[1], $arr[2], $arr[3]).' ';
echo distance2($arr[0], $arr[1], $arr[2], $arr[3]).' ';
echo distanceUnder50m($arr[0], $arr[1], $arr[2], $arr[3]).' <br>';

$arr = [22.253487, 114.245402, 22.253487, 114.244505];
echo distance1($arr[0], $arr[1], $arr[2], $arr[3]).' ';
echo distance2($arr[0], $arr[1], $arr[2], $arr[3]).' ';
echo distanceUnder50m($arr[0], $arr[1], $arr[2], $arr[3]).' <br>';

$arr = [22.254485, 114.243057, 22.253487, 114.244505];
echo distance1($arr[0], $arr[1], $arr[2], $arr[3]).' ';
echo distance2($arr[0], $arr[1], $arr[2], $arr[3]).' ';
echo distanceUnder50m($arr[0], $arr[1], $arr[2], $arr[3]).' <br>';

$arr = [22.254485, 114.243057, 22.253537, 114.245438];
echo distance1($arr[0], $arr[1], $arr[2], $arr[3]).' ';
echo distance2($arr[0], $arr[1], $arr[2], $arr[3]).' ';
echo distanceUnder50m($arr[0], $arr[1], $arr[2], $arr[3]).' <br>';

$arr = [22.242106, 114.245722, 22.229925, 114.251210];
echo distance1($arr[0], $arr[1], $arr[2], $arr[3]).' ';
echo distance2($arr[0], $arr[1], $arr[2], $arr[3]).' ';
echo distanceUnder50m($arr[0], $arr[1], $arr[2], $arr[3]).' <br>';

$arr = [22.257268, 114.236726, 22.229925, 114.251210];
echo distance1($arr[0], $arr[1], $arr[2], $arr[3]).' ';
echo distance2($arr[0], $arr[1], $arr[2], $arr[3]).' ';
echo distanceUnder50m($arr[0], $arr[1], $arr[2], $arr[3]).' <br>';
