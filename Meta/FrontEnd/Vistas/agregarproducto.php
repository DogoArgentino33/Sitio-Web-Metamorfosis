<?php
include('auth.php');
include('conexion.php');

// Tematica
$resultado_tematica = $conexion->query("SELECT id, nombre_tema FROM tematica");

// Categorías
$resultado_categorias = $conexion->query("SELECT id, nombre_cat FROM categoria");

// Tallas
$resultado_tallas = $conexion->query("SELECT id, talla FROM talla");

$errores = [];
function escapar($html) {
    return htmlspecialchars($html, ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $tipo = trim($_POST['tipo']);
    $unidades = intval($_POST['unidades']);
    $precio = floatval($_POST['precio']);
    $fechamod = date('Y-m-d H:i:s');
    $usumod = $_SESSION['nom_usu'];

    // Nuevos campos
    $id_categoria = intval($_POST['categoria']);
    $id_talla = intval($_POST['talla']);
    $id_tematica = intval($_POST['tematica']);

    // Validaciones
    if ($nombre === '' || strlen($nombre) < 3) {
        $errores[] = 'Nombre del producto inválido.';
    }
    if (!in_array($tipo, [1, 2])) {
        $errores[] = 'Tipo inválido.';
    }
    if ($unidades < 0) {
        $errores[] = 'Las unidades disponibles no pueden ser negativas.';
    }
    if ($precio < 0) {
        $errores[] = 'El precio debe ser mayor o igual a 0.';
    }
    if (!$id_categoria) {
        $errores[] = 'Debe seleccionar una categoría.';
    }
    if (!$id_talla) {
        $errores[] = 'Debe seleccionar una talla.';
    }
    if (!$id_tematica) {
        $errores[] = 'Debe seleccionar una temática.';
    }

    if (count($errores) === 0) {
        // 1. Insertar en producto
        $stmt = $conexion->prepare("INSERT INTO producto (nombre, tipo, unidades_disponibles, precio, fechamod, usumod) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("siidss", $nombre, $tipo, $unidades, $precio, $fechamod, $usumod);

        if ($stmt->execute()) {
            $id_producto = $stmt->insert_id;

            // 2. Insertar en categoria
            $stmtCat = $conexion->prepare("INSERT INTO categoria (nombre_cat, id_producto) SELECT nombre_cat, ? FROM categoria WHERE id = ?");
            $stmtCat->bind_param("ii", $id_producto, $id_categoria);
            $stmtCat->execute();

            // 3. Insertar en talla
            $stmtTalla = $conexion->prepare("INSERT INTO talla (talla, id_producto) SELECT talla, ? FROM talla WHERE id = ?");
            $stmtTalla->bind_param("ii", $id_producto, $id_talla);
            $stmtTalla->execute();

            // 4. Insertar en tematica
            $stmtTema = $conexion->prepare("INSERT INTO tematica (nombre_tema, id_producto) SELECT nombre_tema, ? FROM tematica WHERE id = ?");
            $stmtTema->bind_param("ii", $id_producto, $id_tematica);
            $stmtTema->execute();

            echo "<script>alert('Producto agregado correctamente'); window.location.href='panelproductos.php';</script>";
            exit;
        } else {
            $errores[] = 'Error al insertar en la base de datos.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Agregar Producto</title>
    <link rel="stylesheet" href="../Estilos/index.css">
    <link rel="stylesheet" href="../Estilos/agregarproducto.css">
    <style>
        .mensaje-validacion {
            font-size: 0.85em;
            color: red;
            margin-top: 2px;
        }
    </style>
</head>
<body>
<?php include('cabecera.php'); ?>

<main class="dni-card">
    <form action="agregarproducto.php" method="POST" class="dni-info" novalidate>
        <h2 style="text-align:center;">Agregar Producto</h2>

        <p><label for="nombre">Nombre:</label>
        <input id="nombre" type="text" name="nombre" class="boton" required
            value="<?= escapar($_POST['nombre'] ?? '') ?>"></p>

        <p><label for="tipo">Tipo:</label>
        <select id="tipo" name="tipo" class="boton" required>
            <option value="">-- Seleccionar Tipo --</option>
            <option value="1" <?= (isset($_POST['tipo']) && $_POST['tipo'] == 1) ? 'selected' : '' ?>>Disfraz</option>
            <option value="2" <?= (isset($_POST['tipo']) && $_POST['tipo'] == 2) ? 'selected' : '' ?>>Accesorio</option>
        </select></p>

        <p><label for="tematica">Tematica:</label>
        <select id="tematica" name="tematica" class="boton" required>
            <option value="">-- Seleccionar Tematica --</option>
            <?php while ($cat = $resultado_tematica->fetch_assoc()): ?>
                <option value="<?= $cat['id'] ?>" <?= (isset($_POST['tematica']) && $_POST['tematica'] == $cat['id']) ? 'selected' : '' ?>>
                    <?= escapar($cat['nombre_tema']) ?>
                </option>
            <?php endwhile; ?>
        </select></p>

        <p><label for="categoria">Categoría:</label>
        <select id="categoria" name="categoria" class="boton" required>
            <option value="">-- Seleccionar Categoría --</option>
            <?php while ($cat = $resultado_categorias->fetch_assoc()): ?>
                <option value="<?= $cat['id'] ?>" <?= (isset($_POST['categoria']) && $_POST['categoria'] == $cat['id']) ? 'selected' : '' ?>>
                    <?= escapar($cat['nombre_cat']) ?>
                </option>
            <?php endwhile; ?>
        </select></p>

        <p id="grupo-talla"><label for="talla">Talla:</label>
        <select id="talla" name="talla" class="boton" required>
            <option value="">-- Seleccionar Talla --</option>
            <?php while ($t = $resultado_tallas->fetch_assoc()): ?>
                <option value="<?= $t['id'] ?>" <?= (isset($_POST['talla']) && $_POST['talla'] == $t['id']) ? 'selected' : '' ?>>
                    <?= escapar($t['talla']) ?>
                </option>
            <?php endwhile; ?>
        </select></p>

        <p><label for="unidades">Unidades Disponibles:</label>
        <input id="unidades" type="number" name="unidades" class="boton" min="0"
            value="<?= escapar($_POST['unidades'] ?? '') ?>"></p>

        <p><label for="precio">Precio:</label>
        <input id="precio" type="number" name="precio" class="boton" step="0.01" min="0"
            value="<?= escapar($_POST['precio'] ?? '') ?>"></p>

        <?php if (!empty($errores)): ?>
            <div style="background-color: white; color: red; padding: 1vw; border-radius: 1vw;">
                <ul>
                    <?php foreach ($errores as $error) echo "<li>$error</li>"; ?>
                </ul>
            </div>
        <?php endif; ?>

        <button type="submit" class="boton">Registrar Producto</button>
        <a href="panelproductos.php"><div class="boton">Volver al Panel</div></a>
    </form>
</main>

<?php include('footer.php'); ?>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const validaciones = {
        nombre: {
            regex: /^[A-Za-z0-9\s]{3,50}$/,
            mensaje: "El nombre debe tener entre 3 y 50 caracteres. Letras, números y espacios."
        },
        unidades: {
            regex: /^\d+$/,
            mensaje: "Unidades debe ser un número entero positivo."
        },
        precio: {
            regex: /^\d+(\.\d{1,2})?$/,
            mensaje: "Precio debe ser un número válido (hasta 2 decimales)."
        }
    };

        for (const id in validaciones) {
        const input = document.getElementById(id);
        if (input) {
            const barra = document.createElement('div');
            barra.classList.add('barra-validacion');

            const mensaje = document.createElement('div');
            mensaje.classList.add('mensaje-validacion');

            // Orden correcto: input → barra → mensaje
            input.insertAdjacentElement('afterend', barra);
            barra.insertAdjacentElement('afterend', mensaje);

            input.addEventListener('input', () => {
                const valor = input.value.trim();
                const { regex, mensaje: textoMensaje } = validaciones[id];

                if (regex.test(valor)) {
                    barra.className = 'barra-validacion valido';
                    mensaje.className = 'mensaje-validacion valido';
                    mensaje.textContent = 'Dato válido.';
                } else {
                    barra.className = 'barra-validacion invalido';
                    mensaje.className = 'mensaje-validacion invalido';
                    mensaje.textContent = textoMensaje;
                }
            });
        }
    }


    // Validación para select (tipo, temática, categoría, talla)
    // Validación para select (tipo, temática, categoría, talla)
    const selects = ['tipo', 'tematica', 'categoria', 'talla'];
    selects.forEach(id => {
        const select = document.getElementById(id);
        if (select) {
            const mensaje = document.createElement("div");
            mensaje.className = "mensaje-validacion";
            select.insertAdjacentElement("afterend", mensaje);

            select.addEventListener("change", () => {
                if (!select.value) {
                    mensaje.textContent = `Debe seleccionar una opción válida.`;
                    select.classList.remove("valido");
                    select.classList.add("invalido");
                } else {
                    mensaje.textContent = '';
                    select.classList.remove("invalido");
                    select.classList.add("valido");
                }
            });
        }
    });

});
</script>

</body>
</html>
