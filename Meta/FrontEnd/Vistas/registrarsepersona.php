<?php
session_start();
include('conexion.php'); 

function escapar($html) {
    return htmlspecialchars($html, ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8");
}

// función que validará sólo letras y espacios
function validarSoloLetras($cadena) {
    // Permite letras (mayúsculas y minúsculas), espacios y caracteres acentuados
    return preg_match("/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/", $cadena);
}

$errores = [];
$mensaje_dni_duplicado = '';
$error_fecha_nac = '';
$error_nombre = '';
$error_apellido = '';
$error_img = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitizar entradas
    $nombre     = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
    $apellido   = isset($_POST['apellido']) ? trim($_POST['apellido']) : '';
    $dni        = isset($_POST['dni']) ? intval($_POST['dni']) : 0;
    $fec_nac    = isset($_POST['fec-nac']) ? trim($_POST['fec-nac']) : '';
    $domicilio  = isset($_POST['domicilio']) ? trim($_POST['domicilio']) : '';
    $provincia  = isset($_POST['provincia']) ? trim($_POST['provincia']) : '';
    $pais       = isset($_POST['pais']) ? trim($_POST['pais']) : '';
    $genero     = isset($_POST['genero']) ? $_POST['genero'] : '';
    $img        = isset($_FILES['img-persona']) ? $_FILES['img-persona'] : null;

    // Validaciones
   if ($nombre === '' || !validarSoloLetras($nombre)) {
        $errores[] = 'El nombre solo puede contener letras y espacios, sin números ni símbolos.';
    }

    if ($apellido === '' || !validarSoloLetras($apellido)) {
        $errores[] = 'El apellido solo puede contener letras y espacios, sin números ni símbolos.';
    }

    foreach ($errores as $error) {
    if (strpos($error, 'nombre') !== false) {
        $error_nombre = $error;
    }
    if (strpos($error, 'apellido') !== false) {
        $error_apellido = $error;
    }
}

    // Validación de DNI duplicados
    $sql = "SELECT dni FROM persona WHERE dni = '$dni'";
    $result = mysqli_query($conexion, $sql);
    if ($result && mysqli_num_rows($result) > 0) {
        $errores[] = "Ya existe una persona registrada con ese DNI.";
    }

    //en caso de DNI duplicado 
    foreach ($errores as $error) {
        if (strpos($error, 'DNI') !== false) {
            $mensaje_dni_duplicado = $error;
        }
    }

    //validacion de fecha
    if (!empty($fec_nac)) {
    $fecha_nac_timestamp = strtotime($fec_nac);
    $fecha_minima = strtotime('1900-01-01');
    $fecha_hoy = time();
    $edad_18 = strtotime('-18 years', $fecha_hoy);

    if ($fecha_nac_timestamp < $fecha_minima) {
        $errores[] = "La fecha de nacimiento no puede ser anterior al año 1900.";
    } elseif ($fecha_nac_timestamp > $edad_18) {
        $errores[] = "Debes tener al menos 18 años para registrarte.";
    }
    } else {
        $errores[] = "La fecha de nacimiento es obligatoria.";
    }

    foreach ($errores as $error) {
    if (strpos($error, 'fecha de nacimiento') !== false || strpos($error, 'años') !== false) {
        $error_fecha_nac = $error;
    }
    }

    // Validación y procesamiento de imagen
    if ($img && $img['error'] == 0) {
    $permitidos = ['image/jpeg', 'image/png', 'image/gif', 'image/jpg'];
    if (!in_array($img['type'], $permitidos)) {
        $errores[] = "El formato de imagen no es válido. Solo se permiten JPG, PNG o GIF.";
    } else {
        $nombre_img = uniqid() . "_" . basename($img['name']);
        $directorio_destino = "uploads/";
        $ruta_completa = $directorio_destino . $nombre_img;

        if (move_uploaded_file($img['tmp_name'], $ruta_completa)) {
            $ruta_imagen = $ruta_completa;
        } else {
            $errores[] = "No se pudo guardar la imagen.";
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
        // Escapar campos
        $nombre     = mysqli_real_escape_string($conexion, $nombre);
        $apellido   = mysqli_real_escape_string($conexion, $apellido);
        $dni        = mysqli_real_escape_string($conexion, $dni);
        $fec_nac    = mysqli_real_escape_string($conexion, $fec_nac);
        $domicilio  = mysqli_real_escape_string($conexion, $domicilio);
        $provincia  = mysqli_real_escape_string($conexion, $provincia);
        $pais       = mysqli_real_escape_string($conexion, $pais);
        $genero     = mysqli_real_escape_string($conexion, $genero);
        $ruta_imagen = mysqli_real_escape_string($conexion, $ruta_imagen);

        $sql_insert = "INSERT INTO persona (nombre, apellido, dni, fec_nac, domicilio, pais, provincia, genero, img)
                       VALUES ('$nombre', '$apellido', '$dni', '$fec_nac', '$domicilio', '$pais', '$provincia', '$genero', '$ruta_imagen')";

        if (mysqli_query($conexion, $sql_insert)) {
            $_SESSION['id_persona'] = mysqli_insert_id($conexion); // 👈 Esto es lo importante
            header("Location: registrarseusuario.php");
            exit;
        } else {
            $errores[] = "Error al registrar la persona: " . mysqli_error($conexion);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <title>Registrar Persona</title>
    <link rel="icon" type="image/x-icon" href="./assets/favicon.ico" />
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../Estilos/registro.css">
    <link rel="stylesheet" href="../Estilos/index.css">
</head>
<body>
    <?php include('cabecera.php'); ?>
    <section class="nav-route">
        <a href="index.php">Inicio / </a>
        <a href="login.php">Login / </a>
        <a>Registrarse</a>
    </section>
    <br>
    <h1 style="text-align: center;">Registrar Persona</h1>
    <section class="wrapper">
        <form action="registrarsepersona.php" method="post" enctype="multipart/form-data" id="employee">
            <fieldset>
                <legend>Datos personales</legend>

                <!-- Nombre -->
                <section class="input-box">
                    <label for="nombre">Nombre(s):</label>
                    <input id="nombre" name="nombre" type="text" class="solo-letras" required>
                    <span class="error" style="color:red;"><?php echo $error_nombre; ?></span>
                </section>

                <!-- Apellido -->
                <section class="input-box">
                    <label for="apellido">Apellido(s):</label>
                    <input id="apellido" name="apellido" type="text" class="solo-letras" required>
                    <span class="error" style="color:red;"><?php echo $error_apellido; ?></span>
                </section>

                <!-- DNI -->
                <section class="input-box">
                    <label for="DNI:">DNI:</label>
                    <input id="dni" name="dni" type="number" min="3000000" required value="<?php echo isset($_POST['dni']) ? htmlspecialchars($_POST['dni']) : ''; ?>">
                    <span class="error" style="color:red;"><?php echo $mensaje_dni_duplicado; ?></span>
                </section>

                <!-- Fecha Nacimiento -->
                <section class="input-box">
                    <label for="fec-nac">Fecha de nacimiento:</label>
                    <input id="fec-nac" name="fec-nac" type="date" required value="<?php echo isset($_POST['fec-nac']) ? htmlspecialchars($_POST['fec-nac']) : ''; ?>">
                    <span class="error" style="color:red;"><?php echo $error_fecha_nac; ?></span>
                </section>

                <!-- Domicilio -->
                <section class="input-box">
                    <label for="domicilio">Domicilio:</label>
                    <input id="domicilio" name="domicilio" type="text" required>
                </section>

                <!-- Provincia -->
                <section class="input-box">
                    <label for="provincia">Provincia:</label>
                    <input id="provincia" name="provincia" type="text" class="solo-letras" required>
                </section>

                <!-- Pais -->
                <section class="input-box">
                    <label for="pais">País:</label>
                    <input id="pais" name="pais" type="text" class="solo-letras" required>
                </section>

                <!-- Género -->
                <section class="input-box-genero">
                    <label>Género:</label>
                    <br>
                    <label><input type="radio" name="genero" value="masculino" required>Masculino</label><br>
                    <label><input type="radio" name="genero" value="femenino">Femenino</label><br>
                    <label><input type="radio" name="genero" value="prefiero-no-decirlo">Prefiero no decirlo</label>
                </section>

                <!-- Imagen de perfil -->
                <section class="input-box">
                    <br>
                    <label for="img-persona">Imagen personal:</label>
                    <input id="img-persona" name="img-persona" type="file" required>
                    <span class="error" style="color:red;"><?php echo $error_img; ?></span>
                </section>

                <button type="submit" class="btn-register">Registrar persona</button>
                <p><a href="../Vistas/index.php">Volver a Pagina principal</a></p>
            </fieldset>
        </form>
    </section>

    <br>

    <?php include('footer.php');?>

</body>
</html> 