<?php include('auth.php'); include('conexion.php'); // Ajusta la ruta si es necesario

if (isset($_GET['id']) && isset($_GET['tipo']) && $_GET['tipo'] == 3) {
    $idEliminar = intval($_GET['id']);
    $conexion->begin_transaction();

    try {
        // Eliminar imágenes
        $stmt = $conexion->prepare("DELETE FROM img_producto WHERE id_producto = ?");
        $stmt->bind_param("i", $idEliminar);
        $stmt->execute();

        // Eliminar relaciones intermedias
        $stmt = $conexion->prepare("DELETE FROM producto_categoria WHERE id_producto = ?");
        $stmt->bind_param("i", $idEliminar);
        $stmt->execute();

        $stmt = $conexion->prepare("DELETE FROM producto_talla WHERE id_producto = ?");
        $stmt->bind_param("i", $idEliminar);
        $stmt->execute();

        $stmt = $conexion->prepare("DELETE FROM producto_tematica WHERE id_producto = ?");
        $stmt->bind_param("i", $idEliminar);
        $stmt->execute();

        // Finalmente eliminar el producto
        $stmt = $conexion->prepare("DELETE FROM producto WHERE id = ?");
        $stmt->bind_param("i", $idEliminar);
        if (!$stmt->execute()) {
            throw new Exception("No se pudo eliminar el producto: " . $stmt->error);
        }

        $conexion->commit();
        header("Location: panelproductos.php?productoeliminado=ok");
        exit;
    } catch (Exception $e) {
        $conexion->rollback();
        echo "<script>alert('Error al eliminar el producto: " . addslashes($e->getMessage()) . "');</script>";
    }
}

?>
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

            <!-- Barra de navegacion -->
            <?php if (isset($_SESSION['rol']) and $_SESSION['rol'] == 1): ?>
                <!-- Para gerente -->
                <section class="nav-route">
                <a href="index.php">Inicio / </a>
                <a href="gerente.php">Gerente /</a>
                <a>Panel de Productos</a>
                </section>
            <?php endif; ?>

            <?php if (isset($_SESSION['rol']) and $_SESSION['rol'] == 2): ?>
                <!-- Para empleado -->
                <section class="nav-route">
                <a href="index.php">Inicio / </a>
                <a href="empleado.php">Empleado /</a>
                <a>Panel de Productos</a>
            </section>
            <?php endif; ?>

            <!-- Regresando a paneles generales -->
            <?php if (isset($_SESSION['rol']) and $_SESSION['rol'] == 1): ?>
                <!-- Para gerente -->
                <h1><a href="../Vistas/gerente.php" style="padding-right: 3%;" title="volver"><i class="bi bi-arrow-left-circle"></i></a>Panel Administrador de Productos</h1>
            <?php endif; ?>

            <?php if (isset($_SESSION['rol']) and $_SESSION['rol'] == 2): ?>
                <!-- Para empleado -->
               <h1><a href="../Vistas/empleado.php" style="padding-right: 3%;" title="volver"><i class="bi bi-arrow-left-circle"></i></a>Panel de Productos</h1>
            <?php endif; ?>

        <section class="container-table" id="product">
            <section class="nav-table">
                <input type="text" id="search-panel" placeholder="Buscar Productos..." onkeyup="filtrarTabla('product')">
                
            <?php if (isset($_SESSION['rol']) and $_SESSION['rol'] == 1): ?>
                <div class="btn-add-container">
                    <button class="btn-agregar" title="Agregar" onclick="openModalAgregar()"><i class="bi bi-person-plus-fill"></i></button>
                </div>
            <?php endif;?>

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
                        
                        <?php if (isset($_SESSION['rol']) and $_SESSION['rol'] == 1): ?>
                        <!-- Para gerente -->
                         <th>MODIFICAR</th>
                         <th>ELIMINAR</th>
                        <?php endif; ?>

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
                                
                                <?php if (isset($_SESSION['rol']) and $_SESSION['rol'] == 1): ?>
                                <!-- Para gerente -->
                                <td><a href="editarproducto.php?id=<?= $producto['id'] ?>"><button class="editar-btn" title="Editar"><i class="bi bi-pencil-square"></i></button></a></td>
                                <td>
                                    <a href="panelproductos.php?id=<?= $producto['id'] ?>&tipo=3" id="btn-eliminar"><i class="bi bi-trash"></i></a>
                                </td>
                                <?php endif; ?>
                                    
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
document.addEventListener('DOMContentLoaded', () => 
{
    //1.Llamamos y definimos la variable
    document.querySelectorAll('#btn-eliminar').forEach(link => 
    {
        //2.Le asignamos el evento
        link.addEventListener('click', evt => 
        {
            evt.preventDefault();
            const url = link.href;
        
            //3.Agregamos sweetalert
            Swal.fire
            ({
                title: 'Advertencia',
                text: 'Está seguro de eliminar el producto?',
                icon: 'warning',
                showDenyButton: true,
                confirmButtonText: 'Si',
                denyButtonText: 'No',
            })
            .then(res => 
            {
                if (res.isConfirmed) 
                {
                    window.location.href = url;
                }
            });
        });
    });
});
        
function openModalAgregar() 
{
    window.location.href = 'agregarproducto.php';
}
</script>

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

<!-- Funcion SweetAlert: Agregar, modificar, Eliminar-->
<script>
document.addEventListener('DOMContentLoaded', () => 
{
  //1. Traemos lo que definimos
  const p = new URLSearchParams(location.search);

  //2. Como lo definimos como "ok", procede a mostrar el mensaje
  if (p.get('productoagregado') === 'ok') //Para agregado
  {
    Swal.fire({
      position: 'top',
      icon: 'success',
      title: 'Producto agregado con éxito',
      showConfirmButton: false,
      timer: 1500
    });
  //3. Al refrescar la página, no volverá a salir el mensaje
    history.replaceState({}, '', location.pathname);
  }
  if (p.get('productomodificado') === 'ok') //Para modificado
  {
    Swal.fire({
      position: 'top',
      icon: 'success',
      title: 'Producto modificado con éxito',
      showConfirmButton: false,
      timer: 1500
    });
    history.replaceState({},'', location.pathname);
  } 
  if (p.get('productoeliminado') == 'ok') //Para eliminado
  {
    Swal.fire({
        position: 'top',
        icon:  'success',
        title: 'Producto eliminado con éxito',
        showConfirmButton: false,
        timer: 1500
    });
    history.replaceState({},'', location.pathname);
  }
  

});

</script>


</html>
