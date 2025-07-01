<?php include('conexion.php'); ?>

<!-- Aqui inicia HTML-->
<!DOCTYPE html>
<html lang="es">

<!-- cabeza -->
<head>
    <title>Disfraces - Metamorfosis</title>
    <link rel="icon" type="image/x-icon" href="./assets/favicon.ico" />
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../Estilos/index.css">
    <link rel="stylesheet" href="../Estilos/disfraces.css">
    <link rel="stylesheet" href="../Estilos/modales.css">

    <!-- Script de SweetAlert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
</head>

<!-- Cuerpo de la pagina -->
<body>

    <!-- Cabecera - nav -->
    <?php include('cabecera.php'); ?>

<main>
    
    <!-- Título -->
    <section class="nav-route">
        <a href="index.php">Inicio / </a>
        <a>Disfraces</a>
    </section>

    <!-- Buscador -->
        <section class="searchSection">
            <h2>¡Disfraces!</h2>
            <input type="text" id="searchInput" placeholder="Buscar disfraces..." onkeyup="filterDisfraces()">
            <button id="helpInput"><i class="bi bi-question"></i></button>
        </section>
        
    <!-- Contenedor de los disfraces -->
        <section class="cards-container-costume" id="costume-Container">
            <a href="detallesdisfraz.php" class="asection" style="text-decoration: none;">

                <section class="card-costume">
                    <img src="../img/Disfraces/niños/historico/niño/pirata_1_niños.jpeg" class="category-image">
                    <h4>Pirata</h4>
                    <p>Tematica: historia</p>
                    <p>Categoria: niño</p>
                    <label class="btn" disponible="hoy">Disponible hoy</label>
                </section>
            </a>
        </section>
    </main>
    
<section>
    <ul class="pagination">
        <li><a href="#">&laquo; </a></li>
        <li class="active"><a href="#">1</a></li>
        <li><a href="#">2</a></li>
        <li><a href="#">3</a></li>
        <li><a href="#">...</a></li>  
        <li><a href="#"> &raquo;</a></li>
    </ul>
</section>

<?php include('footer.php');?>

<script>
    function filterDisfraces() {
        const input = document.getElementById('searchInput');
        const filter = input.value.toLowerCase();
        const container = document.getElementById('costume-Container');
        const cards = container.getElementsByClassName('card-costume');

        for (let i = 0; i < cards.length; i++) {
            const title = cards[i].getElementsByTagName('h4')[0].innerText.toLowerCase();
            const category = cards[i].getElementsByTagName('p')[1].innerText.toLowerCase();
            const theme = cards[i].getElementsByTagName('p')[0].innerText.toLowerCase();

            if (title.includes(filter) || category.includes(filter) || theme.includes(filter)) {
                cards[i].style.display = "";
            } else {
                cards[i].style.display = "none";
            }
        }
    }
</script>

<!-- Script de Help -->
<script>
    document.addEventListener('DOMContentLoaded', () => 
    {
        const helpInput = document.querySelector("#helpInput");

        helpInput.addEventListener("click",()=>
        {
            Swal.fire
            ({
                title: 'Sobre barra de búsqueda',
                text: 'Puedes filtrar por nombre, tematica o categoria',
                icon: 'info',
                confirmButtonText: 'Ok'
            });
        })
    });
</script>
</body>
</html>