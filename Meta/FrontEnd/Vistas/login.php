<!DOCTYPE html>
<html lang="es">
<head>
    <title>Login</title>
    <link rel="icon" type="image/x-icon" href="./assets/favicon.ico" />
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../Estilos/index.css">
    <link rel="stylesheet" href="../Estilos/login.css">
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
        <a>login</a>
    </section>
    <h1 style="text-align: center;">Iniciar Sesión</h1>
    <section class="wrapper">
        <form action="#" method="post" id="formlogin">
            <fieldset>
                <legend>Iniciar Sesión</legend>
                <section class="input-box">
                    <label for="nombre-correo">Usuario o correo:</label>
                    <input id="nombre-correo" type="text" required>
                </section>
                <section class="input-box">
                    <label for="password">Contraseña:</label>
                    <input id="password" type="password"  required>
                </section>
                <input type="button" value="Login" class="btn">
                <section class="remember-forgot">
                    <label><input type="checkbox">Recordarme</label>
                    <a href="../Vistas/recuperar.php">¿Olvidaste la Contraseña?</a>
                </section>
                <section class="register-link">
                    <p>¿No tenés una cuenta? <a href="../Vistas/registrarse.php">Registrarse</a></p>
                </section>
            </fieldset>

            <section id="mensaje-login" style="text-align:center; color:red; margin-top:10px;"></section>
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



    <script>
        document.querySelector('.btn').addEventListener('click', function () {
            const usuario = document.getElementById('nombre-correo').value.trim();
            const password = document.getElementById('password').value.trim();
            const mensaje = document.getElementById('mensaje-login');

            if (usuario === '' || password === '') {
                mensaje.textContent = 'Por favor, completá todos los campos.';
                mensaje.style.color = 'red';
            } else {
                // Simulación de éxito (esto debería conectarse a un backend real)
                mensaje.textContent = 'Inicio de sesión exitoso.';
                mensaje.style.color = 'green';

                // Podés redirigir o limpiar campos después de unos segundos si querés
                // setTimeout(() => location.href = 'alguna_página.php', 2000);
            }
        });
    </script>

</body>
</html>