<?php
session_start(); // Iniciar sesión
include('conexion.php'); // Ajusta la ruta si es necesario

$mensaje = "";

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $passusu = strtolower($_POST['password']);
    $correo = $_POST['correo'];

    $sql = "SELECT id, correo, passusu FROM usuario WHERE correo='$correo'";

    $result = mysqli_query($conexion, $sql);

    if(mysqli_num_rows($result) > 0){
        $reg = mysqli_fetch_assoc($result);
        // Verificar contraseña usando password_verify
    if (password_verify($passusu, $reg['passusu'])) {
        // Actualizar estado a logueado (2)
        $id = $reg['id'];
        $update_sql = "UPDATE usuario SET estadousu = 2 WHERE id = $id";
        mysqli_query($conexion, $update_sql);

        // Crear variables de sesión
        session_start();
        $_SESSION['id'] = $id;

        header("Location: index.php");
        exit;
    }
    else {
            $mensaje = "Correo o contraseña incorrectos.";
        }
    } else {
        $mensaje = "Usuario no encontrado.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <title>Login</title>
    <link rel="icon" type="image/x-icon" href="./assets/favicon.ico" />
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../Estilos/index.css">
    <link rel="stylesheet" href="../Estilos/login.css">
</head>
<body>
   <?php include('cabecera.php'); ?>
    <section class="nav-route">
        <a href="index.php">Inicio / </a>
        <a>Login</a>
    </section>
    <h1 style="text-align: center;">Iniciar Sesión</h1>
    <section class="wrapper">
        <form action="" method="post" id="formlogin">
            <fieldset>
                <legend>Iniciar Sesión</legend>
                <section class="input-box">
                    <label for="correo">Correo:</label>
                    <input id="correo" name="correo" type="text" required>
                </section>
                <section class="input-box">
                    <label for="password">Contraseña:</label>
                    <input id="password" name="password" type="password" required>
                </section>
                <input type="submit" value="Login" class="btn">
                <section class="remember-forgot">
                    <label><input type="checkbox">Recordarme</label>
                    <a href="../Vistas/recuperar.php">¿Olvidaste la Contraseña?</a>
                </section>

                <section class="register-link">
                    <p>¿No tenés una cuenta? <a href="../Vistas/registrarsepersona.php">Registrar Persona</a></p>
                </section>          

                <?php if (!empty($mensaje)) : ?>
                    <section id="mensaje-login" style="text-align:center; color:red; margin-top:10px;">
                        <?= htmlspecialchars($mensaje) ?>
                    </section>
                <?php endif; ?>
                
            </fieldset>
        </form>
    </section>

    <?php include('footer.php');?>
    
</body>
</html>

