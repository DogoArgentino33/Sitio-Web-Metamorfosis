<?php include('auth.php'); include('conexion.php');

//Verificando si la cuenta no es rol gerente
if (isset($_SESSION['rol']) && $_SESSION['rol'] != 1 && $_SESSION['rol'] != 4){
    header("Location: index.php"); 
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "ID de usuario no válido.";
    exit;
}

$id = intval($_GET['id']);

$stmt = $conexion->prepare("SELECT id, nom_usu, img_perfil, correo, telefono, id_persona, rol, estadousu, fechamod, usumod FROM usuario WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    echo "Usuario no encontrado.";
    exit;
}

$usuario = $resultado->fetch_assoc();
$ruta_imagen_actual = $usuario['img_perfil'];
$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom_usu    = $_POST['nom_usu'];
    $correo     = $_POST['correo'];
    $telefono   = $_POST['telefono'];
    $rol        = $_POST['rol'];
    $estadousu  = $_POST['estadousu']; 
    $fechamod   = date('Y-m-d H:i:s');
    $usumod     = $_SESSION['nom_usu'] ?? 'sistema';

    $ruta_img_final = $ruta_imagen_actual;

    // Procesamiento de imagen si se sube una nueva
    if (isset($_FILES['nueva_imagen']) && $_FILES['nueva_imagen']['error'] === UPLOAD_ERR_OK) {
        $foto = $_FILES['nueva_imagen'];
        $permitidos = ['image/jpeg', 'image/png'];
        $tam_max = 4 * 1024 * 1024; // 4 MB

        if (!in_array($foto['type'], $permitidos)) {
            $errores[] = "El formato de imagen no es válido. Solo se permiten JPG y PNG.";
        } elseif ($foto['size'] > $tam_max) {
            $errores[] = "La imagen no debe superar los 4MB.";
        } else {
            $tmp_name = $foto['tmp_name'];
            list($ancho_orig, $alto_orig) = getimagesize($tmp_name);
            $ancho_max = 1280;
            $alto_max = 1280;

            // Cálculo proporcional
            $ratio_orig = $ancho_orig / $alto_orig;
            $ratio_dest = $ancho_max / $alto_max;

            if ($ratio_orig > $ratio_dest) {
                $ancho_final = $ancho_max;
                $alto_final = intval($ancho_max / $ratio_orig);
            } else {
                $alto_final = $alto_max;
                $ancho_final = intval($alto_max * $ratio_orig);
            }

            // Lienzo blanco base
            $imagen_final = imagecreatetruecolor($ancho_max, $alto_max);
            $blanco = imagecolorallocate($imagen_final, 255, 255, 255);
            imagefill($imagen_final, 0, 0, $blanco);

            // Crear imagen desde archivo
            $origen = null;
            if ($foto['type'] === 'image/jpeg') {
                $origen = imagecreatefromjpeg($tmp_name);
            } elseif ($foto['type'] === 'image/png') {
                $origen = imagecreatefrompng($tmp_name);
            }

            if ($origen) {
                $x = intval(($ancho_max - $ancho_final) / 2);
                $y = intval(($alto_max - $alto_final) / 2);

                imagecopyresampled($imagen_final, $origen, $x, $y, 0, 0, $ancho_final, $alto_final, $ancho_orig, $alto_orig);

                $nombre_img = uniqid('usuario_') . '.jpg';
                $ruta_destino = 'uploads/usuario/' . $nombre_img;

                if (imagejpeg($imagen_final, $ruta_destino, 90)) {
                    $ruta_img_final = $ruta_destino;
                } else {
                    $errores[] = "No se pudo guardar la imagen procesada.";
                }

                imagedestroy($origen);
                imagedestroy($imagen_final);
            } else {
                $errores[] = "Error al procesar la imagen.";
            }
        }
    }

    // Si no hay errores, actualizamos
    if (empty($errores)) {
        if ($ruta_img_final !== $ruta_imagen_actual) {
            $stmt = $conexion->prepare("UPDATE usuario SET nom_usu=?, correo=?, telefono=?, rol=?, img_perfil=?, estadousu=?, fechamod=?, usumod=? WHERE id=?");
            $stmt->bind_param("ssssssssi", $nom_usu, $correo, $telefono, $rol, $ruta_img_final, $estadousu, $fechamod, $usumod, $id);
        } else {
            $stmt = $conexion->prepare("UPDATE usuario SET nom_usu=?, correo=?, telefono=?, rol=?, estadousu=?, fechamod=?, usumod=? WHERE id=?");
            $stmt->bind_param("sssssssi", $nom_usu, $correo, $telefono, $rol, $estadousu, $fechamod, $usumod, $id);
        }

        if ($stmt->execute()) {
            header("Location: panelusuarios.php?usuariomodificado=ok");
            exit;
        } else {
            echo "Error al actualizar el usuario.";
        }
    } else {
        foreach ($errores as $e) {
            echo "<p style='color:red;'>$e</p>";
        }
    }
}
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

        <label for="estadousu">Estado del Usuario</label>
        <select name="estadousu" id="estadousu" required>
            <option value="2" <?= $usuario['estadousu'] == 2 ? 'selected' : '' ?>>Activo</option>
            <option value="1" <?= $usuario['estadousu'] == 1 ? 'selected' : '' ?>>Inactivo</option>
        </select>


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
