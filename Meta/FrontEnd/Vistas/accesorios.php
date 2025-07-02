<!DOCTYPE html>
<html lang="es">
<head>
    
    <title>Disfraces - Metamorfosis</title>
    <link rel="icon" type="image/x-icon" href="./assets/favicon.ico" />
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../Estilos/index.css">
    <link rel="stylesheet" href="../Estilos/accesorios.css">

     <!-- Script de SweetAlert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <?php include('cabecera.php'); ?>
    <main>
        <section class="nav-route">
            <a href="index.php">Inicio / </a>
            <a>Accesorios</a>
        </section>
       
        <section class="searchSection">
            <h2>¡Accesorios!</h2>
            <input type="text" id="searchInput" placeholder="Buscar accesorios..." onkeyup="filterAccesorios()">
            <button id="helpInput"><i class="bi bi-question"></i></button>
        </section>

        <section class="cards-container-accessory" id="accessory-Container">
            <a href="detallesaccesorios.php" class="asection" style="text-decoration: none">
                <section class="card-accessory">
                    <img src="../img/Accesorios/Historico/espada_pirata_1.jpg" class="category-image">
                    <h4> Espada Pirata</h4>
                    <p>Tematica: historia</p>
                    <p>Categoria: niño</p>
                    <label class="btn">Disponible hoy</label>
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

</body>
</html>

<script>
    function filterAccesorios() {
        const input = document.getElementById('searchInput');
        const filter = input.value.toLowerCase();
        const container = document.getElementById('accessory-Container');
        const cards = container.getElementsByClassName('card-accessory');

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