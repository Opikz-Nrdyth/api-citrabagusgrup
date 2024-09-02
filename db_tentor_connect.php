<?php
$host = 'localhost';
$user = 'bimbelc3_Admin';
$password = 'GuxX8J5BrcCsa34';
$dbname = 'bimbelc3_presensitentor';

$tentorDB = new mysqli( $host, $user, $password, 'bimbelc3_presensitentor' );

if ( $tentorDB->connect_error ) {
    die( 'Connection failed: ' . $conn->connect_error );
}
header ( 'Access-Control-Allow-Origin: *' );
header ( 'Access-Control-Allow-Methods: *' );
header ( 'Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With' );
?>