<?php
include('auth.php');
include('conexion.php'); // Ajusta la ruta si es necesario

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "ID de usuario no válido.";
    exit;
}

$id = intval($_GET['id']);

$stmt = $conexion->prepare("SELECT id, nom_usu, img_perfil, correo, telefono, id_persona, rol, estadousu FROM usuario WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    echo "Usuario no encontrado.";
    exit;
}

$usuario = $resultado->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Información del Usuario</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../Estilos/paneles.css">
    <link rel="stylesheet" href="../Estilos/index.css">
    <link rel="stylesheet" href="../Estilos/modales.css">
    <link rel="stylesheet" href="../Estilos/estilousuario.css">
</head>
<body>
    <h1>Información del Usuario</h1>

    <section class="dni-card">
    <div class="dni-img">
        <img class="img-perfil" src="<?= htmlspecialchars($usuario['img_perfil']) ?>" alt="Imagen de perfil" onclick="mostrarModal(this)">

    </div>
    <div class="dni-info">
        <p><strong>ID:</strong> <?= htmlspecialchars($usuario['id']) ?></p>
        <p><strong>Nombre de Usuario:</strong> <?= htmlspecialchars($usuario['nom_usu']) ?></p>
        <p><strong>Correo:</strong> <?= htmlspecialchars($usuario['correo']) ?></p>
        <p><strong>Teléfono:</strong> <?= htmlspecialchars($usuario['telefono']) ?></p>
        <p><strong>ID Persona:</strong> <?= htmlspecialchars($usuario['id_persona']) ?></p>
        <p><strong>Rol:</strong> <?= htmlspecialchars($usuario['rol']) ?></p>
        <p><strong>Estado Usuario:</strong> <?= htmlspecialchars($usuario['estadousu']) ?></p>
        <br>
        <a href="panelusuarios.php"><button type="button" class="boton">Volver al panel</button></a>
    </div>
</section>
<div id="modalImagen" class="modal-imagen" onclick="cerrarModal()">
    <span class="cerrar">&times;</span>
    <img class="modal-contenido" id="imagenAmpliada">
</div>

<script>
function mostrarModal(imagen) {
    const modal = document.getElementById("modalImagen");
    const imgAmpliada = document.getElementById("imagenAmpliada");
    modal.style.display = "block";
    imgAmpliada.src = imagen.src;
}

function cerrarModal() {
    document.getElementById("modalImagen").style.display = "none";
}
</script>

</body>
</html>