<?php
include('conexion.php'); 


function escapar($html) {
    return htmlspecialchars($html, ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8");
}


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
    $mapa = trim($_POST['mapa']);
    $genero = $_POST['genero'];
    $fec_nac = trim($_POST['fec-nac']);

    // Validaciones
    if ($nombre === '' || $apellido === '') {
        $errores[] = 'Nombre y Apellido son obligatorios.';
    }

    if ($dni < 3000000) {
        $errores[] = 'El DNI debe ser mayor o igual a 3 millones.';
    }

    $sql = "SELECT correo FROM nombre, apellido WHERE apellido = '$apellido'";
    $result = mysqli_query($conexion, $sql);

    if (mysqli_num_rows($result) > 0) {
        $errores[] = "La persona esta asociada a un correo que ya está registrado.";
    }

    if (count($errores) === 0) {

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
    <h1 style="text-align: center;">Registrarse</h1>
    <section class="wrapper">
        <form action="registrarse.php" method="post" id="employee">
            <fieldset>
                <legend>Datos personales</legend>

                <!-- Nombre -->
                <section class="input-box">
                    <label for="nombre">Nombre:</label>
                    <input id="nombre" name="nombre" type="text" class="solo-letras" required>
                </section>

                <!-- Apellido -->
                <section class="input-box">
                    <label for="apellido">Apellido:</label>
                    <input id="apellido" name="apellido" type="text" class="solo-letras" required>
                </section>

                <!-- DNI -->
                <section class="input-box">
                    <label for="DNI:">DNI:</label>
                    <input id="dni" name="dni" type="number" min="3000000" required>
                </section>

                <!-- Calle -->
                <section class="input-box">
                    <label for="calle">Calle:</label>
                    <input id="calle" name="calle" type="text" class="solo-letras" required>
                </section>

                <!-- Altura -->
                <section class="input-box">
                    <label for="altura">Altura:</label>
                    <input id="altura" name="altura" type="number" required>
                </section>

                <!-- Departamento -->
                <section class="input-box">
                    <label for="depto">Departamento:</label>
                    <input id="depto" name="depto" type="text" class="solo-letras" required>
                </section>

                <!-- Municipio -->
                <section class="input-box">
                    <label for="municipio">Municipio:</label>
                    <input id="municipio" name="municipio" type="text" class="solo-letras" required>
                </section>

                <!-- Direccion -->
                 <section class="input-box">
                    <label for="direccion">Direccion:</label>
                    <input id="direccion" name="direccion" type="text" required>
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

                <!-- Mapa -->
                <section class="input-box">
                    <label for="mapa">Mapa:</label>
                    <input id="mapa" name="mapa" type="text" class="solo-letras" required>
                </section>

                <!-- Genero -->
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

                <!-- Fecha Nacimiento -->
                <section class="input-box">
                    <label for="fec-nac">Fecha de nacimiento:</label>
                    <input id="fec-nac" name="fec-nac" type="date" required>
                </section>

                <button type="submit" class="btn-register">Registrarse</button>
                <p>¿Ya tienes una cuenta? <a href="../Vistas/login.php">Volver a Login</a></p>
            </fieldset>
        </form>
    </section>

    <br>

    <?php include('footer.php');?>
    
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