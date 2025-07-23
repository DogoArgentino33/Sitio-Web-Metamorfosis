<?php
include('auth.php');
include('conexion.php'); 

if (isset($_GET['id']) && isset($_GET['tipo']) && $_GET['tipo'] == 3) {
    $idEliminar = intval($_GET['id']);
    
    // Primero eliminar de la tabla `usuario`
    $stmt1 = $conexion->prepare("DELETE FROM usuario WHERE id_persona = ?");
    $stmt1->bind_param("i", $idEliminar);
    $stmt1->execute();

    // Luego eliminar de la tabla `persona`
    $stmt2 = $conexion->prepare("DELETE FROM persona WHERE id = ?");
    $stmt2->bind_param("i", $idEliminar);

    if ($stmt2->execute()) {
        // Redirigir para evitar reenvíos y actualizar la tabla
        header("Location: panelpersonas.php?personaeliminada=ok");
        exit;
    } else {
        echo "<script>alert('Error al eliminar la persona');</script>";
    }
}
?>

<!-- CUERPO DE LA PAGINA -->
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
<body>
    <?php include('cabecera.php'); ?>
    <main>
        <section class="nav-route">
            <a href="index.php">Inicio / </a>
            <a href="gerente.php">Gerente /</a>
            <a>Panel de Personas</a>
        </section>
        <h1><a href="../Vistas/gerente.php" style="padding-right: 3%;" title="volver"><i class="bi bi-arrow-left-circle"></i></a>Panel Administrador de Personas</h1>
        
        <section class="container-table" id="user">
            <section class="nav-table">
                <input type="text" id="search-panel" placeholder="Buscar Personas..." onkeyup="filtrarTabla('user')">
                <div class="btn-add-container">
                    <button class="add-panel" id="agregarpersona" title="Agregar" onclick="openModalAgregar()"><i class="bi bi-person-plus-fill"></i></button>
                    
                    
                </div>
            </section>
            <table>
                <thead>
                    <tr>
                        <th>IMAGEN DE PERFIL</th>
                        <th>NOMBRE</th>
                        <th>APELLIDO</th>
                        <th>DNI</th>
                         <th>GENERO</th>
                        <th>VER</th>
                        <th>MODIFICAR</th>
                        <th>ELIMINAR</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                    $por_pagina = 10; // cantidad de registros por página
                    $pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
                    $inicio = ($pagina > 1) ? ($pagina * $por_pagina) - $por_pagina : 0;

                    $total_stmt = $conexion->prepare("SELECT COUNT(*) as total FROM persona");
                    $total_stmt->execute();
                    $total_resultado = $total_stmt->get_result()->fetch_assoc();
                    $total_registros = $total_resultado['total'];
                    $total_paginas = ceil($total_registros / $por_pagina);

                    $stmt = $conexion->prepare("SELECT img, id, nombre, apellido, dni, genero FROM persona ORDER BY id LIMIT ?, ?");
                    $stmt->bind_param("ii", $inicio, $por_pagina);
                    $stmt->execute(); 
                    $result = $stmt->get_result();
                        
                if($result->num_rows > 0) {
                    while($persona = $result->fetch_assoc()) 
                    {
                        ?>

                        <tr>
                        <td>
                        <img src="<?= htmlspecialchars($persona['img']) ?>" alt="Perfil" width="60" height="60" style="object-fit: cover; border-radius: 50%;">
                        </td>

                        <td><?= htmlspecialchars($persona['nombre']) ?></td>
                        <td><?= htmlspecialchars($persona['apellido']) ?></td>
                        <td><?= htmlspecialchars($persona['dni']) ?></td>
                        <td><?= htmlspecialchars($persona['genero']) ?></td>
                    
                        <td><a href="verpersona.php?id=<?= $persona['id'] ?>"><button class="add-panel" title="Ver" onclick="openModalAgregar()"><i class="bi bi-eye"></i></button></a></td>
                        <td><a href="editarpersona.php?id=<?= $persona['id'] ?>"><button class="add-panel" title="Editar" onclick="openModalAgregar()"><i class="bi bi-pencil-square"></i></button></a></a></td>
                

                        <td>
                        
                        <?php if($usuario['estadousu'] == true): ?>
                        <a href="panelpersonas.php?id=<?= $persona['id'] ?>&tipo=3" id="btn-eliminar" class="add-panel"><i class="bi bi-trash"></i></a>
                </a>

                        <?php else: ?>
                        <a href="panelpersonas.php?id=<?= $persona['id'] ?>&tipo=4">Activar</a>
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
    //Boton Eliminar
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
                text: 'Está seguro de eliminar la persona?',
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

<!-- Funcion SweetAlert: modificar, Eliminar-->
<script>
document.addEventListener('DOMContentLoaded', () => 
{
  //1. Traemos lo que definimos
  const p = new URLSearchParams(location.search);

  //2. Como lo definimos como "ok", procede a mostrar el mensaje
  if (p.get('personamodificada') === 'ok') //Para persona modificada
  {
    Swal.fire({
      position: 'top',
      icon: 'success',
      title: 'Persona modificada con éxito',
      showConfirmButton: false,
      timer: 1500
    });
    history.replaceState({},'', location.pathname);
  } 
  if (p.get('personaeliminada') == 'ok') //Para persona eliminada
  {
    Swal.fire({
        position: 'top',
        icon:  'success',
        title: 'Persona eliminada con éxito',
        showConfirmButton: false,
        timer: 1500
    });
    history.replaceState({},'', location.pathname);
  }
});
</script>

</body>
</html>
