<!DOCTYPE html>
<html lang="es">
<head>
    <title>Alquiler de Disfraces - Metamorfosis</title>
    <link rel="icon" type="image/x-icon" href="./assets/favicon.ico" />
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../Estilos/paneles.css">
    <link rel="stylesheet" href="../Estilos/index.css">
    <link rel="stylesheet" href="../Estilos/modales.css">
</head>
<body>
    <?php include('cabecera.php'); ?>
    <main>
        <section class="nav-route">
            <a href="index.php">Inicio / </a>
            <a href="gerente.php">Gerente /</a>
            <a>Panel de Disfraces</a>
        </section>
        <h1><a href="../Vistas/gerente.php" style="padding-right: 3%;" title="volver"><i class="bi bi-arrow-left-circle"></i></a>Panel Administrador de Disfraces</h1>
        <section class="container-table" id="costume">
            <section class="nav-table">
                <input type="text" id="search-panel" placeholder="Buscar disfraces..." onkeyup="filterTable('costume')">
                <div class="btn-add-container">
                    <button class="add-panel" title="Agregar" onclick="openModalAdd()"><i class="bi bi-person-plus"></i></button>
                </div>
            </section>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>NOMBRE</th>
                        <th>TIPO</th>
                        <th>DISPONIBILIDAD</th>
                        <th>UNIDADES DISPONIBLES</th>
                        <th>PRECIO</th>
                        <TH>TALLA</TH>
                        <TH>CATEGORIA</TH>
                        <TH>IMAGEN PRODUCTO</TH>
                        <TH>AUDITORIA</TH>
                        <th>ACCIONES</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                include('conexion.php');

                $sql = "SELECT p.*, i.img AS imagen 
                        FROM producto p
                        LEFT JOIN img_producto i ON p.id_img_producto = i.id";

                $result = $conexion->query($sql);

                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . $row['id'] . "</td>";
                    echo "<td>" . htmlspecialchars($row['nombre']) . "</td>";
                    echo "<td>" . $row['tipo'] . "</td>";
                    echo "<td>" . ($row['disponibilidad'] ? 'S' : 'N') . "</td>";
                    echo "<td>" . $row['unidades_disponibles'] . "</td>";
                    echo "<td>$" . number_format($row['precio'], 2) . "</td>";
                    echo "<td>" . $row['id_talla'] . "</td>";
                    echo "<td>" . $row['id_categoria'] . "</td>";

                    // Mostrar imagen con ruta completa desde base de datos
                    if (!empty($row['imagen'])) {
                        echo "<td><img src='" . htmlspecialchars($row['imagen']) . "' alt='Producto' title='" . htmlspecialchars($row['imagen']) . "' style='width: 100%; border-radius: 5px;'></td>";
                    } else {
                        echo "<td><em>Sin imagen</em></td>";
                    }

                    echo "<td><button class='accion-button' title='Auditoría'><i class='bi bi-shield-check'></i></button></td>";
                    echo "<td>
                            <button class='accion-button' title='Ver'><i class='bi bi-eye'></i></button>
                            <button class='accion-button' title='Editar'><i class='bi bi-pencil'></i></button>
                            <button class='accion-button' title='Eliminar'><i class='bi bi-trash'></i></button>
                        </td>";
                    echo "</tr>";
                }
                ?>
                </tbody>


            </table>
        </section>
    </main>

    <section id="modal-add" class="modal">
        <section class="modal-content">
            <span class="close" onclick="closeModalAgregar()">&times;</span>
            <h1>Agregar Disfraz</h1>
            <form id="form-add">
                <label for="nombre-add">Nombre:</label>
                <input type="text" id="nombre-add" name="nombre" required>

                <label for="categoria-add">Categoría:</label>
                <input type="text" id="categoria-add" name="categoria" required>

                <label for="tematica-add">Temática:</label>
                <input type="text" id="tematica-add" name="tematica" required>

                <label for="talle-add">Talle:</label>
                <input type="text" id="talle-add" name="talle" required>

                <label for="disponibilidad-add">Disponibilidad:</label>
                <select id="disponibilidad-add" name="disponibilidad">
                    <option value="Disponible">Disponible</option>
                    <option value="No Disponible">No Disponible</option>
                </select>

                <label for="precio-add">Precio:</label>
                <input type="number" id="precio-add" name="precio" required>

                <label for="unidad-add">Unidades:</label>
                <input type="number" id="unidad-add" name="unidad" required>


                <button type="submit">Agregar</button>
            </form>
        </section>
    </section>

    <section id="modal-show" class="modal">
        <section class="modal-content">
            <span class="close" onclick="closeModalShow()">&times;</span>
            <h1>Detalles de Disfraz</h1>
            <form id="rentalForm">
                <label for="nombre">Nombre:</label>
                <input type="text" id="nombre" name="nombre"  placeholder="Flash" required readonly>

                <label for="tematica">Tematica:</label>
                <input type="text" id="tematica" name="tematica" placeholder="superhéroes" required readonly>

                <label for="categoria">Categoria:</label>
                <input type="text" id="categoria" name="categoria"  placeholder="Niños" required readonly>

                <label for="direccion">Talle:</label>
                <input type="text" id="talle" name="talle" placeholder="S" required readonly>

                <label for="disponibilidad">Disponibilidad:</label>
                <input type="text" id="disponibilidad" name="disponibilidad" placeholder="Disponible" required readonly>

                <label for="precio">Precio:</label>
                <input type="number" id="precio" name="precio" placeholder="1000" required readonly>

                <label for="unidad">Unidades:</label>
                <input type="number" id="unidad" name="unidad" placeholder="7"  required readonly>
            </form>
        </section>
    </section>


    <section id="modal-edit" class="modal">
        <section class="modal-content">
            <span class="close" onclick="closeModalEdit()">&times;</span>
            <h1>Editar Disfraz</h1>
            <form id="form-edit">
                <label for="nombre-edit">Nombre:</label>
                <input type="text" id="nombre-edit" name="nombre" placeholder="Flash" required>

                <label for="categoria-edit">Categoría:</label>
                <input type="text" id="categoria-edit" name="categoria" placeholder="niños" required>

                <label for="tematica-edit">Temática:</label>
                <input type="text" id="tematica-edit" name="tematica" placeholder="superhéroes" required>

                <label for="talle-edit">Talle:</label>
                <input type="text" id="talle-edit" name="talle" placeholder="S" required>

                <label for="disponibilidad-edit">Disponibilidad:</label>
                <select id="disponibilidad-edit" name="disponibilidad">
                    <option value="Disponible">Disponible</option>
                    <option value="No Disponible">No Disponible</option>
                </select>

                <label for="precio-edit">Precio:</label>
                <input type="number" id="precio-edit" name="precio" placeholder="1000" required>

                <label for="unidad-edit">Unidades:</label>
                <input type="number" id="unidad-edit" name="unidad" placeholder="7" required>

                <button type="submit">Guardar Cambios</button>
            </form>
        </section>
    </section>

    <section id="modal-delete" class="modal">
        <section class="modal-content">
            <span class="close" onclick="closeModalDelete()">&times;</span>
            <h1>Eliminar Usuario</h1>
            <form id="rentalForm">
                <h2>¿Estas seguro de querer Eliminar este disfraz?</h2>
                <button type="submit">Eliminar</button>
            </form>
        </section>
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

    <?php include('footer.php');?>

    <script>
        function filterTable(tipo) {
            let input;
            let table;
            let tr;
            let td;
            let i, j;
            let txtValue;

            // Selecciona el campo de búsqueda correspondiente
            if (tipo === 'costume') 
            {
                input = document.getElementById('search-panel');
                table = document.querySelector('#costume table');
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

<script>
    ///AGREGAR///
    function openModalAdd(costumeName) {
        document.getElementById('modal-add').style.display = 'block';
    }

    function closeModalAgregar() {
        document.getElementById('modal-add').style.display = 'none';
    }
    
</script>

<script>
    ///VER///
    function openModalShow(costumeName) {
        document.getElementById('modal-show').style.display = 'block';
    }

    function closeModalShow() {
        document.getElementById('modal-show').style.display = 'none';
    }
</script>

<script>
    ///EDITAR///
    function openModalEdit(costumeName) {
        document.getElementById('modal-edit').style.display = 'block';
    }

    function closeModalEdit() {
        document.getElementById('modal-edit').style.display = 'none';
    }
</script>

<script>
    ///ELIMINAR///
    function openModalDelete(costumeName) {
        document.getElementById('modal-delete').style.display = 'block';
    }

    function closeModalDelete() {
        document.getElementById('modal-delete').style.display = 'none';
    }
</script>

</body>
</html>
