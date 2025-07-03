<?php
include('auth.php');
include('conexion.php'); // Ajusta la ruta si es necesario

if (isset($_GET['id']) && isset($_GET['tipo']) && $_GET['tipo'] == 3) {
    $idEliminar = intval($_GET['id']);

    // Iniciar una transacción para que sea todo o nada
    $conexion->begin_transaction();

    try {
        // Eliminar imágenes del producto
        $stmt = $conexion->prepare("DELETE FROM img_producto WHERE id_producto = ?");
        $stmt->bind_param("i", $idEliminar);
        $stmt->execute();

        // Eliminar relaciones con categorías
        $stmt = $conexion->prepare("DELETE FROM categoria WHERE id_producto = ?");
        $stmt->bind_param("i", $idEliminar);
        $stmt->execute();

        // Eliminar relaciones con tallas
        $stmt = $conexion->prepare("DELETE FROM talla WHERE id_producto = ?");
        $stmt->bind_param("i", $idEliminar);
        $stmt->execute();

        // Eliminar relaciones con temáticas
        $stmt = $conexion->prepare("DELETE FROM tematica WHERE id_producto = ?");
        $stmt->bind_param("i", $idEliminar);
        $stmt->execute();

        // Eliminar relaciones con img_producto
        $stmt = $conexion->prepare("DELETE FROM img_producto WHERE id_producto = ?");
        $stmt->bind_param("i", $idEliminar);
        $stmt->execute();

        // Finalmente, eliminar el producto
        $stmt = $conexion->prepare("DELETE FROM producto WHERE id = ?");
        $stmt->bind_param("i", $idEliminar);
        if (!$stmt->execute()) {
            throw new Exception("No se pudo eliminar el producto: " . $stmt->error);
        }

        // Confirmar todo
        $conexion->commit();

        // Redirigir tras éxito
        header("Location: panelproductos.php");
        exit;

    } catch (Exception $e) {
        // Revertir cambios si algo falló
        $conexion->rollback();
        echo "<script>alert('Error al eliminar el producto: " . addslashes($e->getMessage()) . "');</script>";
    }
}

//
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
    <link rel="stylesheet" href="../Estilos/panelproducto.css">
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
        <h1><a href="../Vistas/gerente.php" style="padding-right: 3%;" title="volver"><i class="bi bi-arrow-left-circle"></i></a>Panel Administrador de Productos</h1>
        
        <section class="container-table" id="product">
            <section class="nav-table">
                <input type="text" id="search-panel" placeholder="Buscar Productos..." onkeyup="filtrarTabla('product')">
                <div class="btn-add-container">
                    <button class="add-panel" title="Agregar" onclick="openModalAgregar()"><i class="bi bi-person-plus"></i></button>
                </div>
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
                        <th>MODIFICAR</th>
                        <th>ELIMINAR</th>
                    </tr>
                </thead>
                <tbody>
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
                                GROUP BY p.id
                                ORDER BY p.id;
                            ";

                   $stmt = $conexion->prepare($sql);

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

                                <td><a href="verproducto.php?id=<?= $producto['id'] ?>"><button class="add-panel" title="Ver"><i class="bi bi-eye"></i></button></a></td>
                                <td><a href="editarproducto.php?id=<?= $producto['id'] ?>"><button class="add-panel" title="Editar"><i class="bi bi-pencil-square"></i></button></a></td>
                                <td>
                                    <a href="panelproductos.php?id=<?= $producto['id'] ?>&tipo=3" id="btn-eliminar" class="add-panel"><i class="bi bi-trash"></i></a>
                                </td>
                                
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
            <li><a href="#">&laquo; </a></li>
            <li class="active"><a href="#">1</a></li>
            <li><a href="#">2</a></li>
            <li><a href="#">3</a></li>
            <li><a href="#">...</a></li>  
            <li><a href="#"> &raquo;</a></li>
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
                    Swal.fire("Se eliminó el producto","","success"),
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

</html>
