<?php
require_once( 'db_connect.php' );
// Mendapatkan input tanggal, bulan, dan tahun dari $_GET
$tanggal = isset( $_GET[ 'tanggal' ] ) ? $_GET[ 'tanggal' ] : date( 'd' );
$bulan = isset( $_GET[ 'bulan' ] ) ? $_GET[ 'bulan' ] : date( 'm' );
$tahun = isset( $_GET[ 'tahun' ] ) ? $_GET[ 'tahun' ] : date( 'Y' );
$tentor = isset( $_GET[ 'tentor' ] )?$_GET[ 'tentor' ]:'';
$kelas = isset( $_GET[ 'kelas' ] )?$_GET[ 'kelas' ]:'';

$bulanNow = date( 'm' );

// Membuat objek DateTime dengan input yang diberikan
$date = new DateTime( "$tahun-$bulan-$tanggal" );

// Mendapatkan nama bulan dalam bahasa Indonesia
$nama_bulan = array(
    1 => 'Januari',
    2 => 'Februari',
    3 => 'Maret',
    4 => 'April',
    5 => 'Mei',
    6 => 'Juni',
    7 => 'Juli',
    8 => 'Agustus',
    9 => 'September',
    10 => 'Oktober',
    11 => 'November',
    12 => 'Desember'
);

// Mendapatkan nama hari dalam bahasa Indonesia
$nama_hari = array(
    1 => 'Senin',
    2 => 'Selasa',
    3 => 'Rabu',
    4 => 'Kamis',
    5 => 'Jumat',
    6 => 'Sabtu',
    7 => 'Minggu'
);

// Mendapatkan jumlah hari dalam bulan yang dipilih
$jumlah_hari = cal_days_in_month( CAL_GREGORIAN, $bulan, $tahun );

// Mendapatkan nomor hari pertama dan terakhir dalam bulan yang dipilih
$hari_pertama = ( int ) $date->modify( 'first day of this month' )->format( 'N' );
$hari_terakhir = ( int ) $date->modify( 'last day of this month' )->format( 'N' );

// Membuat array kosong untuk menyimpan data kalender
$data = array();

function cekJadwal( $conn, $tentor, $kelas, $tanggalCek ) {
    $sql = mysqli_query( $conn, 'SELECT *, DATE_FORMAT(STR_TO_DATE(jam, "%H:%i" ), "%H:%i" ) AS new_jam FROM `jadwal` ORDER BY new_jam ASC' );
    if ( $tentor != '' ) {
        $sql = mysqli_query( $conn, 'SELECT *, DATE_FORMAT( STR_TO_DATE( jam, "%H:%i" ), "%H:%i" ) AS new_jam FROM `jadwal` WHERE pengajar LIKE "%'.$tentor.'%" ORDER BY new_jam ASC' );
    }

    if ( $kelas != '' ) {
        $sql = mysqli_query( $conn, 'SELECT *, DATE_FORMAT( STR_TO_DATE( jam, "%H:%i" ), "%H:%i" ) AS new_jam FROM `jadwal` WHERE kelas LIKE "%'.$kelas.'%" ORDER BY new_jam ASC' );
    }
    $dataArr = array();
    while( $data_jadwal = mysqli_fetch_assoc( $sql ) ) {
        if ( strtolower( $tanggalCek ) == strtolower( $data_jadwal[ 'hari_tanggal' ] ) ) {
            $dataArr[] = $data_jadwal;
        }
    }
    return $dataArr;
}
// Mengisi data kalender dengan tanggal-tanggal dari bulan sebelumnya jika hari pertama bukan Senin
if ( $hari_pertama != 1 ) {
    // Mendapatkan jumlah hari dalam bulan sebelumnya
    $jumlah_hari_sebelumnya = cal_days_in_month( CAL_GREGORIAN, $bulan, $tahun );

    // Mengisi data kalender dengan tanggal-tanggal dari bulan sebelumnya mulai dari hari terakhir
    for ( $i = $hari_pertama - 1; $i >= 1; $i-- ) {
        $data[] = array(
            'Tanggal' => $jumlah_hari_sebelumnya,
            'Hari' => $nama_hari[ $i ],
            'Tipe' => 'prev',
            'Schedule' => array()
        );
        $jumlah_hari_sebelumnya--;
    }

    // Membalik urutan data kalender agar tanggal-tanggal dari bulan sebelumnya berurutan
    $data = array_reverse( $data );
}

// Mengisi data kalender dengan tanggal-tanggal dari bulan yang dipilih
for ( $i = 1; $i <= $jumlah_hari; $i++ ) {
    // Mendapatkan nomor hari dari tanggal yang dipilih
    $nomor_hari = ( int ) $date->setDate( $tahun, $bulan, $i )->format( 'N' );

    // Menentukan tipe data kalender berdasarkan tanggal yang dipilih
    if ( $i == $tanggal && $bulan == $bulanNow ) {
        $tipe = 'now';
    } else {
        $tipe = 'curr';
    }

    $dmY = $nama_hari[ $nomor_hari ].', '.$i.'/'.$bulan.'/'.$tahun;
    $data[] = array(
        'Tanggal' => $i,
        'Hari' => $nama_hari[ $nomor_hari ],
        'Tipe' => $tipe,
        'Schedule' => cekJadwal( $conn, $tentor, $kelas, $dmY )
        // Anda dapat mengisi array ini dengan schedule yang Anda inginkan
    );
}

// Mengisi data kalender dengan tanggal-tanggal dari bulan selanjutnya jika hari terakhir bukan Minggu
if ( $hari_terakhir != 7 ) {
    // Mengisi data kalender dengan tanggal-tanggal dari bulan selanjutnya mulai dari hari pertama
    $tanggal_selanjutnya = 1;
    for ( $i = $hari_terakhir + 1; $i <= 7; $i++ ) {
        $data[] = array(
            'Tanggal' => $tanggal_selanjutnya,
            'Hari' => $nama_hari[ $i ],
            'Tipe' => 'next',
            'Schedule' => array() // Anda dapat mengisi array ini dengan schedule yang Anda inginkan
        );
        $tanggal_selanjutnya++;
    }
}

// Membuat array untuk menyimpan json kalender
$json = array(
    'Bulan' => $nama_bulan[ $bulan ],
    'Tahun' => $tahun,
    'Data' => $data
);

// Mengubah array menjadi format json
echo json_encode( $json );
?>