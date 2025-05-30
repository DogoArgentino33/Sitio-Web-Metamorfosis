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
    $correo = mysqli_real_escape_string($conexion, trim($_POST['correo']));
    $telefono = preg_replace('/[^0-9]/', '', mysqli_real_escape_string($conexion, trim($_POST['Telefono'])));
    $passusu = trim($_POST['passusu']);
    $passusu1 = trim($_POST['passusu1']);
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
    if (filter_var($correo, FILTER_VALIDATE_EMAIL)) {
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
        echo "entrando en validaciones de imagen";
    $permitidos = ['image/jpeg', 'image/png', 'image/gif', 'image/jpg'];
    if (!in_array($img_perfil['type'], $permitidos)) {
        $errores[] = "El formato de imagen no es válido. Solo se permiten JPG, PNG o GIF.";
    } else {
        $nombre_img = uniqid() . "_" . basename($img_perfil['name']);
        $directorio_destino = "uploads/";
        $ruta_completa = $directorio_destino . $nombre_img;

        if (move_uploaded_file($img_perfil['tmp_name'], $ruta_completa)) {
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
        $nombre_imagen = uniqid() . '_' . basename($img_perfil['name']);
        $ruta_imagen = 'uploads/' . $nombre_imagen;

        if (move_uploaded_file($img_perfil['tmp_name'], $ruta_imagen)) {
            $passusu_hash = password_hash(strtolower($passusu), PASSWORD_DEFAULT);
            $id_persona = $_SESSION['id_persona'];
            echo "INSERTANDO USUARIO";
            $sql_insert = "INSERT INTO usuario(nom_usu, img_perfil, correo, telefono, passusu, id_persona) 
                           VALUES ('$nombre_usu','$ruta_imagen','$correo','$telefono','$passusu_hash','$id_persona')";

            if (mysqli_query($conexion, $sql_insert)) {
                unset($_SESSION['id_persona']);
                header("Location: login.php");
                exit;
            } else {
                $errores[] = "Error al registrar usuario: " . mysqli_error($conexion);
            }
        } else {
            $errores[] = "Error al subir la imagen.";
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
    <h1 style="text-align: center;">Registrar Usuario</h1>
    <section class="wrapper">

        <form action="registrarseusuario.php" method="post" enctype="multipart/form-data" id="employee">
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
                    <label for="Telefono">Teléfono:</label>
                    <input id="Telefono" name="Telefono" type="text" class="solo-num" required value="<?php echo isset($_POST['Telefono']) ? escapar($_POST['Telefono']) : ''; ?>">
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

                <button type="submit" class="btn-register">Registrar usuario</button>
                <p><a href="../Vistas/registrarsepersona.php">Volver a Registrar persona</a></p>
            </fieldset>
        </form>
    </section>

    <br>

    <?php include('footer.php');?>

</body>
</html>
