<?php
// auth.php se encarga de verificar si el usuario está logueado y tiene el estado correcto.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include('conexion.php');

// Verificar si hay ID de sesión
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

$id = $_SESSION['id'];

// Consultar el estado real del usuario en la base
$sql = "SELECT estadousu FROM usuario WHERE id = $id";
$result = mysqli_query($conexion, $sql);

if (!$result || mysqli_num_rows($result) === 0) {
    header("Location: login.php");
    exit;
}

$usuario = mysqli_fetch_assoc($result);

// Validar que el estado sea 2 (logueado)
if ($usuario['estadousu'] != 2) {
    header("Location: login.php");
    exit;
}
