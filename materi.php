<?php
date_default_timezone_set('Asia/Jakarta');
require_once("db_connect.php");
$target_dir = "materi/";
if (isset($_POST["uploadMateri"])) {
    $file = $_FILES["file"]["name"];
    $tentor = $_POST["tentor"];
    $mapel = $_POST["mapel"];
    $tanggal = $_POST["tanggal"];
    $judul = $_POST["judul"];
    $kelas = $_POST["kelas"];

    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));

    $allowed_ext = array('pdf');

    if (!in_array($ext, $allowed_ext)) {
        echo "Invalid Extention";
        exit;
    } else {
        $hash = hash('sha256', $file . time());

        $newFileName = $hash . '.' . $ext;

        $target_file = $target_dir . $newFileName;

        if (move_uploaded_file($_FILES["file"]["tmp_name"], $target_file)) {
            $query = "INSERT INTO `db_materi`(`tentor`, `mapel`, `tanggal`, `materi`, `kelas`, `url_materi`) VALUES ('$tentor','$mapel','$tanggal','$judul','$kelas','$target_file')";
            $sql = $conn->query($query);
            if ($sql === TRUE) {
                echo "Berhasil";
            } else {
                echo "Gagal " . $query;
            }
        } else {
            echo "Error Uploading File";
        }
    }
}

if (isset($_POST["deleteMateri"])) {
    $id = $_POST["id"];
    $query = "SELECT * FROM `db_materi` WHERE id = '$id'";
    $sql = mysqli_query($conn, $query);
    while ($data = mysqli_fetch_assoc($sql)) {
        if (unlink($data["url_materi"])) {
            echo "Berhasil";
            $queryD = "DELETE FROM `db_materi` WHERE id = '$id'";
            $sqlD = mysqli_query($conn, $queryD);
        }
    }
}

if (isset($_GET["readKelas"])) {
    header("Content-Type: application/json");
    $query = "SELECT kelas_list FROM ( SELECT CONCAT(kelas, ' ', tipeKelas) AS kelas_list FROM db_siswa WHERE kelas IS NOT NULL AND kelas <> '' UNION SELECT secondary_kelas AS kelas_list FROM db_siswa WHERE secondary_kelas IS NOT NULL AND secondary_kelas <> '' ) AS combined GROUP BY kelas_list ORDER BY kelas_list;";
    $sql = $conn->query($query);
    $array = array();
    if ($sql) {
        while ($data = $sql->fetch_assoc()) {
            $array[] = $data;
        }
    }
    echo json_encode($array, true);
}

if (isset($_GET["read"])) {
    $kelas = $_GET["kelas"];
    header("Content-Type: application/json");
    $array = array();
    if ($kelas == "admin") {
        $query = "SELECT * FROM `db_materi`";
    } else {
        $query = "SELECT * FROM `db_materi` WHERE `tanggal` <= CURDATE() AND kelas = '$kelas'";
    }
    $sql = mysqli_query($conn, $query);

    $days = array(
        'Sunday' => 'Minggu',
        'Monday' => 'Senin',
        'Tuesday' => 'Selasa',
        'Wednesday' => 'Rabu',
        'Thursday' => 'Kamis',
        'Friday' => 'Jumat',
        'Saturday' => 'Sabtu'
    );

    $months = array(
        '01' => 'Januari',
        '02' => 'Februari',
        '03' => 'Maret',
        '04' => 'April',
        '05' => 'Mei',
        '06' => 'Juni',
        '07' => 'Juli',
        '08' => 'Agustus',
        '09' => 'September',
        '10' => 'Oktober',
        '11' => 'November',
        '12' => 'Desember'
    );

    while ($data = mysqli_fetch_assoc($sql)) {
        $date = DateTime::createFromFormat('Y-m-d', $data['tanggal']);
        if ($date) {
            $hari = $date->format('l');
            $tanggal = $date->format('d');
            $bulan = $date->format('m');
            $tahun = $date->format('Y');
            $tanggalFormat = $days[$hari] . ", " . $tanggal . " " . $months[$bulan] . " " . $tahun;
            $data['tanggal'] = $tanggalFormat;
        }
        $array[] = $data;
    }

    echo json_encode($array, true);
}
