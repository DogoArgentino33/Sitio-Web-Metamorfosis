<?php include('auth.php'); include('conexion.php');

//Verificamos si existe
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) 
{
    echo "ID de usuario no válido.";
    exit;
}

$id = intval($_GET['id']);

//Realizamos la consulta
$stmt = $conexion->prepare("SELECT id, nom_usu, img_perfil, correo, telefono, id_persona, rol, estadousu FROM usuario WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) 
{
    echo "Usuario no encontrado.";
    exit;
}

$usuario = $resultado->fetch_assoc();
?>

<!-- Inicio del Html -->
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Información del Usuario</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../Estilos/verusuario.css">

     <!-- SweetAlert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        .swal2-container {
            z-index: 99999 !important;
        }
    </style>
</head>
<body>
    <h1>Información del Usuario</h1>
    <section class="dni-card">
        <div class="dni-img">
            <img class="img-perfil" src="<?= htmlspecialchars($usuario['img_perfil']) ?>" alt="Imagen de perfil" onclick="mostrarModal(this)">
        </div>
        <div class="dni-info">

        <!-- Datos del usuario -->
            <p><strong>Nombre de Usuario:</strong> <?= htmlspecialchars($usuario['nom_usu']) ?></p>
            <p><strong>Correo:</strong> <?= htmlspecialchars($usuario['correo']) ?></p>
            <p><strong>Teléfono:</strong> <?= htmlspecialchars($usuario['telefono']) ?></p>
            
        <!-- Rol del usuario, dependiendo del número, mostrará el respectivo nombre -->
            <p><strong>Rol:</strong> <?php 
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
                        ?></p>
        <!-- Lo mismo de arriba pero con estado -->
            <p><strong>Estado Usuario:</strong>                         <?php 
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
                        ?></p>
            <br>
            <a href="panelusuarios.php"><button type="button" class="boton">Volver al panel</button></a>
            <button type="button" class="boton-exportar" onclick="abrirModalExportar()">Exportar</button>
        </div>
    </section>

<!-- Imagen del usuario -->
<div id="modalImagen" class="modal-imagen" onclick="cerrarModal()">
    <span class="cerrar">&times;</span>
    <img class="modal-contenido" id="imagenAmpliada">
</div>

<!-- Modal de imagen -->
<script>
function mostrarModal(img)
{
    const modal = document.getElementById('modalImagen');
    document.getElementById('imagenAmpliada').src = img.src;
    modal.classList.add('show');
}

function cerrarModal()
{
  document.getElementById('modalImagen').classList.remove('show');
}
</script>

<!-- Función exportación -->
<section id="modalExportar" onclick="cerrarModalExportar()">
    <section class="modal-exportar-card" onclick="event.stopPropagation();">
        <section class="modal-exportar-content">
            <h2>Exportar Usuario</h2>
            <form id="formExportar" action="exportarusuario.php" method="POST" novalidate>

                <input type="hidden" name="id" value="<?= htmlspecialchars($usuario['id']) ?>">

                <fieldset>
                    <legend>Selecciona los atributos a exportar:</legend>
                    <label><input type="checkbox" name="atributos[]" value="nom_usu" checked> Nombre de Usuario</label>
                    <label><input type="checkbox" name="atributos[]" value="img_perfil"> Imagen de Perfil</label>
                    <label><input type="checkbox" name="atributos[]" value="correo"> Correo</label>
                    <label><input type="checkbox" name="atributos[]" value="telefono"> Teléfono</label>
                    <label><input type="checkbox" name="atributos[]" value="rol"> Rol</label>
                    <label><input type="checkbox" name="atributos[]" value="estadousu"> Estado Usuario</label>
                </fieldset>

                <fieldset>
                    <legend>Formato de exportación:</legend>
                    <label for="formato">Selecciona un formato:</label>
                    <select name="formato" id="formato" required>
                        <option value="" disabled selected>-- Seleccionar formato --</option>
                        <option value="pdf">PDF</option>
                        <option value="xls">XLS</option>
                        <option value="xlsx">XLSX</option>
                        <option value="csv">CSV</option>
                    </select>
                </fieldset>

                <nav class="modal-exportar-buttons" aria-label="Acciones del modal exportar">
                    <button type="button" class="boton" onclick="cerrarModalExportar()">Cancelar</button>
                    <button type="submit" class="boton-exportar">Exportar</button>
                </nav>
            </form>
        </section>
    </section>
</section>


<!-- Para el exportar -->
<script>
    function abrirModalExportar() 
    {
        const modal = document.getElementById('modalExportar');
        modal.style.display = 'flex';  // Aquí sí poner display:flex para mostrarlo
    }

    function cerrarModalExportar() 
    {
        const modal = document.getElementById('modalExportar');
        modal.style.display = 'none';  // Ocultarlo
    }
</script>

<script>
    document.getElementById('formExportar').addEventListener('submit', function(e) {
        const checkboxes = document.querySelectorAll('input[name="atributos[]"]:checked');
        const formato = document.getElementById('formato').value;

        if (checkboxes.length === 0) {
            e.preventDefault(); // Evita que se envíe el formulario

            Swal.fire({
                icon: 'warning',
                title: 'Campos requeridos',
                text: 'Debe seleccionar al menos un campo para exportar.',
                confirmButtonText: 'Entendido'
            });
        }
        else{
            if(formato === ""){
                e.preventDefault(); // Evita que se envíe el formulario

                Swal.fire({
                    icon: 'warning',
                    title: 'Campos requeridos',
                    text: 'Debe seleccionar un formato para exportar.',
                    confirmButtonText: 'Entendido'
                });

            }
        }
    });
</script>

</body>
</html>