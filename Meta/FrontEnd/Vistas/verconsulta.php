<?php include('auth.php'); include('conexion.php');

//Verificando si la cuenta no es rol empleado
if (isset($_SESSION['rol']) and $_SESSION['rol'] != 2) 
{
    header("Location: index.php"); 
    exit;
}

//Verificamos si existe
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) 
{
    echo "ID de consulta no válido.";
    exit;
}

$id = intval($_GET['id']);

//Realizamos la consulta
$stmt = $conexion->prepare("SELECT id, nombre, apellido, correo, consulta FROM consulta WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) 
{
    echo "Consulta no encontrada.";
    exit;
}

$consulta = $resultado->fetch_assoc();
?>

<!-- Inicio del Html -->
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Información de la consulta</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../Estilos/verusuario.css">

     <!-- SweetAlert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        .swal2-container {
            z-index: 99999 !important;
        }
    </style>

    <style>
        #modalExportar {
        display: none;
        position: fixed;
        top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(0,0,0,0.5);
        justify-content: center;
        align-items: center;
        z-index: 9999;
        }

        /* Caja blanca del modal */
        .modal-exportar-card {
        background: white;
        border-radius: 8px;
        max-width: 100vh;
        width: 90%;
        max-height: 90vh;
        overflow-y: auto;
        padding: 20px;
        box-sizing: border-box;
        }

        textarea[name="mensaje"] {
        width: 100%;
        min-height: 10vh;
        max-height: 60vh; /* no más alto que esto */
        resize: none; /* No permitir redimensionar manual */
        padding: 10px;
        font-size: 1rem;
        line-height: 1.4;
        box-sizing: border-box;
        border: 1px solid #ccc;
        border-radius: 4px;
        font-family: inherit;
        overflow-y: hidden; /* Esconder scroll, lo ajustamos con JS */
        }
    </style>
</head>
<body>
    <h1>Información de la consulta</h1>
    <section class="dni-card">
        <div class="dni-info">

        <!-- Datos -->
            <p><strong>Nombre:</strong> <?= htmlspecialchars($consulta['nombre']) ?></p>
            <p><strong>Apellido:</strong> <?= htmlspecialchars($consulta['apellido']) ?></p>
            <p><strong>Correo:</strong> <?= htmlspecialchars($consulta['correo']) ?></p>
            <p><strong>Consulta:</strong> <?= htmlspecialchars($consulta['consulta']) ?></p>
          
            <br>

            <a href="panelconsulta.php"><button type="button" class="boton">Volver al panel</button></a>
            <button type="button" class="boton-exportar" onclick="abrirModalExportar()">Responder</button>

        </div>
    </section>

<!-- Función Responder -->
<section id="modalExportar" onclick="cerrarModalExportar()">
    <section class="modal-exportar-card" onclick="event.stopPropagation();">
        <section class="modal-exportar-content">
            <h2>Responder Consulta</h2>
                <form id="formExportar" action="responderconsulta.php" method="POST" novalidate>
                    <input type="hidden" name="id" value="<?= $consulta['id'] ?>">
                    <input type="hidden" name="correo" value="<?= htmlspecialchars($consulta['correo']) ?>">
                    <textarea name="mensaje" placeholder="Escribe tu respuesta aquí..." required></textarea>
                    <nav class="modal-exportar-buttons" aria-label="Acciones del modal exportar">
                        <button type="button" class="boton" onclick="cerrarModalExportar()">Cancelar</button>
                        <button type="submit" class="boton-exportar">Responder</button>
                    </nav>
                </form>
        </section>
    </section>
</section>


<!-- Para el exportar -->
<script>
    function abrirModalExportar() 
    {
        const modal = document.getElementById('modalExportar');
        modal.style.display = 'flex';  // Aquí sí poner display:flex para mostrarlo
    }

    function cerrarModalExportar() 
    {
        const modal = document.getElementById('modalExportar');
        modal.style.display = 'none';  // Ocultarlo
    }
</script>

<script>
    // Función para auto-ajustar altura del textarea
    function autoGrowTextarea(el) {
        el.style.height = "5px"; // resetear para calcular scrollHeight correcto
        el.style.height = (el.scrollHeight) + "px";
    }

    // Seleccionamos el textarea y le agregamos evento input
    const textarea = document.querySelector('textarea[name="mensaje"]');
    textarea.addEventListener('input', function() {
        autoGrowTextarea(this);
    });

    // Ajustar altura al abrir el modal (por si tiene texto)
    function abrirModalExportar() {
        const modal = document.getElementById('modalExportar');
        modal.style.display = 'flex';
        // Ajustar altura del textarea
        autoGrowTextarea(textarea);
        // Poner foco para mejor UX
        textarea.focus();
    }

    // Mantén la función cerrarModalExportar igual
    function cerrarModalExportar() {
        const modal = document.getElementById('modalExportar');
        modal.style.display = 'none';
    }
</script>


</body>
</html>