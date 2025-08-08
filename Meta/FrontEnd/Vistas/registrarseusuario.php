<?php
session_start();
include('conexion.php');

function escapar($html) {
    return htmlspecialchars($html, ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8");
}

$errores = [];
$error_nombre_usu = '';
$error_correo = '';
$error_telefono = '';
$error_contraseña = '';
$error_contraseña_repetida = '';
$error_img = '';

$mensaje_dni_duplicado = '';
$mostrar_datos_usuario = false;
$id_persona = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ----------- VERIFICACIÓN DE DNI ------------------
    if (isset($_POST['verificar_dni'])) {
        $dni = trim($_POST['dni']);

        // 1. Verificar si el DNI existe en persona
        $sql = "SELECT * FROM persona WHERE dni = '$dni'";
        $result = mysqli_query($conexion, $sql);

        if ($result && mysqli_num_rows($result) > 0) {
            $fila = mysqli_fetch_assoc($result);
            $id_persona = $fila['id'];
            $_SESSION['id_persona'] = $id_persona;
            $_SESSION['datos_persona'] = $fila;

            // Verificar si ya tiene usuario
            $sql_usuario = "SELECT id FROM usuario WHERE id_persona = $id_persona";
            $result_usuario = mysqli_query($conexion, $sql_usuario);

            if ($result_usuario && mysqli_num_rows($result_usuario) > 0) {
                $mensaje_dni_duplicado = "Ya existe un usuario registrado con ese DNI. Intente nuevamente.";
                $mostrar_datos_usuario = false;
            } else {
                $mostrar_datos_usuario = true;
            }
        } else {
            // No existe como persona → redirigir
            $_SESSION['mensaje_dni'] = "El DNI ingresado no existe. Debes registrarte con tus datos personales antes de crear un usuario.";
            header("Location: registrarsepersona.php");
            exit;
        }
    }

    // ----------- REGISTRO DE USUARIO ------------------
    elseif (isset($_POST['registrar_usuario'])) {
        // Recuperar id_persona desde sesión
        $id_persona = $_SESSION['id_persona'] ?? null;

        if (!$id_persona) {
            $errores[] = "No se pudo asociar el usuario con una persona válida.";
        }

        // Sanitizar entradas
        $nombre_usu = mysqli_real_escape_string($conexion, trim($_POST['nombre-usu']));
        $correo     = mysqli_real_escape_string($conexion, trim($_POST['correo']));
        $telefono   = preg_replace('/[^0-9]/', '', mysqli_real_escape_string($conexion, trim($_POST['telefono'])));
        $passusu    = trim($_POST['passusu']);
        $passusu1   = trim($_POST['passusu1']);
        $img_perfil = $_FILES['img-perfil'];

        // --- Validaciones básicas ---
        if ($nombre_usu === '') {
            $errores[] = 'El nombre de usuario es obligatorio.';
        } elseif (!preg_match('/^[A-Za-z0-9]+$/', $nombre_usu)) {
            $errores[] = 'El nombre de usuario solo puede contener letras y números, sin espacios ni símbolos.';
        }

        if ($correo === '') {
            $errores[] = 'El correo electrónico es obligatorio.';
        } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            $errores[] = "El correo electrónico no es válido.";
        } else {
            $sql = "SELECT correo FROM usuario WHERE correo = '$correo'";
            $result = mysqli_query($conexion, $sql);
            if ($result && mysqli_num_rows($result) > 0) {
                $errores[] = "Ya existe un usuario registrado con ese correo.";
            }
        }

        if ($telefono === '') {
            $errores[] = "El número de teléfono es obligatorio.";
        } elseif (!preg_match('/^[0-9]{1,10}$/', $telefono)) {
            $errores[] = "El teléfono debe contener solo números y un máximo de 10 dígitos.";
        }

        if ($passusu === '' || $passusu1 === '') {
            $errores[] = "Ambos campos de contraseña son obligatorios.";
        } elseif (!preg_match('/^[A-Za-z0-9]{6,}$/', $passusu)) {
            $errores[] = "La contraseña debe tener al menos 6 caracteres y solo puede contener letras y números.";
        } elseif ($passusu !== $passusu1) {
            $errores[] = "Las contraseñas no coinciden.";
        }

        // Imagen de perfil
        $ruta_imagen = '';
        if ($img_perfil && $img_perfil['error'] === 0) {
            $permitidos = ['image/jpeg', 'image/png'];

            if (!in_array($img_perfil['type'], $permitidos)) {
                $errores[] = "El formato de imagen no es válido. Solo se permiten JPG y PNG.";
            } elseif ($img_perfil['size'] > 4 * 1024 * 1024) {
                $errores[] = "La imagen no debe superar los 4MB.";
            } else {
                $origen_temp = $img_perfil['tmp_name'];
                list($ancho_original, $alto_original) = getimagesize($origen_temp);

                $ancho_nuevo = 1280;
                $alto_nuevo = 1280;

                $origen = null;
                if ($img_perfil['type'] == 'image/jpeg') {
                    $origen = imagecreatefromjpeg($origen_temp);
                } elseif ($img_perfil['type'] == 'image/png') {
                    $origen = imagecreatefrompng($origen_temp);
                }

                if ($origen) {
                    $imagen_redimensionada = imagecreatetruecolor($ancho_nuevo, $alto_nuevo);
                    $blanco = imagecolorallocate($imagen_redimensionada, 255, 255, 255);
                    imagefill($imagen_redimensionada, 0, 0, $blanco);
                    imagecopyresampled(
                        $imagen_redimensionada,
                        $origen,
                        0, 0, 0, 0,
                        $ancho_nuevo,
                        $alto_nuevo,
                        $ancho_original,
                        $alto_original
                    );

                    $nombre_img = uniqid('usuario_') . ".jpg";
                    $directorio_destino = "uploads/usuario/";
                    $ruta_completa = $directorio_destino . $nombre_img;

                    if (!imagejpeg($imagen_redimensionada, $ruta_completa, 90)) {
                        $errores[] = "No se pudo guardar la imagen.";
                    } else {
                        $ruta_imagen = $ruta_completa;
                    }

                    imagedestroy($origen);
                    imagedestroy($imagen_redimensionada);
                } else {
                    $errores[] = "Error al procesar la imagen.";
                }
            }
        } else {
            $errores[] = "Debe subir una imagen.";
        }

        // Asignar mensajes de error por campo
        foreach ($errores as $error) {
            if (strpos($error, 'usuario') !== false) $error_nombre_usu = $error;
            if (strpos($error, 'correo') !== false) $error_correo = $error;
            if (strpos($error, 'teléfono') !== false || strpos($error, 'Teléfono') !== false) $error_telefono = $error;
            if (strpos($error, 'contraseña') !== false && strpos($error, 'coinciden') === false) $error_contraseña = $error;
            if (strpos($error, 'coinciden') !== false) $error_contraseña_repetida = $error;
            if (strpos($error, 'imagen') !== false || strpos($error, 'formato') !== false) $error_img = $error;
        }

        // Si no hay errores, registrar
        if (count($errores) === 0) {
            $passusu_hash = password_hash(strtolower(trim($passusu)), PASSWORD_DEFAULT);

            $stmt = $conexion->prepare("INSERT INTO usuario (nom_usu, img_perfil, correo, telefono, passusu, id_persona, rol) 
                                        VALUES (?, ?, ?, ?, ?, ?, 0)");

            $stmt->bind_param("sssssi", $nombre_usu, $ruta_imagen, $correo, $telefono, $passusu_hash, $id_persona);

            if ($stmt->execute()) {
                // Limpieza de sesión
                unset($_SESSION['id_persona']);
                unset($_SESSION['datos_persona']);

                // Redirección tras éxito
                header("Location: login.php?registrouser=ok");
                exit;
            } else {
                $errores[] = "Error al registrar usuario: " . $stmt->error;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <title>Registrar Usuario</title>
    <link rel="icon" type="image/x-icon" href="./assets/favicon.ico" />
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../Estilos/registroU.css">
    <link rel="stylesheet" href="../Estilos/index.css">
    <link rel="stylesheet" href="../Estilos/validacion.css">

    <!-- Script de SweetAlert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<script>
    document.addEventListener("DOMContentLoaded", () => {

        // Función para capitalizar texto si fuera necesario
        function capitalizar(texto) {
            return texto
                .replace(/\s+/g, ' ')
                .trim()
                .toLowerCase()
                .split(' ')
                .map(p => p.charAt(0).toUpperCase() + p.slice(1))
                .join(' ');
        }

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

        // Recorre los campos definidos y aplica validación en tiempo real
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

    });
</script>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const inputImg = document.getElementById("img-perfil");
    const maxPesoMB = 4;
    const maxAncho = 1280;
    const maxAlto = 1280;

    inputImg.addEventListener("change", function () {
        const archivo = this.files[0];
        const mensajeError = this.nextElementSibling;

        // Eliminar vista previa anterior y botón cancelar
        const previewExistente = document.getElementById("preview-img");
        if (previewExistente) previewExistente.remove();

        const botonCancelar = document.getElementById("cancelar-img");
        if (botonCancelar) botonCancelar.remove();

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
                // Validar dimensiones
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

<script> //Registro persona
document.addEventListener('DOMContentLoaded', () => 
{
  //1. Traemos lo que definimos en registropersona.php
  const p = new URLSearchParams(location.search);

  //2. Como lo definimos como "ok", procede a mostrar el mensaje
  if (p.get('registropersona') === 'ok') 
  {
    Swal.fire({
      position: 'top',
      icon: 'success',
      title: 'La persona fue registrada',
      showConfirmButton: false,
      timer: 1500
    });

  //3. Al refrescar la página, no volverá a salir el mensaje
    history.replaceState({}, '', location.pathname);
  }
});
</script>


<body>
    <?php include('cabecera.php'); ?>
    <section class="nav-route">
        <a href="index.php">Inicio / </a>
        <a href="login.php">Login / </a>
        <a>Registrarse</a>
    </section>
    <br>
    <h1 style="text-align: center;">Registrar Usuario</h1>
    
        <section>
            <div style="text-align:center; margin-bottom:20px;">
            <video src="uploads/METAMORFOSIS VIDEO REGISTRAR USUARIO.mp4" controls width="480" poster="">
            Tu navegador no soporta la reproducción de video.
            </video>
            <p style="font-size:14px; color:#555;">Video instructivo: Cómo registrar un usuario</p>
            </div>
        </section>

    <section class="wrapperregistro" id="wrapperregistro">

        <h2>Formulario Registrar Usuario</h2>

        <!-- Formulario SOLO para verificar DNI -->
        <form action="registrarseusuario.php" method="post">
            <fieldset>
                <legend>Verificación</legend>
                <section class="input-box">
                    <label for="dni">DNI:</label>
                    <input id="dni" name="dni" type="number" min="3000000" required
                        value="<?php echo isset($_POST['dni']) ? htmlspecialchars($_POST['dni']) : ''; ?>">
                    <span class="error" style="color:red;"><?php echo $mensaje_dni_duplicado; ?></span>
                    <br><br>
                    <input type="submit" name="verificar_dni" value="Verificar DNI" class="btn-verificar-dni">
                </section>
            </fieldset>
        </form>

        <form action="registrarseusuario.php" method="post" enctype="multipart/form-data" id="formregistro">

            <?php if ($mostrar_datos_usuario && isset($_SESSION['datos_persona'])):
                    $p = $_SESSION['datos_persona'];
                ?>
                    <div class="registro-container">
                        <fieldset>
                            <legend>Datos personales</legend>
                            <div class="columna-formulario">
                                <p><strong>Nombre:</strong> <?php echo escapar($p['nombre'] . ' ' . $p['apellido']); ?></p>
                                <p><strong>DNI:</strong> <?php echo escapar($p['dni']); ?></p>
                                <p><strong>Fecha de nacimiento:</strong> <?php echo escapar($p['fec_nac']); ?></p>
                                <p><strong>Género:</strong> <?php echo escapar($p['genero']); ?></p>
                            </div>

                            <section class="input-box">
                                <?php if (!empty($p['img'])): ?>
                                <p><strong>Imagen:</strong><br>
                                    <img src="<?php echo escapar($p['img']); ?>" alt="Imagen de perfil" style="max-width:200px;">
                                </p>
                                <?php endif; ?>
                            </section>

                        </fieldset>

                        <fieldset>
                            <legend>Datos de domicilio</legend>
                            <div class="columna-formulario">
                                <p><strong>Provincia:</strong> <?php echo escapar($p['provincia']); ?></p>
                                <p><strong>Departamento:</strong> <?php echo escapar($p['departamento']); ?></p>
                                <p><strong>Municipio:</strong> <?php echo escapar($p['municipio']); ?></p>
                                <p><strong>Localidad:</strong> <?php echo escapar($p['localidad']); ?></p>
                                <p><strong>Barrio:</strong> <?php echo escapar($p['barrio']); ?></p>
                                <p><strong>Calle:</strong> <?php echo escapar($p['calle']); ?></p>
                                <p><strong>Altura:</strong> <?php echo escapar($p['altura']); ?></p>
                            </div>
                            
                        </fieldset>
                        
                    </div>
            <?php endif; ?>

            <fieldset id="fieldset-usuario" style="display: <?php echo $mostrar_datos_usuario ? 'block' : 'none'; ?>;">
                <legend>Registro de usuario</legend>

                <section class="input-box">
                    <label for="nombre-usu">Nombre de usuario:</label>
                    <input id="nombre-usu" name="nombre-usu" type="text" class="solo-letras" required value="<?php echo isset($_POST['nombre-usu']) ? escapar($_POST['nombre-usu']) : ''; ?>">
                    <span class="error" style="color:red;"><?php echo $error_nombre_usu; ?></span>
                </section>

                <section class="input-box">
                    <label for="correo">Correo:</label>
                    <input id="correo" name="correo" type="text" required value="<?php echo isset($_POST['correo']) ? escapar($_POST['correo']) : ''; ?>">
                    <span class="error" style="color:red;"><?php echo $error_correo; ?></span>
                </section>

                <section class="input-box">
                    <label for="telefono">Teléfono:</label>
                    <input id="telefono" name="telefono" type="text" class="solo-num" required value="<?php echo isset($_POST['telefono']) ? escapar($_POST['telefono']) : ''; ?>">
                    <span class="error" style="color:red;"><?php echo $error_telefono; ?></span>
                </section>

                <section class="input-box">
                    <label for="passusu">Contraseña:</label>
                    <input id="passusu" name="passusu" type="password" class="solo-pass" required>
                    <span class="error" style="color:red;"><?php echo $error_contraseña; ?></span>
                </section>

                <section class="input-box">
                    <label for="passusu1">Repetir contraseña:</label>
                    <input id="passusu1" name="passusu1" type="password" class="solo-pass" required>
                    <span class="error" style="color:red;"><?php echo $error_contraseña_repetida; ?></span>
                </section>

                <section class="input-box">
                    <label for="img-perfil">Imagen de perfil:</label>
                    <input id="img-perfil" name="img-perfil" type="file" required>
                    <span class="error" style="color:red;"><?php echo $error_img; ?></span>
                </section>
                
                <input type="submit" name="registrar_usuario" value="Registrar Usuario" class="btn-registrar-usuario">
            </fieldset>
        </form>
    </section>
    <br>

    <?php include('footer.php');?>
</body>
</html>
