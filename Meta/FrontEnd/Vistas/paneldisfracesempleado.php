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
            <a href="empleado.php">Empleado /</a>
            <a>Panel de Disfraces</a>
        </section>
        <h1><a href="../Vistas/empleado.php" style="padding-right: 3%;" title="volver"><i class="bi bi-arrow-left-circle"></i></a>Panel de Disfraces</h1>
        <section class="container-table" id="costume">
            <section class="nav-table">
                <input type="text" id="search-panel" placeholder="Buscar disfraces..." onkeyup="filtrarTabla('costume')">
            </section>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>NOMBRE</th>
                        <th>CATEGORÍA</th>
                        <th>TALLE</th>
                        <th>ACCIONES</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td>Flash</td>
                        <td>Niño</td>
                        <td>S</td>
                        <td>
                            <button class="accion-button" title="Ver" onclick="openModalVer()"><i class="bi bi-eye"></i></button>
                        </td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td>Blancanieves</td>
                        <td>Niña</td>
                        <td>S</td>
                        <td>
                            <button class="accion-button" title="Ver"><i class="bi bi-eye"></i></button>
                        </td>
                    </tr>
                    <tr>
                        <td>3</td>
                        <td>Gaucho</td>
                        <td>hombre</td>
                        <td>XXL</td>
                        <td>
                            <button class="accion-button" title="Ver"><i class="bi bi-eye"></i></button>
                        </td>
                    </tr>
                    <tr>
                        <td>4</td>
                        <td>Cavernicola</td>
                        <td>mujer</td>
                        <td>XL</td>
                        <td>
                            <button class="accion-button" title="Ver"><i class="bi bi-eye"></i></button>
                        </td>
                    </tr>
                    <tr>
                        <td>5</td>
                        <td>Ardilla</td>
                        <td>niña</td>
                        <td>S</td>
                        <td>
                            <button class="accion-button" title="Ver"><i class="bi bi-eye"></i></button>
                        </td>
                    </tr>
                    <tr>
                        <td>6</td>
                        <td>Fantasta de la B</td>
                        <td>niño</td>
                        <td>S</td>
                        <td>
                            <button class="accion-button" title="Ver"><i class="bi bi-eye"></i></button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </section>
    </main>

    <section id="modal-ver" class="modal">
        <section class="modal-content">
            <span class="close" onclick="closeModalVer()">&times;</span>
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

                <label for="unidad-add">Unidades:</label>
                <input type="number" id="unidad-add" name="unidad" placeholder="7"  required readonly>
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
            if (tipo === 'costume') {
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
    ///VER///
    function openModalVer(costumeName) {
        document.getElementById('modal-ver').style.display = 'block';
    }

    function closeModalVer() {
        document.getElementById('modal-ver').style.display = 'none';
    }
</script>

</body>
</html>
