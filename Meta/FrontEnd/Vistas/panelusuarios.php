<?php
include('auth.php');
include('conexion.php'); // Ajusta la ruta si es necesario

if (isset($_GET['id']) && isset($_GET['tipo']) && $_GET['tipo'] == 3) {
    $idEliminar = intval($_GET['id']);
    
    // Preparar y ejecutar la eliminación
    $stmt = $conexion->prepare("DELETE FROM usuario WHERE id = ?");
    $stmt->bind_param("i", $idEliminar);

    if ($stmt->execute()) {
        // Redirigir nuevamente a panelusuarios para evitar reenvíos y actualizar la tabla
        header("Location: panelusuarios.php");
        exit;
    } else {
        echo "<script>alert('Error al eliminar el usuario');</script>";
    }
}


?>
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
            <a>Panel de Usuarios</a>
        </section>
        <h1><a href="../Vistas/gerente.php" style="padding-right: 3%;" title="volver"><i class="bi bi-arrow-left-circle"></i></a>Panel Administrador de Usuarios</h1>
        
        <section class="container-table" id="user">
            <section class="nav-table">
                <input type="text" id="search-panel" placeholder="Buscar Usuarios..." onkeyup="filtrarTabla('user')">
                <div class="btn-add-container">
                    <button class="add-panel" title="Agregar" onclick="openModalAgregar()"><i class="bi bi-person-plus"></i></button>
                </div>
            </section>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>NOMBRE DE USUARIO</th>
                        <th>IMAGEN DE PERFIL</th>
                        <th>CORREO</th>
                        <th>TELÉFONO</th>
                        <th>ID PERSONA</th>
                        <th>ROL</th>
                        <th>ESTADO</th>
                        <th>VER</th>
                        <th>MODIFICAR</th>
                        <th>ELIMINAR</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                    $stmt = $conexion->prepare("SELECT id, nom_usu, img_perfil, correo, telefono, id_persona, rol, estadousu FROM usuario ORDER BY id");
                    $stmt->execute(); 
                    $result = $stmt->get_result();
    
                if($result->num_rows > 0) {
                    while($usuario = $result->fetch_assoc()) 
                    {
                        ?>

                        <tr>

                        <td><?= htmlspecialchars($usuario['id']) ?></td>
                        <td><?= htmlspecialchars($usuario['nom_usu']) ?></td>
                        <td>
                        <img src="<?= htmlspecialchars($usuario['img_perfil']) ?>" alt="Perfil" width="60" height="60" style="object-fit: cover; border-radius: 50%;">
                        </td>

                        <td><?= htmlspecialchars($usuario['correo']) ?></td>
                        <td><?= htmlspecialchars($usuario['telefono']) ?></td>
                        <td><?= htmlspecialchars($usuario['id_persona']) ?></td>
                        <td><?= htmlspecialchars($usuario['rol']) ?></td>
                        <td><?= htmlspecialchars($usuario['estadousu']) ?></td>
                        <td><a href="verusuario.php?id=<?= $usuario['id'] ?>">Ver</a></td>
                        <td><a href="editarusuario.php?id=<?= $usuario['id'] ?>">Modificar</a></td>
                
                        <td>
                        
                        <?php if($usuario['estadousu'] == true): ?>
                        <a href="panelusuarios.php?id=<?= $usuario['id'] ?>&tipo=3" onclick="return confirmarEliminacion()">Eliminar</a>

                        <?php else: ?>
                        <a href="panelusuarios.php?id=<?= $usuario['id'] ?>&tipo=4">Activar</a>
                        <?php endif; ?>
                        
                        </td>				
                    
                        </tr>
                    <?php
                    }
                    
                    } else {
                    ?>
                    
                    <tr>
                    
                    <td colspan="7">No hay usuarios registrados</td>
                    </tr>
                    <?php
                    }
                        ?>

                </tbody>
            </table>
        </section>
    </main>

    <section id="modal-agregar" class="modal">
        <section class="modal-content">
            <span class="close" onclick="closeModalAgregar()">&times;</span>
            <h1>Agregar Usuario</h1>
            <form id="rentalForm">
                <label for="nombre">Nombre:</label>
                <input type="text" id="nombre" name="nombre" placeholder="Juan" required readonly>

                <label for="apellido">Apellido:</label>
                <input type="text" id="apellido" name="apellido" placeholder="Alvarez" required readonly>

                <label for="dni">DNI:</label>
                <input type="text" id="dni" name="dni" placeholder="44532168" readonly>

                <label for="calle">Calle:</label>
                <input type="text" id="calle" name="calle" placeholder="Av. Pres. Castillo" required readonly>

                <label for="altura">Altura:</label>
                <input type="text" id="altura" name="altura" placeholder="Domicilio 355" required readonly>

                <label for="departamento">Departamento:</label>
                <input type="text" id="departamento" name="departamento" placeholder="Valle Viejo" required readonly>

                <label for="municipio">Municipio:</label>
                <input type="text" id="municipio" name="municipio" placeholder="Valle Viejo" required readonly>

                <label for="provincia">Provincia:</label>
                <input type="text" id="provincia" name="provincia" placeholder="San Fernando Del Valle De Catamarca" required readonly>

                <label for="pais">Pais:</label>
                <input type="text" id="pais" name="pais" placeholder="Argentina" required readonly>

                <label for="genero">Genero:</label>
                <input type="text" id="genero" name="Genero" placeholder="Masculino" required readonly>

                <label for="fechanacimiento">Fecha de nacimiento:</label>
                <input type="text" id="fechanacimiento" name="fechanacimiento" placeholder="10/02/1990" required readonly>

                <label for="nombreusuario">Nombre de Usuario:</label>
                <input type="text" id="nombreusuario" name="nombreusuario" placeholder="Juanpa" required readonly>

                <label for="contraseña">Contraseña:</label>
                <input type="password" id="contraseña" name="contraseña" placeholder="**********" required readonly>

                <label for="rol">Rol:</label>
                <select id="rol" name="rol" required>
                    <option value="cliente">Cliente</option>
                    <option value="empleado">Empleado</option>
                    <option value="gerente">Gerente</option>
                    <option value="administrador">Administrador</option>
                </select>

                <label for="correo">Correo:</label>
                <input type="email" id="correo" name="correo" placeholder="juan@example.com" required readonly>

                <label for="imagenperfil">Imagen de Perfil:</label>
                <img src="../img/img-user.jpg" alt="imagendelusuario" width="30%" height="30%" style="border-radius: 3%;">

                <label for="telefono">Teléfono:</label>
                <input type="text" id="telefono" name="telefono" placeholder="3834124397" required readonly>

                <label for="pais">Geolocalizacion:</label>
                <input type="text" id="geolocalizacion" name="geolocalizacion" placeholder="ingrese su geolocalizacion" required readonly>

                <input type="button" value="Agregar">
            </form>
        </section>
    </section>

    <section id="modal-ver" class="modal">
        <section class="modal-content">
            <span class="close" onclick="closeModalVer()">&times;</span>
            <h1>Detalles de Usuario</h1>
            <form id="rentalForm">
                
                <label for="nombre">Nombre:</label>
                <input type="text" id="nombre" name="nombre" placeholder="Juan" required readonly>

                <label for="apellido">Apellido:</label>
                <input type="text" id="apellido" name="apellido" placeholder="Alvarez" required readonly>

                <label for="dni">DNI:</label>
                <input type="text" id="dni" name="dni" placeholder="44532168" required readonly>

                <label for="calle">Calle:</label>
                <input type="text" id="calle" name="calle" placeholder="Av. Pres. Castillo" required readonly>

                <label for="altura">Altura:</label>
                <input type="text" id="altura" name="altura" placeholder="Domicilio 355" required readonly>

                <label for="departamento">Departamento:</label>
                <input type="text" id="departamento" name="departamento" placeholder="Valle Viejo" required readonly>

                <label for="municipio">Municipio:</label>
                <input type="text" id="municipio" name="municipio" placeholder="Valle Viejo" required readonly>

                <label for="provincia">Provincia:</label>
                <input type="text" id="provincia" name="provincia" placeholder="San Fernando Del Valle De Catamarca" required readonly>

                <label for="pais">Pais:</label>
                <input type="text" id="pais" name="pais" placeholder="Argentina" required readonly>

                <label for="genero">Genero:</label>
                <input type="text" id="genero" name="Genero" placeholder="Masculino" required readonly>

                <label for="fechanacimiento">Fecha de nacimiento:</label>
                <input type="text" id="fechanacimiento" name="fechanacimiento" placeholder="10/02/1990" required readonly>

                <label for="nombreusuario">Nombre de Usuario:</label>
                <input type="text" id="nombreusuario" name="nombreusuario" placeholder="Juanpa" required readonly>

                <label for="contraseña">Contraseña:</label>
                <input type="password" id="contraseña" name="contraseña" placeholder="**********" required readonly>

                <label for="rol">Rol:</label>
                <input type="text" id="rol" name="rol" placeholder="cliente" required readonly>

                <label for="correo">Correo:</label>
                <input type="email" id="correo" name="correo" placeholder="juan@example.com" required readonly>

                <label for="imagenperfil">Imagen de Perfil:</label>
                <img src="../img/img-user.jpg" alt="imagendelusuario" width="30%" height="30%" style="border-radius: 3%;">

                <label for="telefono">Teléfono:</label>
                <input type="text" id="telefono" name="telefono" placeholder="3834124397" required readonly>

                <label for="pais">Geolocalizacion:</label>
                <section class="map-container" style="text-align: center; margin-top: 20px;">
                    <iframe class="iframe"
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3154.823694249857!2d-66.264825!3d-28.467292!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x943a06bdf176be8b%3A0x6de4f091bb6e3e27!2sTucum%C3%A1n%20355%2C%20K4700%20San%20Fernando%20del%20Valle%20de%20Catamarca%2C%20Catamarca!5e0!3m2!1ses-419!2sar!4v1634576401573!5m2!1ses-419!2sar" 
                        width="100%" 
                        height="100%" 
                        style="border:0;" 
                        allowfullscreen="" 
                        loading="lazy">
                    </iframe>
                </section>
                
            </form>
        </section>
    </section>

    <section id="modal-editar" class="modal">
        <section class="modal-content">
            <span class="close" onclick="closeModalEditar()">&times;</span>
            <h1>Editar Usuario</h1>
            <form id="rentalForm">

                <label for="nombre">Nombre:</label>
                <input type="text" id="nombre" name="nombre" placeholder="Juan" required readonly>

                <label for="apellido">Apellido:</label>
                <input type="text" id="apellido" name="apellido" placeholder="Alvarez" required readonly>

                <label for="dni">DNI:</label>
                <input type="text" id="dni" name="dni" placeholder="44532168" readonly>

                <label for="calle">Calle:</label>
                <input type="text" id="calle" name="calle" placeholder="Av. Pres. Castillo" required readonly>

                <label for="altura">Altura:</label>
                <input type="text" id="altura" name="altura" placeholder="Domicilio 355" required readonly>

                <label for="departamento">Departamento:</label>
                <input type="text" id="departamento" name="departamento" placeholder="Valle Viejo" required readonly>

                <label for="municipio">Municipio:</label>
                <input type="text" id="municipio" name="municipio" placeholder="Valle Viejo" required readonly>

                <label for="provincia">Provincia:</label>
                <input type="text" id="provincia" name="provincia" placeholder="San Fernando Del Valle De Catamarca" required readonly>

                <label for="pais">Pais:</label>
                <input type="text" id="pais" name="pais" placeholder="Argentina" required readonly>

                <label for="genero">Genero:</label>
                <input type="text" id="genero" name="Genero" placeholder="Masculino" required readonly>

                <label for="fechanacimiento">Fecha de nacimiento:</label>
                <input type="text" id="fechanacimiento" name="fechanacimiento" placeholder="10/02/1990" readonly>

                <label for="nombreusuario">Nombre de Usuario:</label>
                <input type="text" id="nombreusuario" name="nombreusuario" placeholder="Juanpa" required readonly>

                <label for="contraseña">Contraseña:</label>
                <input type="password" id="contraseña" name="contraseña" placeholder="**********" required readonly>

                <label for="rol">Rol:</label>
                <input type="text" id="rol" name="rol" placeholder="cliente" required readonly>

                <label for="correo">Correo:</label>
                <input type="email" id="correo" name="correo" placeholder="juan@example.com" required readonly>

                <label for="imagenperfil">Imagen de Perfil:</label>
                <img src="../img/img-user.jpg" alt="imagendelusuario" width="30%" height="30%" style="border-radius: 3%;">

                <label for="telefono">Teléfono:</label>
                <input type="text" id="telefono" name="telefono" placeholder="3834124397" required readonly>

                <label for="pais">Geolocalizacion:</label>
                <section class="map-container" style="text-align: center; margin-top: 20px;">
                    <iframe class="iframe"
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3154.823694249857!2d-66.264825!3d-28.467292!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x943a06bdf176be8b%3A0x6de4f091bb6e3e27!2sTucum%C3%A1n%20355%2C%20K4700%20San%20Fernando%20del%20Valle%20de%20Catamarca%2C%20Catamarca!5e0!3m2!1ses-419!2sar!4v1634576401573!5m2!1ses-419!2sar" 
                        width="100%" 
                        height="100%" 
                        style="border:0;" 
                        allowfullscreen="" 
                        loading="lazy">
                    </iframe>
                </section>

                <input type="button" value="Guardar Cambios">
            </form>
        </section>
    </section>

    <section id="modal-eliminar" class="modal">
        <section class="modal-content">
            <span class="close" onclick="closeModalEliminar()">&times;</span>
            <h1>Eliminar Usuario</h1>
            <form id="rentalForm">
                <h2>¿Estas seguro de querer Eliminar este usuario?</h2>
                <input type="button" value="Eliminar">
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
            if (tipo === 'user') 
            {
                input = document.getElementById('search-panel');
                table = document.querySelector('#user table');
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
    function openModalAgregar(costumeName) {
        document.getElementById('modal-agregar').style.display = 'block';
    }

    function closeModalAgregar() {
        document.getElementById('modal-agregar').style.display = 'none';
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
    function openModalEliminar(costumeName) {
        document.getElementById('modal-eliminar').style.display = 'block';
    }

    function closeModalEliminar() {
        document.getElementById('modal-eliminar').style.display = 'none';
    }
</script>

<script>
function confirmarEliminacion() {
    return confirm("¿Estás seguro de que deseas eliminar este usuario?");
}
</script>


</body>
</html>
