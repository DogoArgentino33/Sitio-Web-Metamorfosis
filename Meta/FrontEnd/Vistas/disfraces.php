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
            LEFT JOIN categoria c ON c.id_producto = p.id
            LEFT JOIN talla t ON t.id_producto = p.id
            LEFT JOIN tematica tm ON tm.id_producto = p.id
            WHERE p.tipo = 1
            GROUP BY p.id
            ORDER BY p.id;
        ";
$stmt = $conexion->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();


if ($result && $result->num_rows > 0) 
{
    while ($producto = $result->fetch_assoc()) 
    { ?>
        <section class="cards-container-costume" id="costume-Container">
            <a href="detallesdisfraz.php" class="asection" style="text-decoration: none;">

                <section class="card-costume">
                    <?php if (!empty($producto['imagenes'])): ?>
                        <img src="uploads/producto/<?= htmlspecialchars($producto['imagenes']) ?>" alt="Imagen" width="60" height="60" style="object-fit: cover; border-radius: 50%;">
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
        </section>
    <?php
    }
} 
else 
{
    ?>
    <tr>
    <td colspan="15">No hay disfraces registrados</td>
    </tr>
<?php
}
?>

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