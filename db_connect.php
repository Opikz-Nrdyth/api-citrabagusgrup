<?php

date_default_timezone_set('Asia/Jakarta');

$host = 'localhost';
$user = 'bimbelc3_Admin';
$password = 'GuxX8J5BrcCsa34';
$dbname = 'bimbelc3_citrabagusgrup';

$conn = new mysqli( $host, $user, $password, $dbname );

// Mengecek koneksi
if ( $conn->connect_error ) {
    die( 'Connection failed: ' . $conn->connect_error );
}

header ( 'Access-Control-Allow-Origin: *' );
header ( 'Access-Control-Allow-Methods: *' );
header ( 'Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With' );

?>