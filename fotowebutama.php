<?php
require_once("db_connect.php");
if (isset($_POST["uploadFoto"])) {
    $foto = $_FILES["foto"]["name"];
    $deskripsi = $_POST["deskripsi"];

    $target_dir = "images/";

    $ext = strtolower(pathinfo($foto, PATHINFO_EXTENSION));

    $allowed_ext = array('png', 'jpg', 'jpeg');

    if (!in_array($ext, $allowed_ext)) {
        echo "Invalid Extention";
        exit;
    } else {
        $hash = hash('sha256', $foto . time());

        $newFileName = $hash . '.' . $ext;

        $target_file = $target_dir . $newFileName;

        if (move_uploaded_file($_FILES["foto"]["tmp_name"], $target_file)) {
            $query = "INSERT INTO `foto_webutama`(`foto`, `deskripsi`) VALUES ('$target_file','$deskripsi')";
            $sql = mysqli_query($conn, $query);
            if ($sql) {
                echo "Berhasil";
            } else {
                unlink($target_file);
                echo "Error Uploading Image";
            }
        } else {
            echo "Error Uploading Image";
        }
    }
}
if (isset($_POST["delete"])) {
    $id = $_POST["id"];
    $query = "SELECT * FROM `foto_webutama` WHERE id = '$id'";
    $sql = mysqli_query($conn, $query);
    while ($data = mysqli_fetch_assoc($sql)) {
        if (unlink($data["foto"])) {
            echo "Berhasil";
            $queryD = "DELETE FROM `foto_webutama` WHERE id = '$id'";
            $sqlD = mysqli_query($conn, $queryD);
        }
    }
}
if (isset($_GET["read"])) {
    $array = array();
    $query = "SELECT * FROM `foto_webutama`";
    $sql = mysqli_query($conn, $query);
    while ($data = mysqli_fetch_assoc($sql)) {
        $array[] = $data;
    }
    echo json_encode($array, true);
}
