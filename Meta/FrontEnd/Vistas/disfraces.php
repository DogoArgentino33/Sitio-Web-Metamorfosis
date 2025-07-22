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
        
<?php

// Número de disfraces por página
$disfracesPorPagina = 25;

// Página actual
$paginaActual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$offset = ($paginaActual - 1) * $disfracesPorPagina;

$sql = "SELECT 
                            p.id,
                            p.nombre,
                            p.tipo,
                            p.unidades_disponibles,
                            p.precio,
                            p.fechamod,
                            p.usumod,
                            GROUP_CONCAT(DISTINCT c.nombre_cat SEPARATOR ', ') AS categorias,
                            GROUP_CONCAT(DISTINCT t.talla SEPARATOR ', ') AS tallas,
                            GROUP_CONCAT(DISTINCT tm.nombre_tema SEPARATOR ', ') AS tematicas,
                            (SELECT ip.img FROM img_producto ip WHERE ip.id_producto = p.id LIMIT 1) AS imagenes
                        FROM producto p
                        LEFT JOIN producto_categoria pc ON pc.id_producto = p.id
                        LEFT JOIN categoria c ON c.id = pc.id_categoria
                        LEFT JOIN producto_talla pt ON pt.id_producto = p.id
                        LEFT JOIN talla t ON t.id = pt.id_talla
                        LEFT JOIN producto_tematica ptem ON ptem.id_producto = p.id
                        LEFT JOIN tematica tm ON tm.id = ptem.id_tematica
                        WHERE p.tipo = 1
                        GROUP BY p.id
                        ORDER BY p.id
                        LIMIT $disfracesPorPagina OFFSET $offset
        ";
$stmt = $conexion->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();

$totalSql = "SELECT COUNT(DISTINCT p.id) AS total FROM producto p WHERE p.tipo = 1";
$totalResult = $conexion->query($totalSql);
$totalRow = $totalResult->fetch_assoc();
$totalDisfraces = $totalRow['total'];
$totalPaginas = ceil($totalDisfraces / $disfracesPorPagina);
?>

<!-- Contenedor único para todas las tarjetas -->
<section class="cards-container-costume" id="costume-Container">
    <?php
    if ($result && $result->num_rows > 0) 
    {
        while ($producto = $result->fetch_assoc()) 
        { ?>
            <a href="detallesdisfraz.php" class="asection" style="text-decoration: none;">
                <section class="card-costume">
                    <?php if (!empty($producto['imagenes'])): ?>
                        <img src="uploads/producto/<?= htmlspecialchars($producto['imagenes']) ?>" alt="Imagen" width="250" height="300" style="object-fit: cover; border-radius: 3%;">
                    <?php else: ?>
                        <span>Sin imagen</span>
                    <?php endif; ?>

                    <h4><?= htmlspecialchars($producto['nombre']) ?></h4>
                    <p>Tematica: <?= htmlspecialchars($producto['tematicas']) ?></p>
                    <p>Categoria: <?= htmlspecialchars($producto['categorias']) ?></p>
                    <p>Precio: <?= htmlspecialchars($producto['precio']) ?></p>
                    <label class="btn" disponible="hoy"> Disponible hoy</label>
                </section>
            </a>
        <?php
        }
    } 
    else 
    {
        echo "<p>No hay disfraces registrados.</p>";
    }
    ?>
</section> <!-- Fin del contenedor flex -->

<section>
    <ul class="pagination">
        <?php if ($paginaActual > 1): ?>
            <li><a href="?pagina=<?= $paginaActual - 1 ?>">&laquo;</a></li>
        <?php endif; ?>

        <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
            <li class="<?= ($i == $paginaActual) ? 'active' : '' ?>">
                <a href="?pagina=<?= $i ?>"><?= $i ?></a>
            </li>
        <?php endfor; ?>

        <?php if ($paginaActual < $totalPaginas): ?>
            <li><a href="?pagina=<?= $paginaActual + 1 ?>">&raquo;</a></li>
        <?php endif; ?>
    </ul>
</section>

<!-- Contenedor de los disfraces -->
   <!--  <section class="cards-container-costume" id="costume-Container">
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
      -->

<?php include('footer.php');?>

</body>
</html>

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