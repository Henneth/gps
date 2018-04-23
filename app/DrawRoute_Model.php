<?php

namespace App;

use DB;
use Config;
use PDO;
use Illuminate\Database\Eloquent\Model;

class DrawRoute_Model extends Model
{
    public static function drawRouteUpdate($event_id, $route) {
        DB::insert("INSERT INTO routes (`event_id`, `route`) VALUES (:event_id, :route) ON DUPLICATE KEY UPDATE route = :route2", ["event_id" => $event_id, "route" => $route, "route2"=>$route]);
    }
}
