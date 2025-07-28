<!DOCTYPE html>
<html lang="es">
<head>
    <title>Disfraces - Metamorfosis</title>
    <link rel="icon" type="image/x-icon" href="./assets/favicon.ico" />
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../Estilos/index.css">
    <link rel="stylesheet" href="../Estilos/disfraces.css">
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

        $pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
        $limite = 2;
        $offset = ($pagina - 1) * $limite;

        if (!empty($termino)) {
            $termino_like = "%" . $termino . "%";

            // Contar total de resultados para la búsqueda
            $consulta_total = $conexion->prepare("
                SELECT COUNT(DISTINCT p.id) as total
                FROM producto p
                LEFT JOIN producto_categoria pc ON pc.id_producto = p.id
                LEFT JOIN categoria c ON c.id = pc.id_categoria
                LEFT JOIN producto_talla pt ON pt.id_producto = p.id
                LEFT JOIN talla t ON t.id = pt.id_talla
                LEFT JOIN producto_tematica ptem ON ptem.id_producto = p.id
                LEFT JOIN tematica tm ON tm.id = ptem.id_tematica
                WHERE p.nombre LIKE ? OR c.nombre_cat LIKE ? OR t.talla LIKE ? OR tm.nombre_tema LIKE ?
            ");

            $consulta_total->bind_param("ssss", $termino_like, $termino_like, $termino_like, $termino_like);
            $consulta_total->execute();
            $resultado_total = $consulta_total->get_result();
            $total_filas = $resultado_total->fetch_assoc()['total'];
            $consulta_total->close();

            $total_paginas = ceil($total_filas / $limite);

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
                LIMIT ? OFFSET ?
            ");
            $stmt->bind_param("ssssii", $termino_like, $termino_like, $termino_like, $termino_like, $limite, $offset);
            $stmt->execute();
            $resultado = $stmt->get_result();
            $resultados = $resultado->fetch_all(MYSQLI_ASSOC);
            $stmt->close();

        }
        ?>

        <h2 style="color: black; padding-left: 3%; padding-top: 3%;">Resultados de búsqueda sobre: <?= htmlspecialchars($termino) ?></h2>
        <h3 style="color: black; padding-left: 3%;">Total de resultados: <?= $total_filas ?></h3>


        <section class="cards-container-costume" id="costume-Container">
            <?php if (count($resultados) > 0): ?>
                <?php foreach ($resultados as $fila): ?>
                    <section class="card-costume">
                        <img src="uploads/producto/<?= htmlspecialchars($fila['imagen']) ?>" class="category-image" width="250" height="300" style="object-fit: cover; border-radius: 3%;">
                        <h4><?= htmlspecialchars($fila['nombre']) ?></h4>
                        <p>Temática: <?= htmlspecialchars($fila['tematicas']) ?></p>
                        <p>Categoría: <?= htmlspecialchars($fila['categorias']) ?></p>
                        <a href="detallesproducto.php?id=<?= $fila['id'] ?>&tipo=<?= $fila['tipo'] ?>" class="btn" style="text-decoration: none;">Alquilar</a>
                    </section>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="color: black; padding-left: 3%;">No se encontraron resultados para "<?= htmlspecialchars($termino) ?>".</p>
            <?php endif; ?>
        </section>

        <?php if ($total_paginas > 1): ?>
        <section>
            <ul class="pagination">
                <?php if ($pagina > 1): ?>
                    <li><a href="?busqueda=<?= urlencode($termino) ?>&pagina=<?= $pagina - 1 ?>">&laquo;</a></li>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                    <li class="<?= $i == $pagina ? 'active' : '' ?>">
                        <a href="?busqueda=<?= urlencode($termino) ?>&pagina=<?= $i ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>

                <?php if ($pagina < $total_paginas): ?>
                    <li><a href="?busqueda=<?= urlencode($termino) ?>&pagina=<?= $pagina + 1 ?>">&raquo;</a></li>
                <?php endif; ?>
            </ul>
        </section>
        <?php endif; ?>

        <?php include('footer.php');?>

</body>
</html>