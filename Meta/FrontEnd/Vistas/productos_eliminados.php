<?php include('auth.php'); include('conexion.php'); // Ajusta la ruta si es necesario

//Verificando si la cuenta no es rol gerente
if (isset($_SESSION['rol']) && $_SESSION['rol'] != 1 && $_SESSION['rol'] != 2 && $_SESSION['rol'] != 4 )
{
    header("Location: index.php"); 
    exit;
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
        .eliminar-definitivo-btn {
            background-color: #d12e3b;
            color: white;
            border: none;
            padding: 10px 14px;
            border-radius: 5px;
            font-size: 18px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .eliminar-definitivo-btn:hover {
            background-color: #a1222d;
        }

        .restaurar-btn {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px 14px;
            border-radius: 5px;
            font-size: 18px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .restaurar-btn:hover {
            background-color: #0064cf;
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
                <a>Panel de Productos Eliminados</a>
                </section>
            <?php endif; ?>

            <?php if (isset($_SESSION['rol']) and $_SESSION['rol'] == 2): ?>
                <!-- Para empleado -->
                <section class="nav-route">
                <a href="index.php">Inicio / </a>
                <a href="empleado.php">Empleado /</a>
                <a>Panel de Productos Eliminados</a>
            </section>
            <?php endif; ?>

            <!-- Regresando a paneles generales -->
            <?php if (isset($_SESSION['rol']) and $_SESSION['rol'] == 1 || $_SESSION['rol'] == 4): ?>
                <!-- Para gerente y administrador -->
                <h1><a href="../Vistas/panelproductos.php" style="padding-right: 3%;" title="volver"><i class="bi bi-arrow-left-circle"></i></a>Panel Administrador de Productos Eliminados</h1>
            <?php endif; ?>

            <?php if (isset($_SESSION['rol']) and $_SESSION['rol'] == 2): ?>
                <!-- Para empleado -->
               <h1><a href="../Vistas/empleado.php" style="padding-right: 3%;" title="volver"><i class="bi bi-arrow-left-circle"></i></a>Panel de Productos</h1>
            <?php endif; ?>

        <section class="container-table" id="product">
            <section class="nav-table">
                <input type="text" id="search-panel" placeholder="Buscar Productos...">
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
                        
                        <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] == 1): ?>
                            <th>RESTAURAR</th>
                            <th>ELIMINAR</th>
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
                fetch(`buscar_productos_eliminados.php?buscar=${encodeURIComponent(query)}&pagina=${page}`)
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
                                    <button class="restaurar-btn" data-id="${producto.id}" title="Restaurar">
                                       <i class="bi bi-arrow-clockwise"></i>
                                    </button>
                                </td>
                                <td>
                                    <button class="eliminar-definitivo-btn" data-id="${producto.id}" title="Eliminar definitivamente">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            `;

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

        function agregarEventosEliminar() {
    document.querySelectorAll('.restaurar-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const id = this.getAttribute('data-id');
            Swal.fire({
                title: '¿Restaurar producto?',
                text: "El producto volverá a estar disponible.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Sí, restaurar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    realizarAccion(id, 'restaurar');
                }
            });
        });
    });

    document.querySelectorAll('.eliminar-definitivo-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const id = this.getAttribute('data-id');
            Swal.fire({
                title: '¿Eliminar definitivamente?',
                text: "Esta acción no se puede deshacer.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    realizarAccion(id, 'eliminar_definitivo');
                }
            });
        });
    });
}

function realizarAccion(id, accion) {
    fetch('productos_eliminados_acciones.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `id=${encodeURIComponent(id)}&accion=${encodeURIComponent(accion)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire('Éxito', data.mensaje, 'success').then(() => {
                location.reload(); // o recargar solo la tabla si prefieres
            });
        } else {
            Swal.fire('Error', data.mensaje, 'error');
        }
    })
    .catch(error => {
        Swal.fire('Error', 'Ocurrió un error inesperado.', 'error');
        console.error(error);
    });
}

    </script>
</body>
</html>