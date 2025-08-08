<?php include('auth.php'); include('conexion.php');

//Verificando si la cuenta no es rol gerente
if (isset($_SESSION['rol']) && $_SESSION['rol'] != 1 && $_SESSION['rol'] != 4){
    header("Location: index.php"); 
    exit;
}

//Operación de eliminar usuario
if (isset($_GET['id']) && isset($_GET['tipo']) && $_GET['tipo'] == 3) {
    $idEliminar = intval($_GET['id']);
    
    // 1. Obtener datos actuales del usuario
    $stmtImg = $conexion->prepare("SELECT img_perfil FROM usuario WHERE id = ?");
    $stmtImg->bind_param("i", $idEliminar);
    $stmtImg->execute();
    $stmtImg->bind_result($imgPerfilActual);
    $stmtImg->fetch();
    $stmtImg->close();

    // 2. Eliminar la imagen física si existe y no es la predeterminada
    if (!empty($imgPerfilActual) && $imgPerfilActual !== 'default.png') {
        // Rutas posibles para buscar la imagen
        $rutasBusqueda = [
            __DIR__ . '/uploads/usuario/' . $imgPerfilActual,
            __DIR__ . '/../uploads/usuario/' . $imgPerfilActual,
            $_SERVER['DOCUMENT_ROOT'] . '/Sitio-Web-Metamorfosis/Meta/FrontEnd/Vistas/uploads/usuario/' . $imgPerfilActual
        ];
        
        $imagenEncontrada = false;
        
        foreach ($rutasBusqueda as $rutaImagen) {
            if (file_exists($rutaImagen)) {
                // Intento de eliminación con reintentos
                $intentos = 0;
                $maxIntentos = 3;
                
                while ($intentos < $maxIntentos) {
                    if (unlink($rutaImagen)) {
                        $imagenEncontrada = true;
                        $_SESSION['mensajes_eliminacion'][] = [
                            'tipo' => 'success', 
                            'mensaje' => 'Imagen física eliminada correctamente'
                        ];
                        break 2; // Sale de ambos bucles
                    } else {
                        $intentos++;
                        usleep(500000); // Espera 0.5 segundos entre intentos
                    }
                }
                
                if (!$imagenEncontrada) {
                    error_log("Fallo al eliminar imagen después de $maxIntentos intentos: $rutaImagen");
                }
            }
        }
        
        if (!$imagenEncontrada) {
            $_SESSION['mensajes_eliminacion'][] = [
                'tipo' => 'warning', 
                'mensaje' => 'La imagen no pudo ser eliminada del servidor'
            ];
        }
    }

    // 3. Actualizar la base de datos (eliminación lógica + reset de imagen)
    $conexion->begin_transaction();
    
    try {
        // Primero actualizamos la imagen a default.png
        $stmtUpdate = $conexion->prepare("UPDATE usuario SET img_perfil = 'default.png' WHERE id = ?");
        $stmtUpdate->bind_param("i", $idEliminar);
        $stmtUpdate->execute();
        
        // Luego marcamos como eliminado
        $stmtEliminar = $conexion->prepare("UPDATE usuario SET eliminado = 1 WHERE id = ?");
        $stmtEliminar->bind_param("i", $idEliminar);
        $stmtEliminar->execute();
        
        $conexion->commit();
        
        $_SESSION['mensajes_eliminacion'][] = [
            'tipo' => 'success', 
            'mensaje' => 'Usuario eliminado correctamente'
        ];
    } catch (Exception $e) {
        $conexion->rollback();
        $_SESSION['mensajes_eliminacion'][] = [
            'tipo' => 'error', 
            'mensaje' => 'Error en la transacción: ' . $e->getMessage()
        ];
    }
    
    header("Location: panelusuarios.php?usuarioeliminado=ok");
    exit;
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
    <link rel="stylesheet" href="../Estilos/panelgeneral.css">
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

                        <?php if (isset($_SESSION['rol']) and $_SESSION['rol'] == 1): ?>
                        <!-- Para gerente -->
                         <th>MODIFICAR</th>
                         <th>ELIMINAR</th>
                        <?php endif; ?>
                       
                    </tr>
                </thead>
                <tbody>
                <?php
                    $por_pagina = 10; // cantidad de registros por página
                    $pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
                    $inicio = ($pagina > 1) ? ($pagina * $por_pagina) - $por_pagina : 0;

                    $total_stmt = $conexion->prepare("SELECT COUNT(*) as total FROM usuario WHERE eliminado = 0");
                    $total_stmt->execute();
                    $total_resultado = $total_stmt->get_result()->fetch_assoc();
                    $total_registros = $total_resultado['total'];
                    $total_paginas = ceil($total_registros / $por_pagina);

                    $stmt = $conexion->prepare("SELECT id, nom_usu, img_perfil, correo, telefono, id_persona, rol, estadousu FROM usuario WHERE eliminado = 0 ORDER BY id LIMIT ?, ?");
                    $stmt->bind_param("ii", $inicio, $por_pagina);
                    $stmt->execute(); 
                    $result = $stmt->get_result();
    
                if($result->num_rows > 0) {
                    while($usuario = $result->fetch_assoc()) 
                    {
                        ?>

                        <tr>
                        <td><?= htmlspecialchars($usuario['nom_usu']) ?></td>
                        <td>
                            <img src="<?= htmlspecialchars($usuario['img_perfil']) ?>" alt="Perfil" width="60" height="60" style="object-fit: cover; border-radius: 50%;">
                        </td>

                        
                        <td><?= htmlspecialchars($usuario['correo']) ?></td>
                        <td><?= htmlspecialchars($usuario['telefono']) ?></td>
                        
                        <!-- Determinando Roles de usuario -->
                        <td>
                            <?php
                                switch ($usuario['rol']) {
                                    case 0: echo 'Usuario'; break;
                                    case 1: echo 'Gerente'; break;
                                    case 2: echo 'Empleado'; break;
                                    case 4: echo 'Administrador'; break;
                                    default: echo 'Desconocido'; break;
                                }
                            ?>
                        </td>

                        <td>
                            <?= $usuario['estadousu'] == 2 ? 'Activo' : 'Inactivo'; ?>
                        </td>

                        <td><a href="verusuario.php?id=<?= $usuario['id'] ?>"><button class="ver-btn" title="Ver" onclick="openModalAgregar()"><i class="bi bi-eye"></i></button></a></td>
                        
                        
                        <?php if (isset($_SESSION['rol']) and $_SESSION['rol'] == 1): ?>
                        <!-- Para gerente -->
                            <td><a href="editarusuario.php?id=<?= $usuario['id'] ?>"><button class="editar-btn" title="Editar" onclick="openModalAgregar()"><i class="bi bi-pencil-square"></i></button></a></a></td>
                         
                        <td>
                            <a href="panelusuarios.php?id=<?= $usuario['id'] ?>&tipo=3" id="btn-eliminar">
                                <i class="bi bi-trash"></i>
                            </a>
                        </td>


                        <?php endif; ?>
                        
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
            <?php if($pagina > 1): ?>
                <li><a href="?pagina=<?= $pagina - 1 ?>">&laquo;</a></li>
            <?php endif; ?>

            <?php for($i = 1; $i <= $total_paginas; $i++): ?>
                <li <?= ($i == $pagina) ? 'class="active"' : '' ?>>
                    <a href="?pagina=<?= $i ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>

            <?php if($pagina < $total_paginas): ?>
                <li><a href="?pagina=<?= $pagina + 1 ?>">&raquo;</a></li>
            <?php endif; ?>
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