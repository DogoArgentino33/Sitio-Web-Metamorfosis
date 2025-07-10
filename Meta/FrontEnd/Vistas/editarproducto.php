  <?php include('auth.php'); include('conexion.php');
  
  if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
      echo "ID de producto no válido.";
      exit;
  }
  
  $id = intval($_GET['id']);
  
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $sql_producto = "SELECT * FROM producto WHERE id = ?";
      $stmt = $conexion->prepare($sql_producto);
      $stmt->bind_param("i", $id);
      $stmt->execute();
      $result = $stmt->get_result();
      $producto = $result->fetch_assoc();
      $stmt->close();
  
      $nombre = $_POST['nombre'] ?? $producto['nombre'];
      $tipo = $_POST['tipo'] ?? $producto['tipo'];
      $disponibilidad = $_POST['disponibilidad'] ?? $producto['disponibilidad'];
      $unidades_disponibles = $_POST['unidades_disponibles'] ?? $producto['unidades_disponibles'];
      $precio = $_POST['precio'] ?? $producto['precio'];
      $fechamod = date('Y-m-d H:i:s');
      $usumod = $_SESSION['nom_usu'] ?? 'sistema';
  
      $id_categoria = $_POST['producto_categoria'];
      $id_talla = $_POST['producto_talla'];
      $id_tematica = $_POST['producto_tematica'];
  
      $conexion->begin_transaction();
  
      try {
          // Manejo de imagen si se proporciona
          if (isset($_FILES['nueva_imagen']) && $_FILES['nueva_imagen']['error'] === UPLOAD_ERR_OK) {
              $tmp_name = $_FILES['nueva_imagen']['tmp_name'];
              $nombre_archivo = basename($_FILES['nueva_imagen']['name']);
              $destino = 'uploads/producto/' . uniqid() . '_' . $nombre_archivo;
  
              if (move_uploaded_file($tmp_name, $destino)) {
                  $tipo_img = mime_content_type($destino);
                  $stmt = $conexion->prepare("UPDATE img_producto SET tipo = ?, img = ? WHERE id_producto = ?");
                  $stmt->bind_param("ssi", $tipo_img, $destino, $id);
                  $stmt->execute();
                  $stmt->close();
              } else {
                  throw new Exception("Error al mover la imagen.");
              }
          }
  
          // Actualizar tabla producto
          $stmt = $conexion->prepare("UPDATE producto SET nombre=?, tipo=?, disponibilidad=?, unidades_disponibles=?, precio=?, fechamod=?, usumod=? WHERE id=?");
          $stmt->bind_param("sssids si", $nombre, $tipo, $disponibilidad, $unidades_disponibles, $precio, $fechamod, $usumod, $id);
          $stmt->execute();
          $stmt->close();
  
          // Actualizar categoria
          $stmt = $conexion->prepare("UPDATE producto_categoria SET id_categoria=? WHERE id_producto=?");
          $stmt->bind_param("ii", $id_categoria, $id);
          $stmt->execute();
          $stmt->close();
  
          // Actualizar talla
          $stmt = $conexion->prepare("UPDATE producto_talla SET id_talla=? WHERE id_producto=?");
          $stmt->bind_param("ii", $id_talla, $id);
          $stmt->execute();
          $stmt->close();
  
          // Actualizar tematica
          $stmt = $conexion->prepare("UPDATE producto_tematica SET id_tematica=? WHERE id_producto=?");
          $stmt->bind_param("ii", $id_tematica, $id);
          $stmt->execute();
          $stmt->close();
  
          $conexion->commit();
  
          header("Location: panelproducto.php?msg=Producto actualizado");
          exit;
      } catch (Exception $e) {
          $conexion->rollback();
          echo "Error al actualizar el producto: " . $e->getMessage();
      }
  }
  
  // Obtener datos para mostrar en formulario si no es POST
  $stmt = $conexion->prepare("
        SELECT p.*, pc.id_categoria 
        FROM producto p
        LEFT JOIN producto_categoria pc ON p.id = pc.id_producto
        WHERE p.id = ?
    ");

  $stmt->bind_param("i", $id);
  $stmt->execute();
  $resultado = $stmt->get_result();
  
  if ($resultado->num_rows === 0) {
      echo "Producto no encontrado.";
      exit;
  }
  
  $producto = $resultado->fetch_assoc();
  $stmt->close();
  ?>

<!-- Cuerpo de la página -->
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Producto</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../Estilos/index.css">
    <link rel="stylesheet" href="../Estilos/editarusuario.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <h1>Editar Producto</h1>

    <section class="dni-card">
    <form method="POST" enctype="multipart/form-data">
        <section class="perfil-editar">
            <div class="foto-perfil-editar">
                <img src="<?= htmlspecialchars($producto['img'] ?? '') ?>" alt="Imagen del producto" id="foto-perfil"
                style="width: 8vw; height: 8vw; object-fit: cover; border-radius: 50%; border: 0.25vw solid white;">
                <img id="preview-img" class="preview" style="display: none; width: 8vw; height: 8vw; object-fit: cover; border-radius: 50%; border: 0.25vw solid gray;">
            </div>
            <div class="input-imagen">
                <label for="nueva_imagen">Cambiar imagen del producto:</label>
                <input type="file" name="nueva_imagen" id="nueva_imagen" accept="image/*">
            </div>
        </section>

        <label for="nombre">Nombre:</label>
        <input type="text" name="nombre" id="nombre" value="<?= htmlspecialchars($producto['nombre']) ?>" required><br>

        <label for="tipo">Tipo:</label>
        <input type="text" name="tipo" id="tipo" value="<?= htmlspecialchars($producto['tipo']) ?>" required><br>

        <label for="disponibilidad">Disponibilidad:</label>
        <input type="text" name="disponibilidad" id="disponibilidad" value="<?= htmlspecialchars($producto['disponibilidad']) ?>" required><br>

        <label for="unidades_disponibles">Unidades Disponibles:</label>
        <input type="number" name="unidades_disponibles" id="unidades_disponibles" value="<?= htmlspecialchars($producto['unidades_disponibles']) ?>" required><br>

        <label for="precio">Precio:</label>
        <input type="text" name="precio" id="precio" value="<?= htmlspecialchars($producto['precio']) ?>" required><br>

        <label>Categoría:</label>
        <select name="producto_categoria" required>
            <?php 
            $sql = "SELECT id_categoria, nombre_categoria FROM categoria";
            $result = mysqli_query($conexion, $sql);
            while ($row = $result->fetch_assoc()) {
                $selected = ($row['id_categoria'] == $producto['id_categoria']) ? 'selected' : '';
                echo "<option value='{$row['id_categoria']}' $selected>{$row['nombre_categoria']}</option>";
            }
            ?>
        </select>


        <label>Talla:</label>
        <select name="producto_talla" required>
            <?php 
            $sql = "SELECT id_talla, nombre_talla FROM talla";
            $result = mysqli_query($conexion, $sql);
            while ($row = $result->fetch_assoc()) {
                $selected = ($row['id_talla'] == $producto['id_talla']) ? 'selected' : '';
                echo "<option value='{$row['id_talla']}' $selected>{$row['nombre_talla']}</option>";
            }
            ?>
        </select>

        <label>Temática:</label>
        <select name="producto_tematica" required>
            <?php 
            $sql = "SELECT id_tematica, nombre_tematica FROM tematica";
            $result = mysqli_query($conexion, $sql);
            while ($row = $result->fetch_assoc()) {
                $selected = ($row['id_tematica'] == $producto['id_tematica']) ? 'selected' : '';
                echo "<option value='{$row['id_tematica']}' $selected>{$row['nombre_tematica']}</option>";
            }
            ?>
        </select>

        <label for="fechamod">Última Modificación:</label>
        <input type="text" name="fechamod" id="fechamod" value="<?= htmlspecialchars($producto['fechamod']) ?>" readonly><br>

        <label for="usumod">Usuario que modificó:</label>
        <input type="text" name="usumod" id="usumod" value="<?= htmlspecialchars($producto['usumod']) ?>" readonly><br>

        <button type="submit">Guardar cambios</button>
        <a href="panelproducto.php"><button type="button">Cancelar</button></a>
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
                    fotoperfil.style.display = 'none';
                };
                reader.readAsDataURL(file);
            } else {
                fotoperfil.style.display = 'block';
                preview.style.display = 'none';
                preview.src = '';
            }
        });
    </script>
</body>
</html>
