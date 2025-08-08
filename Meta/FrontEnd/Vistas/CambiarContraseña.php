<?php
session_start();
require_once('conexion.php');

$mensaje = "";

if (!isset($_SESSION['correo_recuperacion'])) {
    // Si no hay correo en sesión, redirigir
    header("Location: recuperar.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nueva = $_POST['nueva'];
    $confirmar = $_POST['confirmar'];

    if ($nueva !== $confirmar) {
        $mensaje = "Las contraseñas no coinciden.";
    } elseif (strlen($nueva) < 6) {
        $mensaje = "La contraseña debe tener al menos 6 caracteres.";
    } else {
        $hash = password_hash($nueva, PASSWORD_BCRYPT);
        $correo = $_SESSION['correo_recuperacion'];

        $stmt = $conexion->prepare("UPDATE usuario SET passusu = ? WHERE correo = ?");
        $stmt->bind_param("ss", $hash, $correo);
        $stmt->execute();

        // Limpio sesión
        session_destroy();

        echo '
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8" />
            <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        </head>
        <body>
        <script>
        Swal.fire({
            icon: "success",
            title: "Contraseña actualizada",
            text: "Tu contraseña fue modificada con éxito.",
            confirmButtonText: "Iniciar sesión"
        }).then(() => {
            window.location.href = "login.php";
        });
        </script>
        </body>
        </html>';
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <title>Cambiar Contraseña</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" href="../Estilos/index.css">
    <link rel="stylesheet" href="../Estilos/recuperar.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<?php include('cabecera.php'); ?>

<section class="nav-route">
    <a href="index.php">Inicio / </a>
    <a href="login.php">Login / </a>
    <a href="recuperar.php">Recuperar / </a>
    <a>Cambiar Contraseña</a>
</section>

<h1 style="text-align: center;">Cambiar Contraseña</h1>

<section class="wrapper" style="display: flex; justify-content: center;">
    <form method="post" action="CambiarContraseña.php" class="form-recover" style="max-width: 600px;">
        <fieldset style="border: 1px solid #ddd; padding: 10px;">
            <legend>Nueva Contraseña</legend>

            <?php if ($mensaje): ?>
                <p style="color: red;"><?php echo $mensaje; ?></p>
            <?php endif; ?>

            <section class="input-box">
                <label for="nueva">Nueva Contraseña</label>
                <input type="password" name="nueva" id="nueva" placeholder="Nueva contraseña" required>
            </section>

            <br>

            <section class="input-box">
                <label for="confirmar">Confirmar Contraseña</label>
                <input type="password" name="confirmar" id="confirmar" placeholder="Confirmar contraseña" required>
            </section>

            <br>
            <button type="submit" class="button">Actualizar Contraseña</button>
            <p><a href="login.php">Volver al Login</a></p>
        </fieldset>
    </form>
</section>

<?php include('footer.php'); ?>

<script>
// Validación simple en frontend
document.querySelector('form').addEventListener('submit', function (e) {
    const pass1 = document.getElementById('nueva').value;
    const pass2 = document.getElementById('confirmar').value;

    if (pass1.length < 6) {
        e.preventDefault();
        Swal.fire("Error", "La contraseña debe tener al menos 6 caracteres.", "error");
    } else if (pass1 !== pass2) {
        e.preventDefault();
        Swal.fire("Error", "Las contraseñas no coinciden.", "error");
    }
});
</script>

</body>
</html>
