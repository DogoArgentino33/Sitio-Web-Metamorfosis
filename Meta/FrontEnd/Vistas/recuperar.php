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
    <?php include('cabecera.php'); ?>
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

    <?php include('footer.php');?>

</body>
</html>
