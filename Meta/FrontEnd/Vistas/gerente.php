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
            <a href="../Vistas/panelusuarios.php" class="nav-tablas-administrador" title="Usuarios"><i class="bi bi-person"></i>Usuarios</a>
            <a href="../Vistas/panelproductos.php" class="nav-tablas-administrador" title="Productos"><i class="bi bi-balloon"></i>Productos</a>
            <a href="../Vistas/panelalquileres.php" class="nav-tablas-administrador" title="Alquileres"><i class="bi bi-calendar"></i>Alquileres</a>
            <a href="../Vistas/estadisticas.php" class="nav-tablas-administrador" title="Alquileres"><i class="bi bi-bar-chart"></i>Estadisticas</a>
        </section>
    </main>

    <?php include('footer.php');?>
    
</body>
</html>