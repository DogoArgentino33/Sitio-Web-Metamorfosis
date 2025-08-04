<?php include('conexion.php'); 
session_start();

$nombre_session = '';
$apellido_session = '';
$correo_session = '';

if (isset($_SESSION['usuario'])) {
    $nombre_session = $_SESSION['usuario']['nombre'] ?? '';
    $apellido_session = $_SESSION['usuario']['apellido'] ?? '';
    $correo_session = $_SESSION['usuario']['correo'] ?? '';
}

function escapar($html) 
{
    return htmlspecialchars($html, ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8");
}

$errores = [];
$error_nombre = '';
$error_apellido = '';
$error_correo = '';
$error_consulta = '';


if ($_SERVER['REQUEST_METHOD'] == 'POST') 
{
    //Sanitizando
    $nombre    = mysqli_real_escape_string($conexion, trim($_POST['nombre']));
    $apellido  = mysqli_real_escape_string($conexion, trim($_POST['apellido']));
    $correo    = mysqli_real_escape_string($conexion, trim($_POST['correo']));
    $consulta  = mysqli_real_escape_string($conexion, trim($_POST['consulta']));
    
    //Acumulando errores nombre
    foreach ($errores as $error) 
    {
        if (strpos($error, 'nombre') !== false) 
        {
            $error_nombre = $error;
        }
    }

    //Apellido
    if ($apellido === '') 
    {
        $errores[] = 'El apellido es obligatorio.';
    } 
    elseif (!preg_match('/^[A-Za-z0-9]+$/', $apellido)) 
    {
        $errores[] = 'El apellido solo puede contener letras y números, sin espacios ni símbolos.';
    }

    //Acumulando errores apellido
    foreach ($errores as $error) 
    {
        if (strpos($error, 'apellido') !== false) 
        {
            $error_apellido = $error;
        }
    }

    //Correo
    if ($correo === '') 
    {
        $errores[] = 'El correo electrónico es obligatorio.';
    } 
    elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) 
    {
        $errores[] = "El correo electrónico ingresado no es válido.";
    }

    //Acumulando errores correo
    foreach ($errores as $error) 
    {
        if (strpos($error, 'correo') !== false) 
        {
            $error_correo = $error;
        }
    }

    if (isset($_SESSION['usuario'])) {
        if ($_SESSION['usuario']['nombre'] !== $nombre ||
            $_SESSION['usuario']['apellido'] !== $apellido ||
            $_SESSION['usuario']['correo'] !== $correo) {
            $errores[] = "Los datos ingresados no coinciden con tu sesión activa.";
        }
    }

    //Verificando si error es igual a 0, TRUE -> preparando para insert
    if (count($errores) === 0) 
    {
        //Escapar campos
        $nombre       = mysqli_real_escape_string($conexion, $nombre);
        $apellido     = mysqli_real_escape_string($conexion, $apellido);
        $correo       = mysqli_real_escape_string($conexion, $correo);
        $consulta     = mysqli_real_escape_string($conexion, $consulta);

        // Preparando la consulta
        $stmt = $conexion->prepare("INSERT INTO consulta (nombre, apellido, correo, consulta) VALUES (?, ?, ?, ?)");

        $stmt->bind_param("ssss", $nombre, $apellido, $correo, $consulta);

        // Ejecutar y verificar si se realizó
        if ($stmt->execute()) 
        {
            header("Location: contactos.php?envioconsulta=ok");
            exit;
        } 
        else 
        {
            $errores[] = "Error al enviar la consulta: " . $stmt->error;
        }
        
        $stmt->close();
    }

}
?>

<!-- Funcion de validacion -->
 <script>
    document.addEventListener("DOMContentLoaded", () => 
    {
        const validaciones = 
        {
            correo: 
            {
                regex: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
                mensaje: 'Debe ser un correo electrónico válido.'
            }
        };

        // Recorre los campos definidos y aplica validación en tiempo real
        for (const id in validaciones) 
        {
            const input = document.getElementById(id);

            if (input) 
            {
                const barra = document.createElement('div');
                barra.classList.add('barra-validacion');

                const mensaje = document.createElement('div');
                mensaje.classList.add('mensaje-validacion');

                input.insertAdjacentElement('afterend', barra);
                barra.insertAdjacentElement('afterend', mensaje);

                input.addEventListener('input', () => 
                {
                    const valor = input.value.trim();
                    const { regex, mensaje: textoMensaje } = validaciones[id];

                    if (regex.test(valor)) 
                    {
                        barra.className = 'barra-validacion valido';
                        mensaje.className = 'mensaje-validacion valido';
                        mensaje.textContent = 'Dato válido.';
                    } 
                    else 
                    {
                        barra.className = 'barra-validacion invalido';
                        mensaje.className = 'mensaje-validacion invalido';
                        mensaje.textContent = textoMensaje;
                    }
                });
            }
        }

    })
 </script>

<!-- SweetAlert: Consulta enviada -->
 <script>
document.addEventListener('DOMContentLoaded', () => 
{
  //1. Traemos lo que definimos
  const p = new URLSearchParams(location.search);

  //2. Como lo definimos como "ok", procede a mostrar el mensaje
  if (p.get('envioconsulta') === 'ok') 
  {
    Swal.fire({
      position: 'top',
      icon: 'success',
      title: 'La consulta fue enviada',
      showConfirmButton: false,
      timer: 1500
    });

  //3. Al refrescar la página, no volverá a salir el mensaje
    history.replaceState({}, '', location.pathname);
  }
});
</script>


<!DOCTYPE html>
<html lang="es">
<head>
    <title>Contactos - Metamorfosis</title>
    <link rel="icon" type="image/x-icon" href="./assets/favicon.ico" />
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../Estilos/index.css">
    <link rel="stylesheet" href="../Estilos/contactanos.css">
    <!-- Script de SweetAlert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
   <?php include('cabecera.php'); ?>
    
    <main>
        <section class="nav-route">
            <a href="index.php">Inicio / </a>
            <a>Contactos</a>
        </section>

        <h2 class="main-h2">Contáctanos</h2>
        
        <!-- FORMULARIO -->
        <section class="wrapper">
            <form action="contactos.php" method="post" enctype="multipart/form-data" id="formconsulta">
                <fieldset>
                    <legend>Deja aquí tu consulta y responderemos lo antes posible</legend>

                    <h4>Datos personales</h4>
                    <section class="input-box">
                        <label for="nombre">Nombre: </label>
                       <input id="nombre" name="nombre" type="text" required readonly
                        value="<?php echo isset($_POST['nombre']) ? escapar($_POST['nombre']) : escapar($nombre_session); ?>">
                        <span class="error" style="color:red;"><?php echo $error_nombre; ?></span>
                    </section>

                    <section class="input-box">
                        <label for="apellido">Apellido: </label>
                        <input id="apellido" name="apellido" type="text" required readonly
                         value="<?php echo isset($_POST['apellido']) ? escapar($_POST['apellido']) : escapar($apellido_session); ?>">
                        <span class="error" style="color:red;"><?php echo $error_apellido; ?></span>
                    </section>

                    <section class="input-box">
                        <label for="correo">Correo Electrónico: </label>
                        <input id="correo" name="correo" type="email" required readonly
                        value="<?php echo isset($_POST['correo']) ? escapar($_POST['correo']) : escapar($correo_session); ?>">
                        <span class="error" style="color:red;"><?php echo $error_correo; ?></span>
                    </section>

                    <section class="input-box">
                        <label for="consulta" class="floating-placeholder">Deja tu consulta:</label>
                        <input type="text" id="consulta" name="consulta" max="500" required value="<?php echo isset($_POST['consulta']) ? escapar($_POST['consulta']) : ''; ?>">
                    </section>

                    <button type="submit" class="btn">Enviar</button>
                </fieldset>
            </form>
        </section>
        
        <section class="contact-info">
            <p>Si tienes alguna pregunta o consulta, no dudes en ponerte en contacto con nosotros:</p>
            <ul>
                <li><i class="bi bi-geo-alt-fill"></i> Tucumán 355, K4700 San Fernando del Valle de Catamarca, Catamarca</li>
                <li><i class="bi bi-telephone-fill"></i> Teléfono: +54 123 456 789</li>
                <li><i class="bi bi-envelope-fill"></i> Email: <a href="mailto:info@metamorfosis.com">info@metamorfosis.com</a></li>
                <li><i class="bi bi-instagram"></i> Instagram: <a href="https://www.instagram.com/disfracesmetamorfosis/">@disfracesmetamorfosis</a></li>
            </ul>
        </section>
        
        <section class="map-container" style="text-align: center; margin-top: 20px;">
            <h3>Nuestra Ubicación</h3>
            <iframe class="iframe"
                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3154.823694249857!2d-66.264825!3d-28.467292!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x943a06bdf176be8b%3A0x6de4f091bb6e3e27!2sTucum%C3%A1n%20355%2C%20K4700%20San%20Fernando%20del%20Valle%20de%20Catamarca%2C%20Catamarca!5e0!3m2!1ses-419!2sar!4v1634576401573!5m2!1ses-419!2sar" 
                width="600" 
                height="450" 
                style="border:0;" 
                allowfullscreen="" 
                loading="lazy">
            </iframe>
        </section>
    </main>
    
    <?php include('footer.php');?>
</body>
</html>