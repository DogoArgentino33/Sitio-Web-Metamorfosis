<?php include('auth.php'); include('conexion.php'); // Ajusta la ruta si es necesario

//Verificando si la cuenta no es rol gerente o empleado
if (isset($_SESSION['rol']) && $_SESSION['rol'] != 1 && $_SESSION['rol'] != 2 && $_SESSION['rol'] != 4 )
{
    header("Location: index.php"); 
    exit;
}

if (isset($_GET['id']) && isset($_GET['tipo']) && $_GET['tipo'] == 3) 
{
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
        header("Location: panelalquileres.php?alquilereliminado=ok");
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
    <title>Alquiler de Disfraces - Metamorfosis - Panel Alquiler</title>
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
            <?php if (isset($_SESSION['rol']) and $_SESSION['rol'] == 1 || $_SESSION['rol'] == 4): ?>
                <!-- Para gerente -->
                <section class="nav-route">
                <a href="index.php">Inicio / </a>
                <a href="gerente.php">Gerente /</a>
                <a>Panel de Alquiler</a>
                </section>
            <?php endif; ?>

            <?php if (isset($_SESSION['rol']) and $_SESSION['rol'] == 2): ?>
                <!-- Para empleado -->
                <section class="nav-route">
                <a href="index.php">Inicio / </a>
                <a href="empleado.php">Empleado /</a>
                <a>Panel de Alquiler</a>
            </section>
            <?php endif; ?>

            <!-- Regresando a paneles generales -->
            <?php if (isset($_SESSION['rol']) and $_SESSION['rol'] == 1 || $_SESSION['rol'] == 4): ?>
                <!-- Para gerente y administrador -->
                <h1><a href="../Vistas/gerente.php" style="padding-right: 3%;" title="volver"><i class="bi bi-arrow-left-circle"></i></a>Panel Administrador de Alquiler</h1>
            <?php endif; ?>

            <?php if (isset($_SESSION['rol']) and $_SESSION['rol'] == 2): ?>
                <!-- Para empleado -->
               <h1><a href="../Vistas/empleado.php" style="padding-right: 3%;" title="volver"><i class="bi bi-arrow-left-circle"></i></a>Panel de Alquiler</h1>
            <?php endif; ?>

        <section class="container-table" id="product">
            <section class="nav-table">
                <input type="text" id="search-panel" placeholder="Buscar..." onkeyup="filtrarTabla('product')">

            </section>
            <table>
                <thead>
                    <tr>
                        <th>DISFRAZ</th>
                        <th>USUARIO</th>
                        <th>DESDE</th>
                        <th>HASTA</th>
                        <th>PRECIO</th>
                        <th>VER</th>
                        
                        <?php if (isset($_SESSION['rol']) and $_SESSION['rol'] == 1): ?>
                        <!-- Para gerente -->
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
                        SELECT alquiler.id,id_usuario,id_producto, desde, hasta,total FROM alquiler
                        INNER JOIN usuario
                        ON usuario.id = alquiler.id_usuario
                        INNER JOIN producto
                        ON producto.id = alquiler.id_producto
                        GROUP BY alquiler.id
                        ORDER BY alquiler.id
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
                            
                                <td><?= htmlspecialchars($producto['disfraz']) ?></td>
                                <td><?= htmlspecialchars($producto['usuario']) ?></td>
                                <td><?= htmlspecialchars($producto['desde']) ?></td>
                                <td><?= htmlspecialchars($producto['hasta']) ?></td>
                                <td><?= htmlspecialchars($producto['total']) ?></td>

                                <td><a href="veralquiler.php?id=<?= $producto['id'] ?>"><button class="ver-btn" title="Ver"><i class="bi bi-eye"></i></button></a></td>
                                
                                <?php if (isset($_SESSION['rol']) and $_SESSION['rol'] == 1): ?>
                                <!-- Para gerente -->
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
                            <td colspan="15">No hay alquileres registrados</td>
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
                text: 'Está seguro de eliminar el alquiler?',
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
</script>
        

<script>
document.addEventListener('DOMContentLoaded', () => 
{
  //1. Traemos lo que definimos
  const p = new URLSearchParams(location.search);

  if (p.get('alquilereliminado') == 'ok') //Para eliminado
  {
    Swal.fire({
        position: 'top',
        icon:  'success',
        title: 'Alquiler eliminado con éxito',
        showConfirmButton: false,
        timer: 1500
    });
    history.replaceState({},'', location.pathname);
  }
  

});

</script>

</html>