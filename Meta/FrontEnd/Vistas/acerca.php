<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <title>Acerca de - Metamorfosis</title>
    <link rel="icon" type="image/x-icon" href="./assets/favicon.ico" />
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../Estilos/acerca.css">
    <link rel="stylesheet" href="../Estilos/index.css">
    <link rel="stylesheet" href="../Estilos/modales.css">
</head>
<body>
    <?php include('cabecera.php'); ?>

    <main>
        <section class="nav-route">
            <a href="index.php">Inicio / </a>
            <a>Acerca de</a>
        </section>
        <h2 class="h2">Acerca de Metamorfosis</h2>
        <section class="about-info"> 
            <p>En Metamorfosis nos dedicamos a ofrecerte la mejor experiencia en el alquiler de disfraces y accesorios. Nuestro objetivo es brindar productos de alta calidad que te permitan disfrutar de cada ocasión al máximo.</p>
            <p>Desde nuestra fundación, hemos crecido y evolucionado para ofrecerte una amplia variedad de disfraces, desde los más clásicos hasta los más modernos y creativos.</p>
            <p>Estamos comprometidos con la satisfacción de nuestros clientes y trabajamos arduamente para garantizar que cada detalle sea perfecto. ¡Únete a la diversión y transforma tus momentos especiales con nosotros!</p>

            <section style="text-align: center;">
                <img src="../img/img-gerente.jpg" width="50%" height="40%" style="border-radius: 3%;">
            </section>
            
            <?php if(isset($_SESSION['rol']) and $_SESSION['rol'] == 1):
            {
                echo '<input type="button" value="Editar Descripcion" class="action-button" onclick="openModalShow()">';
            }
            endif?>
        </section>
    </main>

    <?php include('footer.php');?>

    <script>
        ///VER///
        function openModalShow(costumeName) {
            document.getElementById('modal-show').style.display = 'block';
        }
    
        function closeModalShow() {
            document.getElementById('modal-show').style.display = 'none';
        }
    </script>
</body>
</html>
