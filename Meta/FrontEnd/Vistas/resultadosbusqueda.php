<!DOCTYPE html>
<html lang="es">
<head>
    <title>Disfraces - Metamorfosis</title>
    <link rel="icon" type="image/x-icon" href="./assets/favicon.ico" />
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../Estilos/index.css">
    <link rel="stylesheet" href="../Estilos/modales.css">
</head>
<body>
    <?php include('cabecera.php'); ?>
    <main>
        <section class="nav-route">
            <a href="index.php">Inicio / </a>
            <a>Resultados de Busqueda</a>
        </section>
        <h2 style="color: black; padding-left: 3%; padding-top: 3%;">Resultados de busqueda sobre: Circo</h2>
        <h3 style="color: black; padding-left: 3%;">Total de resultados: 4</h3>

        <section class="cards-container-costume" id="costume-Container">
            <section class="card-costume">
                <img src="../img/Disfraces/niños/circo/niño/mago_3_niños.jpg" class="category-image">
                <h4>Mago</h4>
                <p>Tematica: circo</p>
                <p>Categoria: niño</p>
                <button type="button" class="btn" onclick="openModal('Pirata')">Alquilar</button>
            </section>
            <section class="card-costume">
                <img src="../img/Disfraces/niños/circo/niña/payaso_6_niños.jpg" class="category-image">
                <h4>Payasa</h4>
                <p>Tematica: circo</p>
                <p>Categoria: niña</p>
                <button type="button" class="btn" onclick="openModal('Pirata')">Alquilar</button>
            </section>
            <section class="card-costume">
                <img src="../img/Disfraces/adultos/circo/hombre/domador_1_adultos.jpg" class="category-image">
                <h4>Domador</h4>
                <p>Tematica: circo</p>
                <p>Categoria: hombre</p>
                <button type="button" class="btn" onclick="openModal('Pirata')">Alquilar</button>
            </section>
            <section class="card-costume">
                <img src="../img/Disfraces/adultos/halloween/mujer/payasoasesino_2_adultos.jpg" class="category-image">
                <h4>Payasa</h4>
                <p>Tematica: circo</p>
                <p>Categoria: mujer</p>
                <button type="button" class="btn" onclick="openModal('Pirata')">Alquilar</button>
            </section>


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
        </section>

        <?php include('footer.php');?>

</body>
</html>