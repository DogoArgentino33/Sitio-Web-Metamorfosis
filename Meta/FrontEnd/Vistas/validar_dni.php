<?php 
session_start(); 
include('conexion.php'); 

// Validación de DNI en tiempo real
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['dni'])) {
    $dni = preg_replace('/\D/', '', $_GET['dni']); // Limpiar entrada

    $sql = "SELECT id FROM persona WHERE dni = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("s", $dni);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res && $res->num_rows > 0) {
        $fila = $res->fetch_assoc();
        $id_persona = $fila['id'];

        $stmt2 = $conexion->prepare("SELECT id FROM usuario WHERE id_persona = ?");
        $stmt2->bind_param("i", $id_persona);
        $stmt2->execute();
        $res2 = $stmt2->get_result();

        if ($res2 && $res2->num_rows > 0) {
            echo json_encode(['valido' => true, 'mensaje' => 'DNI válido y vinculado a un usuario.']);
        } else {
            echo json_encode(['valido' => false, 'mensaje' => 'El DNI está registrado como persona, pero no tiene un usuario asociado.']);
        }

    } else {
        echo json_encode(['valido' => false, 'mensaje' => 'El DNI no está registrado en el sistema.']);
    }

    exit;
}

?>