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
            <a>Panel de Alquileres</a>
        </section>
        <h1><a href="../Vistas/gerente.php" style="padding-right: 3%;" title="volver"><i class="bi bi-arrow-left-circle"></i></a>Panel Administrador de Alquileres</h1>
  
        <section class="container-table" id="rent">
            <section class="nav-table">
                <input type="text" id="search-panel" placeholder="Buscar alquileres..." onkeyup="filterTable('rent')">
            </section>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>DISFRAZ</th>
                        <th>USUARIO</th>
                        <th>DESDE</th>
                        <th>HASTA</th>
                        <th>PRECIO</th>
                        <th>ACCIONES</th> 
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td>Pirata</td>
                        <td>Juan Pérez</td>
                        <td>01/10/2023</td>
                        <td>05/10/2023</td>
                        <td>$500</td>
                        <td>
                            <button class="accion-button" title="Ver" onclick="openModalShow()"><i class="bi bi-eye"></i></button>
                            <button class="accion-button" title="Eliminar" onclick="openModalDelete()"><i class="bi bi-trash"></i></button>
                        </td> 
                    </tr>
                    <tr>
                        <td>2</td>
                        <td>Superhéroe</td>
                        <td>Ana Gómez</td>
                        <td>02/10/2023</td>
                        <td>06/10/2023</td>
                        <td>$600</td>
                        <td>
                            <button class="accion-button" title="Ver"><i class="bi bi-eye"></i></button>
                            <button class="accion-button" title="Eliminar"><i class="bi bi-trash"></i></button>
                        </td>
                    </tr>
                    <tr>
                        <td>3</td>
                        <td>Princesa</td>
                        <td>Pedro López</td>
                        <td>03/10/2023</td>
                        <td>07/10/2023</td>
                        <td>$700</td>
                        <td>
                            <button class="accion-button" title="Ver"><i class="bi bi-eye"></i></button>
                            <button class="accion-button" title="Eliminar"><i class="bi bi-trash"></i></button>
                        </td>
                    </tr>
                    <tr>
                        <td>4</td>
                        <td>Zombie</td>
                        <td>Lucía Martínez</td>
                        <td>04/10/2023</td>
                        <td>08/10/2023</td>
                        <td>$550</td>
                        <td>
                            <button class="accion-button" title="Ver"><i class="bi bi-eye"></i></button>
                            <button class="accion-button" title="Eliminar"><i class="bi bi-trash"></i></button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </section>
    </main>

    <section id="modal-show" class="modal">
        <section class="modal-content">
            <span class="close" onclick="closeModalShow()">&times;</span>
            <h1>Detalles de Alquiler</h1>
            <form id="rentalForm">
              
                <h3>Datos del usuario</h3>
                <label for="nombre">Nombre:</label>
                <input type="text" id="nombre" name="nombre" placeholder="Juan" required readonly>

                <label for="apellido">Apellido:</label>
                <input type="text" id="apellido" name="apellido" placeholder="Perez" required readonly>

                <label for="correo">Correo:</label>
                <input type="email" id="correo" name="correo" placeholder="perez@gmail.com" required readonly>

                <label for="telefono">Teléfono:</label>
                <input type="text" id="telefono" name="telefono" placeholder="3834665412" required readonly>

                <label for="direccion">Dirección:</label>
                <input type="text" id="direccion" name="direccion" placeholder="Calle 1" required readonly>

                <label for="dni">DNI:</label>
                <input type="text" id="dni" name="dni" placeholder="44098541" required readonly>

                <h3>Datos del disfraz</h3>
                <label for="disfraz">Disfraz:</label>
                <input type="text" id="disfraz" name="disfraz" placeholder="Pirata"  required readonly>

                <label for="categoria">Categoria:</label>
                <input type="text" id="categoria" name="categoria" placeholder="Adultos" required readonly>

                <label for="tematica">Tematica:</label>
                <input type="text" id="tematica" name="tematica" placeholder="Historico" required readonly>

                <label for="talle">Talla:</label>
                <input type="text" id="talla" name="talla" placeholder="M" required readonly>

                <label for="precio">Precio:</label>
                <input type="text" id="precio" name="precio" placeholder="500" required readonly>

                <h3>Datos del alquiler</h3>
                <label for="desde">Desde:</label>
                <input type="text" id="desde" name="desde" placeholder="01/10/2023" required readonly>

                <label for="hasta">Hasta:</label>
                <input type="text" id="hasta" name="hasta" placeholder="05/10/2023" required readonly>

            </form>
        </section>
    </section>

    <section id="modal-delete" class="modal">
        <section class="modal-content">
            <span class="close" onclick="closeModalDelete()">&times;</span>
            <h1>Cancelar Alquiler</h1>
            <form id="rentalForm">
                <h2>¿Estas seguro de querer Cancelar este Alquiler?</h2>
                <button type="submit">Cancelar</button>
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

    <footer>
        <p><i class="bi bi-geo-alt-fill"></i> Tucumán 355, K4700 San Fernando del Valle de Catamarca, Catamarca</p>
        <p><i class="bi bi-envelope-fill"></i> info@metamorfosis.com</p>
        <p><i class="bi bi-telephone-fill"></i> +54 123 456 789</p>
        <p>&copy; 2024 Metamorfosis. Todos los derechos reservados.</p>
        <section class="social-icons">
            <a href="https://www.instagram.com/disfracesmetamorfosis/"><i class="bi bi-instagram"></i></a>
            <i class="bi bi-twitter-x"></i>
            <i class="bi bi-facebook"></i>
            <i class="bi bi-whatsapp"></i>
        </section>
    </footer>

    <script>
        function filterTable(tipo) {
            let input;
            let table;
            let tr;
            let td;
            let i, j;
            let txtValue;

            if (tipo === 'rent') {
                input = document.getElementById('search-panel');
                table = document.querySelector('#rent table');
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
    function openModalEditar(costumeName) {
        document.getElementById('modal-editar').style.display = 'block';
    }

    function closeModalEditar() {
        document.getElementById('modal-editar').style.display = 'none';
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
