<?php
require_once("db_connect.php");
if(isset($_GET["post"])){
    $title = $_POST["title"];
    $deskripsi = $_POST["deskripsi"];
    $date = $_POST["tanggal_terbit"];

    $query = "INSERT INTO `information`(`title`, `deskripsi`, `tanggal_terbit`) VALUES ('$title','$deskripsi','$date')";
    $sql = mysqli_query($conn, $query);
    if($sql){
        echo "Berhasil";
    }
}
if(isset($_GET["get"])){
    $dataArray = array();
    $query = "SELECT * FROM `information` WHERE tanggal_terbit <= CURRENT_DATE();";
    if(isset($_GET["getAll"])){
        $query = "SELECT * FROM `information`";
    }
    $sql = mysqli_query($conn, $query);
    if($sql){
        while($data = mysqli_fetch_assoc($sql)){
            $dataArray[] = $data;
        }
    }

    header("Content-Type: application/json");
    echo json_encode($dataArray, JSON_PRETTY_PRINT);
}
if(isset($_GET["update"])){
    $title = $_POST["title"];
    $deskripsi = $_POST["deskripsi"];
    $date = $_POST["tanggal_terbit"];
    $id = $_POST["id"];

    $query = "UPDATE `information` SET `title`='$title',`deskripsi`='$deskripsi',`tanggal_terbit`='$date' WHERE id=$id";
    $sql = mysqli_query($conn, $query);
    if($sql){
        echo "Berhasil";
    }
}
if(isset($_GET["delete"])){
    $id = $_POST["id"];

    $query = "DELETE FROM `information` WHERE id=$id";
    $sql = mysqli_query($conn, $query);
    if($sql){
        echo "Berhasil";
    }
}
?>