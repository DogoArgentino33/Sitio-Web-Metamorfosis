<!DOCTYPE html>
<html lang="es">
<head>
    <title>Disfraces - Metamorfosis</title>
    <link rel="icon" type="image/x-icon" href="./assets/favicon.ico" />
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../Estilos/index.css">
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
            <h4>Puedes filtrar nuestros accesorios por su nombre, tematica o categoria</h4>
        </section>

        <section class="cards-container-accessory" id="accessory-Container">
            <a href="detallesaccesorios.php" class="asection">
                <section class="card-accessory">
                    <img src="../img/Accesorios/Historico/espada_pirata_1.jpg" class="category-image">
                    <h4> Espada Pirata</h4>
                    <p>Tematica: historia</p>
                    <p>Categoria: niño</p>
                    <label class="btn">Disponible hoy</label>
                </section>
            </a>

            <section class="card-accessory">
                <img src="../img/Accesorios/Fantasia/alas_mariposa_1.jpg.jpg" class="category-image">
                <h4>Alas de mariposa</h4>
                <p>Tematica: fantasia</p>
                <p>Categoria: niña</p>
                <label class="btn">Disponible hoy</label>

            </section>
            <section class="card-accessory">
                <img src="../img/Accesorios/Argentina/sablesanmartin_1.png" class="category-image">
                <h4>Sable de Granadero</h4>
                <p>Tematica: folcklore argentino</p>
                <p>Categoria: niño</p>
                <label class="btn">Disponible hoy</label>
 
            </section>
            <section class="card-accessory">
                <img src="../img/Accesorios/Fiesta/corbatalunares_1.jpg" class="category-image">
                <h4>Corbata de lunares</h4>
                <p>Tematica: circo</p>
                <p>Categoria: niño</p>
                <label class="btn">Disponible hoy</label>
            </section>
            <section class="card-accessory">
                <img src="../img/Accesorios/Halloween/mascara_3_halloween.jpg" class="category-image">
                <h4>Mascara de bruja 01</h4>
                <p>Tematica: halloween</p>
                <p>Categoria: niña</p>
                <label class="btn">Disponible hoy</label>
            </section>


            <section class="card-accessory">
                <img src="../img/Accesorios/Fiesta/moñolunares_1.jpg" class="category-image">
                <h4>Moño de lunares</h4>
                <p>Tematica: circo</p>
                <p>Categoria: niño</p>
                <label class="btn">Disponible hoy</label>
            </section>
            <section class="card-accessory">
                <img src="../img/Accesorios/Oficios/hacha_1.jpg" class="category-image">
                <h4>Hacha de bombero</h4>
                <p>Tematica: oficio</p>
                <p>Categoria: niño</p>
                <label class="btn">Disponible hoy</label>
            </section>
            <section class="card-accessory">
                <img src="../img/Accesorios/Historico/bastonreina_1.jpg" class="category-image">
                <h4>Baston de reina</h4>
                <p>Tematica: historia</p>
                <p>Categoria: niña</p>
                <label class="btn">Disponible hoy</label>
            </section>
            <section class="card-accessory">
                <img src="../img/Accesorios/Navidad/gorropapanoel_1.jpg" class="category-image">
                <h4>Gorro de Papa Noel</h4>
                <p>Tematica: navidad</p>
                <p>Categoria: niño</p>
                <label class="btn">Disponible hoy</label>
            </section>
            <section class="card-accessory">
                <img src="../img/Accesorios/Argentina/bastoncurvonegro_1.jpg" class="category-image">
                <h4>Baston de caballero</h4>
                <p>Tematica: folcklore argentino</p>
                <p>Categoria: niño</p>
                <label class="btn">Disponible hoy</label>
            </section>

            
            <section class="card-accessory">
                <img src="../img/Accesorios/Fantasia/alas_hada_2.jpg" class="category-image">
                <h4>Alas de hada</h4>
                <p>Tematica: fantasia</p>
                <p>Categoria: niña</p>
                <label class="btn">Disponible hoy</label>
            </section>
            <section class="card-accessory">
                <img src="../img/Accesorios/Halloween/escobabruja_1.jpg" class="category-image">
                <h4>Escoba de bruja</h4>
                <p>Tematica: halloween</p>
                <p>Categoria: niña</p>
                <label class="btn">Disponible desde 30/12</label>
            </section>
            <section class="card-accessory">
                <img src="../img/Accesorios/Oficios/casco_bombero_1.jpg" class="category-image">
                <h4>Casco de bombero</h4>
                <p>Tematica: oficio</p>
                <p>Categoria: niño</p>
                <label class="btn">Disponible hoy</label>
            </section>
            <section class="card-accessory">
                <img src="../img/Accesorios/Fiesta/abanicoplumas_1.jpg" class="category-image">
                <h4>Abanico de plumas</h4>
                <p>Tematica: fantasia</p>
                <p>Categoria: niña</p>
                <label class="btn">Disponible hoy</label>
            </section>
            <section class="card-accessory">
                <img src="../img/Accesorios/SanPatricio/galerairlandesa_1.jpg" class="category-image">
                <h4>Gorro de San Patricio</h4>
                <p>Tematica: fantasia</p>
                <p>Categoria: niño</p>
                <label class="btn">Disponible hoy</label>
            </section>


            <section class="card-accessory">
                <img src="../img/Accesorios/Religion/alas_angel_1.jpg" class="category-image">
                <h4>Alas de angel</h4>
                <p>Tematica: fantasia</p>
                <p>Categoria: niño</p>
                <label class="btn">Disponible hoy</label>
            </section>
            <section class="card-accessory">
                <img src="../img/Accesorios/Halloween/mascara_1_halloween.jpg" class="category-image">
                <h4>Mascara de bruja 02</h4>
                <p>Tematica: halloween</p>
                <p>Categoria: niña</p>
                <label class="btn">Disponible hoy</label>
            </section>
            <section class="card-accessory">
                <img src="../img/Accesorios/Halloween/calderobruja_1.jpg" class="category-image">
                <h4>Caldera de bruja</h4>
                <p>Tematica: halloween</p>
                <p>Categoria: niña</p>
                <label class="btn">Disponible hoy</label>
            </section>
            <section class="card-accessory">
                <img src="../img/Accesorios/Oficios/varitamago_1.jpg" class="category-image">
                <h4>Varita magica</h4>
                <p>Tematica: fantasia</p>
                <p>Categoria: niño</p>
                <label class="btn">Disponible hoy</label>
            </section>
            <section class="card-accessory">
                <img src="../img/Accesorios/Oficios/sombreropolicia_1.png" class="category-image">
                <h4>Gorro de Gorra</h4>
                <p>Tematica: oficio</p>
                <p>Categoria: niño</p>
                <label class="btn">Disponible hoy</label>
            </section>

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

</body>
</html>