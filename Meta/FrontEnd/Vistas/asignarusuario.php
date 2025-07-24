<?php
include('auth.php');
include('conexion.php');

// Procesar asignación si se envía el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_persona = intval($_POST['id_persona']);

    // Verificar si ya hay usuario para esta persona
    $verifica = $conexion->prepare("SELECT id FROM usuario WHERE id_persona = ?");
    $verifica->bind_param("i", $id_persona);
    $verifica->execute();
    $verifica_result = $verifica->get_result();

    if ($verifica_result->num_rows === 0) {
        // Redirigir a agregarusuario con la persona preseleccionada
        header("Location: agregarusuario.php?id_persona=$id_persona");
        exit;
    } else {
        echo "<script>alert('La persona ya tiene un usuario asignado.');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Asignar Usuario a Persona</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
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
            <a href="asignarusuario.php">Asignar Usuario</a>
        </section>
        <h1><a href="gerente.php" style="padding-right: 3%;" title="volver"><i class="bi bi-arrow-left-circle"></i></a>Asignar Usuario a Persona</h1>

        <section class="cards-container">
  
                <form method="POST" style="max-width: 600px; margin: auto; padding: 2rem; background-color: #f9f9f9; border-radius: 10px;">
                    <label for="id_persona">Persona sin usuario:</label><br>
                    <select name="id_persona" id="id_persona" required>
                        <option value="">-- Seleccionar persona --</option>
                        <?php
                        $personas = $conexion->query("SELECT p.id, p.nombre, p.apellido 
                                                    FROM persona p 
                                                    LEFT JOIN usuario u ON p.id = u.id_persona 
                                                    WHERE u.id_persona IS NULL");
                        while ($p = $personas->fetch_assoc()) {
                            echo "<option value='{$p['id']}'>{$p['nombre']} {$p['apellido']}</option>";
                        }
                        ?>
                    </select>
                    <br><br>
                    <button type="button" class="btn" onclick="verPersona()">Ver Persona</button>
                    <button type="button" class="btn" onclick="crearUsuario()">Crear Usuario</button>
                </form>

        </section>


    </main>

    <script>
        function verPersona() {
            const select = document.getElementById('id_persona');
            const personaId = select.value;
            if (personaId) {
                window.location.href = `verpersona.php?id=${personaId}`;
            } else {
                Swal.fire("Por favor selecciona una persona.");
            }
        }

        function crearUsuario() {
            const select = document.getElementById('id_persona');
            const personaId = select.value;
            if (personaId) {
                window.location.href = `agregarusuario.php?id_persona=${personaId}`;
            } else {
                Swal.fire("Selecciona una persona para crear su usuario.");
            }
        }
    </script>
    
<?php include('footer.php');?>
</body>
</html>
