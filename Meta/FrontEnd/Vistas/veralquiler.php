<?php include('auth.php'); include('conexion.php');

//Verificando si la cuenta no es rol gerente o empleado
if (isset($_SESSION['rol']) && $_SESSION['rol'] != 1 && $_SESSION['rol'] != 2 && $_SESSION['rol'] != 4 )
{
    header("Location: index.php"); 
    exit;
}

//Verificamos si existe
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) 
{
    echo "ID de alquiler no válido.";
    exit;
}

$id = intval($_GET['id']);

//Realizamos la consulta
$stmt = $conexion->prepare(" SELECT alquiler.id,id_usuario,id_producto, desde, hasta,total, cantidad, id_metodopago FROM alquiler
                                    INNER JOIN usuario
                                    ON usuario.id = alquiler.id_usuario
                                    INNER JOIN producto 
                                    ON producto.id = alquiler.id_producto 
                                    INNER JOIN metodo_pago
                                    ON metodo_pago.id = alquiler.id_metodopago;
                                    WHERE alquiler.id = ?;");
$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) 
{
    echo "Alquiler no encontrado.";
    exit;
}

$consulta = $resultado->fetch_assoc();
?>

<!-- Inicio del HTML -->
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
</head>
<body>
    <h1>Información de la consulta</h1>
    <section class="dni-card">
        <div class="dni-info">

        <!-- Datos -->
            <p><strong>Disfraz:</strong> <?= htmlspecialchars($consulta['id_usuario']) ?></p>
            <p><strong>Nombre:</strong> <?= htmlspecialchars($consulta['id_disfraz']) ?></p>
            <p><strong>Correo:</strong> <?= htmlspecialchars($consulta['desde']) ?></p>
            <p><strong>Consulta:</strong> <?= htmlspecialchars($consulta['hasta']) ?></p>
            <p><strong>Consulta:</strong> <?= htmlspecialchars($consulta['total']) ?></p>
            <p><strong>Consulta:</strong> <?= htmlspecialchars($consulta['cantidad']) ?></p>
            <p><strong>Consulta:</strong> <?= htmlspecialchars($consulta['id_metodopago']) ?></p>
          
            <br>

            <a href="panelalquileres.php"><button type="button" class="boton">Volver al panel</button></a>
            <button type="button" class="boton-exportar" onclick="abrirModalExportar()">Responder</button>
        </div>
    </section>

<!-- Función Responder -->
<section id="modalExportar" onclick="cerrarModalExportar()">
    <section class="modal-exportar-card" onclick="event.stopPropagation();">
        <section class="modal-exportar-content">
            <h2>Responder Consulta</h2>
            <form id="formExportar" action="exportarusuario.php" method="POST" novalidate>

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

</body>
</html>