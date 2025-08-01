<?php 
    include('auth.php'); 
    include('conexion.php'); 

    // Verificando roles válidos
    if (isset($_SESSION['rol']) && $_SESSION['rol'] != 1 && $_SESSION['rol'] != 2 && $_SESSION['rol'] != 4) {
        header("Location: index.php"); 
        exit;
    }

    // Eliminación de alquiler
    if (isset($_GET['id']) && isset($_GET['tipo']) && $_GET['tipo'] == 3) {
        $idEliminar = intval($_GET['id']);
        $conexion->begin_transaction();

        try {
            $stmt = $conexion->prepare("DELETE FROM alquiler WHERE id = ?");
            $stmt->bind_param("i", $idEliminar);

            if (!$stmt->execute()) {
                throw new Exception("No se pudo eliminar el alquiler: " . $stmt->error);
            }

            $conexion->commit();
            header("Location: panelalquileres.php?alquilereliminado=ok");
            exit;
        } catch (Exception $e) {
            $conexion->rollback();
            echo "<script>alert('Error al eliminar el alquiler: " . addslashes($e->getMessage()) . "');</script>";
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
                        <th>USUARIO</th>
                        <th>PRODUCTO</th>
                        <th>DESDE</th>
                        <th>HASTA</th>
                        <th>CANTIDAD</th>
                        <th>TOTAL ($)</th>
                        <th>METODO PAGO</th>
                        
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
                    $inicio = ($pagina - 1) * $por_pagina;

                    // Total registros
                    $total_sql = "SELECT COUNT(*) AS total FROM alquiler";
                    $total_result = $conexion->query($total_sql);
                    $total_fila = $total_result->fetch_assoc();
                    $total_paginas = ceil($total_fila['total'] / $por_pagina);

                    // Consulta paginada con JOINs
                    $sql = "
                       SELECT 
                            a.id,
                            a.desde,
                            a.hasta,
                            a.cantidad,
                            a.total,
                            u.nom_usu AS usuario,
                            p.nombre AS producto,
                            m.nombre AS metodo_pago,
                            a.fechamod,
                            a.usumod
                        FROM alquiler a
                        INNER JOIN usuario u ON a.id_usuario = u.id
                        INNER JOIN producto p ON a.id_producto = p.id
                        INNER JOIN metodo_pago m ON a.id_metodopago = m.id
                        ORDER BY a.desde DESC
                        LIMIT ?, ?;

                    ";

                    $stmt = $conexion->prepare($sql);
                    if (!$stmt) {
                        die("Error al preparar la consulta: " . $conexion->error);
                    }
                    $stmt->bind_param("ii", $inicio, $por_pagina);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    if ($result && $result->num_rows > 0) {
                        while ($alquiler = $result->fetch_assoc()) {
                            ?>
                            <tr>
                                <td><?= htmlspecialchars($alquiler['usuario']) ?></td>
                                <td><?= htmlspecialchars($alquiler['producto']) ?></td>
                                <td><?= htmlspecialchars($alquiler['desde']) ?></td>
                                <td><?= htmlspecialchars($alquiler['hasta']) ?></td>
                                <td><?= htmlspecialchars($alquiler['cantidad']) ?></td>
                                <td><?= htmlspecialchars(number_format($alquiler['total'], 2)) ?></td>
                                <td><?= htmlspecialchars($alquiler['metodo_pago']) ?></td>
                                
                                <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] == 1): ?>
                                <td>
                                    <a href="panelalquileres.php?id=<?= $alquiler['id'] ?>&tipo=3" id="btn-eliminar"><i class="bi bi-trash"></i></a>
                                </td>
                                <?php endif; ?>
                            </tr>
                            <?php
                        }
                    } else {
                        ?>
                        <tr>
                            <td colspan="8">No hay alquileres registrados</td>
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