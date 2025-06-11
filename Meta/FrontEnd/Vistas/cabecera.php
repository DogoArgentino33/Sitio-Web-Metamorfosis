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
                    <i class="bi bi-box-arrow-right"></i> <!-- Cambiá el ícono si querés -->
                </a>
            <?php else: ?>
                <!-- Si NO está logueado -->
                <a href="login.php" title="Iniciar sesión">
                    <i class="bi bi-person-circle"></i>
                </a>
            <?php endif; ?>

            <!-- Estos son accesos fijos -->
            <a href="gerente.php"><i class="bi bi-gear-fill"></i></a>
            <a href="empleado.php"><i class="bi bi-pencil-square"></i></a>
            <a href="administrador.php"><i class="bi bi-pc-display"></i></a>
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
</header>