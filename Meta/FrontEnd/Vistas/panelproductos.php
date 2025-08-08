<?php include('auth.php'); include('conexion.php'); // Ajusta la ruta si es necesario

//Verificando si la cuenta no es rol gerente
if (isset($_SESSION['rol']) && $_SESSION['rol'] != 1 && $_SESSION['rol'] != 2 && $_SESSION['rol'] != 4 )
{
    header("Location: index.php"); 
    exit;
}

if (isset($_GET['id']) && isset($_GET['tipo']) && $_GET['tipo'] == 3) {
    $idEliminar = intval($_GET['id']);
    $usuario = $_SESSION['nom_usu'];
    $conexion->begin_transaction();

    try {
        // Soft delete imágenes
        $stmt = $conexion->prepare("UPDATE img_producto SET eliminado = 1 WHERE id_producto = ?");
        $stmt->bind_param("i", $idEliminar);
        $stmt->execute();

        // Eliminar relaciones intermedias
        $stmt = $conexion->prepare("UPDATE producto_categoria SET eliminado = 1 WHERE id_producto = ?");
        $stmt->bind_param("i", $idEliminar);
        $stmt->execute();

        $stmt = $conexion->prepare("UPDATE producto_talla SET eliminado = 1 WHERE id_producto = ?");
        $stmt->bind_param("i", $idEliminar);
        $stmt->execute();

        $stmt = $conexion->prepare("UPDATE producto_tematica SET eliminado = 1 WHERE id_producto = ?");
        $stmt->bind_param("i", $idEliminar);
        $stmt->execute();

        // Finalmente Dar de baja el producto
        $stmt = $conexion->prepare("UPDATE producto SET eliminado = 1 WHERE id = ?");
        $stmt->bind_param("i", $idEliminar);
        if (!$stmt->execute()) {
            throw new Exception("No se pudo eliminar el producto: " . $stmt->error);
        }

        $stmt = $conexion->prepare("UPDATE img_producto SET eliminado = 1 WHERE id_producto = ?");
        $stmt->bind_param("i", $idEliminar);
        $stmt->execute();

        // Auditoría
        $stmt = $conexion->prepare("INSERT INTO auditoria_producto (id_producto, accion, usuario) VALUES (?, 'BAJA', ?)");
        $stmt->bind_param("is", $idEliminar, $usuario);
        $stmt->execute();;

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

    <style>
        #btn-productos-eliminados {
            background-color: #d12e3b;
            color: white;
            border: none;
            padding: 10px 14px;
            border-radius: 5px;
            font-size: 18px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        #btn-productos-eliminados:hover {
            background-color: #a1222d;
        }
    </style>
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
            <?php if (isset($_SESSION['rol']) and $_SESSION['rol'] == 1 || $_SESSION['rol'] == 4): ?>
                <!-- Para gerente y administrador -->
                <h1><a href="../Vistas/gerente.php" style="padding-right: 3%;" title="volver"><i class="bi bi-arrow-left-circle"></i></a>Panel Administrador de Productos</h1>
            <?php endif; ?>

            <?php if (isset($_SESSION['rol']) and $_SESSION['rol'] == 2): ?>
                <!-- Para empleado -->
               <h1><a href="../Vistas/empleado.php" style="padding-right: 3%;" title="volver"><i class="bi bi-arrow-left-circle"></i></a>Panel de Productos</h1>
            <?php endif; ?>

        <section class="container-table" id="product">
            <section class="nav-table">
                <input type="text" id="search-panel" placeholder="Buscar Productos...">
        
                <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] == 1): ?>
                    <div class="btn-add-container">
                        <button class="btn-agregar" title="Agregar" onclick="openModalAgregar()">
                            <i class="bi bi-person-plus-fill"></i>
                        </button>
                        <a href="../Vistas/productos_eliminados.php" id="btn-productos-eliminados" title="Productos Eliminados">
                            <i class="bi bi-trash"></i>
                        </a>
                    </div>
                <?php endif; ?>

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
                        
                        <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] == 1): ?>
                            <th>MODIFICAR</th>
                            <th>DAR DE BAJA</th>
                        <?php endif; ?>

                    </tr>
                </thead>
                <tbody id="tabla-productos">
                    <!-- Para cargar dinamicamente -->
                </tbody>
            </table>
            <ul class="pagination"></ul>
            
        </section>
    </main>

    <?php include('footer.php');?>

    <script>
            const usuarioRol = <?= $_SESSION['rol'] ?? 0 ?>;

            document.addEventListener("DOMContentLoaded", function () {
            const input = document.getElementById("search-panel");
            const tbody = document.getElementById("tabla-productos");

            function cargarProductos(query = "", page = 1) {
                fetch(`buscar_productos.php?buscar=${encodeURIComponent(query)}&pagina=${page}`)
                    .then(res => res.json())
                    .then(data => {
                        tbody.innerHTML = "";

                        if (data.length === 0) {
                            tbody.innerHTML = "<tr><td colspan='15'>No se encontraron productos</td></tr>";
                            return;
                        }

                        data.productos.forEach(producto => {
                            const tr = document.createElement("tr");

                            const imagen = producto.imagen
                                ? `<img src="uploads/producto/${producto.imagen}" width="60" height="60" style="object-fit: cover; border-radius: 50%;">`
                                : 'Sin imagen';

                            const tipoTexto = producto.tipo == 1 ? 'Disfraz' : (producto.tipo == 2 ? 'Accesorio' : 'Otro');

                            let acciones = `
                                <td>
                                    <a href="verproducto.php?id=${producto.id}">
                                        <button class="ver-btn" title="Ver">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </a>
                                </td>
                            `;

                            if (usuarioRol == 1) {
                                acciones += `
                                    <td>
                                        <a href="editarproducto.php?id=${producto.id}">
                                            <button class="editar-btn" title="Editar">
                                                <i class="bi bi-pencil-square"></i>
                                            </button>
                                        </a>
                                    </td>
                                    
                                    <td>
                                        <a href="panelproductos.php?id=${producto.id}&tipo=3" id="btn-eliminar" title="Dar de Baja">
                                            <i class="bi bi-slash-circle"></i>
                                        </a>
                                    </td>
                                `;
                            } else {
                                acciones += `<td></td><td></td>`;
                            }

                            tr.innerHTML = `
                                <td>${imagen}</td>
                                <td>${producto.nombre}</td>
                                <td>${tipoTexto}</td>
                                <td>${producto.categorias || ''}</td>
                                <td>${producto.tallas || ''}</td>
                                <td>${producto.tematicas || ''}</td>
                                <td>${producto.unidades_disponibles}</td>
                                <td>${producto.precio}</td>
                                ${acciones}
                            `;

                            tbody.appendChild(tr);
                        });

                        // Generar paginación
                        generarPaginacion(data.total_paginas, data.pagina_actual, query);
                        // Agregamos el SweetAlert a los botones nuevos
                        agregarEventosEliminar();
                         
                    })
                    .catch(error => {
                        console.error("Error al cargar productos:", error);
                    });
            }

            // Buscar mientras se escribe
            input.addEventListener("keyup", () => {
                cargarProductos(input.value);
            });

            // Cargar todos al inicio
            cargarProductos();

        function agregarEventosEliminar() {
            document.querySelectorAll('#btn-eliminar').forEach(link => {
            link.addEventListener('click', evt => {
                evt.preventDefault();
                const url = link.href;
                const idProducto = new URL(url).searchParams.get("id");

                fetch(`verificar_alquiler.php?id=${idProducto}`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.alquilado) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'No se puede dar de baja',
                                text: 'Este producto está actualmente alquilado.',
                                confirmButtonText: 'Aceptar'
                            });
                        } else {
                            Swal.fire({
                                title: '¿Dar de baja?',
                                text: 'El producto dejará de estar disponible.',
                                icon: 'question',
                                showDenyButton: true,
                                confirmButtonText: 'Sí, dar de baja',
                                denyButtonText: 'Cancelar',
                            }).then(res => {
                                if (res.isConfirmed) {
                                    window.location.href = url;
                                }
                            });
                        }
                    });
            });
        });
        }

        function generarPaginacion(totalPaginas, paginaActual, query) {
                const paginacion = document.querySelector('.pagination');
                paginacion.innerHTML = '';

                if (totalPaginas <= 1) return; // No mostrar si solo hay una página

                if (paginaActual > 1) {
                    paginacion.innerHTML += `
                        <li><a href="#" data-page="${paginaActual - 1}" data-query="${query}">&laquo;</a></li>
                    `;
                }

                for (let i = 1; i <= totalPaginas; i++) {
                    paginacion.innerHTML += `
                        <li ${i === paginaActual ? 'class="active"' : ''}>
                            <a href="#" data-page="${i}" data-query="${query}">${i}</a>
                        </li>
                    `;
                }

                if (paginaActual < totalPaginas) {
                    paginacion.innerHTML += `
                        <li><a href="#" data-page="${paginaActual + 1}" data-query="${query}">&raquo;</a></li>
                    `;
                }

                //Reasignar eventos después de generar los botones
                document.querySelectorAll('.pagination a').forEach(link => {
                    link.addEventListener('click', e => {
                        e.preventDefault();
                        const page = parseInt(link.getAttribute('data-page'));
                        const search = link.getAttribute('data-query') || '';
                        cargarProductos(search, page);
                    });
                });

            }
        });

        function openModalAgregar() {
            window.location.href = 'agregarproducto.php';

            setTimeout(() => {
                const tipoSelect = document.getElementById('tipo');
                const tallaSelect = document.getElementById('talla');

                if (!tipoSelect || !tallaSelect) return;

                function toggleTalla() {
                    if (tipoSelect.value === '2') {
                        tallaSelect.disabled = true;
                        tallaSelect.value = '';
                    } else {
                        tallaSelect.disabled = false;
                    }
                }

                toggleTalla();
                tipoSelect.addEventListener('change', toggleTalla);
            }, 100); // Ajusta el delay si es necesario
        }
        
    </script>
</body>

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
        title: 'Producto Dado de baja con éxito',
        showConfirmButton: false,
        timer: 1500
    });
    history.replaceState({},'', location.pathname);
  }
  

});

</script>
</html>