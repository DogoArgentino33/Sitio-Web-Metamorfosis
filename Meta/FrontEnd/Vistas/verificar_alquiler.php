<?php
header('Content-Type: application/json'); // Muy importante

// Mostrar errores en desarrollo (opcional)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include('auth.php');
include('conexion.php');

try {
    if (!isset($_GET['id'])) {
        echo json_encode(['success' => false, 'error' => 'ID no recibido']);
        exit;
    }

    $id = intval($_GET['id']);

    $sql = "SELECT COUNT(*) AS total 
            FROM alquiler 
            WHERE id_producto = ? 
              AND CURDATE() BETWEEN desde AND hasta";

    $stmt = $conexion->prepare($sql);
    if (!$stmt) {
        throw new Exception("Error en la preparación de la consulta.");
    }

    $stmt->bind_param("i", $id);
    $stmt->execute();

    $result = $stmt->get_result();
    $data = $result->fetch_assoc();

    echo json_encode(['success' => true, 'alquilado' => $data['total'] > 0]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
