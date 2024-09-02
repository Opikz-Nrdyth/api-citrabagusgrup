<?php
require_once('db_connect.php');
function tampilResponse($data, $type)
{
    if ($type == "json") {
        header("Content-Type: application/json");
        echo $data;
    } else {
        echo $data;
    }
}

function generateRandomString()
{
    $length = 100;
    $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

if (isset($_GET['karyawan'])) {
    $dataArray = array();
    $sql = mysqli_query($conn, "SELECT * FROM `karyawan`");
    if ($sql) {
        while ($data = mysqli_fetch_assoc($sql)) {
            $dataArray[] = $data;
        }
    }
    tampilResponse(json_encode($dataArray), "json");
}

if (isset($_GET['absenKaryawan'])) {
    $dataArray = array();
    $nama = $_GET['nama'];
    $nowDate = date('m-Y');
    $query = "SELECT * FROM `absen_karyawan` WHERE nama = '$nama' && tanggal LIKE '%$nowDate%'";
    if (isset($_GET['tanggal_awal']) && isset($_GET['tanggal_akhir'])) {
        $tanggal_awal = $_GET['tanggal_awal'];
        $tanggal_akhir = $_GET['tanggal_akhir'];
        if ($tanggal_akhir == "") {
            $tanggal_akhir = date('Y-m-d');
        }
        $query = "SELECT * FROM `absen_karyawan` WHERE nama = '$nama' && STR_TO_DATE(tanggal, '%d-%m-%Y') BETWEEN '$tanggal_awal' AND '$tanggal_akhir'";
    }
    $sql = mysqli_query($conn, $query);
    if ($sql) {
        while ($data = mysqli_fetch_assoc($sql)) {
            $dataArray[] = $data;
        }
    }

    tampilResponse(json_encode($dataArray), "json");
    // echo $query;
}

if (isset($_POST["addKaryawan"])) {
    $nama = $_POST['nama'];
    $status = $_POST['status'];
    $jobDesk = $_POST['job_desk'];
    $query = "INSERT INTO `karyawan`(`id`, `nama`, `job_desk`, `status`) VALUES ('" . generateRandomString() . "','$nama','$jobDesk','$status')";

    $sql = mysqli_query($conn, $query);
    if ($sql) {
        tampilResponse("Berhasil", "text");
    } else {
        tampilResponse("Error pada koneksi ke database", "text");
    }
}

if (isset($_POST['editKaryawan'])) {
    $id = $_POST['id'];
    $nama = $_POST['nama'];
    $status = $_POST['status'];
    $jobDesk = $_POST['job_desk'];
    $sql = mysqli_query($conn, "UPDATE `karyawan` SET `nama`='$nama',`job_desk`='$jobDesk', status='$status' WHERE id LIKE '%$id%'");
    if ($sql) {
        tampilResponse("Berhasil", "text");
    } else {
        tampilResponse("Error pada koneksi ke database", "text");
    }
}

if (isset($_POST['deleteData'])) {
    $id = $_POST['id'];
    $sql = mysqli_query($conn, "DELETE FROM `karyawan` WHERE id LIKE '%$id%'");
    if ($sql) {
        tampilResponse("Berhasil", "text");
    } else {
        tampilResponse("Error pada koneksi ke database", "text");
    }
}

if (isset($_POST['editAbsen'])) {
    $id = $_POST['id'];
    $time = $_POST['time'];
    $status = $_POST['status'];
    $query = "UPDATE `absen_karyawan` SET `jam`='$time',`Kehadiran`='H' WHERE id='$id'";
    if ($status == "alfa") {
        $query = "UPDATE `absen_karyawan` SET `jam`='',`Kehadiran`='-' WHERE id='$id'";
    }
    $sql = mysqli_query($conn, $query);
    if ($sql) {
        tampilResponse("Berhasil", "text");
    } else {
        tampilResponse("Error pada koneksi ke database", "text");
    }
}

if (isset($_POST['deleteAbsen'])) {
    $id = $_POST['id'];
    $sql = mysqli_query($conn, "DELETE FROM `absen_karyawan` WHERE id LIKE '%$id%'");
    if ($sql) {
        tampilResponse("Berhasil", "text");
    } else {
        tampilResponse("Error pada koneksi ke database", "text");
    }
}

if (isset($_GET['tambahAbsen'])) {
    $sqlCek = mysqli_query($conn, "SELECT * FROM `karyawan` WHERE status = 'aktif'");
    foreach ($sqlCek as $d) {
        $nama = $d['nama'];
        $tanggal = date('d-m-Y');
        $sqlCekAbsen = mysqli_query($conn, "SELECT * FROM `absen_karyawan`WHERE nama = '$nama' && tanggal LIKE '%$tanggal%'");
        if (mysqli_num_rows($sqlCekAbsen) == 0) {
            $query = "INSERT INTO `absen_karyawan`(`nama`, `tanggal`, `jam`, `Kehadiran`) VALUES ('$nama','$tanggal','','-')";
            $sqlKirim = mysqli_query($conn, $query);
        }
    }
}

if (isset($_POST['updateAbsen'])) {
    $id = $_POST['id'];
    $sqlCek = mysqli_query($conn, "SELECT * FROM `karyawan` WHERE id LIKE '%$id%'");
    $data = mysqli_fetch_assoc($sqlCek);
    $nama = $data['nama'];
    $tanggal = date('d-m-Y');
    $jam = date('H:i:s');
    $query = "UPDATE `absen_karyawan` SET `jam`='$jam',`Kehadiran`='H' WHERE nama = '$nama' && tanggal = '$tanggal'";
    $sql = mysqli_query($conn, $query);
    if ($sql) {
        echo "Berhasil";
    } else {
        echo "Gagal Mengirim Absensi";
    }
}

if (isset($_GET['absenHarian'])) {
    $dataArray = array();
    $nowDate = date('d-m-Y');
    $query = "SELECT * FROM `absen_karyawan` WHERE tanggal LIKE '%$nowDate%' ORDER BY STR_TO_DATE(jam, '%H:%i:%s') DESC;";
    $sql = mysqli_query($conn, $query);
    if ($sql) {
        while ($data = mysqli_fetch_assoc($sql)) {
            $dataArray[] = $data;
        }
        tampilResponse(json_encode($dataArray), "json");
    }
}
