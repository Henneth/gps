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
    array( 'db' => 'created_at', 'dt' => 1 ),
    array( 'db' => 'TIMEDIFF(datetime, created_at)', 'dt' => 2 ),
    array( 'db' => 'device_id', 'dt' => 3 ),
    array( 'db' => 'longitude', 'dt' => 4 ),
    array( 'db' => 'latitude', 'dt' => 5 ),
    array( 'db' => 'battery_level', 'dt' => 6 )
);

// SQL server connection information
$sql_details = array(
    'user' => 'root',
    'pass' => 'root',
    'db'   => !empty($_GET['live']) ? "gps_live_".$_GET['live'] : 'gps',
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
