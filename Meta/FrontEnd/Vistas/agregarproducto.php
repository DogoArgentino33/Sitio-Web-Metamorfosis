<?php
include('auth.php');
include('conexion.php');

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
    $usumod = $_SESSION['nom_usu']; // Ahora se guarda el nombre del usuario

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

    if (count($errores) === 0) {
        $stmt = $conexion->prepare("INSERT INTO producto (nombre, tipo, unidades_disponibles, precio, fechamod, usumod) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("siidss", $nombre, $tipo, $unidades, $precio, $fechamod, $usumod);

        if ($stmt->execute()) {
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
    <link rel="stylesheet" href="../Estilos/validacion.css">
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
            <option value="">-- Seleccionar --</option>
            <option value="1" <?= (isset($_POST['tipo']) && $_POST['tipo'] == 1) ? 'selected' : '' ?>>Disfraz</option>
            <option value="2" <?= (isset($_POST['tipo']) && $_POST['tipo'] == 2) ? 'selected' : '' ?>>Accesorio</option>
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
    const campos = {
        nombre: { min: 3, mensaje: "El nombre debe tener al menos 3 caracteres." },
        unidades: { min: 0, mensaje: "Unidades no puede ser negativo." },
        precio: { min: 0, mensaje: "El precio debe ser mayor o igual a 0." },
    };

    for (const id in campos) {
        const input = document.getElementById(id);
        if (input) {
            const mensaje = document.createElement("div");
            mensaje.className = "mensaje-validacion";
            input.insertAdjacentElement("afterend", mensaje);

            input.addEventListener("input", () => {
                const valor = input.value.trim();
                if (valor.length < campos[id].min || parseFloat(valor) < campos[id].min) {
                    mensaje.textContent = campos[id].mensaje;
                } else {
                    mensaje.textContent = "";
                }
            });
        }
    }
});
</script>
</body>
</html>
