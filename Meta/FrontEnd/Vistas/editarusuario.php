<?php include('auth.php'); include('conexion.php');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "ID de usuario no válido.";
    exit;
}

$id = intval($_GET['id']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_GET['id']; // o $_POST['id'], según cómo lo llames
    $sql_usuario = "SELECT * FROM usuario WHERE id = $id";
    $result_usuario = mysqli_query($conexion, $sql_usuario);
    $usuario = mysqli_fetch_assoc($result_usuario);
    $nom_usu = $_POST['nom_usu'];
    $correo = $_POST['correo'];
    $telefono = $_POST['telefono'];
    $rol = $_POST['rol'];
    $estadousu = $_POST['estadousu']; 
    $fechamod = date('Y-m-d H:i:s'); // Fecha de modificación
    $usumod = $_SESSION['nom_usu'] ?? 'sistema'; // Usuario que realiza la modificación


    if (isset($_FILES['nueva_imagen']) && $_FILES['nueva_imagen']['error'] === UPLOAD_ERR_OK) {
        $tmp_name = $_FILES['nueva_imagen']['tmp_name'];
        $nombre_archivo = basename($_FILES['nueva_imagen']['name']);
        $destino = 'uploads/usuario/' . uniqid() . '_' . $nombre_archivo;

        if (move_uploaded_file($tmp_name, $destino)) {
            // Si se subió una nueva imagen, actualizamos también img_perfil
            $stmt = $conexion->prepare("UPDATE usuario SET nom_usu=?, correo=?, telefono=?, rol=?, img_perfil=?, fechamod=?, usumod=? WHERE id=?");
            $stmt->bind_param("sssssssi", $nom_usu, $correo, $telefono, $rol, $destino, $fechamod, $usumod, $id);
        } else {
            echo "Error al mover la imagen.";
            exit;
        }
    } else {
        // Si no se subió imagen, dejamos img_perfil como está
        $stmt = $conexion->prepare("UPDATE usuario SET nom_usu=?, correo=?, telefono=?, rol=?, fechamod=?, usumod=? WHERE id=?");
        $stmt->bind_param("ssssssi", $nom_usu, $correo, $telefono, $rol, $fechamod, $usumod, $id);
    }



    if ($stmt->execute()) 
    {
        header("Location: panelusuarios.php?usuariomodificado=ok");
        exit;
    } else {
        echo "Error al actualizar el usuario.";
    }
}

$stmt = $conexion->prepare("SELECT id, nom_usu, img_perfil, correo, telefono, id_persona, rol, estadousu, fechamod, usumod FROM usuario WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    echo "Usuario no encontrado.";
    exit;
}

$usuario = $resultado->fetch_assoc();
$ruta_imagen = $usuario['img_perfil']; // Mantener actual por defecto
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Usuario</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../Estilos/index.css">
    <link rel="stylesheet" href="../Estilos/editarusuario.css">
    <!-- Script de SweetAlert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <h1>Editar Usuario</h1>

    <section class="dni-card">
        
    <form method="POST" enctype="multipart/form-data">
        <section class="perfil-editar">
            <div class="foto-perfil-editar">
                <img src="<?= htmlspecialchars($usuario['img_perfil']) ?>" alt="Foto de perfil" id="foto-perfil"
                style="width: 8vw; height: 8vw; object-fit: cover; border-radius: 50%; border: 0.25vw solid white;">
                <img id="preview-img" class="preview" style="display: none; width: 8vw; height: 8vw; object-fit: cover; border-radius: 50%; border: 0.25vw solid gray;">


            </div>
                <div class="input-imagen">
                    <label for="nueva_imagen">Cambiar foto de perfil:</label>
                    <input type="file" name="nueva_imagen" id="nueva_imagen" accept="image/*">

            </div>
        </section>


        <label>Nombre de Usuario:</label>
        <input type="text" name="nom_usu" value="<?= htmlspecialchars($usuario['nom_usu']) ?>" required><br>

        <label>Correo:</label>
        <input type="email" name="correo" value="<?= htmlspecialchars($usuario['correo']) ?>" required><br>

        <label>Teléfono:</label>
        <input type="text" name="telefono" value="<?= htmlspecialchars($usuario['telefono']) ?>" required><br>

        <label>Rol:</label>
        <select name="rol" required>
            <option value="4" <?= $usuario['rol'] === 'admin' ? 'selected' : '' ?>>Administrador</option>
            <option value="1" <?= $usuario['rol'] === 'gerente' ? 'selected' : '' ?>>Gerente</option>
            <option value="2" <?= $usuario['rol'] === 'empleado' ? 'selected' : '' ?>>Empleado</option>
            <option value="0" <?= $usuario['rol'] === 'usuario' ? 'selected' : '' ?>>Usuario</option>
            <!-- Agregá más roles si tenés -->
        </select><br>

        <label for="estadosusu">Estado del Usuario</label>
        <input type="text" name="estadousu" id="estadousu" value="<?php 
                            if($usuario['estadousu'] == 2){
                                ?><?= htmlspecialchars('Activo') ?>
                            <?php
                            }
                            else{
                                if($usuario['estadousu'] == 1){
                                    ?><?= htmlspecialchars('Inactivo') ?>
                                <?php
                                }
                            }
                        ?>" readonly>

        <label for="fechamod">Última Modificacion</label>
        <input type="text" name="fechamod" id="fechamod" value="<?= htmlspecialchars($usuario['fechamod']) ?>" readonly><br>

        <label for="usumod">Usuario que realizó la modificacion</label>
        <input type="text" name="usumod" id="usumod" value="<?= htmlspecialchars($usuario['usumod']) ?>" readonly><br>

        <button type="submit">Guardar cambios</button>
        <a href="panelusuarios.php"><button type="button">Cancelar</button></a>
    </form>
    </section>


    <script>
        document.getElementById('nueva_imagen').addEventListener('change', function(event) {
        const fotoperfil = document.getElementById('foto-perfil');
        const preview = document.getElementById('preview-img');
        const file = event.target.files[0];

        if (file) {
            const reader = new FileReader();

            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
                fotoperfil.style.display = 'none'; // Ocultar la imagen original
            };

            reader.readAsDataURL(file);
        } else {
            fotoperfil.style.display = 'block'; // Mostrar la imagen original si no hay archivo
            preview.style.display = 'none';
            preview.src = '';
        }
        });
    </script>


</body>
</html>
