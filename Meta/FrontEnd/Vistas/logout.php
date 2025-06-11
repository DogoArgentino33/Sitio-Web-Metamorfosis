<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include('conexion.php');

if (isset($_SESSION['id'])) {
    $id = $_SESSION['id'];
    $sql = "UPDATE usuario SET estadousu = 1 WHERE id = $id";
    mysqli_query($conexion, $sql);
}

session_unset();
session_destroy();

header("Location: login.php");
exit;
