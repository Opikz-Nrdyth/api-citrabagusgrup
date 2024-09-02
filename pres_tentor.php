<?php
require_once("db_tentor_connect.php");
require_once("db_connect.php");

if(isset($_GET["read_hadir"])){
    $query = "SELECT * FROM `users_logs` WHERE checkindate = '".date("Y-m-d")."'";
    $sql = mysqli_query($tentorDB, $query);
    $array = array();
    while($data = mysqli_fetch_assoc($sql)){
        $array[] = $data;
    }
    echo json_encode($array, true);
}

if(isset($_GET["read_tentor"])){
    $query = "SELECT * FROM `users` WHERE status = 'Tentor'";
    $sql = mysqli_query($tentorDB, $query);
    $array = array();
    while($data = mysqli_fetch_assoc($sql)){
        $array[] = $data;
    }
    echo json_encode($array, true);
}

if(isset($_POST["add_absen"])){
    $nama = $_POST["nama"];
    $serialnumber = $_POST["serialnumber"];
    $status = $_POST["status"];
    $fingerprint_id = $_POST["fingerprint_id"];
    $checkindate = date("Y-m-d");
    $timein = $_POST["timein"];

    if($nama == "" || $serialnumber == "" || $status == "" || $fingerprint_id == "" || $timein == ""){
        echo "Data tidak terload dengan baik, coba ulangi lagi atau cek pada data tentor di website presensi tentor";
    }else{
        $query = "INSERT INTO `users_logs`(`nama`, `serialnumber`, `status`, `fingerprint_id`, `checkindate`, `timein`) VALUES ('$nama','$serialnumber','$status','$fingerprint_id','$checkindate','$timein')";
        $sql = mysqli_query($tentorDB, $query);
        if($sql){
            echo "Berhasil";
        }else{
            echo "Gagal";
        }
    }
}

if(isset($_POST["delete_absen"])){
    $id = $_POST["id"];
    $query = "DELETE FROM `users_logs` WHERE id=$id";
    $sql = mysqli_query($tentorDB, $query);
    if($sql){
        echo "Berhasil";
    }else{
        echo "Gagal";
    }
}
?>