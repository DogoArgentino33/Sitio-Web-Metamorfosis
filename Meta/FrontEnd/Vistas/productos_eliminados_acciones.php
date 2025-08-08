<?php
session_start();
include('conexion.php');

// Mostrar errores en desarrollo (quitar en producción)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Asegurarse de que la respuesta será JSON
header('Content-Type: application/json');

// Verifica si el usuario está autenticado y tiene los roles permitidos
if (!isset($_SESSION['rol']) || !in_array($_SESSION['rol'], [1, 2, 4])) {
    echo json_encode(['success' => false, 'mensaje' => 'Acceso no autorizado']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'], $_POST['accion'])) {
    $id = intval($_POST['id']);
    $accion = $_POST['accion'];

    // Verifica que el nombre de usuario esté en sesión
    if (!isset($_SESSION['nom_usu'])) {
        echo json_encode(['success' => false, 'mensaje' => 'Sesión inválida (nombre de usuario no disponible)']);
        exit;
    }

    $usuario = $_SESSION['nom_usu'];

    $conexion->begin_transaction();

    try {
        if ($accion === 'restaurar') {
            // Restaurar registros
            $queries = [
                "UPDATE producto SET eliminado = 0 WHERE id = ?",
                "UPDATE img_producto SET eliminado = 0 WHERE id_producto = ?",
                "UPDATE producto_categoria SET eliminado = 0 WHERE id_producto = ?",
                "UPDATE producto_talla SET eliminado = 0 WHERE id_producto = ?",
                "UPDATE producto_tematica SET eliminado = 0 WHERE id_producto = ?"
            ];

            foreach ($queries as $sql) {
                $stmt = $conexion->prepare($sql);
                if (!$stmt) throw new Exception("Error en la consulta: " . $conexion->error);
                $stmt->bind_param("i", $id);
                $stmt->execute();
            }

            // Auditoría
            $stmt = $conexion->prepare("INSERT INTO auditoria_producto (id_producto, accion, usuario) VALUES (?, 'RESTAURAR', ?)");
            $stmt->bind_param("is", $id, $usuario);
            $stmt->execute();

            $conexion->commit();
            echo json_encode(['success' => true, 'mensaje' => 'Producto restaurado exitosamente']);
            exit;

        } elseif ($accion === 'eliminar_definitivo') {
            // Obtener nombres de archivos antes de eliminar de la base de datos
            $stmt = $conexion->prepare("SELECT img FROM img_producto WHERE id_producto = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $resultado = $stmt->get_result();

            $imagenes = [];
            while ($fila = $resultado->fetch_assoc()) {
                $imagenes[] = $fila['img'];
            }
            $stmt->close();

            // Crear carpeta papelera si no existe
            $carpetaPapelera = __DIR__ . "/uploads/producto/papelera/";
            if (!file_exists($carpetaPapelera)) {
                mkdir($carpetaPapelera, 0777, true); // Crea la carpeta con permisos
            }

            // Mover físicamente las imágenes del servidor a la papelera
            foreach ($imagenes as $nombreImagen) {
                $origen = __DIR__ . "/uploads/producto/" . $nombreImagen;
                $destino = $carpetaPapelera . $nombreImagen;

                if (file_exists($origen)) {
                    // Evitar sobreescritura accidental en papelera
                    $contador = 1;
                    $nombreFinal = $nombreImagen;

                    while (file_exists($destino)) {
                        $info = pathinfo($nombreImagen);
                        $nombreBase = $info['filename'];
                        $extension = isset($info['extension']) ? '.' . $info['extension'] : '';
                        $nombreFinal = $nombreBase . "_$contador" . $extension;
                        $destino = $carpetaPapelera . $nombreFinal;
                        $contador++;
                    }

                    rename($origen, $destino); // Mueve la imagen
                }
            }

            // Eliminar registros relacionados y el producto
            $queries = [
                "DELETE FROM producto_categoria WHERE id_producto = ?",
                "DELETE FROM producto_talla WHERE id_producto = ?",
                "DELETE FROM producto_tematica WHERE id_producto = ?",
                "DELETE FROM img_producto WHERE id_producto = ?",
                "DELETE FROM producto WHERE id = ?"
            ];

            foreach ($queries as $sql) {
                $stmt = $conexion->prepare($sql);
                if (!$stmt) throw new Exception("Error en la consulta: " . $conexion->error);
                $stmt->bind_param("i", $id);
                $stmt->execute();
            }

            // Auditoría
            $stmt = $conexion->prepare("INSERT INTO auditoria_producto (id_producto, accion, usuario) VALUES (?, 'ELIMINAR', ?)");
            $stmt->bind_param("is", $id, $usuario);
            $stmt->execute();

            $conexion->commit();
            echo json_encode(['success' => true, 'mensaje' => 'Producto eliminado definitivamente']);
            exit;

        } else {
            throw new Exception("Acción no válida");
        }

    } catch (Exception $e) {
        $conexion->rollback();
        echo json_encode(['success' => false, 'mensaje' => 'Error: ' . $e->getMessage()]);
        exit;
    }

} else {
    echo json_encode(['success' => false, 'mensaje' => 'Solicitud no válida']);
    exit;
}