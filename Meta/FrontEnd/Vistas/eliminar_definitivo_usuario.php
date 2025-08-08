<?php
include('conexion.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = intval($_POST['id']);

    // Primero eliminar el registro de la tabla usuario
    $stmtUsuario = $conexion->prepare("DELETE FROM usuario WHERE id = ?");
    $stmtUsuario->bind_param("i", $id);

    if ($stmtUsuario->execute()) {
        echo "ok";
    } else {
        echo "Error al eliminar el usuario: " . $stmtUsuario->error;
    }

    $stmtUsuario->close();
} else {
    echo "Solicitud no válida.";
}
?>
