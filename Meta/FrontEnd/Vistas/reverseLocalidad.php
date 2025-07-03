<?php
//Funcion complementaria de mapa
require 'conexion.php';                      

//Traemos los valores
$latitud = isset($_GET['latitud']) ? floatval($_GET['latitud']) : 0;
$longitud = isset($_GET['longitud']) ? floatval($_GET['longitud']) : 0;
if (!$latitud || !$longitud) { echo json_encode(['error'=>'coords']); exit; }

//Hacemos la consulta 
$sql = "
SELECT  provincias.codprov, provincias.nomprov , localidades.codloc, localidades.nomloc, municipio.codmun, municipio.nommun, dpto.coddpto, dpto.nomdpto,
        ((localidades.latitud-?)*(localidades.latitud-?)+(localidades.longitud-?)*(localidades.longitud-?)) AS dist2
FROM    localidades 
JOIN    municipio  
ON localidades.codmun  = municipio.codmun
JOIN    dpto       
ON municipio.coddpto = dpto.coddpto
JOIN provincias
ON dpto.codprov = provincias.codprov 
ORDER BY dist2
LIMIT 1";

//Preparamos y llevamos esto al registrar persona
$stmt = $conexion->prepare($sql);
if (!$stmt) {
    // Muestra el motivo exacto
    echo json_encode(['error' => $conexion->error], JSON_UNESCAPED_UNICODE);
    exit;
}
$stmt->bind_param('dddd', $latitud, $latitud, $longitud, $longitud);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();

echo json_encode($row ?: ['error'=>'notfound'], JSON_UNESCAPED_UNICODE); 
?>