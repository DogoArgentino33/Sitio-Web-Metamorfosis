<?php include('auth.php'); include('conexion.php');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "ID de usuario no válido.";
    exit;
}

$id = intval($_GET['id']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom_usu = $_POST['nom_usu'];
    $correo = $_POST['correo'];
    $telefono = $_POST['telefono'];
    $rol = $_POST['rol'];



    if (isset($_FILES['nueva_imagen']) && $_FILES['nueva_imagen']['error'] === UPLOAD_ERR_OK) {
        $tmp_name = $_FILES['nueva_imagen']['tmp_name'];
        $nombre_archivo = basename($_FILES['nueva_imagen']['name']);
        $destino = 'uploads/' . uniqid() . '_' . $nombre_archivo;

        if (move_uploaded_file($tmp_name, $destino)) {
            // Si se subió una nueva imagen, actualizamos también img_perfil
            $stmt = $conexion->prepare("UPDATE usuario SET nom_usu=?, correo=?, telefono=?, rol=?, img_perfil=? WHERE id=?");
            $stmt->bind_param("sssssi", $nom_usu, $correo, $telefono, $rol, $destino, $id);
        } else {
            echo "Error al mover la imagen.";
            exit;
        }
    } else {
        // Si no se subió imagen, dejamos img_perfil como está
        $stmt = $conexion->prepare("UPDATE usuario SET nom_usu=?, correo=?, telefono=?, rol=? WHERE id=?");
        $stmt->bind_param("ssssi", $nom_usu, $correo, $telefono, $rol, $id);
    }



    if ($stmt->execute()) {
        header("Location: panelusuarios.php?msg=Usuario actualizado");
        exit;
    } else {
        echo "Error al actualizar el usuario.";
    }
}

$stmt = $conexion->prepare("SELECT id, nom_usu, img_perfil, correo, telefono, id_persona, rol, estadousu FROM usuario WHERE id = ?");
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
                <img src="<?= htmlspecialchars($usuario['img_perfil']) ?>" alt="Foto de perfil"
                style="width: 6vw; height: 6vw; object-fit: cover; border-radius: 50%; border: 0.25vw solid white;">

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
        </select><br><br>

        <button type="submit">Guardar cambios</button>
        <a href="panelusuarios.php"><button type="button">Cancelar</button></a>
    </form>
    </section>
</body>
</html>
