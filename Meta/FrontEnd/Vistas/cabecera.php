<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

?>

<header>
    <section class="logo-container">
        <h1>Metamorfosis</h1>

        <form action="resultadosbusqueda.php" class="formcentrado">
        <input type="text" id="Idinputtextbuscar" placeholder="Buscar">
        </form>

        <section class="container-login-cart">
            <?php if (isset($_SESSION['id'])): ?>
                <!-- Si el usuario está logueado -->
                <a href="logout.php" title="Cerrar sesión">
                    <i class="bi bi-box-arrow-right"></i> <!-- Icono -->
                </a>
            <?php else: ?> <!-- caso contrario -->
                <a href="login.php" title="Iniciar sesión">
                    <i class="bi bi-person-circle"></i>
                </a>
            <?php endif; ?>

             <?php if (isset($_SESSION['rol']) and $_SESSION['rol'] == 2): ?>
                <!-- Para empleado -->
                <a href="empleado.php" title="Panel empleado">
                    <i class="bi bi-pencil-square"></i>
                </a>
                <?php endif; ?>

                <?php if (isset($_SESSION['rol']) and $_SESSION['rol'] == 1): ?>
                <!-- Para Gerente -->
                <a href="gerente.php" title="Panel gerente">
                    <i class="bi bi-gear-fill"></i>
                </a>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['rol']) and $_SESSION['rol'] == 4): ?>
                <!-- Para administrador -->
                <a href="gerente.php" title="Panel gerente">
                    <i class="bi bi-gear-fill"></i>
                </a>

                <a href="empleado.php" title="Panel empleado">
                    <i class="bi bi-pencil-square"></i>
                </a>

                <a href="administrador.php" title="Panel administrador">
                    <i class="bi bi-pc-display"></i>
                </a>
                <?php endif; ?>
                
        </section>
    </section>

    <br>
    <section class="container-nav">
        <p id="nav-links">
            <a href="index.php">Inicio</a>
            <a href="disfraces.php">Disfraces</a>
            <a href="accesorios.php">Accesorios</a>
            <a href="contactos.php">Contactos</a>
            <a href="acerca.php">Acerca de</a>
        </p>
    </section>

    <!-- mensaje de bievenida -->
    <?php if (isset($_SESSION['id'])):?>
            <h2>Bienvenido <?php  echo $_SESSION['nom_usu']?> !!</h2>
    <?php endif; ?>

</header>