<?php
include('conexion.php'); // Ajusta la ruta si es necesario

#Funcion del conector de servidor
function escapar($html) {
    return htmlspecialchars($html, ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8");
}

#Los errores son almacenados en un vector
$errores = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = trim($_POST['nombre']);
    $apellido = trim($_POST['apellido']);
    $dni = intval($_POST['dni']);
    $calle = trim($_POST['calle']);
    $altura = trim($_POST['altura']);
    $depto = trim($_POST['depto']);
    $municipio = trim($_POST['municipio']);
    $direccion = trim($_POST['direccion']);
    $provincia = trim($_POST['provincia']);
    $pais = trim($_POST['pais']);
    $cod_pos = trim($_POST['cod-pos']);
    $mapa = trim($_POST['mapa']);
    $genero = $_POST['genero'];
    $nombre_usuario = $_POST['nombre-usuario'];
    $fec_nac = trim($_POST['fec-nac']);
    $telefono = trim($_POST['telefono']);
    $email = strtolower(trim($_POST['email']));
    $password = $_POST['password'];
    $password1 = $_POST['repetir-contraseña'];

    // Validaciones
    if ($nombre === '' || $apellido === '') {
        $errores[] = 'Nombre y Apellido son obligatorios.';
    }

    if ($dni < 3000000) {
        $errores[] = 'El DNI debe ser mayor o igual a 3 millones.';
    }

    if (strlen($password) < 6) {
        $errores[] = 'La contraseña debe tener al menos 6 caracteres.';
    }

    if ($password !== $password1) {
        $errores[] = 'Las contraseñas no coinciden.';
    }

    // Verificar si el correo ya existe
    $sql = "SELECT correo FROM usuario WHERE correo = '$email'";
    $result = mysqli_query($conexion, $sql);

    if (mysqli_num_rows($result) > 0) {
        $errores[] = "El correo ya está registrado.";
    }

    if (count($errores) === 0) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        $sql_insert = "INSERT INTO usuario(nombre, apellido, nom_usu, correo, telefono, direccion, dni, genero, cod_postal, fec_nac, passusu, calle, altura, depto, municipio, provincia, pais, mapa) 
        VALUES ('$nombre','$apellido','$nombre_usuario','$email','$telefono','$direccion','$dni','$genero','$cod_pos','$fec_nac','$password_hash','$calle','$altura','$depto','$municipio','$provincia','$pais','$mapa')";

        if (mysqli_query($conexion, $sql_insert)) {
            header("Location: login.php");
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
    <title>Registrarse</title>
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
    <h1 style="text-align: center;">Registrarse</h1>
    <section class="wrapper">
        <form action="registrarse.php" method="post" id="employee">
            <fieldset>
                <legend>Datos personales</legend>
                <section class="input-box">
                    <label for="nombre">Nombre:</label>
                    <input id="nombre" name="nombre" type="text" class="solo-letras" required>
                </section>
                <section class="input-box">
                    <label for="apellido">Apellido:</label>
                    <input id="apellido" name="apellido" type="text" class="solo-letras" required>
                </section>
                <section class="input-box">
                    <label for="DNI:">DNI:</label>
                    <input id="dni" name="dni" type="number" min="3000000" required>
                </section>
                <section class="input-box">
                    <label for="calle">Calle:</label>
                    <input id="calle" name="calle" type="text" class="solo-letras" required>
                </section>
                <section class="input-box">
                    <label for="altura">Altura:</label>
                    <input id="altura" name="altura" type="text" class="solo-letras" required>
                </section>
                <section class="input-box">
                    <label for="depto">Departamento:</label>
                    <input id="depto" name="depto" type="text" class="solo-letras" required>
                </section>
                <section class="input-box">
                    <label for="municipio">Municipio:</label>
                    <input id="municipio" name="municipio" type="text" class="solo-letras" required>
                </section>
                 <section class="input-box">
                    <label for="direccion">Direccion:</label>
                    <input id="direccion" name="direccion" type="text" required>
                </section>
                <section class="input-box">
                    <label for="provincia">Provincia:</label>
                    <input id="provincia" name="provincia" type="text" class="solo-letras" required>
                </section>
                <section class="input-box">
                    <label for="pais">País:</label>
                    <input id="pais" name="pais" type="text" class="solo-letras" required>
                </section>
                <section class="input-box">
                    <label for="cod-pos">Codigo Postal:</label>
                    <input id="cod-pos" name="cod-pos" type="text" required>
                </section>
                <section class="input-box">
                    <label for="mapa">Mapa:</label>
                    <input id="mapa" name="mapa" type="text" class="solo-letras" required>
                </section>
                <section class="input-box">
                    <label>Género:</label><br>
                    <label>
                        <input type="radio" name="genero" value="masculino" required>
                        Masculino
                    </label>
                    <label>
                        <input type="radio" name="genero" value="femenino">
                        Femenino
                    </label>
                    <label>
                        <input type="radio" name="genero" value="prefieronodecirlo">
                        Prefiero no decirlo
                    </label>
                </section>
                <section class="input-box">
                    <label for="fec-nac">Fecha de nacimiento:</label>
                    <input id="fec-nac" name="fec-nac" type="date" required>
                </section>
                <section class="input-box">
                    <label for="nombre-usuario">Nombre de usuario:</label>
                    <input id="nombre-usuario" name="nombre-usuario" type="text" class="solo-letras" required>
                </section>
                <section class="input-box">
                    <label for="img-perfil">Imagen de Perfil:</label>
                    <input id="img-perfil" name="img-perfil" type="file" required>
                </section>
                <section class="input-box">
                    <label for="telefono">Teléfono:</label>
                    <input id="telefono" name="telefono" type="text" class="solo-num" maxlength="10" required>
                </section>
                <section class="input-box">
                    <label for="email">Correo Electrónico:</label>
                    <input id="email" name="email" type="email" required>
                </section>
                <section class="input-box">
                    <label for="password">Contraseña:</label>
                    <input id="password" name="password" type="password" required>
                </section>
                <section class="input-box">
                    <label for="repetir-contraseña">Repetir contraseña:</label>
                    <input id="repetir-contraseña" name="repetir-contraseña" type="password" required>
                </section>
                <section class="remember-forgot">
                    <label><input type="checkbox"> Recordarme</label>
                </section>
                <button type="submit" class="btn-register">Registrarse</button>
                <p>¿Ya tienes una cuenta? <a href="../Vistas/login.php">Volver a Login</a></p>
            </fieldset>
        </form>
    </section>

    <br>

    <footer>
        <p><i class="bi bi-geo-alt-fill"></i> Tucumán 355, K4700 San Fernando del Valle de Catamarca, Catamarca</p>
        <p><i class="bi bi-envelope-fill"></i> info@metamorfosis.com</p>
        <p><i class="bi bi-telephone-fill"></i> +54 123 456 789</p>
        <p>&copy; 2024 Metamorfosis. Todos los derechos reservados.</p>
        <section class="social-icons">
            <a href="https://www.instagram.com/disfracesmetamorfosis/"><i class="bi bi-instagram"></i></a>
            <i class="bi bi-twitter-x"></i>
            <i class="bi bi-facebook"></i>
            <i class="bi bi-whatsapp"></i>
        </section>
    </footer>

    <script>
        document.querySelectorAll('.solo-letras').forEach(input => {
            input.addEventListener('input', function () {
                const soloLetras = /^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]*$/;
                if (!soloLetras.test(this.value)) {
                    this.setCustomValidity("Solo se permiten letras y espacios.");
                } else {
                    this.setCustomValidity("");
                }
            });
        });
    </script>


    <script>
        document.querySelectorAll('.solo-num').forEach(input => {
            input.addEventListener('input', function () {
                const solonum = /^[\d{1,8}]*$/;
                if (!solonum.test(this.value)) {
                    this.setCustomValidity("Solo se permiten numeros.");
                } else {
                    this.setCustomValidity("");
                }
            });
        });
    </script>


</body>
</html>