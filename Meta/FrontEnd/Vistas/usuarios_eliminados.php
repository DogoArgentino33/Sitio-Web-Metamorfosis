<?php
include('auth.php');
include('conexion.php');

// Verificar rol
if (isset($_SESSION['rol']) && $_SESSION['rol'] != 1 && $_SESSION['rol'] != 4) {
    header("Location: index.php");
    exit;
}

// Restaurar usuario (desde JS con fetch POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $idRestaurar = intval($_POST['id']);

    $stmt = $conexion->prepare("UPDATE usuario SET eliminado = 0 WHERE id = ?");
    $stmt->bind_param("i", $idRestaurar);

    if ($stmt->execute()) {
        echo "ok";
    } else {
        echo "error";
    }

    exit;
}

// Eliminar definitivamente usuario (tipo 3)
if (isset($_GET['id']) && isset($_GET['tipo']) && $_GET['tipo'] == 3) {
    $idEliminar = intval($_GET['id']);

    // Eliminación definitiva
    $stmtEliminar = $conexion->prepare("DELETE FROM usuario WHERE id = ?");
    $stmtEliminar->bind_param("i", $idEliminar);

    if ($stmtEliminar->execute()) {
        $_SESSION['mensajes_eliminacion'][] = [
            'tipo' => 'success',
            'mensaje' => 'Usuario eliminado definitivamente.'
        ];
    } else {
        $_SESSION['mensajes_eliminacion'][] = [
            'tipo' => 'error',
            'mensaje' => 'Error al eliminar el usuario.'
        ];
    }

    header("Location: usuarios_eliminados.php");
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

        <style>
        #btn-productos-eliminados {
            background-color: #d12e3b;
            color: white;
            border: none;
            padding: 10px 14px;
            border-radius: 5px;
            font-size: 18px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        #btn-productos-eliminados:hover {
            background-color: #a1222d;
        }
    </style>

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
        <h1><a href="../Vistas/panelusuarios.php" style="padding-right: 3%;" title="volver"><i class="bi bi-arrow-left-circle"></i></a>Panel Administrador de Usuarios Eliminados</h1>
        
        <section class="container-table" id="product">
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

                        <?php if (isset($_SESSION['rol']) and $_SESSION['rol'] == 1): ?>
                        <!-- Para gerente -->
                         <th>RESTAURAR</th>
                         <th>ELIMINAR</th>
                        <?php endif; ?>
                       
                    </tr>
                </thead>
                <tbody>
                <?php
                    $por_pagina = 10; // cantidad de registros por página
                    $pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
                    $inicio = ($pagina > 1) ? ($pagina * $por_pagina) - $por_pagina : 0;

                    $total_stmt = $conexion->prepare("SELECT COUNT(*) as total FROM usuario WHERE eliminado = 1");
                    $total_stmt->execute();
                    $total_resultado = $total_stmt->get_result()->fetch_assoc();
                    $total_registros = $total_resultado['total'];
                    $total_paginas = ceil($total_registros / $por_pagina);

                    $stmt = $conexion->prepare("SELECT id, nom_usu, img_perfil, correo, telefono, id_persona, rol, estadousu FROM usuario WHERE eliminado = 1 ORDER BY id LIMIT ?, ?");
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
                        
                                                <?php if (isset($_SESSION['rol']) and $_SESSION['rol'] == 1): ?>
                        <!-- Para gerente -->
                            <td>    
                                <button class="restaurar-btn" data-id="<?= $usuario['id'] ?>" title="Restaurar">
                                    <i class="bi bi-arrow-clockwise"></i>
                                </button>
                            </td>
                         
                        <td>
                            <button class="eliminar-definitivo-btn" data-id="<?= $usuario['id'] ?>" title="Eliminar definitivamente">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                        
                        <?php endif; ?>
                        </tr>
                    <?php
                    }
                    
                    } else {
                    ?>
                    
                    <tr>
                    
                    <td colspan="7">No hay usuarios eliminados</td>
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
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.eliminar-definitivo-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const idUsuario = this.getAttribute('data-id');

            Swal.fire({
                title: 'Advertencia',
                text: '¿Está seguro de eliminar el usuario? Se borrará para siempre (Eso es mucho tiempo)',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Redirigir con GET para eliminar definitivamente
                    window.location.href = `usuarios_eliminados.php?id=${idUsuario}&tipo=3`;
                }
            });
        });
    });
});


</script>


<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.restaurar-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const idUsuario = this.getAttribute('data-id');

            Swal.fire({
                title: '¿Estás seguro de que quieres restaurar este usuario?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Sí, restaurar',
                cancelButtonText: 'Cancelar',
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('usuarios_eliminados.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'id=' + encodeURIComponent(idUsuario)
                    })
                    .then(res => res.text())
                    .then(data => {
                        if (data.trim() === 'ok') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Usuario restaurado correctamente.',
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error al restaurar el usuario',
                                text: data
                            });
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error de conexión al restaurar el usuario.'
                        });
                    });
                }
            });
        });
    });
});
</script>


</body>
</html>