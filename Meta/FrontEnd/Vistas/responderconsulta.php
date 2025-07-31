<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Incluye el autoload de Composer
require __DIR__ . '/../vendor/autoload.php';
require_once('conexion.php');

// Validar si se recibió el ID por GET o POST
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID no válido.");
}

$id = intval($_GET['id']);

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
$respuesta = "Gracias por comunicarte con nosotros. Respondemos a tu consulta:\n\n" . $textoConsulta . "\n\nRespuesta: Estaremos en contacto pronto.";

// CONFIGURA tu cuenta de envío
$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com'; // o el de tu proveedor
    $mail->SMTPAuth = true;
    $mail->Username = 'huguitonavelino@gmail.com.com'; // tu correo Gmail
    $mail->Password = 'ynin cdzc tplq ast'; // generada desde Gmail
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    $mail->setFrom('tuemail@gmail.com', 'Nombre del remitente');
    $mail->addAddress($correoDestino, $nombreDestino);
    $mail->Subject = 'Respuesta a tu consulta';
    $mail->Body    = $respuesta;

    $mail->send();
    echo "<script>
        alert('Correo enviado correctamente.');
        window.location.href = 'verconsulta.php?id=$id';
    </script>";
} catch (Exception $e) {
    echo "<script>
        alert('Error al enviar el correo: " . $mail->ErrorInfo . "');
        window.location.href = 'verconsulta.php?id=$id';
    </script>";
}
?>
