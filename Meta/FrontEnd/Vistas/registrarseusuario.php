<?php
session_start();
if (!isset($_SESSION['id_persona'])) {
    // Si alguien intenta entrar a registrarseusuario.php sin pasar por registrarsepersona.php
    header("Location: registrarsepersona.php");
    exit;
}

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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitizar entradas
    $nombre_usu = mysqli_real_escape_string($conexion, trim($_POST['nombre-usu']));
    $correo     = mysqli_real_escape_string($conexion, trim($_POST['correo']));
    $telefono   = preg_replace('/[^0-9]/', '', mysqli_real_escape_string($conexion, trim($_POST['telefono'])));
    $passusu    = trim($_POST['passusu']);
    $passusu1   = trim($_POST['passusu1']);
    $img_perfil = $_FILES['img-perfil'];

    // Validaciones básicas
    if ($nombre_usu === '') {
        echo "entrando en validaciones basicas";
        $errores[] = 'El nombre de usuario es obligatorio.';
    } elseif (!preg_match('/^[A-Za-z0-9]+$/', $nombre_usu)) {
        $errores[] = 'El nombre de usuario solo puede contener letras y números, sin espacios ni símbolos.';
    }

    foreach ($errores as $error) {
    if (strpos($error, 'usuario') !== false) {
            $error_nombre_usu = $error;
        }
    }

     //validamos correo
    if ($correo === '') {
        echo "entrando en validaciones de correo 1";
        $errores[] = 'El correo electrónico es obligatorio.';
    } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $errores[] = "El correo electrónico no es válido.";
    }

    foreach ($errores as $error) {
        if (strpos($error, 'correo') !== false) {
            $error_correo = $error;
        }
    }

    // Verificar si ya existe el correo solo si el formato es válido
    if (filter_var($correo, FILTER_VALIDATE_EMAIL)) 
    {
        echo "entrando en validaciones de correo 2";
        $sql = "SELECT correo FROM usuario WHERE correo = '$correo'";
        $result = mysqli_query($conexion, $sql);
        if ($result && mysqli_num_rows($result) > 0) {
            $errores[] = "Ya existe un usuario registrado con ese correo.";
        }
    }

    foreach ($errores as $error) {
        if (strpos($error, 'correo') !== false || strpos($error, 'Correo') !== false) {
            $error_correo = $error;
        }
    }


    //validamos telefono
    if ($telefono === '') {
        echo "entrando en validaciones de telefono";
        $errores[] = "El número de teléfono es obligatorio.";
    } elseif (!preg_match('/^[0-9]{1,10}$/', $telefono)) {
        $errores[] = "El teléfono debe contener solo números y un máximo de 10 dígitos.";
    }

    foreach ($errores as $error) {
        if (strpos($error, 'teléfono') !== false || strpos($error, 'Teléfono') !== false) {
            $error_telefono = $error;
        }
    }

    //validamos contraseñas
    if ($passusu === '' || $passusu1 === '') {
        echo "entrando en validaciones de contraseñas";
        $errores[] = "Ambos campos de contraseña son obligatorios.";
    } elseif (!preg_match('/^[A-Za-z0-9]{6,}$/', $passusu)) {
        $errores[] = "La contraseña debe tener al menos 6 caracteres y solo puede contener letras y números, sin espacios ni símbolos.";
    } elseif ($passusu !== $passusu1) {
        $errores[] = "Las contraseñas no coinciden.";
    }

    foreach ($errores as $error) {
        if (strpos($error, 'coinciden') !== false) {
            $error_contraseña_repetida = $error;
        } elseif (strpos($error, 'contraseña') !== false) {
            $error_contraseña = $error;
        }
    }

    
    // Validación y procesamiento de imagen
    if ($img_perfil && $img_perfil['error'] == 0) {

        $permitidos = ['image/jpeg', 'image/png', 'image/gif', 'image/jpg'];

        if (!in_array($img_perfil['type'], $permitidos)) {
            $errores[] = "El formato de imagen no es válido. Solo se permiten JPG, PNG o GIF.";
        } else {
            $origen_temp = $img_perfil['tmp_name'];
            list($ancho_original, $alto_original) = getimagesize($origen_temp);
            $ancho_nuevo = 1280;
            $alto_nuevo = 1280;

            // Crear imagen desde el archivo original según tipo MIME
            switch ($img_perfil['type']) {
                case 'image/jpeg':
                case 'image/jpg':
                    $origen = imagecreatefromjpeg($origen_temp);
                    break;
                case 'image/png':
                    $origen = imagecreatefrompng($origen_temp);
                    break;
                case 'image/gif':
                    $origen = imagecreatefromgif($origen_temp);
                    break;
                default:
                    $errores[] = "Tipo de imagen no soportado.";
                    $origen = false;
            }

            if ($origen) {
                // Crear lienzo en blanco de 1280x1280
                $imagen_redimensionada = imagecreatetruecolor($ancho_nuevo, $alto_nuevo);

                // Rellenar fondo blanco si la imagen original es menor
                $blanco = imagecolorallocate($imagen_redimensionada, 255, 255, 255);
                imagefill($imagen_redimensionada, 0, 0, $blanco);

                // Reescalar la imagen original para que encaje en el nuevo tamaño
                imagecopyresampled(
                    $imagen_redimensionada,
                    $origen,
                    0, 0, 0, 0,
                    $ancho_nuevo,
                    $alto_nuevo,
                    $ancho_original,
                    $alto_original
                );

                // Guardar imagen
                $nombre_img = uniqid() . ".jpg"; // Siempre guardamos como JPG
                $directorio_destino = "uploads/usuario/";
                $ruta_completa = $directorio_destino . $nombre_img;

                if (imagejpeg($imagen_redimensionada, $ruta_completa, 90)) {
                    $ruta_imagen = $ruta_completa;
                } else {
                    $errores[] = "No se pudo guardar la imagen redimensionada.";
                }

                // Liberar memoria
                imagedestroy($origen);
                imagedestroy($imagen_redimensionada);
            }
        }

    } else {
        $errores[] = "Debes subir una imagen válida.";
    }

    foreach ($errores as $error) {
        if (strpos($error, 'imagen') !== false || strpos($error, 'formato') !== false) {
            $error_img = $error;
        }
    }

    if (count($errores) === 0) {

        //Escapar campos
        $nombre_usu  = mysqli_real_escape_string($conexion, $nombre_usu);
        $correo      = mysqli_real_escape_string($conexion, $correo);
        $telefono    = mysqli_real_escape_string($conexion, $telefono);
        $passusu     = mysqli_real_escape_string($conexion, $passusu);
        $passusu1    = mysqli_real_escape_string($conexion, $passusu1);
        $ruta_imagen = mysqli_real_escape_string($conexion, $ruta_imagen);

        $passusu_hash = password_hash(strtolower($passusu), PASSWORD_DEFAULT);
        $id_persona = $_SESSION['id_persona'];
            
        $sql_insert = "INSERT INTO usuario(nom_usu, img_perfil, correo, telefono, passusu, id_persona, rol) 
                            VALUES ('$nombre_usu','$ruta_imagen','$correo','$telefono','$passusu_hash','$id_persona',0)";

        if (mysqli_query($conexion, $sql_insert)) {
            $_SESSION['id_persona'] = mysqli_insert_id($conexion);
            echo "<script>alert('Usuario Registrado Exitosamente'); window.location.href='login.php';</script>";
            exit;
        } else {
            $errores[] = "Error al registrar usuario: " . mysqli_error($conexion);
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
    <link rel="stylesheet" href="../Estilos/registrarpersona.css">
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

        <form action="registrarseusuario.php" method="post" enctype="multipart/form-data" id="formregistro">
            <h2>Formulario Registrar Usuario</h2>
            <fieldset>
                <legend>Datos de usuario</legend>

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

                <button type="submit" class="btn">Registrar usuario</button>
                <p><a href="../Vistas/registrarsepersona.php">Volver a Registrar persona</a></p>
            </fieldset>
        </form>
    </section>
    <br>

    <?php include('footer.php');?>
</body>
</html>
