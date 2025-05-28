<!DOCTYPE html>
<html lang="en">
<head>
    <title>Recuperar Contraseña</title>
    <link rel="icon" type="image/x-icon" href="./assets/favicon.ico" />
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../Estilos/recuperar.css">
    <link rel="stylesheet" href="../Estilos/index.css">
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
        <a href="login.php">Login / </a>
        <a>Recuperar</a>
    </section>
    <h1 style="text-align: center;">Recuperar Contraseña</h1>
    <section class="wrapper" style="display: flex; justify-content: center; align-items: center;">
        <form name="form-ia" method="get" action="VistaContraseñaForgot.php" style="text-align: left; max-width: 600px; margin: 0 auto;" class="form-recover">
            <fieldset style="border: 1px solid #ddd; padding: 10px;">
                <legend>Recuperar Contraseña</legend>
                <section class="input-box">
                    <input type="text" placeholder="Nombre de Usuario" required>
                </section>
                <section class="input-box">
                    <input type="text" placeholder="Correo Electrónico" required>
                </section>
                <br>
                <section>
                    <label>Enviar código de recuperación</label>
                    <button type="button" class="btn-send">Enviar Código</button>
                </section>
                <br>
                <section class="input-box">
                    <input type="password" placeholder="Código de recuperación" required>
                </section>
                <br>
                <button type="submit" class="button">Confirmar Código</button>
                <section class="Login-link"></section>
                <p><a href="../Vistas/login.php">Volver a Login</a></p>
            </fieldset>
        </form>
    </section>

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
