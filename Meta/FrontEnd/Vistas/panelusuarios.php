<?php include('auth.php'); include('conexion.php');

//Operación de eliminar usuario
if (isset($_GET['id']) && isset($_GET['tipo']) && $_GET['tipo'] == 3) {
    $idEliminar = intval($_GET['id']);
    
    // Preparar y ejecutar la eliminación
    $stmt = $conexion->prepare("DELETE FROM usuario WHERE id = ?");
    $stmt->bind_param("i", $idEliminar);

    if ($stmt->execute()) {
        // Redirigir nuevamente a panelusuarios para evitar reenvíos y actualizar la tabla
        header("Location: panelusuarios.php?usuarioeliminado=ok");
        exit;
    } 
    else 
    {
        echo "<script>alert('Error al eliminar el usuario');</script>";
    }
}
?>

<!-- Cuerpo de la página -->
<!DOCTYPE html>
<html lang="es">
<head>
    <title>Alquiler de Disfraces - Metamorfosis</title>
    <link rel="icon" type="image/x-icon" href="./assets/favicon.ico" />
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../Estilos/index.css">
    <link rel="stylesheet" href="../Estilos/panelusuario.css">
    <!-- Script de SweetAlert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<!-- Cuerpo de la página -->
<body>
    <?php include('cabecera.php'); ?>
    <main>
        <!-- Navegador -->
        <section class="nav-route">
            <a href="index.php">Inicio / </a>
            <a href="gerente.php">Gerente /</a>
            <a>Panel de Usuarios</a>
        </section>
        <h1><a href="../Vistas/gerente.php" style="padding-right: 3%;" title="volver"><i class="bi bi-arrow-left-circle"></i></a>Panel Administrador de Usuarios</h1>
        
        <section class="container-table" id="user">
            <section class="nav-table">
                <input type="text" id="search-panel" placeholder="Buscar Usuarios..." onkeyup="filtrarTabla('user')">
            </section>
            <!-- Tabla -->
            <table>
                <thead>
                    <tr>
                        <th>NOMBRE DE USUARIO</th>
                        <th>IMAGEN DE PERFIL</th>
                        <th>CORREO</th>
                        <th>TELÉFONO</th>
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
                        <td>
                            <img src="<?= htmlspecialchars($usuario['img_perfil']) ?>" alt="Perfil" width="60" height="60" style="object-fit: cover; border-radius: 50%;">
                        </td>

                        <td><?= htmlspecialchars($usuario['nom_usu']) ?></td>
                        
                        <td><?= htmlspecialchars($usuario['correo']) ?></td>
                        <td><?= htmlspecialchars($usuario['telefono']) ?></td>
                        
                        <!-- Determinando Roles de usuario -->
                        <?php 
                            if($usuario['rol'] == 0){
                                ?><td><?= htmlspecialchars('Usuario') ?></td>
                            <?php
                            }
                            else{
                                if($usuario['rol'] == 1){
                                    ?><td><?= htmlspecialchars('Gerente') ?></td>
                                <?php
                                }
                            }
                            if($usuario['rol'] == 2){
                                ?><td><?= htmlspecialchars('Empleado') ?></td>
                            <?php
                            }
                            else{
                                if($usuario['rol'] == 4){
                                    ?><td><?= htmlspecialchars('Administrador') ?></td>
                                <?php
                                }
                            }
                        ?>
                        <?php 
                            if($usuario['estadousu'] == 2){
                                ?><td><?= htmlspecialchars('Activo') ?></td>
                            <?php
                            }
                            else{
                                if($usuario['estadousu'] == 1){
                                    ?><td><?= htmlspecialchars('Inactivo') ?></td>
                                <?php
                                }
                            }
                        ?>
                        <td><a href="verusuario.php?id=<?= $usuario['id'] ?>"><button class="add-panel" title="Ver" onclick="openModalAgregar()"><i class="bi bi-eye"></i></button></a></td>
                        <td><a href="editarusuario.php?id=<?= $usuario['id'] ?>"><button class="add-panel" title="Editar" onclick="openModalAgregar()"><i class="bi bi-pencil-square"></i></button></a></a></td>
                
                        <td>
                        
                        <?php if($usuario['estadousu'] == true): ?>
                            <!-- ESTO DE ABAJO TIENE UN ONCLICK QUE LLEVA A MODAL -->
                        <a href="panelusuarios.php?id=<?= $usuario['id'] ?>&tipo=3" id="btn-eliminar" class="add-panel"><i class="bi bi-trash"></i></a>
                </a>

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

    <!-- Filtrando datos -->
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
        text: 'Está seguro de eliminar el usuario?',
        icon: 'warning',
        showDenyButton: true,
        confirmButtonText: 'Si',
        denyButtonText: 'No',
      })
      .then(res => {
        if (res.isConfirmed) 
        {
            window.location.href = url;
        }
      });
    });
  });
});

function openModalAgregar() 
{
    window.location.href = 'agregarusuario.php';
}
</script>

<!-- Funcion SweetAlert: Agregar, modificar, Eliminar-->
<script>
document.addEventListener('DOMContentLoaded', () => 
{
  //1. Traemos lo que definimos
  const p = new URLSearchParams(location.search);

  //2. Como lo definimos como "ok", procede a mostrar el mensaje
  if (p.get('usuarioagregado') === 'ok') //Para usuario agregado
  {
    Swal.fire({
      position: 'top',
      icon: 'success',
      title: 'Usuario agregado con éxito',
      showConfirmButton: false,
      timer: 1500
    });
  //3. Al refrescar la página, no volverá a salir el mensaje
    history.replaceState({}, '', location.pathname);
  }
  if (p.get('usuariomodificado') === 'ok') //Para usuario modificado
  {
    Swal.fire({
      position: 'top',
      icon: 'success',
      title: 'Usuario modificado con éxito',
      showConfirmButton: false,
      timer: 1500
    });
    history.replaceState({},'', location.pathname);
  } 
  if (p.get('usuarioeliminado') == 'ok') //Para usuario eliminado
  {
    Swal.fire({
        position: 'top',
        icon:  'success',
        title: 'Usuario eliminado con éxito',
        showConfirmButton: false,
        timer: 1500
    });
    history.replaceState({},'', location.pathname);
  }
  

});

</script>


</body>
</html>
