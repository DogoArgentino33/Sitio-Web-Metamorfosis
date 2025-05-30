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
            <a>Panel de Accesorios</a>
        </section>
        <h1><a href="../Vistas/empleado.php" style="padding-right: 3%;" title="volver"><i class="bi bi-arrow-left-circle"></i></a>Panel de Accesorios</h1>
        <section class="container-table" id="accessory">
            <section class="nav-table">
                <input type="text" id="search-panel" placeholder="Buscar accesorios..." onkeyup="filterTable('accessory')">
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
                        </td> 
                    </tr>
                </tbody>
            </table>
        </section>
    </main>

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

                <label for="unidad-add">Unidades:</label>
                <input type="number" id="unidad-add" name="unidad" placeholder="5"  required readonly>
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
             if (tipo === 'accessory') {
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
    ///VER///
    function openModalShow(costumeName) {
        document.getElementById('modal-show').style.display = 'block';
    }

    function closeModalShow() {
        document.getElementById('modal-show').style.display = 'none';
    }
</script>

</body>
</html>
