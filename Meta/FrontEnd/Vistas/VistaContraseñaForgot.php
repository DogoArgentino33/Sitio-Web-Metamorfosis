<?php
session_start();

if (!isset($_GET['codigo']) || $_GET['codigo'] != $_SESSION['codigo_recuperacion']) {
    echo "<script>alert('Código incorrecto.'); window.location.href='recuperar.php';</script>";
    exit;
}

// Código correcto
// Redirigir a formulario para cambiar la contraseña
header("Location: CambiarContraseña.php");
exit;
?>
