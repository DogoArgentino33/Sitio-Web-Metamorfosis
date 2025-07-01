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
    $img          = isset($_FILES['img-persona']) ? $_FILES['img-persona'] : null;
    $img_base64 = isset($_POST['foto']) ? $_POST['foto'] : null;
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
    if ($img_base64) {
        // Procesar imagen desde base64 (cámara)
        $data = explode(',', $img_base64);
        if (count($data) === 2) {
            $imagen_decodificada = base64_decode($data[1]);

            $nombre_img = uniqid() . ".jpg";
            $directorio_destino = "uploads/persona/";
            $ruta_completa = $directorio_destino . $nombre_img;

            if (file_put_contents($ruta_completa, $imagen_decodificada)) {
                $ruta_imagen = $ruta_completa;
            } else {
                $errores[] = "No se pudo guardar la imagen capturada.";
            }
        } else {
            $errores[] = "Formato de imagen de cámara no válido.";
        }

    } elseif ($img && $img['error'] == 0) {
        // Procesar imagen subida por archivo
        $permitidos = ['image/jpeg', 'image/png', 'image/gif', 'image/jpg'];

        if (!in_array($img['type'], $permitidos)) {
            $errores[] = "El formato de imagen no es válido. Solo se permiten JPG, PNG o GIF.";
        } else {
            $origen_temp = $img['tmp_name'];
            list($ancho_original, $alto_original) = getimagesize($origen_temp);
            $ancho_nuevo = 1280;
            $alto_nuevo = 1280;

            switch ($img['type']) {
                case 'image/jpeg':
                case 'image/jpg':
                    $origen = imagecreatefromjpeg($origen_temp);
                    break;
                case 'image/png':
                    $origen = imagecreatefrompng($origen_temp);
                    break;
                case 'image/gif':
                    $origen = imagecreatefromgif($origen_temp);
                    break;
                default:
                    $errores[] = "Tipo de imagen no soportado.";
                    $origen = false;
            }

            if ($origen) {
                $imagen_redimensionada = imagecreatetruecolor($ancho_nuevo, $alto_nuevo);
                $blanco = imagecolorallocate($imagen_redimensionada, 255, 255, 255);
                imagefill($imagen_redimensionada, 0, 0, $blanco);

                imagecopyresampled(
                    $imagen_redimensionada,
                    $origen,
                    0, 0, 0, 0,
                    $ancho_nuevo,
                    $alto_nuevo,
                    $ancho_original,
                    $alto_original
                );

                $nombre_img = uniqid() . ".jpg";
                $directorio_destino = "uploads/persona/";
                $ruta_completa = $directorio_destino . $nombre_img;

                if (imagejpeg($imagen_redimensionada, $ruta_completa, 90)) {
                    $ruta_imagen = $ruta_completa;
                } else {
                    $errores[] = "No se pudo guardar la imagen redimensionada.";
                }

                imagedestroy($origen);
                imagedestroy($imagen_redimensionada);
            }
        }
    } else {
        $errores[] = "Debes subir una imagen válida o tomar una con la cámara.";
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
        $ruta_imagen  = mysqli_real_escape_string($conexion, $ruta_imagen);
        $lat          = mysqli_real_escape_string($conexion, $lat);
        $lng          = mysqli_real_escape_string($conexion, $lng);

        $sql_insert = "INSERT INTO `persona`(`nombre`, `apellido`, `dni`, `fec_nac`, `pais`, `provincia`, `genero`, `img`, `calle`, `altura`, `barrio`, `departamento`, `municipio`, `localidad`, `lat`, `lng`) 
        VALUES ('$nombre','$apellido','$dni','$fec_nac','$pais','$provincia','$genero','$ruta_imagen','$calle','$altura','$barrio','$departamento','$municipio','$localidad','$lat','$lng')";

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

<script>
document.addEventListener("DOMContentLoaded", () => {

  /* ---------- Variables generales ---------- */
  const inputImg = document.getElementById("img-persona");
  const maxPesoMB = 4;
  const maxAncho = 1280;
  const maxAlto = 1280;
  const hidden = document.getElementById("foto-base64");

  const modal = document.getElementById("modalMAIN");
  const btn = document.getElementById("btnmostrarmodalverbo");
  const span = document.getElementsByClassName("close")[0];
  const video = document.getElementById("video");
  const canvas = document.getElementById("canvas");
  const startBt = document.getElementById("sacar-foto");

  const width = 350;
  let height = 0;
  let streaming = false;
  let stream = null;

  /* ---------- Función de previsualización común ---------- */
  function mostrarPreviewFoto(base64) {
    // Eliminar preview anterior y botón cancelar si existen
    const previewExistente = document.getElementById("preview-img");
    if (previewExistente) previewExistente.remove();
    const botonCancelar = document.getElementById("cancelar-img");
    if (botonCancelar) botonCancelar.remove();

    // Crear imagen preview
    const img = document.createElement("img");
    img.id = "preview-img";
    img.src = base64;
    img.style.maxWidth = "200px";
    img.style.marginTop = "10px";

    const contenedor = inputImg.parentNode;
    contenedor.appendChild(img);

    // Crear botón cancelar
    const btnCancelar = document.createElement("button");
    btnCancelar.id = "cancelar-img";
    btnCancelar.textContent = "Cancelar imagen";
    btnCancelar.type = "button";
    btnCancelar.style.display = "block";
    btnCancelar.style.marginTop = "10px";
    btnCancelar.onclick = function () {
      inputImg.value = "";
      hidden.value = "";
      img.remove();
      btnCancelar.remove();
      inputImg.disabled = false;
    };

    contenedor.appendChild(btnCancelar);
    inputImg.disabled = true;
  }

  /* ---------- Vista previa al subir imagen desde archivo ---------- */
  inputImg.addEventListener("change", function () {
    const archivo = this.files[0];
    const mensajeError = this.nextElementSibling;

    const previewExistente = document.getElementById("preview-img");
    if (previewExistente) previewExistente.remove();
    const botonCancelar = document.getElementById("cancelar-img");
    if (botonCancelar) botonCancelar.remove();

    if (!archivo) return;

    if (archivo.size > maxPesoMB * 1024 * 1024) {
      mensajeError.textContent = `La imagen no debe superar los ${maxPesoMB} MB.`;
      this.value = "";
      return;
    }

    const reader = new FileReader();
    reader.onload = function (e) {
      const img = new Image();
      img.src = e.target.result;

      img.onload = function () {
        if (img.width !== maxAncho || img.height !== maxAlto) {
          mensajeError.textContent = `Nota: la imagen será redimensionada automáticamente a ${maxAncho} x ${maxAlto} píxeles.`;
        } else {
          mensajeError.textContent = "";
        }

        mostrarPreviewFoto(e.target.result);
      };
    };

    reader.readAsDataURL(archivo);
  });

  /* ---------- Control del modal ---------- */
  btn.onclick = () => {
    modal.style.display = "block";
    initCamera();
  };

  span.onclick = closeModal;
  window.onclick = (e) => { if (e.target === modal) closeModal(); };

  function closeModal() {
    inputImg.disabled = false;
    modal.style.display = "none";
    stopCamera();
  }

  /* ---------- Cámara ---------- */
  function initCamera() {
    if (stream) return;

    navigator.mediaDevices
      .getUserMedia({ video: { facingMode: "user" }, audio: false })
      .then(s => {
        stream = s;
        video.srcObject = s;
        video.play();
      })
      .catch(err => console.error("Error cámara:", err));
  }

  function stopCamera() {
    if (stream) {
      stream.getTracks().forEach(t => t.stop());
      stream = null;
      streaming = false;
    }
  }

  video.addEventListener("canplay", () => {
    if (!streaming) {
      height = video.videoHeight / (video.videoWidth / width);
      if (isNaN(height)) height = width / (4 / 3);

      video.width = width;
      video.height = height;
      canvas.width = width;
      canvas.height = height;
      streaming = true;
    }
  });

  startBt.addEventListener("click", e => {
    e.preventDefault();
    takePicture();
  });



  function takePicture() {
    document.getElementById("img-persona").required = false;
    document.getElementById("img-persona-mobile").required = false;
    if (!streaming) return;
    const ctx = canvas.getContext("2d");
    ctx.drawImage(video, 0, 0, width, height);
    const data = canvas.toDataURL("image/png");

    hidden.value = data;
    canvas.style.display = "none";
    closeModal();
    mostrarPreviewFoto(data);
  }
});

        // Validación al enviar el formulario para asegurarse de que haya una imagen
    document.querySelector("form").addEventListener("submit", function (e) {
        const inputFilePC = document.getElementById("img-persona");
        const inputFileMobile = document.getElementById("img-persona-mobile");
        const base64 = document.getElementById("foto-base64").value;

        if (!inputFilePC.value && !inputFileMobile.value && !base64) {
            e.preventDefault();
            alert("Debes subir una imagen o tomar una foto.");
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
                    <label for="img-persona">Imagen personal:</label>
                    <!-- Input para PC -->
                    <input id="img-persona" name="img-persona" type="file" accept="image/*" required style="display:none;">
                    <!-- Input para móvil -->
                    <input id="img-persona-mobile" name="img-persona" type="file" accept="image/*" capture="environment" style="display:none;" required>

                    <script>
                            document.addEventListener("DOMContentLoaded", function () {
                                function esMovil() {
                                    return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
                                }

                                if (esMovil()) {
                                    const inputMobile = document.getElementById('img-persona-mobile');
                                    const inputPC = document.getElementById('img-persona');
                                    inputMobile.style.display = 'block';
                                    inputMobile.required = true;
                                    inputPC.required = false;
                                } else {
                                    const inputPC = document.getElementById('img-persona');
                                    const inputMobile = document.getElementById('img-persona-mobile');
                                    inputPC.style.display = 'block';
                                    inputPC.required = true;
                                    inputMobile.required = false;
                                }
                            });

                    </script>
                <!-- Camara -->
                 <br>
                 <input type="button" value="Activar Camara" id="btnmostrarmodalverbo">

                <!-- Modal de la Cámara -->
                <section id="modalMAIN" class="modal">
                <section class="modal-content">

                <!-- Aqui inicia el contenido de la cámara -->
                <section class="camera">
                <video id="video">Video stream not available.</video>    
                </section>

                <!-- Muestra la imagen -->
                <canvas id="canvas" style="display:none;"></canvas>
                
                <br>
                <br>
                <section class="modal-btns">
                    <!-- Botones de sacar foto y guardar en base de datos -->
                    <input id="sacar-foto" type="button" value="Tomar foto"></input>
                </section>


                <!-- este  imput guarda y envia la foto -->
                <input type="hidden" name="foto" id="foto-base64" />

                <span class="close">X</span>
                </section>
                </section>
                <!-- Fin Cámara -->


                    <span class="error" style="color:red;"><?php echo $error_img; ?></span>
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