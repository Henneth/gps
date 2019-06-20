<?php

/*
 * DataTables example server-side processing script.
 *
 * Please note that this script is intentionally extremely simply to show how
 * server-side processing can be implemented, and probably shouldn't be used as
 * the basis for a large complex system. It is suitable for simple use cases as
 * for learning.
 *
 * See http://datatables.net/usage/server-side for full details on the server-
 * side processing requirements of DataTables.
 *
 * @license MIT - http://datatables.net/license_mit
 */

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * Easy set variables
 */

// DB table to use
$table = 'raw_data';

// Table's primary key
$primaryKey = 'id';

// Array of database columns which should be read and sent back to DataTables.
// The `db` parameter represents the column name in the database, while the `dt`
// parameter represents the DataTables column identifier. In this case simple
// indexes
$columns = array(
    array( 'db' => 'datetime', 'dt' => 0 ),
    array( 'db' => 'sent_at', 'dt' => 1 ),
    array( 'db' => 'created_at', 'dt' => 2 ),
    array( 'db' => 'TIMEDIFF(created_at, datetime)', 'dt' => 3 ),
    array( 'db' => 'TIMEDIFF(datetime, (SELECT MAX(datetime) FROM raw_data AS r1 WHERE r1.device_id = r2.device_id AND r1.datetime < r2.datetime LIMIT 1))', 'dt' => 4 ),
    array( 'db' => 'device_id', 'dt' => 5 ),
    array( 'db' => 'longitude', 'dt' => 6 ),
    array( 'db' => 'latitude', 'dt' => 7 ),
    array( 'db' => 'battery_level', 'dt' => 8 )
);

require_once('../setEnv.php');

// SQL server connection information
$sql_details = array(
    'user' => 'root',
    'pass' => ($env == 'server') ? 'rts123' : 'root',
    'db'   => !empty($_GET['event_id']) ? "gps_live_".$_GET['event_id'] : 'gps',
    'host' => '127.0.0.1'
);


/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * If you just want to use the basic configuration for DataTables with PHP
 * server-side, there is no need to edit below this line.
 */

require( '../libs/ssp.class.php' );

echo json_encode(
    SSP::simple( $_GET, $sql_details, $table, $primaryKey, $columns )
);
