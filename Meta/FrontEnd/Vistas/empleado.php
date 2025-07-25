<?php include('auth.php'); ?>

<!DOCTYPE html>
<html lang="es">
<head>
    <title>Alquiler de Disfraces - Metamorfosis</title>
    <link rel="icon" type="image/x-icon" href="./assets/favicon.ico" />
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../Estilos/index.css">
    <link rel="stylesheet" href="../Estilos/empleado.css">
</head>
<body>
    <?php include('cabecera.php'); ?>
    <section class="nav-route">
        <a href="index.php">Inicio / </a>
        <a>Empleado</a>
    </section>
    <main>
        <h1 style="text-align: center;">Panel Administrador de Metamorfosis - Empleado</h1>
        <br>
        <section class="table-container">
            <a href="../Vistas/paneldisfracesempleado.php" class="nav-tablas-administrador" title="Disfraces"><i class="bi bi-balloon"></i>Disfraces</a>
            <a href="../Vistas/panelaccesoriosempleado.php" class="nav-tablas-administrador" title="Accesorios"><i class="bi bi-eyeglasses"></i>Accesorios</a>
            <a href="../Vistas/panelalquileresempleado.php" class="nav-tablas-administrador" title="Alquileres"><i class="bi bi-calendar"></i>Alquileres</a>
            <a href="../Vistas/panelconsulta.php"             class="nav-tablas-administrador" title="Consultas"><i class="bi bi-file-earmark-text"></i>Consultas</a>
        </section>
    </main>

    <?php include('footer.php');?>

</body>
</html>