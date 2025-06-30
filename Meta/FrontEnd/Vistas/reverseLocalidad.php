<?php
// Funcion complementaria de mapa
header('Content-Type: application/json; charset=utf-8');
require 'conexion.php';

// Validar que existan y sean numéricos los datos POST
if (
    !isset($_POST['latitud']) || !isset($_POST['longitud']) ||
    !is_numeric($_POST['latitud']) || !is_numeric($_POST['longitud'])
) {
    echo json_encode(['error' => 'coords']);
    exit;
}

$latitud = floatval($_POST['latitud']);
$longitud = floatval($_POST['longitud']);

// Validación extra por si las coordenadas son 0,0 (puedes ajustarla según necesidad)
if ($latitud == 0 && $longitud == 0) {
    echo json_encode(['error' => 'coords']);
    exit;
}

$sql = "
SELECT  localidades.codloc, localidades.nomloc, municipio.codmun, municipio.nommun, dpto.coddpto, dpto.nomdpto,
        ((localidades.latitud-?)*(localidades.latitud-?)+(localidades.longitud-?)*(localidades.longitud-?)) AS dist2
FROM    localidades 
JOIN    municipio  
ON localidades.codmun  = municipio.codmun
JOIN    dpto       
ON municipio.coddpto = dpto.coddpto
ORDER BY dist2
LIMIT 1";

$stmt = $conexion->prepare($sql);
$stmt->bind_param('dddd', $latitud, $latitud, $longitud, $longitud);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();

echo json_encode($row ?: ['error' => 'notfound'], JSON_UNESCAPED_UNICODE);
