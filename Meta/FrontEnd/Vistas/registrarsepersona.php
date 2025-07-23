<?php session_start(); include('conexion.php'); 

//Función escapar
function escapar($html) {
    return htmlspecialchars($html, ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8");
}

// función de validación: sólo letras y espacios
function validarSoloLetras($cadena) {
    // Permite letras (mayúsculas y minúsculas), espacios y caracteres con acentos
    return preg_match("/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/", $cadena);
}

//Inicializando variables de errores y de registro existoso
$errores = [];
$mensaje_dni_duplicado = '';
$error_fecha_nac = '';
$error_nombre = '';
$error_apellido = '';
$error_img = '';

$registro_exitoso = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitizar entradas
    $nombre       = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
    $apellido     = isset($_POST['apellido']) ? trim($_POST['apellido']) : '';
    $dni          = isset($_POST['dni']) ? intval($_POST['dni']) : 0;
    $fec_nac      = isset($_POST['fec-nac']) ? trim($_POST['fec-nac']) : '';
    $calle        = isset($_POST['calle']) ? trim($_POST['calle']) : '';
    $altura       = isset($_POST['altura']) ? trim($_POST['altura']) : '';
    $barrio       = isset($_POST['barrio']) ? trim($_POST['barrio']) : '';
    $departamento = isset($_POST['departamento']) ? trim($_POST['departamento']) : '';
    $municipio    = isset($_POST['municipio']) ? trim($_POST['municipio']) : '';
    $localidad    = isset($_POST['localidad']) ? trim($_POST['localidad']) : '';
    $provincia    = isset($_POST['provincia']) ? trim($_POST['provincia']) : '';
    $pais         = isset($_POST['pais']) ? trim($_POST['pais']) : '';
    $genero       = isset($_POST['genero']) ? $_POST['genero'] : '';
    $foto = $_FILES['foto'];
    $lat = isset($_POST['lat']) ? floatval($_POST['lat']) : 0;
    $lng = isset($_POST['lng']) ? floatval($_POST['lng']) : 0;


    // Validaciones
    if ($nombre === '' || !validarSoloLetras($nombre)) {
        $errores[] = 'El nombre solo puede contener letras y espacios, sin números ni símbolos.';
    }

    if ($apellido === '' || !validarSoloLetras($apellido)) {
        $errores[] = 'El apellido solo puede contener letras y espacios, sin números ni símbolos.';
    }

    foreach ($errores as $error) {
        if (strpos($error, 'nombre') !== false) {
            $error_nombre = $error;
        }
        if (strpos($error, 'apellido') !== false) {
            $error_apellido = $error;
        }
    }

    // Validación de DNI duplicados
    $sql = "SELECT dni FROM persona WHERE dni = '$dni'";
    $result = mysqli_query($conexion, $sql);
    if ($result && mysqli_num_rows($result) > 0) {
        $errores[] = "Ya existe una persona registrada con ese DNI.";
    }

    //en caso de DNI duplicado 
    foreach ($errores as $error) {
        if (strpos($error, 'DNI') !== false) {
            $mensaje_dni_duplicado = $error;
        }
    }

    //validacion de fecha
    if (!empty($fec_nac)) {
        $fecha_nac_timestamp = strtotime($fec_nac);
        $fecha_minima = strtotime('1900-01-01');
        $fecha_hoy = time();
        $edad_18 = strtotime('-18 years', $fecha_hoy);

        if ($fecha_nac_timestamp < $fecha_minima) {
            $errores[] = "La fecha de nacimiento no puede ser anterior al año 1900.";
        } elseif ($fecha_nac_timestamp > $edad_18) {
            $errores[] = "Debes tener al menos 18 años para registrarte.";
        }
    } else {
        $errores[] = "La fecha de nacimiento es obligatoria.";
    }

    foreach ($errores as $error) {
        if (strpos($error, 'fecha de nacimiento') !== false || strpos($error, 'años') !== false) {
            $error_fecha_nac = $error;
        }
    }

    // Validación y procesamiento de imagen
    if ($foto && $foto['error'] === UPLOAD_ERR_OK) {
        $permitidos = ['image/jpeg', 'image/png'];
        $tipoImagen = mime_content_type($foto['tmp_name']);

        if (!in_array($tipoImagen, $permitidos)) {
            $errores[] = "El formato de imagen no es válido. Solo se permiten JPG y PNG.";
        } elseif ($foto['size'] > 4 * 1024 * 1024) {
            $errores[] = "La imagen no debe superar los 4MB.";
        } else {
            // Obtener dimensiones originales
            list($ancho, $alto) = getimagesize($foto['tmp_name']);

            // Definir dimensiones máximas
            $maxWidth = 1280;
            $maxHeight = 1280;

            // Mantener proporción al redimensionar
            $ratio = min($maxWidth / $ancho, $maxHeight / $alto, 1);
            $anchoNuevo = (int)($ancho * $ratio);
            $altoNuevo = (int)($alto * $ratio);

            // Crear imagen original según tipo
            switch ($tipoImagen) {
                case 'image/jpeg':
                    $origen = imagecreatefromjpeg($foto['tmp_name']);
                    break;
                case 'image/png':
                    $origen = imagecreatefrompng($foto['tmp_name']);
                    break;
                default:
                    $origen = null;
                    break;
            }

            if ($origen) {
                // Crear lienzo blanco y redimensionar con fondo blanco
                $imagenFinal = imagecreatetruecolor($anchoNuevo, $altoNuevo);
                $blanco = imagecolorallocate($imagenFinal, 255, 255, 255);
                imagefill($imagenFinal, 0, 0, $blanco);

                imagecopyresampled($imagenFinal, $origen, 0, 0, 0, 0, $anchoNuevo, $altoNuevo, $ancho, $alto);

                // Generar nombre único y guardar como JPEG
                $nombreArchivo = uniqid('img_', true) . '.jpg';
                $ruta = "uploads/persona/" . $nombreArchivo;

                if (!imagejpeg($imagenFinal, $ruta, 90)) {
                    $errores[] = "No se pudo guardar la imagen.";
                }

                imagedestroy($origen);
                imagedestroy($imagenFinal);
            } else {
                $errores[] = "Error al procesar la imagen.";
            }
        }
    } else {
        $errores[] = "Debe subir o tomar una imagen.";
    }

    // Extraer error específico relacionado con imagen
    foreach ($errores as $error) {
        if (stripos($error, 'imagen') !== false || stripos($error, 'formato') !== false) {
            $error_img = $error;
        }
    }


    if (count($errores) === 0) {
        // Escapar campos
        $nombre       = mysqli_real_escape_string($conexion, $nombre);
        $apellido     = mysqli_real_escape_string($conexion, $apellido);
        $dni          = mysqli_real_escape_string($conexion, $dni);
        $fec_nac      = mysqli_real_escape_string($conexion, $fec_nac);
        $calle        = mysqli_real_escape_string($conexion, $calle);
        $altura       = mysqli_real_escape_string($conexion, $altura);
        $barrio       =  mysqli_real_escape_string($conexion, $barrio);
        $departamento = mysqli_real_escape_string($conexion, $departamento);
        $municipio    = mysqli_real_escape_string($conexion, $municipio);
        $localidad    = mysqli_real_escape_string($conexion, $localidad);
        $provincia    = mysqli_real_escape_string($conexion, $provincia);
        $pais         = mysqli_real_escape_string($conexion, $pais);
        $genero       = mysqli_real_escape_string($conexion, $genero);
        $ruta         = mysqli_real_escape_string($conexion, $ruta);
        $lat          = mysqli_real_escape_string($conexion, $lat);
        $lng          = mysqli_real_escape_string($conexion, $lng);

        $sql_insert = "INSERT INTO `persona`(`nombre`, `apellido`, `dni`, `fec_nac`, `pais`, `provincia`, `genero`, `img`, `calle`, `altura`, `barrio`, `departamento`, `municipio`, `localidad`, `lat`, `lng`) 
        VALUES ('$nombre','$apellido','$dni','$fec_nac','$pais','$provincia','$genero','$ruta','$calle','$altura','$barrio','$departamento','$municipio','$localidad','$lat','$lng')";

        if (mysqli_query($conexion, $sql_insert)) {
            $_SESSION['id_persona'] = mysqli_insert_id($conexion);
            header("Location: registrarseusuario.php?registropersona=ok");
            exit;
        } else {
            $errores[] = "Error al registrar la persona: " . mysqli_error($conexion);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <title>Registrar Persona</title>
    <link rel="icon" type="image/x-icon" href="./assets/favicon.ico" />
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../Estilos/index.css">
    <link rel="stylesheet" href="../Estilos/validacion.css">
    <link rel="stylesheet" href="../Estilos/registroP.css">

    <!-- Link y script de Leaflet -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
     integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
     crossorigin=""/>

     <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
     integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
     crossorigin=""> </script>

     <!-- Link y Script de Sweetalert2 -->
     <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>

<script>
    document.addEventListener("DOMContentLoaded", () => 
    {

        function capitalizar(texto) 
        {
            return texto
                .replace(/\s+/g, ' ') // un solo espacio
                .trim()
                .toLowerCase()
                .split(' ')
                .map(palabra => palabra.charAt(0).toUpperCase() + palabra.slice(1))
                .join(' ');
        }

        const validaciones = {
            nombre: {
                regex: /^[A-Za-zÁÉÍÓÚáéíóúÑñ]+(?:\s[A-Za-zÁÉÍÓÚáéíóúÑñ]+)*$/,
                mensaje: "El nombre solo puede contener letras y espacios simples. No numeros ni sibolos"
            },
            apellido: {
                regex: /^[A-Za-zÁÉÍÓÚáéíóúÑñ]+(?:\s[A-Za-zÁÉÍÓÚáéíóúÑñ]+)*$/,
                mensaje: "El apellido solo puede contener letras y espacios simples. No numeros ni sibolos"
            },
            dni: {
                regex: /^[0-9]{7,8}$/,
                mensaje: "El DNI debe tener entre 7 y 8 números, sin letras ni símbolos."
            },
            pais: {
                regex: /^[A-Za-zÁÉÍÓÚáéíóúÑñ]+(?:\s[A-Za-zÁÉÍÓÚáéíóúÑñ]+)*$/,
                mensaje: "El país debe contener solo letras y espacios simples. No numeros ni sibolos"
            },
            calle: {
                regex: /^[A-Za-zÁÉÍÓÚáéíóúÑñ]+(?:\s[A-Za-zÁÉÍÓÚáéíóúÑñ]+)*$/,
                mensaje: "La calle solo puede contener letras, números y espacios simples. No se permiten símbolos."
            },
            altura: {
                regex: /^[0-9]+$/,
                mensaje: "La altura debe ser un número positivo sin letras ni símbolos."
            },
            barrio: {
                regex: /^[A-Za-zÁÉÍÓÚáéíóúÑñ]+(?:\s[A-Za-zÁÉÍÓÚáéíóúÑñ]+)*$/,
                mensaje: "El barrio solo puede contener letras, números y espacios simples. No se permiten símbolos."
            }
        };

        for (const id in validaciones) {
            const input = document.getElementById(id);

            if (input) {
                const barra = document.createElement('div');
                barra.classList.add('barra-validacion');

                const mensaje = document.createElement('div');
                mensaje.classList.add('mensaje-validacion');

                input.insertAdjacentElement('afterend', barra);
                barra.insertAdjacentElement('afterend', mensaje);

                input.addEventListener('input', () => {
                    const valor = input.value.trim();
                    const { regex, mensaje: textoMensaje } = validaciones[id];

                    if (regex.test(valor)) {
                        barra.className = 'barra-validacion valido';
                        mensaje.className = 'mensaje-validacion valido';
                        mensaje.textContent = 'Dato válido.';
                    } else {
                        barra.className = 'barra-validacion invalido';
                        mensaje.className = 'mensaje-validacion invalido';
                        mensaje.textContent = textoMensaje;
                    }
                });

                input.addEventListener('blur', () => {
                    input.value = capitalizar(input.value);
                });
            }
        }

        // Asegurar capitalización a todos los campos tipo texto al salir
        const todosLosTextos = document.querySelectorAll('input[type="text"]');
        todosLosTextos.forEach(input => {
            input.addEventListener('blur', () => {
                input.value = capitalizar(input.value);
            });
        });

        // Validación de fecha de nacimiento
        const inputFecha = document.getElementById('fec-nac');
        if (inputFecha) {
            const barra = document.createElement('div');
            barra.classList.add('barra-validacion');

            const mensaje = document.createElement('div');
            mensaje.classList.add('mensaje-validacion');

            inputFecha.insertAdjacentElement('afterend', barra);
            barra.insertAdjacentElement('afterend', mensaje);

            inputFecha.addEventListener('input', () => {
                const valor = inputFecha.value;
                const fechaIngresada = new Date(valor);
                const hoy = new Date();
                const fechaMinima = new Date('1900-01-01');
                const edadMinima = new Date(hoy.getFullYear() - 18, hoy.getMonth(), hoy.getDate());

                if (!valor) {
                    barra.className = 'barra-validacion invalido';
                    mensaje.className = 'mensaje-validacion invalido';
                    mensaje.textContent = 'La fecha de nacimiento es obligatoria.';
                } else if (fechaIngresada < fechaMinima) {
                    barra.className = 'barra-validacion invalido';
                    mensaje.className = 'mensaje-validacion invalido';
                    mensaje.textContent = 'La fecha no puede ser anterior al año 1900.';
                } else if (fechaIngresada > edadMinima) {
                    barra.className = 'barra-validacion invalido';
                    mensaje.className = 'mensaje-validacion invalido';
                    mensaje.textContent = 'Debes tener al menos 18 años.';
                } else {
                    barra.className = 'barra-validacion valido';
                    mensaje.className = 'mensaje-validacion valido';
                    mensaje.textContent = 'Fecha válida.';
                }
            });
        }
    });
</script>

<!-- Función del mapa actualizado -->
<script>
document.addEventListener('DOMContentLoaded', () => {

  /* ---- mapa ---- */
  const map = L.map('map').setView([-28.4689,-65.7790], 14);
  L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png',
              {maxZoom:19, attribution:'&copy; OSM'}).addTo(map);

  let marker;

  /* selects */
  const select_provincia    = document.getElementById('provincia');
  const select_departamento = document.getElementById('departamento');
  const select_municipio    = document.getElementById('municipio');
  const select_localidad    = document.getElementById('localidad');

  map.on('click', e => 
  {
    const {lat,lng} = e.latlng;
    document.getElementById('lat').value = lat.toFixed(6);
    document.getElementById('lng').value = lng.toFixed(6);

    fetch(`reverseLocalidad.php?latitud=${lat}&longitud=${lng}`)
      .then(r => r.json())
      .then(async data => {
        if (data.error) { console.warn(data.error); return; }

        //Guardando datos en cada select
        select_provincia.value = data.codprov;
        await fillMunicipios(data.coddpto, data.codmun); 
        await fillLocalidades(data.codmun, data.codloc);
        await fillDpto(data.codprov, data.coddpto);

        //Funcion de movimiento
        moveMarker(lat,lng);
      })
      .catch(console.error);
  });

  //Esta es la funcion de movimiento
  function moveMarker(lat,lng) 
  {
    const punto = L.latLng(lat,lng);
    
    if (marker) 
    {
        marker.setLatLng(punto);
        /* popup */
        marker.openPopup();
    }
    else        
    {
        marker = L.marker(punto).addTo(map);
        map.setView(punto, 14);
    }
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

  function fillDpto(codprov, codDptoSel)
  {
    const fd = new FormData();  fd.append('codprov', codprov);
    return fetch('GetDpto.php', {method:'POST', body:fd})
      .then(r => r.text())
      //Esta parte cambia el valor de select, no tocar
      .then(html => 
      {
        select_departamento.innerHTML = '<option value="">Seleccionar…</option>'+html;
        select_departamento.value = codDptoSel;
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

<!-- Funcion de obtener Provincia -->
 <script>
    document.addEventListener("DOMContentLoaded", () => {
    const cbxProvincia = document.getElementById('provincia');
    const cbxDepartamento    = document.getElementById('departamento');

    cbxProvincia.addEventListener('change', GetDpto);

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

function GetDpto() {
  const provincia = cbxProvincia.value;
  if (!provincia) {          // nada elegido → vaciar segundo select
    cbxDepartamento.innerHTML = '<option value="">Seleccionar…</option>';
    cbxDepartamento.disabled  = true;
    return;
  }

  const formData = new FormData();
  formData.append('codprov', provincia);

  fetchAndSetData('GetDpto.php', formData, cbxDepartamento);
}
    });
 </script> 

<!-- Cuerpo del formulario -->
<body>
    <?php include('cabecera.php'); ?>
    <section class="nav-route">
        <a href="index.php">Inicio / </a>
        <a href="login.php">Login / </a>
        <a>Registrarse</a>
    </section>
    <br>
    <h1 id="titulo-formulario" style="text-align:center;">Registrar Persona</h1>

    <section id="wrapperregistro">

        <section style="text-align:center;">
           <div class="contenedor-video">
            <video src="uploads/METAMORFOSIS VIDEO REGISTRAR PERSONA.mp4" controls width="480" poster="">
            Tu navegador no soporta la reproducción de video.
            </video>
            <p class="titulo-video">Video instructivo: Cómo registrar una persona</p>
            </div>
        </section>

        <form action="registrarsepersona.php" method="post" enctype="multipart/form-data" id="formregistro">
            <h2>Formulario Registrar Persona</h2>

            <div class="form-columns">
                <fieldset>
                    <legend>Datos personales</legend>

                    <div class="columna-formulario">
                        <!-- Nombre -->
                        <section class="input-box">
                            <label for="nombre">Nombre(s):</label>
                            <input id="nombre" name="nombre" type="text" class="solo-letras" required>
                            <span class="error" style="color:red;"><?php echo $error_nombre; ?></span>
                        </section>

                        <!-- Apellido -->
                        <section class="input-box">
                            <label for="apellido">Apellido(s):</label>
                            <input id="apellido" name="apellido" type="text" class="solo-letras" required>
                            <span class="error" style="color:red;"><?php echo $error_apellido; ?></span>
                        </section>

                        <!-- DNI -->
                        <section class="input-box">
                            <label for="DNI:">DNI:</label>
                            <input id="dni" name="dni" type="number" min="3000000" required value="<?php echo isset($_POST['dni']) ? htmlspecialchars($_POST['dni']) : ''; ?>">
                            <span class="error" style="color:red;"><?php echo $mensaje_dni_duplicado; ?></span>
                        </section>

                        <!-- Fecha Nacimiento -->
                        <section class="input-box">
                            <label for="fec-nac">Fecha de nacimiento:</label>
                            <input id="fec-nac" name="fec-nac" type="date" required value="<?php echo isset($_POST['fec-nac']) ? htmlspecialchars($_POST['fec-nac']) : ''; ?>">
                            <span class="error" style="color:red;"><?php echo $error_fecha_nac; ?></span>
                        </section>

                        <!-- Genero -->
                         <section class="input-box">
                            <label for="genero">Género:</label>
                            <select class="input-box-genero" id="genero" name="genero" required>
                                <option value="" disabled selected>-- Seleccionar Género --</option>
                                <option value="masculino">Masculino</option>
                                <option value="femenino">Femenino</option>
                                <option value="prefiero-no-decirlo">Prefiero no decirlo</option>
                            </select>
                        </section>
                    </div>

                    <!-- Imagen de perfil -->
                    <section class="input-box">
                        <br>
                        <label for="foto">Subir foto o usar cámara:</label><br>
                        <input type="file" name="foto" id="foto" accept="image/*" capture="user" required>
                        <span class="error"><?= $error_img ?></span><br>
                        <img id="preview-img" class="preview" style="display:none;" /><br>
                        <button type="button" id="cancelar-imagen" style="display:none;" class="btn" ><i class="bi bi-x-lg"></i> Cancelar Imagen</button><br>
                        <video id="video" width="320" height="240" autoplay style="display:none;"></video><br>
                        <canvas id="canvas" width="320" height="240" style="display:none;"></canvas><br>

                        
                        <button type="button" id="activar-camara" onclick="iniciarCamara()"  class="btn">Activar cámara</button>
                        <br><br>
                        <button type="button" id="abrir-camara" onclick="capturarFoto()" style="display:none;" class="btn" ><i class="bi bi-camera"></i> Tomar Foto</button>
                        <button type="button" id="cerrar-camara" onclick="cerrarCamara()" style="display:none; background-color: #d12e3b;" class="btn"><i class="bi bi-x-circle"></i> Cerrar Cámara</button><br>


            
                        <script>
                            const activarCamaraBtn = document.getElementById("activar-camara");
                            const abrirCamaraBtn = document.getElementById("abrir-camara");
                            const fotoInput = document.getElementById("foto");
                            const previewImg = document.getElementById("preview-img");
                            const cerrarBtn = document.getElementById("cerrar-camara");
                            const cancelarImgBtn = document.getElementById("cancelar-imagen");
                            let stream;

                            fotoInput.addEventListener("change", () => {
                                const archivo = fotoInput.files[0];
                                if (!archivo) return;

                                if (archivo.size > 4 * 1024 * 1024) {
                                    alert("La imagen no debe superar los 4MB.");
                                    fotoInput.value = "";
                                    previewImg.style.display = "none";
                                    cancelarImgBtn.style.display = "none";
                                    return;
                                }

                                const lector = new FileReader();
                                lector.onload = function(e) {
                                    previewImg.src = e.target.result;
                                    previewImg.style.display = "block";
                                    cancelarImgBtn.style.display = "inline";
                                };
                                lector.readAsDataURL(archivo);
                            });

                            function iniciarCamara() {
                                const esMovil = /Android|iPhone|iPad|iPod|Opera Mini|IEMobile|Mobile/i.test(navigator.userAgent);
                                navigator.mediaDevices.getUserMedia({ video: true })
                                    .then(mediaStream => {
                                        stream = mediaStream;
                                        const video = document.getElementById("video");
                                        video.srcObject = stream;
                                        video.style.display = "block";
                                        video.style.border = "2px solidrgb(122, 0, 0)";
                                        video.style.borderRadius = "10px";
                                        video.style.boxShadow = "0 2px 8px rgba(0,0,0,0.15)";
                                        video.style.margin = "1vw auto";
                                        video.style.display = "block";
                                        activarCamaraBtn.style.display = "none";

                                        if (esMovil) {
                                            console.log("Usando cámara en dispositivo móvil");
                                            // Podés ajustar resoluciones o UI específicas para móviles
                                        } else {
                                            console.log("Usando cámara en PC");
                                            // Opcional: mostrar instrucciones distintas
                                        }

                                        cerrarBtn.style.display = "inline";
                                        abrirCamaraBtn.style.display = "inline";
                                    })
                                    .catch(() => alert("No se pudo acceder a la cámara."));
                            }

                            function capturarFoto() {
                                const video = document.getElementById("video");
                                const canvas = document.getElementById("canvas");
                                const ctx = canvas.getContext("2d");
                                ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

                                canvas.toBlob(blob => {
                                    const archivo = new File([blob], "captura.jpg", { type: "image/jpeg" });
                                    const dataTransfer = new DataTransfer();
                                    dataTransfer.items.add(archivo);
                                    fotoInput.files = dataTransfer.files;

                                    const lector = new FileReader();
                                    lector.onload = function(e) {
                                        previewImg.src = e.target.result;
                                        previewImg.style.display = "block";
                                        previewImg.style.border = "3px solid rgb(122, 0, 0)";
                                        cancelarImgBtn.style.display = "inline";
                                    };
                                    lector.readAsDataURL(archivo);
                                }, "image/jpeg", 0.95);
                            }

                            function cerrarCamara() 
                            {
                                const video = document.getElementById("video");
                                if (video && video.srcObject) {
                                    const tracks = video.srcObject.getTracks();
                                    tracks.forEach(track => track.stop());
                                    video.srcObject = null;
                                    video.style.display = "none";
                                }
                                if (typeof stream !== "undefined" && stream) {
                                    stream.getTracks().forEach(track => track.stop());
                                    stream = null;
                                }
                                abrirCamaraBtn.style.display = "none";
                                cerrarBtn.style.display = "none";
                                activarCamaraBtn.style.display = "inline";
                            }

                            // Función para cancelar imagen seleccionada o capturada
                            cancelarImgBtn.addEventListener("click", () => {
                                fotoInput.value = "";
                                previewImg.src = "";
                                previewImg.style.display = "none";
                                cancelarImgBtn.style.display = "none";
                            });

                        </script>
                    </section>
                </fieldset>

                <fieldset>
                    <legend>Datos de Domicilio</legend>

                    <div class="columna-formulario">
                        <!-- Provincia -->
                        <section class="input-box">
                            <label for="provincia">Provincia:</label>
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
                        </section>

                        <!-- Departamento -->
                        <section class="input-box">
                            <label for="departamento">Departamento:</label>
                            <select name="departamento" id="departamento" required>
                                <option value="">Seleccionar</option>
                            </select>
                        </section>

                        <!-- Municipio -->
                        <section class="input-box">
                            <label for="municipio">Municipio:</label>
                            <select name="municipio" id="municipio" required>
                                <option value="">Seleccionar</option>
                            </select>
                        </section>

                        <!-- Localidad -->
                        <section class="input-box">
                            <label for="localidad">Localidad:</label>
                            <select name="localidad" id="localidad" required>
                                <option value="">Seleccionar</option>
                            </select>
                        </section>

                        <!-- Barrio -->
                        <section class="input-box">
                            <label for="barrio">Barrio:</label>
                            <input id="barrio" name="barrio" type="text" required>
                        </section>

                        <!-- Calle -->
                        <section class="input-box">
                            <label for="calle">Calle:</label>
                            <input id="calle" name="calle" type="text" required>
                        </section>

                        <!-- Altura -->
                        <section class="input-box">
                            <label for="altura">Altura:</label>
                            <input id="altura" name="altura" type="text" required>
                        </section>
                    </div>

                     <!-- Div del mapa -->
                    <div id="map"></div>

                    <!-- lat -->
                    <label for="lat"></label>
                    <input type="number" id="lat" name="lat" readonly hidden><br>
                    <!-- lng -->
                    <label for="lng"></label>
                    <input type="number" id="lng" name="lng" readonly hidden><br>
                </fieldset>
            </div>

            <section class="register-link" >
                <input type="submit" value="Registrar Persona" class="btn">
                <p><a href="../Vistas/index.php">Volver a Pagina principal</a></p>
            </section>
        </form>
    </section>
    <?php include('footer.php');?>
</body>
</html>