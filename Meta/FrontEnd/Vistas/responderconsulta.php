<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Incluye el autoload de Composer
require __DIR__ . '/../../../vendor/autoload.php';
require_once('conexion.php');

// Validar si se recibió el ID por GET o POST
if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    die("ID no válido.");
}

$id = intval($_POST['id']);


// Obtener datos de la consulta desde la base de datos
$stmt = $conexion->prepare("SELECT nombre, apellido, correo, consulta FROM consulta WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    die("Consulta no encontrada.");
}

$datos = $resultado->fetch_assoc();
$correoDestino = $datos['correo'];
$nombreDestino = $datos['nombre'] . ' ' . $datos['apellido'];
$textoConsulta = $datos['consulta'];

// Aquí va tu respuesta (en un caso real vendría desde un formulario)
$respuesta = "Gracias por comunicarte con nosotros. Respondemos a tu consulta a continuación:\n\n";
$respuesta .= $_POST['mensaje'];
$respuesta .= "\n\nSaludos cordiales,\nEl equipo de Metamorfosis.";



// CONFIGURA tu cuenta de envío
$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com'; // o el de tu proveedor
    $mail->SMTPAuth = true;
    $mail->Username = 'huguitonavelino@gmail.com'; // tu correo Gmail
    $mail->Password = 'gmiwdezudiqofvpg'; // generada desde Gmail
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    $mail->setFrom('huguitonavelino@gmail.com', 'Metamorfosis');
    $mail->addAddress($correoDestino, $nombreDestino);
    $mail->Subject = 'Respuesta a tu consulta';
    $mail->Body    = $respuesta;

    $mail->send();
    echo '<!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8" />
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    </head>
    <body>
    <script>
    Swal.fire({
        icon: "success",
        title: "Correo enviado",
        text: "La respuesta fue enviada correctamente.",
        confirmButtonText: "Aceptar"
    }).then(() => {
        window.location.href = "verconsulta.php?id=' . $id . '";
    });
    </script>
    </body>
    </html>';
} catch (Exception $e) {
    echo '<!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8" />
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    </head>
    <body>
    <script>
    Swal.fire({
        icon: "error",
        title: "Error al enviar",
        text: "Ocurrió un problema: ' . addslashes($mail->ErrorInfo) . '",
        confirmButtonText: "Cerrar"
    }).then(() => {
        window.location.href = "verconsulta.php?id=' . $id . '";
    });
    </script>
    </body>
    </html>';
}
?>
