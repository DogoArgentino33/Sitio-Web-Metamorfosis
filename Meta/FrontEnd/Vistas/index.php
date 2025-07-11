<!-- Inicio del HTML -->
<!DOCTYPE html>
<html lang="es">
<head>
    <title>Alquiler de Disfraces - Metamorfosis</title>
    <link rel="icon" type="image/x-icon" href="./assets/favicon.ico" />
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../Estilos/index.css">

    <!-- Script de SweetAlert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <?php include('cabecera.php'); ?>
    
    <main>
         <!--Nav-->
        <section class="nav-route">
            <a>Inicio /</a>
        </section>

        <!-- Texto Principal -->
        <h2 style="text-align: center;">¡Bienvenido a Metamorfosis - Alquiler de disfraces y accesorios!</h2>

        <!-- Aqui inicia el Slider -->
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
                <button class="control-button" id="botonSliderPrev"><i class="bi bi-arrow-left-circle"></i></button>
                <button class="control-button" id="botonSliderNext"><i class="bi bi-arrow-right-circle"></i></button>
            </section>
        </section>

        <!-- Aqui inicia el catalogo -->
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
    
    <?php include('footer.php');?>
</body>
</html>

    <!-- Funcion del slider -->
<script>
document.addEventListener('DOMContentLoaded', () => 
{
    let currentSlide = 0;
    const slides = document.querySelector('.slides');
    const intervalMs   = 5000; //Esto equivale a 5 segunfos
    let timerId        = null;
    movimiento();  //Iniciamos el proceso

    // Iniciamos los botones  //
    document.getElementById('botonSliderPrev').addEventListener('click', () => 
    {
        CambioSlide(-1);
        ReiniciarMov();
    });

    document.getElementById('botonSliderNext').addEventListener('click', () => 
    {
        CambioSlide(1);
        ReiniciarMov();
    });

    // Agregamos el cambio de imagen //
    function CambioSlide(direction = 1) 
    { 
        const totalSlides = document.querySelectorAll('.slide').length;
        currentSlide = (currentSlide + direction + totalSlides) % totalSlides;
        slides.style.transform = `translateX(-${currentSlide * 100}%)`;
    }

    // Agregamos movimiento //
    function movimiento() 
    {
        if (timerId) 
        {
            return;
        }                        
            timerId = setInterval(() => CambioSlide(1), intervalMs);
    }

    function pararmovimiento() 
    {
        clearInterval(timerId);
        timerId = null;
    }
});
</script>

<!-- Función de Login alert - Continuación - -->
<script>
document.addEventListener('DOMContentLoaded', () => 
{
  //1. Traemos lo que definimos en login.php
  const p = new URLSearchParams(location.search);

  //2. Como lo definimos como "ok", procede a mostrar el mensaje
  if (p.get('login') === 'ok') 
  {
    Swal.fire({
      position: 'top',
      icon: 'success',
      title: 'Sesión iniciada con éxito',
      showConfirmButton: false,
      timer: 1500
    });

  //3. Al refrescar la página, no volverá a salir el mensaje
    history.replaceState({}, '', location.pathname);
  }
});
</script>