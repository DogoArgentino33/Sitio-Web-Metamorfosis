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
    <header>
        <section class="logo-container">
            <h1>Metamorfosis</h1>
            <form action="resultadosbusqueda.php" class="formcentrado">
                <input type="text" id="Idinputtextbuscar" placeholder="Buscar">
            </form>

            <section class="container-login-cart">
                <a href="../Vistas/login.php"><i class="bi bi-person-circle"></i></a>
                <a href="../Vistas/gerente.php"><i class="bi bi-gear-fill"></i></a>
                <a href="../Vistas/empleado.php"><i class="bi bi-pencil-square"></i></a>
                <a href="../Vistas/administrador.php"><i class="bi bi-pc-display"></i></a>
            </section>
        </section>
        <br>
        <section class="container-nav">
            <p id="nav-links">
                <a href="../Vistas/index.php">Inicio</a>
                <a href="../Vistas/disfraces.php">Disfraces</a>
                <a href="../Vistas/accesorios.php">Accesorios</a>
                <a href="../Vistas/contactos.php">Contactos</a>
                <a href="../Vistas/acerca.php">Acerca de</a>
            </p>
        </section>
    </header> 
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
        </section>
    </main>

    <footer>
        <p><i class="bi bi-geo-alt-fill"></i> Tucumán 355, K4700 San Fernando del Valle de Catamarca, Catamarca</p>
        <p><i class="bi bi-envelope-fill"></i> info@metamorfosis.com</p>
        <p><i class="bi bi-telephone-fill"></i> +54 123 456 789</p>
        <p>&copy; 2024 Metamorfosis. Todos los derechos reservados.</p>
        <section class="social-icons">
            <a href="https://www.instagram.com/disfracesmetamorfosis/"><i class="bi bi-instagram"></i></a>
            <i class="bi bi-twitter-x"></i>
            <i class="bi bi-facebook"></i>
            <i class="bi bi-whatsapp"></i>
        </section>
    </footer>

</body>
</html>