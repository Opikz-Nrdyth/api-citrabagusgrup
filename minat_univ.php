<?php
require_once("db_connect.php");
if(isset($_POST["read_all_univ"])){
    
    $query = "SELECT * FROM `daftar_universitas`";
    $sql = mysqli_query($conn, $query);
    $array = array();
    if($sql){
        while($data = mysqli_fetch_assoc($sql)){
            $array[] = $data;
        }
    }
    echo json_encode($array, true);
}

if(isset($_POST["add_univ"])){
    $induk = $_POST["user"];
    $nama = $_POST["nama"];
    $univ1 = $_POST["univ1"];
    $univ2 = $_POST["univ2"];
    $univ3 = $_POST["univ3"];

    $queryRead = "SELECT * FROM `minat_univ` WHERE induk = '$induk'";
    $sqlRead = mysqli_query($conn, $queryRead);
    if(mysqli_num_rows($sqlRead) == 0){
        $query = "INSERT INTO `minat_univ`(`induk`, `nama`, `minat_1`, `minat_2`, `minat_3`) VALUES ('$induk','$nama','$univ1','$univ2','$univ3')";

        $sql = mysqli_query($conn, $query);
        if($sql){
            echo "Berhasil";
        }else{
            echo "Gagal";
        }
    }else{
        echo "Data sudah pernah ditambahkan";
    }
}

if(isset($_POST["read_univ"])){
    $query = "SELECT * FROM `minat_univ`";
    $sql = mysqli_query($conn, $query);
    $array = array();
    if($sql){
        while($data = mysqli_fetch_assoc($sql)){
            $array[] = $data;
        }
    }
    echo json_encode($array, true);
}

if(isset($_POST["remove_univ"])){
    $id = $_POST["induk"];
    $query = "DELETE FROM `minat_univ` WHERE induk = '$id'";
    $sql = mysqli_query($conn, $query);
    if($sql){
        echo "Berhasil";
    }else{
        "Gagal";
    }
}

if(isset($_POST["remove_all"])){
    $query = "TRUNCATE `minat_univ`";
    $sql = mysqli_query($conn, $query);
    if($sql){
        echo "Berhasil";
    }else{
        "Gagal";
    }
}
?>