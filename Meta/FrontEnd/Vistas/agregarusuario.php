<?php
include('auth.php');
include('conexion.php');

$errores = [];
function escapar($html) {
    return htmlspecialchars($html, ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8");
}

// Manejo del formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre_usu = trim($_POST['nombre-usu']);
    $correo = trim($_POST['correo']);
    $telefono = preg_replace('/[^0-9]/', '', trim($_POST['telefono']));
    $passusu = trim($_POST['passusu']);
    $passusu1 = trim($_POST['passusu1']);
    $rol = intval($_POST['rol']);
    $img_perfil = $_FILES['img-perfil'];

    if ($nombre_usu === '' || !preg_match('/^[A-Za-z0-9]{3,20}$/', $nombre_usu)) {
        $errores[] = 'Nombre de usuario inválido.';
    }

    if ($correo === '' || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $errores[] = 'Correo electrónico inválido.';
    } else {
        $stmt = $conexion->prepare("SELECT id FROM usuario WHERE correo = ?");
        $stmt->bind_param("s", $correo);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $errores[] = "Ya existe un usuario con ese correo.";
        }
    }

    if ($telefono === '' || !preg_match('/^[0-9]{6,10}$/', $telefono)) {
        $errores[] = 'Teléfono inválido.';
    }

    if ($passusu !== $passusu1 || strlen($passusu) < 6 || !preg_match('/^[A-Za-z0-9]{6,}$/', $passusu)) {
        $errores[] = 'Las contraseñas no coinciden o son inválidas.';
    }

    if (!in_array($rol, [1, 2])) {
        $errores[] = 'Rol inválido.';
    }

    if ($img_perfil && $img_perfil['error'] === 0) {
        $permitidos = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($img_perfil['type'], $permitidos)) {
            $errores[] = "Formato de imagen no permitido.";
        } else {
            $nombre_img = uniqid() . ".jpg";
            $ruta = "uploads/usuario/" . $nombre_img;
            move_uploaded_file($img_perfil['tmp_name'], $ruta);
        }
    } else {
        $errores[] = "Se requiere una imagen válida.";
    }

    if (count($errores) === 0) {
        $pass_hash = password_hash(strtolower($passusu), PASSWORD_DEFAULT);
        $stmt = $conexion->prepare("INSERT INTO usuario(nom_usu, img_perfil, correo, telefono, passusu, rol, estadousu) VALUES (?, ?, ?, ?, ?, ?, 1)");
        $stmt->bind_param("sssssi", $nombre_usu, $ruta, $correo, $telefono, $pass_hash, $rol);
        if ($stmt->execute()) {
            header("Location: panelusuarios.php?usuarioagregado=ok");
            exit;
        } else {
            $errores[] = "Error en la base de datos.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Agregar Usuario</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../Estilos/index.css">
    <link rel="stylesheet" href="../Estilos/validacion.css">
    <link rel="stylesheet" href="../Estilos/agregarusuario.css">
    <!-- Script de SweetAlert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        /* Estilos para validaciones */
        .barra-validacion {
            height: 5px;
            margin-top: 3px;
            transition: background-color 0.3s ease;
        }

        .barra-validacion.valido {
            background-color: #4caf50;
        }

        .barra-validacion.invalido {
            background-color: #f44336;
        }

        .mensaje-validacion {
            font-size: 0.85em;
            margin-top: 2px;
        }

        .mensaje-validacion.valido {
            color: #4caf50;
        }

        .mensaje-validacion.invalido {
            color: #f44336;
        }
    </style>
</head>

<body>
<?php include('cabecera.php'); ?>

<main class="dni-card">
    <form action="agregarusuario.php" method="POST" enctype="multipart/form-data" class="dni-info" novalidate>
        <h2 style="text-align:center;">Agregar Usuario</h2>

        <p><label for="nombre-usu">Nombre de Usuario:</label>
        <input id="nombre-usu" type="text" name="nombre-usu" class="boton" required
            value="<?= escapar($_POST['nombre-usu'] ?? '') ?>"></p>

        <p><label for="correo">Correo:</label>
        <input id="correo" type="email" name="correo" class="boton" required
            value="<?= escapar($_POST['correo'] ?? '') ?>"></p>

        <p><label for="telefono">Teléfono:</label>
        <input id="telefono" type="text" name="telefono" class="boton" required
            value="<?= escapar($_POST['telefono'] ?? '') ?>"></p>

        <p><label for="passusu">Contraseña:</label>
        <input id="passusu" type="password" name="passusu" class="boton" required></p>

        <p><label for="passusu1">Repetir Contraseña:</label>
        <input id="passusu1" type="password" name="passusu1" class="boton" required></p>

        <p><label for="rol">Rol:</label>
        <select id="rol" name="rol" class="boton" required>
            <option value="">-- Seleccionar --</option>
            <option value="1" <?= (isset($_POST['rol']) && $_POST['rol'] == 1) ? 'selected' : '' ?>>Gerente</option>
            <option value="2" <?= (isset($_POST['rol']) && $_POST['rol'] == 2) ? 'selected' : '' ?>>Empleado</option>
        </select></p>

        <p><label for="img-perfil">Imagen de Perfil:</label>
        <input id="img-perfil" type="file" name="img-perfil" class="boton" required accept="image/jpeg,image/png,image/gif"></p>
        <div class="mensaje-validacion" id="img-error"></div>

        <?php if (!empty($errores)): ?>
            <div style="background-color: white; color: red; padding: 1vw; border-radius: 1vw;">
                <ul>
                    <?php foreach ($errores as $error) echo "<li>$error</li>"; ?>
                </ul>
            </div>
        <?php endif; ?>

        <button type="submit" class="boton">Registrar</button>
        <a href="panelusuarios.php"><div class="boton">Volver al Panel</div></a>
    </form>
</main>

<?php include('footer.php'); ?>

<script>
document.addEventListener("DOMContentLoaded", () => {

    // Validaciones definidas
    const validaciones = {
        'nombre-usu': {
            regex: /^[A-Za-z0-9]{3,20}$/,
            mensaje: 'El nombre de usuario debe tener entre 3 y 20 caracteres. Solo letras y números, sin espacios ni símbolos.'
        },
        'correo': {
            regex: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
            mensaje: 'Debe ser un correo electrónico válido.'
        },
        'telefono': {
            regex: /^[0-9]{6,10}$/,
            mensaje: 'Debe contener solo números (entre 6 y 10 dígitos).'
        },
        'passusu': {
            regex: /^[A-Za-z0-9]{6,}$/,
            mensaje: 'La contraseña debe tener al menos 6 caracteres, solo letras y números. Sin espacios ni símbolos.'
        },
        'passusu1': {
            regex: /^[A-Za-z0-9]{6,}$/,
            mensaje: 'La contraseña repetida debe tener al menos 6 caracteres, solo letras y números. Sin espacios ni símbolos.'
        }
    };

    // Añadir barras y mensajes de validación para cada input
    for (const id in validaciones) {
        const input = document.getElementById(id);

        if (input) {
            const barra = document.createElement('div');
            barra.classList.add('barra-validacion');

            const mensaje = document.createElement('div');
            mensaje.classList.add('mensaje-validacion');

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

    // Validar coincidencia entre contraseñas
    const pass1 = document.getElementById('passusu');
    const pass2 = document.getElementById('passusu1');

    if (pass1 && pass2) {
        const barra = document.createElement('div');
        barra.classList.add('barra-validacion');

        const mensaje = document.createElement('div');
        mensaje.classList.add('mensaje-validacion');

        pass2.insertAdjacentElement('afterend', barra);
        barra.insertAdjacentElement('afterend', mensaje);

        function validarCoincidencia() {
            if (pass1.value && pass2.value && pass1.value !== pass2.value) {
                barra.className = 'barra-validacion invalido';
                mensaje.className = 'mensaje-validacion invalido';
                mensaje.textContent = 'Las contraseñas no coinciden.';
            } else if (pass1.value && pass1.value === pass2.value) {
                barra.className = 'barra-validacion valido';
                mensaje.className = 'mensaje-validacion valido';
                mensaje.textContent = 'Contraseñas coinciden.';
            } else {
                barra.className = '';
                mensaje.textContent = '';
            }
        }

        pass1.addEventListener('input', validarCoincidencia);
        pass2.addEventListener('input', validarCoincidencia);
    }

    // Validación y vista previa de la imagen
    const inputImg = document.getElementById("img-perfil");
    const maxPesoMB = 4;
    const maxAncho = 1280;
    const maxAlto = 1280;
    const mensajeError = document.getElementById("img-error");

    inputImg.addEventListener("change", function () {
        const archivo = this.files[0];

        // Remover preview y botón cancelar anteriores si existían
        const previewExistente = document.getElementById("preview-img");
        if (previewExistente) previewExistente.remove();

        const botonCancelar = document.getElementById("cancelar-img");
        if (botonCancelar) botonCancelar.remove();

        mensajeError.textContent = "";

        if (!archivo) return;

        // Validar tamaño
        if (archivo.size > maxPesoMB * 1024 * 1024) {
            mensajeError.textContent = `La imagen no debe superar los ${maxPesoMB} MB.`;
            this.value = "";
            return;
        }

        const reader = new FileReader();
        reader.onload = function (e) {
            const img = new Image();
            img.src = e.target.result;

            img.onload = function () {
                if (img.width !== maxAncho || img.height !== maxAlto) {
                    mensajeError.textContent = `Nota: la imagen será redimensionada automáticamente a ${maxAncho} x ${maxAlto} píxeles.`;
                } else {
                    mensajeError.textContent = "";
                }

                // Mostrar vista previa
                img.id = "preview-img";
                img.style.maxWidth = "200px";
                img.style.marginTop = "10px";
                inputImg.parentNode.appendChild(img);

                // Agregar botón cancelar
                const btnCancelar = document.createElement("button");
                btnCancelar.id = "cancelar-img";
                btnCancelar.textContent = "Cancelar imagen";
                btnCancelar.type = "button";
                btnCancelar.style.display = "block";
                btnCancelar.style.marginTop = "10px";
                btnCancelar.onclick = function () {
                    inputImg.value = "";
                    img.remove();
                    btnCancelar.remove();
                    mensajeError.textContent = "";
                };
                inputImg.parentNode.appendChild(btnCancelar);
            };
        };

        reader.readAsDataURL(archivo);
    });
});
</script>

</body>
</html>
