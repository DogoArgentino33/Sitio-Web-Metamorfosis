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
            <a>Panel de Accesorios</a>
        </section>
        <h1><a href="../Vistas/gerente.php" style="padding-right: 3%;" title="volver"><i class="bi bi-arrow-left-circle"></i></a>Panel Administrador de Accesorios</h1>
        <section class="container-table" id="accessory">
            <section class="nav-table">
                <input type="text" id="search-panel" placeholder="Buscar accesorios..." onkeyup="filtrarTabla('accessory')">
                <div class="btn-add-container">
                    <button class="add-panel" title="Agregar" onclick="openModalAdd()"><i class="bi bi-person-plus"></i></button>
                </div>
            </section>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>NOMBRE</th>
                        <th>CATEGORÍA</th>
                        <th>DISPONIBILIDAD</th>
                        <th>PRECIO</th>
                        <th>ACCIONES</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td>Sombrero de Vaquero</td>
                        <td>cabeza</td>
                        <td>Disponible</td>
                        <td>$10</td>
                        <td>
                            <button class="accion-button" title="Ver" onclick="openModalShow()"><i class="bi bi-eye"></i></button>
                            <button class="accion-button" title="Editar" onclick="openModalEdit()"><i class="bi bi-pencil"></i></button>
                            <button class="accion-button" title="Eliminar" onclick="openModalDelete()"><i class="bi bi-trash"></i></button>
                        </td> 
                    </tr>
                    <tr>
                        <td>2</td>
                        <td>Barba Postiza</td>
                        <td>cara</td>
                        <td>No Disponible</td>
                        <td>$5</td>
                        <td>
                            <button class="accion-button" title="Ver"><i class="bi bi-eye"></i></button>
                            <button class="accion-button" title="Editar"><i class="bi bi-pencil"></i></button>
                            <button class="accion-button" title="Eliminar"><i class="bi bi-trash"></i></button>
                        </td> 
                    </tr>
                    <tr>
                        <td>3</td>
                        <td>Gafas de Sol</td>
                        <td>ojos</td>
                        <td>Disponible</td>
                        <td>$15</td>
                        <td>
                            <button class="accion-button" title="Ver"><i class="bi bi-eye"></i></button>
                            <button class="accion-button" title="Editar"><i class="bi bi-pencil"></i></button>
                            <button class="accion-button" title="Eliminar"><i class="bi bi-trash"></i></button>
                        </td> 
                    </tr>
                    <tr>
                        <td>4</td>
                        <td>Collar de Perlas</td>
                        <td>cuello</td>
                        <td>Disponible</td>
                        <td>$12</td>
                        <td>
                            <button class="accion-button" title="Ver"><i class="bi bi-eye"></i></button>
                            <button class="accion-button" title="Editar"><i class="bi bi-pencil"></i></button>
                            <button class="accion-button" title="Eliminar"><i class="bi bi-trash"></i></button>
                        </td> 
                    </tr>
                </tbody>
            </table>
        </section>
    </main>

    <section id="modal-add" class="modal">
        <section class="modal-content">
            <span class="close" onclick="closeModalAdd()">&times;</span>
            <h1>Agregar Accesorio</h1>
            <form id="form-add">
                <label for="nombre-add">Nombre:</label>
                <input type="text" id="nombre-add" name="nombre" required>

                <label for="categoria-add">Categoría:</label>
                <input type="text" id="categoria-add" name="categoria" required>

                <label for="tematica-add">Temática:</label>
                <input type="text" id="tematica-add" name="tematica" required>

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
            <h1>Detalles de Accesorio</h1>
            <form id="rentalForm">
                <label for="nombre">Nombre:</label>
                <input type="text" id="nombre" name="nombre" placeholder="Sombrero de Vaquero" required readonly>

                <label for="categoria">Categoría:</label>
                <input type="text" id="categoria" name="categoria" placeholder="Cabeza" required readonly>

                <label for="tematica">Temática:</label>
                <input type="text" id="tematica" name="tematica" placeholder="Lejano oeste" readonly>

                <label for="disponibilidad">Disponibilidad:</label>
                <input type="text" id="disponibilidad" name="disponibilidad" placeholder="Disponible" required readonly>

                <label for="precio">Precio:</label>
                <input type="number" id="precio" name="precio" placeholder="10" required readonly>

                <label for="unidad">Unidades:</label>
                <input type="number" id="unidad" name="unidad" placeholder="5" required readonly>
            </form>
        </section>
    </section>


    <section id="modal-edit" class="modal">
        <section class="modal-content">
            <span class="close" onclick="closeModalEdit()">&times;</span>
            <h1>Editar Accesorio</h1>
            <form id="form-edit">
                <label for="nombre-edit">Nombre:</label>
                <input type="text" id="nombre-edit" name="nombre" placeholder="Sombrero de Vaquero" required>

                <label for="categoria-edit">Categoría:</label>
                <input type="text" id="categoria-edit" name="categoria" placeholder="Cabeza" required>

                <label for="tematica-edit">Temática:</label>
                <input type="text" id="tematica-edit" name="tematica" placeholder="Lejano oeste"  required>

                <label for="disponibilidad-edit">Disponibilidad:</label>
                <input type="text" id="disponibilidad-edit" name="disponibilidad" placeholder="Disponible"  required >

                <label for="precio-edit">Precio:</label>
                <input type="number" id="precio-edit" name="precio" placeholder="10"  required>

                <label for="unidad-add">Unidades:</label>
                <input type="number" id="unidad-add" name="unidad" placeholder="5" required>

                <button type="submit">Guardar Cambios</button>
            </form>
        </section>
    </section>

    <section id="modal-delete" class="modal">
        <section class="modal-content">
            <span class="close" onclick="closeModalDelete()">&times;</span>
            <h1>Eliminar Accesorio</h1>
            <form id="rentalForm">
                <h2>¿Estas seguro de querer Eliminar este accesorio?</h2>
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
        function filtrarTabla(tipo) {
            let input;
            let table;
            let tr;
            let td;
            let i, j;
            let txtValue;

            // Selecciona el campo de búsqueda correspondiente
           if(tipo === 'accessory') 
            {
                input = document.getElementById('search-panel');
                table = document.querySelector('#accessory table');
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

    function closeModalAdd() {
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
