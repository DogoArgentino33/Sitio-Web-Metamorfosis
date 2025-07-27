<?php include('auth.php'); include('conexion.php');

//Verificando si la cuenta no es rol gerente
if (isset($_SESSION['rol']) and $_SESSION['rol'] != 1) 
{
    header("Location: index.php"); 
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) { 
    echo "ID de persona no válido."; 
    exit;
}

$id = intval($_GET['id']);

// Obtener datos actuales de la persona
$stmt = $conexion->prepare("SELECT * FROM persona WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    echo "Usuario no encontrado.";
    exit;
}

$persona = $resultado->fetch_assoc();
$ruta_imagen_actual = $persona['img']; // Ruta actual de la imagen

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre       = $_POST['nombre'] ?? $persona['nombre'];
    $apellido     = $_POST['apellido'] ?? $persona['apellido'];
    $dni          = $_POST['dni'] ?? $persona['dni'];
    $fec_nac      = $_POST['fec_nac'] ?? $persona['fec_nac'];
    $pais         = $_POST['pais'];
    $provincia    = $_POST['provincia'];
    $genero       = $_POST['genero'] ?? $persona['genero'];
    $calle        = $_POST['calle'];
    $altura       = intval($_POST['altura']);
    $barrio       = $_POST['barrio'];
    $departamento = $_POST['departamento'];
    $municipio    = $_POST['municipio'];
    $localidad    = $_POST['localidad'];
    $fechamod     = date('Y-m-d H:i:s');
    $usumod       = $_SESSION['nom_usu'] ?? 'sistema';

    $errores = [];
    $ruta_nueva_img = $ruta_imagen_actual;

    // Procesar imagen si se sube una nueva
    if (isset($_FILES['nueva_imagen']) && $_FILES['nueva_imagen']['error'] === UPLOAD_ERR_OK) {
        $foto = $_FILES['nueva_imagen'];
        $permitidos = ['image/jpeg', 'image/png'];
        $tam_max = 4 * 1024 * 1024; // 4MB

        if (!in_array($foto['type'], $permitidos)) {
            $errores[] = "El formato de imagen no es válido. Solo se permiten JPG y PNG.";
        } elseif ($foto['size'] > $tam_max) {
            $errores[] = "La imagen no debe superar los 4MB.";
        } else {
            $tmp_name = $foto['tmp_name'];
            list($ancho_orig, $alto_orig) = getimagesize($tmp_name);
            $ancho_max = 1280;
            $alto_max = 1280;

            // Redimensionamiento proporcional
            $ratio_orig = $ancho_orig / $alto_orig;
            $ratio_dest = $ancho_max / $alto_max;

            if ($ratio_orig > $ratio_dest) {
                $ancho_final = $ancho_max;
                $alto_final = intval($ancho_max / $ratio_orig);
            } else {
                $alto_final = $alto_max;
                $ancho_final = intval($alto_max * $ratio_orig);
            }

            // Crear imagen blanca base
            $imagen_final = imagecreatetruecolor($ancho_max, $alto_max);
            $blanco = imagecolorallocate($imagen_final, 255, 255, 255);
            imagefill($imagen_final, 0, 0, $blanco);

            // Cargar imagen original
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

                $nombre_img = uniqid('persona_') . '.jpg';
                $ruta_destino = 'uploads/persona/' . $nombre_img;

                if (imagejpeg($imagen_final, $ruta_destino, 90)) {
                    $ruta_nueva_img = $ruta_destino;
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

    // Si no hay errores, actualizar la base
    if (empty($errores)) {
        $stmt = $conexion->prepare("UPDATE persona SET pais=?, provincia=?, img=?, calle=?, altura=?, barrio=?, departamento=?, municipio=?, localidad=?, fechamod=?, usumod=? WHERE id=?");
        $stmt->bind_param("ssssissssssi", 
            $pais, $provincia, $ruta_nueva_img, $calle, $altura, 
            $barrio, $departamento, $municipio, $localidad, 
            $fechamod, $usumod, $id
        );

        if ($stmt->execute()) {
            header("Location: panelpersonas.php?personamodificada=ok");
            exit;
        } else {
            echo "Error al actualizar la persona.";
        }
    } else {
        foreach ($errores as $error) {
            echo "<p style='color:red;'>$error</p>";
        }
    }
}
?>

<!-- Función del mapa actualizado -->
<script>
document.addEventListener('DOMContentLoaded', () => {

  /* ---- mapa ---- */
  const map = L.map('map').setView([-28.4689,-65.7790], 14);
  L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png',
              {maxZoom:19, attribution:'&copy; OSM'}).addTo(map);

  let marker;

  /* selects */
  const select_departamento = document.getElementById('departamento');
  const select_municipio    = document.getElementById('municipio');
  const select_localidad    = document.getElementById('localidad');

  map.on('click', e => {
    const {lat,lng} = e.latlng;

    fetch(`reverseLocalidad.php?latitud=${lat}&longitud=${lng}`)
      .then(r => r.json())
      .then(async data => {
        if (data.error) { console.warn(data.error); return; }

        //Guardando datos en cada select
        select_departamento.value = data.coddpto;
        await fillMunicipios(data.coddpto, data.codmun); 
        await fillLocalidades(data.codmun, data.codloc);

        //Funcion de movimiento
        moveMarker(lat,lng);
      })
      .catch(console.error);
  });

  //Esta es la funcion de movimiento
  function moveMarker(lat,lng) 
  {
    const punto = L.latLng(lat,lng);
    if (marker) marker.setLatLng(punto);
    else        marker = L.marker(punto).addTo(map);
    map.setView(punto, 14);
  }

  //funciones de relleno
  function fillMunicipios(coddpto, codmunSel) 
  {
    const fd = new FormData();  fd.append('coddpto', coddpto);
    return fetch('getMunicipio.php', {method:'POST', body:fd})
      .then(r => r.text())
      //Esta parte cambia el valor de select, no tocar
      .then(html => 
      {
        select_municipio.innerHTML = '<option value="">Seleccionar…</option>'+html;
        select_municipio.value = codmunSel;
      });
  }

  function fillLocalidades(codmun, codlocSel) {
    const fd = new FormData();  fd.append('codmun', codmun);
    return fetch('getLocalidad.php', {method:'POST', body:fd})
      .then(r => r.text())
      //Esta parte cambia el valor de select, no tocar
      .then(html => 
      {
        select_localidad.innerHTML = '<option value="">Seleccionar…</option>'+html;
        select_localidad.value = codlocSel;
      });
  }

});
</script>

<!-- Funcion de obtener Municipio -->
 <script>
    document.addEventListener("DOMContentLoaded", () => {
    const cbxDepartamento = document.getElementById('departamento');
    const cbxMunicipio    = document.getElementById('municipio');

    cbxDepartamento.addEventListener('change', getMunicipios);

    function fetchAndSetData(url, formData, targetElement) {
    return fetch(url, 
    {
      method: 'POST',
      body: formData            // mismo dominio → no necesitas 'mode:cors'
    })
    .then(r => r.text())          // ⬅ HTML, no JSON
    .then(html => {
      targetElement.innerHTML  =
        '<option value="">Seleccionar…</option>' + html;
      targetElement.disabled = false;   // por si estaba deshabilitado
    })
    .catch(err => 
    {
      console.error(err);
      targetElement.innerHTML =
        '<option value="">(error)</option>';
      targetElement.disabled = true;
    });
}

function getMunicipios() {
  const departamento = cbxDepartamento.value;
  if (!departamento) {          // nada elegido → vaciar segundo select
    cbxMunicipio.innerHTML = '<option value="">Seleccionar…</option>';
    cbxMunicipio.disabled  = true;
    return;
  }

  const formData = new FormData();
  formData.append('coddpto', departamento);

  fetchAndSetData('getMunicipio.php', formData, cbxMunicipio);
}
    });
 </script>

<!-- Funcion de obtener Localidad -->
 <script>
    document.addEventListener("DOMContentLoaded", () => {
    const cbxMunicipio = document.getElementById('municipio');
    const cbxLocalidad    = document.getElementById('localidad');

    cbxMunicipio.addEventListener('change', getLocalidades);

    function fetchAndSetData(url, formData, targetElement) {
    return fetch(url, 
    {
      method: 'POST',
      body: formData            // mismo dominio → no necesitas 'mode:cors'
    })
    .then(r => r.text())          // ⬅ HTML, no JSON
    .then(html => {
      targetElement.innerHTML  =
        '<option value="">Seleccionar…</option>' + html;
      targetElement.disabled = false;   // por si estaba deshabilitado
    })
    .catch(err => 
    {
      console.error(err);
      targetElement.innerHTML =
        '<option value="">(error)</option>';
      targetElement.disabled = true;
    });
}

function getLocalidades() {
  const municipio = cbxMunicipio.value;
  if (!municipio) {          // nada elegido → vaciar segundo select
    cbxMunicipio.innerHTML = '<option value="">Seleccionar…</option>';
    cbxMunicipio.disabled  = true;
    return;
  }

  const formData = new FormData();
  formData.append('codmun', municipio);

  fetchAndSetData('getLocalidad.php', formData, cbxLocalidad);
  } 
    });
</script>

<!-- Cuerpo de la página -->
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Persona</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../Estilos/index.css">
    <link rel="stylesheet" href="../Estilos/editarpersona.css">
    <!-- Script de SweetAlert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <h1 style="text-align:center;">Editar Persona</h1>
      <form method="POST" enctype="multipart/form-data">

          <div class="form-columns">

            <section class="columna-formulario">
              <h3>Datos Personales</h3>

              <section class="perfil-editar">
                  <div class="foto-perfil-editar">
                      <img src="<?= htmlspecialchars($persona['img']) ?>" alt="Foto de perfil" id="foto-perfil"
                      style="width: 8vw; height: 8vw; object-fit: cover; border-radius: 50%; border: 0.25vw solid white;">
                      <img id="preview-img" class="preview" style="display: none; width: 8vw; height: 8vw; object-fit: cover; border-radius: 50%; border: 0.25vw solid gray;">

                  </div>
                  <div class="input-imagen">
                      <label for="nueva_imagen">Cambiar foto de perfil:</label>
                      <input type="file" name="nueva_imagen" id="nueva_imagen" accept="image/*">
                  </div>
              </section>

              <label for="nombre">Nombre:</label>
              <input type="text" name="nombre" id="nombre" value="<?= htmlspecialchars($persona['nombre']) ?>" readonly><br>

              <label for="apellido">Apellido:</label>
              <input type="text" name="apellido" id="apellido" value="<?= htmlspecialchars($persona['apellido']) ?>" readonly><br>

              <label for="dni">DNI:</label>
              <input type="text" name="dni" id="dni" value="<?= htmlspecialchars($persona['dni']) ?>" readonly><br>

              <label for="fec_nac">Fecha de Nacimiento:</label>
              <input type="date" name="fec_nac" id="fec_nac" value="<?= htmlspecialchars($persona['fec_nac']) ?>" readonly><br>

              <label for="genero">Género:</label>
              <input type="text" name="genero" id="genero" value="<?= htmlspecialchars($persona['genero']) ?>" readonly><br>
            </section>
            
            <section class="columna-formulario">
              <h3>Datos de Domicilio</h3>

              <label>Provincia:</label>
                  <select name="provincia" id="provincia" required>
                      <?php 
                          $sql = "SELECT codprov, nomprov FROM provincias";
                          $result = mysqli_query($conexion, $sql);

                          while($row = $result->fetch_assoc()) 
                            { ?>
                              <option value="<?php echo $row['codprov'];?>"><?php echo $row['nomprov'];?></option>
                              <?php 
                            }
                      ?>
                  </select>

              <label>Departamento:</label>
                  <select name="departamento" id="departamento" required>
                      <?php 
                      $sql = "SELECT coddpto, nomdpto FROM dpto";
                      $result = mysqli_query($conexion, $sql);

                      while($row = $result->fetch_assoc()) {
                          $selected = ($row['coddpto'] == $persona['departamento']) ? 'selected' : '';
                          echo "<option value='{$row['coddpto']}' $selected>{$row['nomdpto']}</option>";
                      }
                      ?>
                  </select>
              <label>Municipio:</label>
                  <select name="municipio" id="municipio" required>
                      <?php 
                      $sql = "SELECT codmun, nommun FROM municipio";
                      $result = mysqli_query($conexion, $sql);

                      while($row = $result->fetch_assoc()) {
                          $selected = ($row['codmun'] == $persona['municipio']) ? 'selected' : '';
                          echo "<option value='{$row['codmun']}' $selected>{$row['nommun']}</option>";
                      }
                      ?>
                  </select>

              <label>Localidad:</label>
                  <select name="localidad" id="localidad" required>
                      <?php 
                      $sql = "SELECT codloc, nomloc FROM localidades";
                      $result = mysqli_query($conexion, $sql);

                      while($row = $result->fetch_assoc()) {
                          $selected = ($row['codloc'] == $persona['localidad']) ? 'selected' : '';
                          echo "<option value='{$row['codloc']}' $selected>{$row['nomloc']}</option>";
                      }
                      ?>
                  </select>

                <label>Barrio:</label>
                <input type="text" name="barrio" value="<?= htmlspecialchars($persona['barrio']) ?>" required><br>

                <label>Calle:</label>
                <input type="text" name="calle" value="<?= htmlspecialchars($persona['calle']) ?>" required><br>

                <label>Altura:</label>
                <input type="text" name="altura" value="<?= htmlspecialchars($persona['altura']) ?>" required><br>

                <label for="fechamod">Última Modificacion</label>
                <input type="text" name="fechamod" id="fechamod" value="<?= htmlspecialchars($persona['fechamod']) ?>" readonly><br>

                <label for="usumod">Usuario que realizó la modificacion</label>
                <input type="text" name="usumod" id="usumod" value="<?= htmlspecialchars($persona['usumod']) ?>" readonly><br>
            </section>

          </div>

          <div class="form-botones">
            <button type="button" class="boton boton-cancelar" onclick="location.href='panelpersonas.php'">Cancelar</button>
            <button type="submit" class="boton boton-guardar">Guardar cambios</button>
          </div>

      </form>

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