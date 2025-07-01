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
    // Validación imagen
    if ($foto && $foto['error'] === 0) {
        $permitidos = ['image/jpeg', 'image/png'];
        if (!in_array($foto['type'], $permitidos)) {
            $errores[] = "El formato de imagen no es válido. Solo se permiten JPG y PNG.";
        } elseif ($foto['size'] > 4 * 1024 * 1024) {
            $errores[] = "La imagen no debe superar los 4MB.";
        } else {
            // Redimensionar si es necesario
            $origen_temp = $foto['tmp_name'];
            list($ancho, $alto) = getimagesize($origen_temp);
            $ancho_nuevo = 1280;
            $alto_nuevo = 1280;

            $origen = null;
            if ($foto['type'] == 'image/jpeg') $origen = imagecreatefromjpeg($origen_temp);
            elseif ($foto['type'] == 'image/png') $origen = imagecreatefrompng($origen_temp);

            if ($origen) {
                $imagen_final = imagecreatetruecolor($ancho_nuevo, $alto_nuevo);
                $blanco = imagecolorallocate($imagen_final, 255, 255, 255);
                imagefill($imagen_final, 0, 0, $blanco);
                imagecopyresampled($imagen_final, $origen, 0, 0, 0, 0, $ancho_nuevo, $alto_nuevo, $ancho, $alto);

                $nombre_archivo = uniqid() . ".jpg";
                $ruta = "uploads/persona/" . $nombre_archivo;

                if (!imagejpeg($imagen_final, $ruta, 90)) {
                    $errores[] = "No se pudo guardar la imagen.";
                }

                imagedestroy($origen);
                imagedestroy($imagen_final);
            } else {
                $errores[] = "Error al procesar la imagen.";
            }
        }
    } else {
        $errores[] = "Debe subir o tomar una imagen.";
    }
    
    foreach ($errores as $error) {
        if (strpos($error, 'imagen') !== false || strpos($error, 'formato') !== false) {
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
            echo '<script>alert("Persona registrada exitosamente."); window.location.href="registrarseusuario.php";</script>';
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
    <link rel="stylesheet" href="../Estilos/registrarpersona.css">

    <!-- Link y script de Leaflet -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
     integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
     crossorigin=""/>

     <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
     integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
     crossorigin=""> </script>

     <!-- Link y Script de Sweetalert2 -->
     <script src="sweetalert2.min.js"></script>
     <link rel="stylesheet" href="sweetalert2.min.css">

</head>

<script>
    document.addEventListener("DOMContentLoaded", () => {

        function capitalizar(texto) {
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
            provincia: {
                regex: /^[A-Za-zÁÉÍÓÚáéíóúÑñ]+(?:\s[A-Za-zÁÉÍÓÚáéíóúÑñ]+)*$/,
                mensaje: "La provincia debe contener solo letras y espacios simples. No numeros ni sibolos"
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
  const select_departamento = document.getElementById('departamento');
  const select_municipio    = document.getElementById('municipio');
  const select_localidad    = document.getElementById('localidad');

  map.on('click', e => {
    const {lat,lng} = e.latlng;
    document.getElementById('lat').value = lat.toFixed(6);
    document.getElementById('lng').value = lng.toFixed(6);

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
    
    if (marker) 
    {
        marker.setLatLng(punto);
        /* popup */
        marker.bindPopup("").openPopup();
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


<!-- Cuerpo del formulario -->
<body>
    <?php include('cabecera.php'); ?>
    <section class="nav-route">
        <a href="index.php">Inicio / </a>
        <a href="login.php">Login / </a>
        <a>Registrarse</a>
    </section>
    <br>
    <h1 style="text-align: center;">Registrar Persona</h1>

        <section>
           <div style="text-align:center; margin-bottom:20px; justify-content:center;">
            <video src="uploads/METAMORFOSIS VIDEO REGISTRAR PERSONA.mp4" controls width="480" poster="">
            Tu navegador no soporta la reproducción de video.
            </video>
            <p style="font-size:14px; color:#555;">Video instructivo: Cómo registrar una persona</p>
            </div>
        </section>

    <section class="wrapperregistro" id="wrapperregistro">
        <form action="registrarsepersona.php" method="post" enctype="multipart/form-data" id="formregistro">
            <h2>Formulario Registrar Persona</h2>
            <fieldset>
                <legend>Datos personales</legend>

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

                <!-- Barrio -->
                <section class="input-box">
                    <label for="barrio">Barrio:</label>
                    <input id="barrio" name="barrio" type="text" required>
                </section>

                <!-- Departamento -->
                <section class="input-box">
                    <label for="departamento">Departamento:</label>
                    <select name="departamento" id="departamento" required>
                        <?php 
                        $sql = "SELECT coddpto, nomdpto FROM dpto";
                        $result = mysqli_query($conexion, $sql);

                        while($row = $result->fetch_assoc()) 
                        { ?>
                            <option value="<?php echo $row['coddpto'];?>"><?php echo $row['nomdpto'];?></option>
                        <?php }
                        
                        
                        ?>
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

                <!-- Provincia -->
                <section class="input-box">
                    <label for="provincia">Provincia:</label>
                    <input id="provincia" name="provincia" type="text" class="solo-letras" required>
                </section>

                <!-- Pais -->
                <section class="input-box">
                    <label for="pais">País:</label>
                    <input id="pais" name="pais" type="text" class="solo-letras" required>
                </section>

                <!-- Estilo de Leaflet -->
                <style>
                #map {
                        width: 100%;
                        max-width: 400px;
                        aspect-ratio: 1 / 1;
                        height: auto;
                        margin: auto;
                        z-index: 1;
                        position: relative;
                        border-radius: 10px;
                        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
                }
                @media (max-width: 500px) {
                        #map {
                                max-width: 100vw;
                                min-width: 0;
                                aspect-ratio: 1 / 1;
                        }
                }
                /* Asegura que el modal esté por encima del mapa */
                #modalMAIN {
                        z-index: 9999 !important;
                        position: fixed !important;
                }
                </style>

                <!-- Div del mapa -->
                <div id="map"></div>

                <!-- lat -->
                <label for="lat"></label>
                <input type="number" id="lat" name="lat" readonly hidden><br>
                <!-- lng -->
                <label for="lng"></label>
                <input type="number" id="lng" name="lng" readonly hidden><br>

                <!-- Género -->
                <section class="input-box-genero">
                    <label>Género:</label>
                    <br>
                    <label><input type="radio" name="genero" value="masculino" required>Masculino</label><br>
                    <label><input type="radio" name="genero" value="femenino">Femenino</label><br>
                    <label><input type="radio" name="genero" value="prefiero-no-decirlo">Prefiero no decirlo</label>
                </section>

                <!-- Imagen de perfil -->
                <!-- Imagen de perfil -->
                <section class="input-box">
                    <br>
                     <label for="foto">Subir foto o usar cámara:</label><br>
    <input type="file" name="foto" id="foto" accept="image/*" capture="user" required>
    <span class="error"><?= $error_img ?></span><br>
    <img id="preview-img" class="preview" style="display:none;" /><br><br>

    <video id="video" width="320" height="240" autoplay style="display:none;"></video><br>
    <canvas id="canvas" width="320" height="240" style="display:none;"></canvas><br>

    <button type="button" onclick="iniciarCamara()">Activar cámara</button>
    <button type="button" onclick="capturarFoto()">Tomar Foto</button><br><br>

    <button type="submit">Registrar Persona</button>
</form>

<script>
const fotoInput = document.getElementById("foto");
const previewImg = document.getElementById("preview-img");

fotoInput.addEventListener("change", () => {
    const archivo = fotoInput.files[0];
    if (!archivo) return;

    if (archivo.size > 4 * 1024 * 1024) {
        alert("La imagen no debe superar los 4MB.");
        fotoInput.value = "";
        previewImg.style.display = "none";
        return;
    }

    const lector = new FileReader();
    lector.onload = function(e) {
        previewImg.src = e.target.result;
        previewImg.style.display = "block";
    };
    lector.readAsDataURL(archivo);
});

let stream;

function iniciarCamara() {
    navigator.mediaDevices.getUserMedia({ video: true })
        .then(mediaStream => {
            stream = mediaStream;
            document.getElementById("video").srcObject = stream;
            document.getElementById("video").style.display = "block";
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
        };
        lector.readAsDataURL(archivo);
    }, "image/jpeg", 0.95);
}
</script>
                </section>

                <button type="submit" class="btn">Registrar persona</button>
                <p><a href="../Vistas/index.php">Volver a Pagina principal</a></p>
            </fieldset>
        </form>
    </section>
    <br>

    <?php include('footer.php');?>
</body>
</html>