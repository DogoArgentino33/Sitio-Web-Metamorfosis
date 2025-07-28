<?php include('auth.php'); include('conexion.php'); 

//Verificando si la cuenta no es rol gerente
if (isset($_SESSION['rol']) && $_SESSION['rol'] != 1 && $_SESSION['rol'] != 4){
    header("Location: index.php"); 
    exit;
}



if (!isset($_GET['id']) || !is_numeric($_GET['id'])) 
{
    echo "ID de persona no válido.";
    exit;
}

$id = intval($_GET['id']);

$stmt = $conexion->prepare("SELECT  id, nombre, apellido, dni, fec_nac, pais, nomprov, nomdpto, nommun, nomloc, barrio, calle, altura, genero, img, lat, lng FROM persona
INNER JOIN localidades ON persona.localidad  = localidades.codloc
INNER JOIN municipio ON localidades.codmun  = municipio.codmun
INNER JOIN dpto ON municipio.coddpto = dpto.coddpto
INNER JOIN provincias ON dpto.codprov = provincias.codprov
WHERE persona.id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    echo "Persona no encontrada.";
    exit;
}

$persona = $resultado->fetch_assoc();
?>

<style>
    #map 
    {
    height: 350px; 
    width: 400px;
    margin: auto;
    }
</style>

<!-- Cuerpo de la pagina -->
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Información de Persona</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../Estilos/verusuario.css">
     <!-- Leaflet -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
     crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
     crossorigin=""> </script>

     <!-- SweetAlert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        .swal2-container {
            z-index: 99999 !important;
        }
    </style>
</head>
<body>
    <h1>Información de la Persona</h1>
    <section class="dni-card">
        <div class="dni-img">
            <img class="img-perfil" src="<?= htmlspecialchars($persona['img']) ?>" alt="Imagen de perfil" onclick="mostrarModal(this)">
        </div>
        <div class="dni-info">
            <h3>Datos Personales</h3>
            <p><strong>Nombre:</strong> <?= htmlspecialchars($persona['nombre']) ?></p>
            <p><strong>Apellido:</strong> <?= htmlspecialchars($persona['apellido']) ?></p>
            <p><strong>Dni:</strong> <?= htmlspecialchars($persona['dni']) ?></p>
            <p><strong>Fecha de Nacimiento:</strong> <?= htmlspecialchars($persona['fec_nac']) ?></p>
            <p><strong>Genero:</strong> <?= htmlspecialchars($persona['genero']) ?></p>

            <h3>Datos de Domicilio</h3>
            <p><strong>Pais:</strong> <?= htmlspecialchars($persona['pais']) ?></p>
            <p><strong>Provincia:</strong> <?= htmlspecialchars($persona['nomprov']) ?></p>
            <p><strong>Departamento:</strong> <?= htmlspecialchars($persona['nomdpto']) ?></p>
            <p><strong>Municipio:</strong> <?= htmlspecialchars($persona['nommun']) ?></p>
            <p><strong>Localidad:</strong> <?= htmlspecialchars($persona['nomloc']) ?></p>
            <p><strong>Barrio:</strong> <?= htmlspecialchars($persona['barrio']) ?></p>
            <p><strong>Calle:</strong> <?= htmlspecialchars($persona['calle']) ?></p>
            <p><strong>Altura:</strong> <?= htmlspecialchars($persona['altura']) ?></p>
            <div id="map"></div>
            <br>
            <a href="panelpersonas.php"><button type="button" class="boton">Volver al panel</button></a>
            <button type="button" class="boton-exportar" onclick="abrirModalExportar()">Exportar</button>
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
        imgAmpliada.src = imagen.src;
        modal.style.display = "flex";
    }

    function cerrarModal() {
        document.getElementById("modalImagen").style.display = "none";
    }
</script>

<!-- Modal de exportación -->
<section id="modalExportar"  onclick="cerrarModalExportar()">
    <section class="modal-exportar-card" onclick="event.stopPropagation();">
        <section class="modal-exportar-content">
            <h2>Exportar Persona</h2>
            <form id="formExportar" action="exportarpersona.php" method="POST" novalidate>
                <input type="hidden" name="id" value="<?= htmlspecialchars($persona['id']) ?>">

               <fieldset>
                    <legend>Selecciona los atributos a exportar:</legend>

                    <div class="exportar-categorias">
                        <div class="categoria">
                            <h3>Datos Personales</h3>
                            <label><input type="checkbox" name="atributos[]" value="img" checked> Imagen</label>
                            <label><input type="checkbox" name="atributos[]" value="nombre" checked> Nombre</label>
                            <label><input type="checkbox" name="atributos[]" value="apellido"> Apellido</label>
                            <label><input type="checkbox" name="atributos[]" value="dni"> DNI</label>
                            <label><input type="checkbox" name="atributos[]" value="genero"> Género</label>
                            <label><input type="checkbox" name="atributos[]" value="fec_nac"> Fecha de Nacimiento</label>
                        </div>

                        <div class="categoria">
                            <h3>Datos de Domicilio</h3>
                            <label><input type="checkbox" name="atributos[]" value="nomprov"> Provincia</label>
                            <label><input type="checkbox" name="atributos[]" value="nomdpto"> Departamento</label>
                            <label><input type="checkbox" name="atributos[]" value="nommun"> Municipio</label>
                            <label><input type="checkbox" name="atributos[]" value="nomloc"> Localidad</label>
                            <label><input type="checkbox" name="atributos[]" value="barrio"> Barrio</label>
                            <label><input type="checkbox" name="atributos[]" value="calle"> Calle</label>
                            <label><input type="checkbox" name="atributos[]" value="altura"> Altura</label>
                        </div>
                    </div>
                </fieldset>

                <fieldset>
                    <legend>Formato de exportación:</legend>
                    <label for="formato">Selecciona un formato:</label>
                    <select name="formato" id="formato" required>
                        <option value="" disabled selected>-- Seleccionar formato --</option>
                        <option value="pdf">PDF</option>
                        <option value="xls">XLS</option>
                        <option value="xlsx">XLSX</option>
                        <option value="csv">CSV</option>
                    </select>
                </fieldset>

                <nav class="modal-exportar-buttons" aria-label="Acciones del modal exportar">
                    <button type="button" class="boton" onclick="cerrarModalExportar()">Cancelar</button>
                    <button type="submit" class="boton-exportar">Exportar</button>
                </nav>
            </form>
        </section>
    </section>
</section>

<script>
    function abrirModalExportar() {
        const modal = document.getElementById('modalExportar');
        modal.style.display = 'flex';  // Aquí sí poner display:flex para mostrarlo
    }

    function cerrarModalExportar() {
        const modal = document.getElementById('modalExportar');
        modal.style.display = 'none';  // Ocultarlo
    }
</script>

<!-- Script de mapa -->
<script>
    // datos del usuario y direccion
    const lat = <?= floatval($persona['lat']) ?>;
    const lng = <?= floatval($persona['lng']) ?>;

    // centramos el mapa 
    const map = L.map('map').setView([lat, lng], 20);

    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap'
    }).addTo(map);

    // agregamos el mapa
    L.marker([lat, lng]).addTo(map)
        .bindPopup(`<strong>Ubicacion</strong>`)
        .openPopup();
</script>

<script>
    document.getElementById('formExportar').addEventListener('submit', function(e) {
        const checkboxes = document.querySelectorAll('input[name="atributos[]"]:checked');
        const formato = document.getElementById('formato').value;

        if (checkboxes.length === 0) {
            e.preventDefault(); // Evita que se envíe el formulario

            Swal.fire({
                icon: 'warning',
                title: 'Campos requeridos',
                text: 'Debe seleccionar al menos un campo para exportar.',
                confirmButtonText: 'Entendido'
            });
        }
        else{
            if(formato === ""){
                e.preventDefault(); // Evita que se envíe el formulario

                Swal.fire({
                    icon: 'warning',
                    title: 'Campos requeridos',
                    text: 'Debe seleccionar un formato para exportar.',
                    confirmButtonText: 'Entendido'
                });

            }
        }
    });
</script>

</body>
</html>