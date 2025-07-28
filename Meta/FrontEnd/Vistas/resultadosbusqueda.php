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
        <?php
        include("conexion.php"); // Asegurate de tener esto arriba

        $termino = $_GET['busqueda'] ?? '';
        $resultados = [];

        if (!empty($termino)) {
            $termino_like = "%" . $termino . "%";

            $stmt = $conexion->prepare("
                SELECT 
                    p.id,
                    p.nombre,
                    p.tipo,
                    p.unidades_disponibles,
                    p.precio,
                    GROUP_CONCAT(DISTINCT c.nombre_cat SEPARATOR ', ') AS categorias,
                    GROUP_CONCAT(DISTINCT t.talla SEPARATOR ', ') AS tallas,
                    GROUP_CONCAT(DISTINCT tm.nombre_tema SEPARATOR ', ') AS tematicas,
                    (SELECT ip.img FROM img_producto ip WHERE ip.id_producto = p.id LIMIT 1) AS imagen
                FROM producto p
                LEFT JOIN producto_categoria pc ON pc.id_producto = p.id
                LEFT JOIN categoria c ON c.id = pc.id_categoria
                LEFT JOIN producto_talla pt ON pt.id_producto = p.id
                LEFT JOIN talla t ON t.id = pt.id_talla
                LEFT JOIN producto_tematica ptem ON ptem.id_producto = p.id
                LEFT JOIN tematica tm ON tm.id = ptem.id_tematica
                WHERE p.nombre LIKE ? OR c.nombre_cat LIKE ? OR t.talla LIKE ? OR tm.nombre_tema LIKE ?
                GROUP BY p.id
            ");
            $stmt->bind_param("ssss", $termino_like, $termino_like, $termino_like, $termino_like);
            $stmt->execute();
            $resultado = $stmt->get_result();
            $resultados = $resultado->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
        }
        ?>

        <h2 style="color: black; padding-left: 3%; padding-top: 3%;">Resultados de búsqueda sobre: <?= htmlspecialchars($termino) ?></h2>
        <h3 style="color: black; padding-left: 3%;">Total de resultados: <?= count($resultados) ?></h3>

        <section class="cards-container-costume" id="costume-Container">
            <?php if (count($resultados) > 0): ?>
                <?php foreach ($resultados as $fila): ?>
                    <section class="card-costume">
                        <img src="uploads/producto/<?= htmlspecialchars($fila['imagen']) ?>" class="category-image" width="250" height="300" style="object-fit: cover; border-radius: 3%;">
                        <h4><?= htmlspecialchars($fila['nombre']) ?></h4>
                        <p>Temática: <?= htmlspecialchars($fila['tematicas']) ?></p>
                        <p>Categoría: <?= htmlspecialchars($fila['categorias']) ?></p>
                        <button type="button" class="btn" onclick="openModal('<?= htmlspecialchars($fila['nombre']) ?>')">Alquilar</button>
                    </section>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="color: black; padding-left: 3%;">No se encontraron resultados para "<?= htmlspecialchars($termino) ?>".</p>
            <?php endif; ?>
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