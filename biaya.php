<?php
require_once("db_connect.php");

if(isset($_GET['read_biaya'])){
    $dataArray = array();
    $sql = mysqli_query($conn, "SELECT * FROM `biaya_bimbel` ORDER BY `biaya_bimbel`.`Kelas` ASC");
    if($sql){
        while($data = mysqli_fetch_assoc($sql)){
            $dataArray[]= $data;
        }
    }
    header("Content-Type: application/json");
    echo json_encode($dataArray);
}
if(isset($_GET['read_program'])){
    $dataArray = array();
    $sql = mysqli_query($conn, "SELECT `program` FROM `biaya_bimbel`");
    if($sql){
        while($data = mysqli_fetch_assoc($sql)){
            $dataArray[]= $data;
        }
        $dataArray = array_values(array_unique($dataArray, SORT_REGULAR));
        sort($dataArray);
    }
    header("Content-Type: application/json");
    echo json_encode($dataArray);
}
if(isset($_POST['add_biaya'])){
    $kelas = $_POST['kelas'];
    $biaya = $_POST['biaya'];
    $program = $_POST['program'];
    $sql = mysqli_query($conn, "INSERT INTO `biaya_bimbel`(`Kelas`, `Biaya`, `Program`) VALUES ('$kelas','$biaya','$program')");
    if($sql){
        echo "Berhasil";
    }else{
        echo "Gagal";
    }
}
if(isset($_POST['update_biaya'])){
    $id = $_POST['id'];
    $kelas = $_POST['kelas'];
    $biaya = $_POST['biaya'];
    $program = $_POST['program'];
    $sql = mysqli_query($conn, "UPDATE `biaya_bimbel` SET `Kelas`='$kelas',`Biaya`='$biaya',`Program`='$program' WHERE id=$id");
    if($sql){
        echo "Berhasil";
    }else{
        echo "Gagal";
    }
}
if(isset($_POST['delete_biaya'])){
    $id = $_POST['id'];
    $sql = mysqli_query($conn, "DELETE FROM `biaya_bimbel` WHERE id=$id");
    if($sql){
        echo "Berhasil";
    }else{
        echo "Gagal";
    }
}
?>