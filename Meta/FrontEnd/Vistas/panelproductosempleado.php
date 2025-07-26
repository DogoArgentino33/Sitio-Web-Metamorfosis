<?php include('auth.php'); include('conexion.php'); // Ajusta la ruta si es necesario ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <title>Alquiler de Disfraces - Metamorfosis - Panel Productos</title>
    <link rel="icon" type="image/x-icon" href="./assets/favicon.ico" />
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../Estilos/index.css">
    <link rel="stylesheet" href="../Estilos/panelgeneral.css">
     <!-- Script de SweetAlert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <?php include('cabecera.php'); ?>
    <main>
        <section class="nav-route">
            <a href="index.php">Inicio / </a>
            <a href="gerente.php">Gerente /</a>
            <a>Panel de Productos</a>
        </section>
        <h1><a href="../Vistas/gerente.php" style="padding-right: 3%;" title="volver"><i class="bi bi-arrow-left-circle"></i></a>Panel de Productos</h1>
        
        <section class="container-table" id="product">
            <section class="nav-table">
                <input type="text" id="search-panel" placeholder="Buscar Productos..." onkeyup="filtrarTabla('product')">
            </section>
            <table>
                <thead>
                    <tr>
                        <th>IMAGEN</th>
                        <th>NOMBRE</th>
                        <th>TIPO</th>
                        <th>CATEGORÍAS</th>
                        <th>TALLAS</th>
                        <th>TEMÁTICAS</th>
                        <th>UNIDADES DISPONIBLES</th>
                        <th>PRECIO</th>
                        <th>VER</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                    $por_pagina = 10;
                    $pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
                    $inicio = ($pagina > 1) ? ($pagina * $por_pagina) - $por_pagina : 0;

                    $total_sql = "SELECT COUNT(DISTINCT p.id) AS total FROM producto p";
                    $total_stmt = $conexion->prepare($total_sql);
                    $total_stmt->execute();
                    $total_resultado = $total_stmt->get_result()->fetch_assoc();
                    $total_registros = $total_resultado['total'];
                    $total_paginas = ceil($total_registros / $por_pagina);

                    $sql = "
                        SELECT 
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
                        GROUP BY p.id
                        ORDER BY p.id
                        LIMIT ?, ?;
                        ";

                    $stmt = $conexion->prepare($sql);
                    $stmt->bind_param("ii", $inicio, $por_pagina);

                    if (!$stmt) {
                        die("Error en prepare: " . $conexion->error);
                    }

                    $stmt->execute();
                    $result = $stmt->get_result();


                    if ($result && $result->num_rows > 0) {
                        while ($producto = $result->fetch_assoc()) {
                            ?>
                            <tr>
                                <td>
                                    <?php if (!empty($producto['imagenes'])): ?>
                                        <img src="uploads/producto/<?= htmlspecialchars($producto['imagenes']) ?>" alt="Imagen" width="60" height="60" style="object-fit: cover; border-radius: 50%;">
                                    <?php else: ?>
                                        <span>Sin imagen</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($producto['nombre']) ?></td>
                                <?php 
                                    if($producto['tipo'] == 1){
                                        ?><td><?= htmlspecialchars('Disfraz') ?></td>
                                    <?php
                                    }
                                    else{
                                        if($producto['tipo'] == 2){
                                            ?><td><?= htmlspecialchars('Accesorio') ?></td>
                                        <?php
                                        }
                                    }
                                ?>
                                <td><?= htmlspecialchars($producto['categorias']) ?></td>
                                <td><?= htmlspecialchars($producto['tallas']) ?></td>
                                <td><?= htmlspecialchars($producto['tematicas']) ?></td>
                                <td><?= htmlspecialchars($producto['unidades_disponibles']) ?></td>
                                <td><?= htmlspecialchars($producto['precio']) ?></td>

                                <td><a href="verproducto.php?id=<?= $producto['id'] ?>"><button class="ver-btn" title="Ver"><i class="bi bi-eye"></i></button></a></td>
                                
                            </tr>
                            <?php
                        }
                    } else {
                        ?>
                        <tr>
                            <td colspan="15">No hay productos registrados</td>
                        </tr>
                        <?php
                    }
                ?>
                </tbody>
            </table>
        </section>
    </main>

    <section>
        <ul class="pagination">
            <?php if ($pagina > 1): ?>
                <li><a href="?pagina=<?= $pagina - 1 ?>">&laquo;</a></li>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                <li <?= ($i == $pagina) ? 'class="active"' : '' ?>>
                    <a href="?pagina=<?= $i ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>

            <?php if ($pagina < $total_paginas): ?>
                <li><a href="?pagina=<?= $pagina + 1 ?>">&raquo;</a></li>
            <?php endif; ?>
        </ul>
    </section>

    <?php include('footer.php');?>

    <script>
        function filtrarTabla(tipo) {
            let input;
            let table;
            let tr;
            let td;
            let i, j;
            let txtValue;

            if (tipo === 'product') {
                input = document.getElementById('search-panel');
                table = document.querySelector('#product table');
            }

            const filter = input.value.toUpperCase();
            tr = table.getElementsByTagName("tr");

            for (i = 1; i < tr.length; i++) { 
                tr[i].style.display = "none"; 
                td = tr[i].getElementsByTagName("td");
                for (j = 0; j < td.length; j++) {
                    if (td[j]) {
                        txtValue = td[j].textContent || td[j].innerText;
                        if (txtValue.toUpperCase().indexOf(filter) > -1) {
                            tr[i].style.display = "";
                            break;
                        }
                    }
                }
            }
        }
    </script>
</body>
    

<script>
document.addEventListener('DOMContentLoaded', () => {
    const tipoSelect = document.getElementById('tipo');
    const tallaSelect = document.getElementById('talla');

    function toggleTalla() {
        if (tipoSelect.value === '2') { // 2 = Accesorio
            tallaSelect.disabled = true;
            tallaSelect.value = ''; // Limpiar selección si estaba activa
        } else {
            tallaSelect.disabled = false;
        }
    }

    // Ejecutar al cargar por si ya está seleccionado
    toggleTalla();

    // Ejecutar cuando cambia el tipo
    tipoSelect.addEventListener('change', toggleTalla);
});
</script>

</html>