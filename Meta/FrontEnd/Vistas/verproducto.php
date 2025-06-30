<?php
include('auth.php');
include('conexion.php');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "ID de producto no válido.";
    exit;
}

$id = intval($_GET['id']);

$stmt = $conexion->prepare("SELECT id, nombre, tipo, unidades_disponibles, precio, fechamod, usumod FROM producto WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    echo "Producto no encontrado.";
    exit;
}

$producto = $resultado->fetch_assoc();
$tipos = [1 => "Disfraz", 2 => "Accesorio"];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Información del Producto</title>
    <link rel="stylesheet" href="../Estilos/verusuario.css"> <!-- Puedes reutilizar este estilo -->
</head>
<body>
    <h1>Información del Producto</h1>
    <section class="dni-card">
        <div class="dni-info">
            <p><strong>ID:</strong> <?= htmlspecialchars($producto['id']) ?></p>
            <p><strong>Nombre:</strong> <?= htmlspecialchars($producto['nombre']) ?></p>
            <p><strong>Tipo:</strong> <?= $tipos[$producto['tipo']] ?? 'Desconocido' ?></p>
            <p><strong>Unidades Disponibles:</strong> <?= htmlspecialchars($producto['unidades_disponibles']) ?></p>
            <p><strong>Precio:</strong> $<?= number_format($producto['precio'], 2) ?></p>
            <p><strong>Fecha de Modificación:</strong> <?= htmlspecialchars($producto['fechamod']) ?></p>
            <p><strong>Modificado por:</strong> <?= htmlspecialchars($producto['usumod']) ?></p>
            <br>
            <a href="panelproductos.php"><button type="button" class="boton">Volver al panel</button></a>
        </div>
    </section>
</body>
</html>
