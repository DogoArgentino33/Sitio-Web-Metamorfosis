<?php include('auth.php'); 

//Verificando si la cuenta no es rol gerente
if (isset($_SESSION['rol']) && $_SESSION['rol'] != 1 && $_SESSION['rol'] != 4) 
{
    header("Location: index.php"); 
    exit;
}
?>

<!-- Cuerpo de la página -->
<!DOCTYPE html>
<html lang="es">
<head>
    <title>Alquiler de Disfraces - Metamorfosis</title>
    <link rel="icon" type="image/x-icon" href="./assets/favicon.ico" />
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../Estilos/index.css">
    <link rel="stylesheet" href="../Estilos/gerente.css">
    <!-- Script de SweetAlert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <?php include('cabecera.php'); ?>
    <section class="nav-route">
        <a href="index.php">Inicio / </a>
        <a>Gerente</a>
    </section>
    <main>
        <h1 style="text-align: center;">Panel Administrador de Metamorfosis - Gerente</h1>
        <section class="table-container">

            <!-- Área de acceso a los paneles -->
            <a href="../Vistas/panelpersonas.php" class="nav-tablas-administrador" title="Personas"><i class="bi bi-person-vcard"></i>Personas</a>
            <a href="../Vistas/panelusuarios.php" class="nav-tablas-administrador" title="Usuarios"><i class="bi bi-person-circle"></i>Usuarios</a>
            <a href="../Vistas/panelproductos.php" class="nav-tablas-administrador" title="Productos"><i class="bi bi-gift"></i>Productos</a>
            <a href="../Vistas/asignarusuario.php" class="nav-tablas-administrador" title="Asignar Persona a Usuario"><i class="bi bi-people"></i>Asignar</a>
            <a href="../Vistas/panelalquileres.php" class="nav-tablas-administrador" title="Alquileres"><i class="bi bi-calendar2-week"></i>Alquileres</a>
            <a href="../Vistas/estadisticas.php" class="nav-tablas-administrador" title="Alquileres"><i class="bi bi-clipboard-data"></i>Estadisticas</a>
            
        </section>
    </main>

    <?php include('footer.php');?>
</body>
</html>         