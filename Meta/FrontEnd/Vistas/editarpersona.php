<?php include('auth.php'); include('conexion.php');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) { echo "ID de persona no válido."; exit;}

$id = intval($_GET['id']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_GET['id']; // o $_POST['id'], según cómo lo llames
    $sql_persona = "SELECT * FROM persona WHERE id = $id";
    $result_persona = mysqli_query($conexion, $sql_persona);
    $persona = mysqli_fetch_assoc($result_persona);
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

        <button type="submit">Guardar cambios</button>
        <a href="panelpersonas.php"><button type="button">Cancelar</button></a>
    </form>
    </section>
</body>
</html>