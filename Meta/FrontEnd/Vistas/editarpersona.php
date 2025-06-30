<?php include('auth.php'); include('conexion.php');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) { echo "ID de persona no válido."; exit;}

$id = intval($_GET['id']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pais = $_POST['pais'];
    $provincia = $_POST['provincia'];
    $calle = $_POST['calle'];
    $altura = intval($_POST['altura']);
    $barrio = $_POST['barrio'];
    $departamento = $_POST['departamento'];
    $municipio = $_POST['municipio'];
    $localidad = $_POST['localidad'];
    $fechamod = date('Y-m-d H:i:s');
    $usumod = $_SESSION['usuario'] ?? 'sistema';

    // Manejo de imagen
    if (isset($_FILES['nueva_imagen']) && $_FILES['nueva_imagen']['error'] === UPLOAD_ERR_OK) {
        $tmp_name = $_FILES['nueva_imagen']['tmp_name'];
        $nombre_archivo = basename($_FILES['nueva_imagen']['name']);
        $destino = 'uploads/' . uniqid() . '_' . $nombre_archivo;

        if (move_uploaded_file($tmp_name, $destino)) {
            $stmt = $conexion->prepare("UPDATE persona SET pais=?, provincia=?, img=?, calle=?, altura=?, barrio=?, departamento=?, municipio=?, localidad=?, fechamod=?, usumod=? WHERE id=?");
            $stmt->bind_param("ssssissssssi", 
                $pais, $provincia, $destino, $calle, $altura, 
                $barrio, $departamento, $municipio, $localidad, 
                $fechamod, $usumod, $id);
        } else {
            echo "Error al subir la imagen.";
            exit;
        }
    } else {
            $stmt = $conexion->prepare("UPDATE persona SET pais=?, provincia=?, img=?, calle=?, altura=?, barrio=?, departamento=?, municipio=?, localidad=?, fechamod=?, usumod=? WHERE id=?");
            $stmt->bind_param("ssssissssssi", 
                $pais, $provincia, $destino, $calle, $altura, 
                $barrio, $departamento, $municipio, $localidad, 
                $fechamod, $usumod, $id);
    }

    if ($stmt->execute()) {
        header("Location: panelpersonas.php?msg=Persona actualizada");
        exit;
    } else {
        echo "Error al actualizar la persona.";
    }
}


$stmt = $conexion->prepare("SELECT * FROM persona WHERE id = ?");

$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    echo "Usuario no encontrado.";
    exit;
}

$persona = $resultado->fetch_assoc();
$ruta_imagen = $persona['img']; // imagen actual

?>


<!-- Cuerpo de la página -->
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Persona</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../Estilos/index.css">
    <link rel="stylesheet" href="../Estilos/editarusuario.css">
</head>
<body>
    <h1>Editar Persona</h1>

    <section class="dni-card">
        
    <form method="POST" enctype="multipart/form-data">
        <section class="perfil-editar">
            <div class="foto-perfil-editar">
                <img src="<?= htmlspecialchars($persona['img']) ?>" alt="Foto de perfil"
                style="width: 6vw; height: 6vw; object-fit: cover; border-radius: 50%; border: 0.25vw solid white;">

            </div>
            <div class="input-imagen">
                <label for="nueva_imagen">Cambiar foto de perfil:</label>
                <input type="file" name="nueva_imagen" id="nueva_imagen" accept="image/*">
            </div>
        </section>


        <label>País:</label>
        <input type="text" name="pais" value="<?= htmlspecialchars($persona['pais']) ?>" required><br>

        <label>Provincia:</label>
        <input type="text" name="provincia" value="<?= htmlspecialchars($persona['provincia']) ?>" required><br>

        <label>Departamento:</label>
        <input type="text" name="departamento" value="<?= htmlspecialchars($persona['departamento']) ?>" required><br>

        <label>Municipio:</label>
        <input type="text" name="municipio" value="<?= htmlspecialchars($persona['municipio']) ?>" required><br>

        <label>Localidad:</label>
        <input type="text" name="localidad" value="<?= htmlspecialchars($persona['localidad']) ?>" required><br>

        <label>Barrio:</label>
        <input type="text" name="barrio" value="<?= htmlspecialchars($persona['barrio']) ?>" required><br>

        <label>Calle:</label>
        <input type="text" name="calle" value="<?= htmlspecialchars($persona['calle']) ?>" required><br>

        <label>Altura:</label>
        <input type="text" name="altura" value="<?= htmlspecialchars($persona['altura']) ?>" required><br>

        <button type="submit">Guardar cambios</button>
        <a href="panelpersonas.php"><button type="button">Cancelar</button></a>
    </form>
    </section>
</body>
</html>