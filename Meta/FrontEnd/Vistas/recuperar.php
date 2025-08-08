<?php
session_start();
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../../../vendor/autoload.php';
require_once('conexion.php');

$mensaje = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['correo'])) {
    $correo = $_POST['correo'];

    // Verificar si el correo está registrado
    $stmt = $conexion->prepare("SELECT id, nom_usu FROM usuario WHERE correo = ?");
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 1) {
        $usuario = $resultado->fetch_assoc();
        $codigo = rand(100000, 999999); // Código de 6 dígitos

        // Guardar en sesión
        $_SESSION['codigo_recuperacion'] = $codigo;
        $_SESSION['correo_recuperacion'] = $correo;

        // Enviar correo
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'huguitonavelino@gmail.com';
            $mail->Password = 'gmiwdezudiqofvpg'; // usa una contraseña de aplicación
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom('huguitonavelino@gmail.com', 'Metamorfosis');
            $mail->addAddress($correo, $usuario['nom_usu']);
            $mail->Subject = 'Codigo de recuperacion de contrasena';
            $mail->Body = "Hola {$usuario['nom_usu']},\n\nTu código de recuperación es: $codigo\n\nSi no solicitaste esto, ignora el mensaje.";

            $mail->send();
            
            $mensaje = "Código enviado al correo electrónico.";
        } catch (Exception $e) {
            $mensaje = "Error al enviar correo: " . $mail->ErrorInfo;
        }
    } else {
        $mensaje = "Correo no registrado.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <title>Recuperar Contraseña</title>
    <link rel="icon" type="image/x-icon" href="./assets/favicon.ico" />
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../Estilos/recuperar.css">
    <link rel="stylesheet" href="../Estilos/index.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>

<body>
<?php include('cabecera.php'); ?>

<section class="nav-route">
    <a href="index.php">Inicio / </a>
    <a href="login.php">Login / </a>
    <a>Recuperar</a>
</section>

<h1 style="text-align: center;">Recuperar Contraseña</h1>

<section style="align-items: center; display: flex; justify-content: center;">
    <form method="post" action="recuperar.php" class="form-recover" >
    <fieldset style="border: 1px solid #ddd;">
        <legend>Recuperar Contraseña</legend>
        <?php if ($mensaje): ?>
            <p style="color: red;"><?php echo $mensaje; ?></p>
        <?php endif; ?>
        <section class="input-box">
            <label for="correo">Correo Electrónico</label>
            <input type="text" placeholder="Correo Electrónico" required id="correo" name="correo" 
                   value="<?php echo isset($_POST['correo']) ? htmlspecialchars($_POST['correo']) : '' ?>">
        </section>
        <section>
            <label>Enviar código de recuperación</label>
            <button type="submit" class="btn-send">Enviar Código</button>
    </fieldset>
    </form>
</section>
<br>
<section style="align-items: center; display: flex; justify-content: center;">
    <!-- Este segundo formulario es completamente separado -->
    <form method="get" action="VistaContraseñaForgot.php" class="form-recover">
        <fieldset>
            <legend>Confirmar Código</legend>

            <section class="input-box">
                <label for="codigo">Código de recuperación</label>
                <input type="text" placeholder="Código de recuperación" name="codigo" id="codigo" required>
            </section>
            
            <button type="submit" class="button">Confirmar Código</button>
            <p><a href="login.php">Volver a Login</a></p>
        </fieldset>
    </form>
</section>


</section>

<?php if (!empty($mensaje)): ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    Swal.fire({
        icon: "<?php echo (strpos($mensaje, 'Error') === 0) ? 'error' : 'success'; ?>",
        title: "<?php echo (strpos($mensaje, 'Error') === 0) ? 'Error al enviar' : 'Correo enviado'; ?>",
        text: "<?php echo addslashes($mensaje); ?>",
        confirmButtonText: "Aceptar"
    }).then(() => {
        window.location.href = "recuperar.php";
    });
</script>
<?php endif; ?>
</body>
</html>

<?php include('footer.php'); ?>
</body>
</html>
