<?php
include('auth.php');
include('conexion.php');

// Procesar asignación si se envía el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_usuario = intval($_POST['id_usuario']);
    $id_persona = intval($_POST['id_persona']);

    // Verificar que el usuario no tenga ya persona
    $verifica = $conexion->prepare("SELECT id FROM usuario WHERE id = ? AND id_persona IS NULL");
    $verifica->bind_param("i", $id_usuario);
    $verifica->execute();
    $verifica_result = $verifica->get_result();

    if ($verifica_result->num_rows > 0) {
        $update = $conexion->prepare("UPDATE usuario SET id_persona = ? WHERE id = ?");
        $update->bind_param("ii", $id_persona, $id_usuario);

        if ($update->execute()) {
            echo "<script>alert('Persona asignada correctamente.'); window.location.href='panelusuarios.php';</script>";
            exit;
        } else {
            echo "<script>alert('Error al asignar persona: " . $conexion->error . "');</script>";
        }
    } else {
        echo "<script>alert('El usuario ya tiene una persona asignada o no existe.');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Asignar Persona a Usuario</title>
    <link rel="stylesheet" href="../Estilos/index.css">
    <link rel="stylesheet" href="../Estilos/panelusuario.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <?php include('cabecera.php'); ?>
    <main>
        <section class="nav-route">
            <a href="index.php">Inicio / </a>
            <a href="gerente.php">Gerente /</a>
            <a>Asignar Persona</a>
        </section>
        <h1><a href="panelusuarios.php" style="padding-right: 3%;" title="volver"><i class="bi bi-arrow-left-circle"></i></a>Asignar Persona a Usuario</h1>

        <form method="POST" style="max-width: 600px; margin: auto; padding: 2rem; background-color: #f9f9f9; border-radius: 10px;">
            <label for="id_usuario">Usuario sin persona:</label><br>
            <select name="id_usuario" id="id_usuario" required>
                <option value="">-- Seleccionar usuario --</option>
                <?php
                $usuarios = $conexion->query("SELECT id, nom_usu FROM usuario WHERE id_persona IS NULL");
                while ($u = $usuarios->fetch_assoc()) {
                    echo "<option value='{$u['id']}'>{$u['nom_usu']}</option>";
                }
                ?>
            </select>
            <button type="button" class="btn" onclick="verUsuario()">Ver Usuario</button><br><br>
            
            <label for="id_persona">Persona sin usuario:</label><br>
            <select name="id_persona" id="id_persona" required>
                <option value="">-- Seleccionar persona --</option>
                <?php
                $personas = $conexion->query("SELECT p.id, p.nombre, p.apellido FROM persona p LEFT JOIN usuario u ON p.id = u.id_persona WHERE u.id_persona IS NULL");
                while ($p = $personas->fetch_assoc()) {
                    echo "<option value='{$p['id']}'>{$p['nombre']} {$p['apellido']}</option>";
                }
                ?>
            </select>
            <button type="button" class="btn" onclick="verPersona()">Ver Persona</button><br><br>

            <button type="submit" class="btn">Asignar</button>
        </form>
    </main>

    <script>
        function verUsuario() {
            const select = document.getElementById('id_usuario');
            const userId = select.value;
            if (userId) {
                window.location.href = `verusuario.php?id=${userId}`;
            } else {
                Swal.fire("Por favor selecciona un usuario.");
            }
        }

        function verPersona() {
            const select = document.getElementById('id_persona');
            const personaId = select.value;
            if (personaId) {
                window.location.href = `verpersona.php?id=${personaId}`;
            } else {
                Swal.fire("Por favor selecciona una persona.");
            }
        }
    </script>
</body>
</html>
