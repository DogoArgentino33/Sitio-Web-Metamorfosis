<!DOCTYPE html>
<html lang="es">
<head>
    <title>Alquiler de Disfraces - Metamorfosis</title>
    <link rel="icon" type="image/x-icon" href="./assets/favicon.ico" />
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
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
    
    <main> 
        <section class="nav-route">
            <a>Inicio /</a>
        </section>
        <h2 style="text-align: center;">¡Bienvenido a Metamorfosis - Alquiler de disfraces y accesorios!</h2>

        <section class="slider">
            <section class="slides">
                <section class="slide">
                    <img src="../img/slider 1.jpg" alt="Promoción 1">
                </section>
                <section class="slide">
                    <img src="../img/slider 2.jpg" alt="Promoción 2">
                </section>
                <section class="slide">
                    <img src="../img/slider 3.jpg" alt="Promoción 3">
                </section>
            </section>
            <section class="slider-controls">
                <button class="control-button" id="btnSlider"  onclick="changeSlide(-1)"><i class="bi bi-arrow-left-circle"></i></button>
                <button class="control-button" id="btnSlider" onclick="changeSlide(1)"><i class="bi bi-arrow-right-circle"></i></button>
            </section>
        </section>

        <h2 style="text-align: center;">Dale un vistazo a nuestro catalogo</h2>
        
        <section class="cards-container">
            <section class="card">
                <img src="../img/pexels-mikhail-nilov-9147251.jpg" alt="Disfraz Niños" class="category-image-index">
                <h4>Niños</h4>
                <p>Encuentra disfraces creativos y cómodos para niños de todas las edades. Desde superhéroes hasta personajes de cuentos, nuestros disfraces garantizan diversión y comodidad durante todo el día.</p>
                <a href="../Vistas/disfraces.php"><button type="button" class="btn">Ver más</button></a>
            </section>
            <section class="card">
                <img src="../img/pexels-leish-5600428.jpg" alt="Disfraz Adultos" class="category-image-index">
                <h4>Adultos</h4>
                <p>Explora nuestra colección de disfraces para adultos, ideales para cualquier ocasión, ya sea una fiesta de disfraces, un evento temático o una celebración especial.</p>
                <a href="../Vistas/disfraces.php"><button type="button" class="btn">Ver más</button></a>
            </section>
            <section class="card">
                <img src="../img/pexels-aleksmagnusson-3071456.jpg" alt="Disfraz Temáticas" class="category-image-index">
                <h4>Accesorios</h4>
                <p>Sumérgete en temáticas emocionantes con nuestros accesorios temáticos. Desde épocas históricas hasta fantasías futuristas, encuentra el accesorio perfecto para destacar en cualquier evento.</p>
                <a href="../Vistas/accesorios.php"><button type="button" class="btn">Ver más</button></a>
            </section>
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

    <script>
        let currentSlide = 0;
        const slides = document.querySelector('.slides');

        function changeSlide(direction) {
            const totalSlides = document.querySelectorAll('.slide').length;
            currentSlide = (currentSlide + direction + totalSlides) % totalSlides;
            slides.style.transform = `translateX(-${currentSlide * 100}%)`;
        }
    </script>

</body>
</html>
