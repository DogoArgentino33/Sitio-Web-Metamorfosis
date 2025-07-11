<?php
include('auth.php');
include('conexion.php');

// Verificar ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "ID de producto no válido.";
    exit;
}

$id_producto = intval($_GET['id']);

// Obtener datos actuales del producto
$sql = "SELECT p.*, 
               pc.id_categoria, 
               pt.id_talla, 
               ptm.id_tematica 
        FROM producto p 
        LEFT JOIN producto_categoria pc ON p.id = pc.id_producto
        LEFT JOIN producto_talla pt ON p.id = pt.id_producto
        LEFT JOIN producto_tematica ptm ON p.id = ptm.id_producto
        WHERE p.id = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id_producto);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Producto no encontrado.";
    exit;
}

$producto = $result->fetch_assoc();

// Listados para selects
$resultado_categorias = $conexion->query("SELECT id, nombre_cat FROM categoria");
$resultado_tallas = $conexion->query("SELECT id, talla FROM talla");
$resultado_tematica = $conexion->query("SELECT id, nombre_tema FROM tematica");

$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre   = trim($_POST['nombre']);
    $tipo     = intval($_POST['tipo']);
    $unidades = intval($_POST['unidades']);
    $precio   = floatval($_POST['precio']);
    $fechamod = date('Y-m-d H:i:s');
    $usumod   = $_SESSION['nom_usu'];

    $id_categoria = intval($_POST['categoria']);
    $id_talla     = intval($_POST['talla']);
    $id_tematica  = intval($_POST['tematica']);

    // Validaciones
    if ($nombre === '' || strlen($nombre) < 3) {
        $errores[] = 'Nombre inválido.';
    }
    if (!in_array($tipo, [1, 2])) {
        $errores[] = 'Tipo inválido.';
    }
    if ($unidades < 0) {
        $errores[] = 'Unidades no válidas.';
    }
    if ($precio < 0) {
        $errores[] = 'Precio no válido.';
    }

    if (count($errores) === 0) {
        // 1. Actualizar producto
        $stmt = $conexion->prepare("UPDATE producto SET nombre = ?, tipo = ?, unidades_disponibles = ?, precio = ?, fechamod = ?, usumod = ? WHERE id = ?");
        $stmt->bind_param("siidssi", $nombre, $tipo, $unidades, $precio, $fechamod, $usumod, $id_producto);
        $stmt->execute();

        // 2. Actualizar relaciones
        $conexion->query("DELETE FROM producto_categoria WHERE id_producto = $id_producto");
        $conexion->query("DELETE FROM producto_talla WHERE id_producto = $id_producto");
        $conexion->query("DELETE FROM producto_tematica WHERE id_producto = $id_producto");

        $conexion->query("INSERT INTO producto_categoria (id_producto, id_categoria) VALUES ($id_producto, $id_categoria)");
        $conexion->query("INSERT INTO producto_talla (id_producto, id_talla) VALUES ($id_producto, $id_talla)");
        $conexion->query("INSERT INTO producto_tematica (id_producto, id_tematica) VALUES ($id_producto, $id_tematica)");

        // 3. Subida de nueva imagen si existe
// 3. Subida de nueva imagen si existe
if (isset($_FILES['imagenes']) && count($_FILES['imagenes']['name']) > 0) {
    $tipos_permitidos = ['image/jpeg', 'image/png', 'image/gif'];
    $directorio_subida = 'uploads/producto/';

    if (!is_dir($directorio_subida)) {
        mkdir($directorio_subida, 0755, true);
    }

    // Eliminar imágenes anteriores
    $query_imagen = $conexion->query("SELECT img FROM img_producto WHERE id_producto = $id_producto");
    while ($img = $query_imagen->fetch_assoc()) {
        $ruta_imagen = $directorio_subida . $img['img'];
        if (file_exists($ruta_imagen)) {
            unlink($ruta_imagen);
        }
    }

    // Borrar registros de la base
    $conexion->query("DELETE FROM img_producto WHERE id_producto = $id_producto");

    // Subir nuevas imágenes
    foreach ($_FILES['imagenes']['tmp_name'] as $key => $tmp_name) {
        $nombre_original = $_FILES['imagenes']['name'][$key];
        $tipo_archivo = $_FILES['imagenes']['type'][$key];

        if (in_array($tipo_archivo, $tipos_permitidos)) {
            $extension = pathinfo($nombre_original, PATHINFO_EXTENSION);
            $nombre_nuevo = uniqid('img_') . '.' . $extension;
            $ruta_destino = $directorio_subida . $nombre_nuevo;

            if (move_uploaded_file($tmp_name, $ruta_destino)) {
                $tipo_producto = intval($tipo);
                $stmtImg = $conexion->prepare("INSERT INTO img_producto (tipo, img, id_producto) VALUES (?, ?, ?)");
                $stmtImg->bind_param("isi", $tipo_producto, $nombre_nuevo, $id_producto);
                $stmtImg->execute();
            } else {
                $errores[] = "Error al subir $nombre_original.";
            }
        } else {
            $errores[] = "Tipo de archivo no permitido: $nombre_original.";
        }
    }
}



        // 4. Confirmación
        header("Location: panelproductos.php?productomodificado=ok");
        exit;
    }
}

?>


<!-- Cuerpo de la página -->
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Producto</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../Estilos/index.css">
    <link rel="stylesheet" href="../Estilos/editarusuario.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>
<body>
<?php
// Obtener datos del producto
$sql = "SELECT p.*, 
        (SELECT id_categoria FROM producto_categoria WHERE id_producto = p.id LIMIT 1) as id_categoria,
        (SELECT id_talla FROM producto_talla WHERE id_producto = p.id LIMIT 1) as id_talla,
        (SELECT id_tematica FROM producto_tematica WHERE id_producto = p.id LIMIT 1) as id_tematica
        FROM producto p WHERE p.id = $id_producto";
$resultado = $conexion->query($sql);
$producto = $resultado->fetch_assoc();

// Cargar selects
$categorias = $conexion->query("SELECT id, nombre_cat FROM categoria");
$tallas     = $conexion->query("SELECT id, talla FROM talla");
$tematicas  = $conexion->query("SELECT id, nombre_tema FROM tematica");

// Obtener imágenes actuales
$imagen = $conexion->query("SELECT img FROM img_producto WHERE id_producto = $id_producto");
?>

<h1>Modificar Producto</h1>

    <section class="dni-card">
        <form method="POST" enctype="multipart/form-data">
        <section class="perfil-editar">
        
            <div class="input-imagen">
                <label>Reemplazar por nuevas imágenes:</label>
                <input type="file" name="imagenes[]" id="nueva_imagen" accept="image/*" multiple>
                
            </div>
        </section>
            <div id="preview-contenedor">
                <?php
                $query_imagenes = $conexion->query("SELECT img FROM img_producto WHERE id_producto = $id_producto");
                while ($img = $query_imagenes->fetch_assoc()) :
                ?>
                    <img src="uploads/producto/<?= htmlspecialchars($img['img']) ?>" style="width: 8vw; height: 8vw; object-fit: cover; border: 0.1vw solid gray; margin: 0.5vw;">
                <?php endwhile; ?>
            </div>
    <label>Nombre:</label>
    <input type="text" name="nombre" required value="<?= htmlspecialchars($producto['nombre']) ?>">            

    <label>Tipo:</label>
    <select name="tipo" required>
        <option value="1" <?= $producto['tipo'] == 1 ? 'selected' : '' ?>>Disfraz</option>
        <option value="2" <?= $producto['tipo'] == 2 ? 'selected' : '' ?>>Accesorio</option>
    </select>

    <label>Unidades disponibles:</label>
    <input type="number" name="unidades" min="0" required value="<?= $producto['unidades_disponibles'] ?>">

    <label>Precio:</label>
    <input type="number" name="precio" step="0.01" min="0" required value="<?= $producto['precio'] ?>">

    <label>Categoría:</label>
    <select name="categoria" required>
        <?php while ($cat = $categorias->fetch_assoc()) : ?>
            <option value="<?= $cat['id'] ?>" <?= $cat['id'] == $producto['id_categoria'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($cat['nombre_cat']) ?>
            </option>
        <?php endwhile; ?>
    </select>

    <label>Talla:</label>
    <select name="talla" required>
        <?php while ($t = $tallas->fetch_assoc()) : ?>
            <option value="<?= $t['id'] ?>" <?= $t['id'] == $producto['id_talla'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($t['talla']) ?>
            </option>
        <?php endwhile; ?>
    </select>

    <label>Temática:</label>
    <select name="tematica" required>
        <?php while ($tema = $tematicas->fetch_assoc()) : ?>
            <option value="<?= $tema['id'] ?>" <?= $tema['id'] == $producto['id_tematica'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($tema['nombre_tema']) ?>
            </option>
        <?php endwhile; ?>
    </select>

    <br><br>
    <button type="submit">Guardar Cambios</button>
    <a href="panelproductos.php"><button type="button">Cancelar</button></a>
</form>
    </section>

<script>
document.getElementById('nueva_imagen').addEventListener('change', function(event) {
    const file = event.target.files[0];
    const preview = document.querySelector('.preview-img');
    const original = document.querySelector('.img-original');

    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
            original.style.display = 'none';
        };
        reader.readAsDataURL(file);
    } else {
        preview.style.display = 'none';
        original.style.display = 'block';
    }
});
</script>

<script>
document.getElementById('nueva_imagen').addEventListener('change', function(event) {
    const files = event.target.files;
    const contenedor = document.getElementById('preview-contenedor');
    contenedor.innerHTML = ''; // Limpiar previews anteriores

    Array.from(files).forEach(file => {
        const reader = new FileReader();
        reader.onload = function(e) {
            const img = document.createElement('img');
            style="display: flex; flex-wrap: wrap; gap: 10px; margin-top: 10px;"
            img.style.display = 'flex-wrap';
            img.style.display = 'gap';
            img.src = e.target.result;
            img.style.width = '8vw';
            img.style.height = '8vw';
            img.style.objectFit = 'cover';
            img.style.border = '0.1vw solid gray';
            img.style.margin = '0.5vw';
            contenedor.appendChild(img);
        };
        reader.readAsDataURL(file);
    });
});
</script>


</body>
</html>