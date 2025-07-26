<?php
include('auth.php');
include('conexion.php');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "ID de producto no válido.";
    exit;
}

$id = intval($_GET['id']);

// Obtener datos del producto
$stmt = $conexion->prepare("SELECT nombre, tipo, unidades_disponibles, precio, fechamod, usumod FROM producto WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    echo "Producto no encontrado.";
    exit;
}

$producto = $resultado->fetch_assoc();
$tipos = [1 => "Disfraz", 2 => "Accesorio"];

// Obtener todas las imágenes del producto
$imagenes = [];
$query_imagenes = $conexion->query("SELECT img FROM img_producto WHERE id_producto = $id");
while ($row = $query_imagenes->fetch_assoc()) {
    $imagenes[] = $row['img'];
}
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

    <div class="dni-img">
        <?php if (!empty($imagenes)): ?>
            <?php foreach ($imagenes as $img): ?>
                <img 
    src="uploads/producto/<?= htmlspecialchars($img) ?>" 
    onclick="mostrarModal(this)"
    style="display: flex; flex-wrap: wrap; gap: 10px; margin-top: 10px; width: 8vw; height: 8vw; object-fit: cover; border: 0.1vw solid gray; margin: 0.5vw;">

            <?php endforeach; ?>
        <?php else: ?>
            <span>Sin imágenes</span>
        <?php endif; ?>
    </div>

    <section class="dni-card">
        <div class="dni-info">
            <p><strong>Nombre:</strong> <?= htmlspecialchars($producto['nombre']) ?></p>
            <p><strong>Tipo:</strong> <?= $tipos[$producto['tipo']] ?? 'Desconocido' ?></p>
            <p><strong>Unidades Disponibles:</strong> <?= htmlspecialchars($producto['unidades_disponibles']) ?></p>
            <p><strong>Precio:</strong> $<?= number_format($producto['precio'], 2) ?></p>
            <p><strong>Fecha de Modificación:</strong> <?= htmlspecialchars($producto['fechamod']) ?></p>
            <p><strong>Modificado por:</strong> <?= htmlspecialchars($producto['usumod']) ?></p>
            <br>

            <?php if (isset($_SESSION['rol']) and $_SESSION['rol'] == 1): ?>
                <!-- Para gerente -->
                <a href="panelproductos.php"><button type="button" class="boton">Volver al panel</button></a>
            <?php endif; ?>

            <?php if (isset($_SESSION['rol']) and $_SESSION['rol'] == 2): ?>
                <!-- Para empleado -->
                <a href="panelproductosempleado.php"><button type="button" class="boton">Volver al panel</button></a>
            <?php endif; ?>

            
            
        
        
        </div>
    </section>

    <!-- Modal de imagen -->
    <div id="modalImagen" class="modal-imagen" onclick="cerrarModal()">
        <span class="cerrar">&times;</span>
        <img class="modal-contenido" id="imagenAmpliada">
    </div>

    <script>
        function mostrarModal(imagen) {
            const modal = document.getElementById("modalImagen");
            const imgAmpliada = document.getElementById("imagenAmpliada");
            imgAmpliada.src = imagen.src;
            modal.style.display = "flex";
        }

        function cerrarModal() {
            document.getElementById("modalImagen").style.display = "none";
        }
    </script>

</body>
</html>