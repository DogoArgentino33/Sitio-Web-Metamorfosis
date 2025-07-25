<?php include('auth.php'); include('conexion.php'); ?>

<!-- Cuerpo de la página -->
<!DOCTYPE html>
<html lang="es">
<head>
    <title>Alquiler de Disfraces - Metamorfosis</title>
    <link rel="icon" type="image/x-icon" href="./assets/favicon.ico" />
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../Estilos/index.css">
    <link rel="stylesheet" href="../Estilos/panelgeneral.css">
</head>

<!-- Cuerpo de la página -->
<body>
    <?php include('cabecera.php'); ?>
    <main>
        <!-- Navegador -->
        <section class="nav-route">
            <a href="index.php">Inicio / </a>
            <a href="empleado.php">Empleado /</a>
            <a>Panel de Consultas</a>
        </section>
        <h1><a href="../Vistas/empleado.php" style="padding-right: 3%;" title="volver"><i class="bi bi-arrow-left-circle"></i></a>Panel de Consultas</h1>
        
        <section class="container-table" id="user">
            <section class="nav-table">
                <input type="text" id="search-panel" placeholder="Buscar consultas..." onkeyup="filtrarTabla('user')">
            </section>
            <!-- Tabla -->
            <table>
                <thead>
                    <tr>
                        <th>NOMBRE</th>
                        <th>APELLIDO</th>
                        <th>CORREO</th>
                        <th>VER CONSULTA</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                    $por_pagina = 10; // cantidad de registros por página
                    $pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
                    $inicio = ($pagina > 1) ? ($pagina * $por_pagina) - $por_pagina : 0;

                    $total_stmt = $conexion->prepare("SELECT COUNT(*) as total FROM consulta");
                    $total_stmt->execute();
                    $total_resultado = $total_stmt->get_result()->fetch_assoc();
                    $total_registros = $total_resultado['total'];
                    $total_paginas = ceil($total_registros / $por_pagina);

                    $stmt = $conexion->prepare("SELECT id, nombre, apellido, correo FROM consulta ORDER BY id LIMIT ?, ?");
                    $stmt->bind_param("ii", $inicio, $por_pagina);
                    $stmt->execute(); 
                    $result = $stmt->get_result();
    
                if($result->num_rows > 0) {
                    while($consulta = $result->fetch_assoc()) 
                    {
                        ?>

                        <tr>
                        <td><?= htmlspecialchars($consulta['nombre']) ?></td>
                        <td><?= htmlspecialchars($consulta['apellido']) ?></td>
                        <td><?= htmlspecialchars($consulta['correo']) ?></td>
                        
                        <!-- boton ver -->
                    
                        <td><a href="verconsulta.php?id=<?= $consulta['id'] ?>"><button class="ver-btn" title="Ver" onclick="openModalAgregar()"><i class="bi bi-eye"></i></button></a></td>		
                    
                        </tr>
                    <?php
                    }
                    
                    } 
                    else 
                    {
                    ?>
                    
                    <tr>
                    
                    <td colspan="7">No hay consultas</td>
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
            <?php if($pagina > 1): ?>
                <li><a href="?pagina=<?= $pagina - 1 ?>">&laquo;</a></li>
            <?php endif; ?>

            <?php for($i = 1; $i <= $total_paginas; $i++): ?>
                <li <?= ($i == $pagina) ? 'class="active"' : '' ?>>
                    <a href="?pagina=<?= $i ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>

            <?php if($pagina < $total_paginas): ?>
                <li><a href="?pagina=<?= $pagina + 1 ?>">&raquo;</a></li>
            <?php endif; ?>
        </ul>
    </section>

    <?php include('footer.php');?>

    <!-- Filtrando datos -->
    <script>
        function filtrarTabla(tipo) {
            let input;
            let table;
            let tr;
            let td;
            let i, j;
            let txtValue;

            // Selecciona el campo de búsqueda correspondiente
            if (tipo === 'user') 
            {
                input = document.getElementById('search-panel');
                table = document.querySelector('#user table');
            } 

            const filter = input.value.toUpperCase();
            tr = table.getElementsByTagName("tr");

            for (i = 1; i < tr.length; i++) { // Comienza desde 1 para saltar el encabezado
                tr[i].style.display = "none"; // Oculta todas las filas
                td = tr[i].getElementsByTagName("td");
                for (j = 0; j < td.length; j++) {
                    if (td[j]) {
                        txtValue = td[j].textContent || td[j].innerText;
                        if (txtValue.toUpperCase().indexOf(filter) > -1) {
                            tr[i].style.display = ""; // Muestra la fila si coincide
                            break;
                        }
                    }
                }
            }
        }
    </script>

</body>
</html>
