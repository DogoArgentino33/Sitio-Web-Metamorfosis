<?php
//Funcion complementaria del select anidado
include 'conexion.php';

//Traemos el dato
$idProvincia = isset($_POST['codprov']) ? intval($_POST['codprov']) : 0;

//Hacemos la consulta
$sql = "SELECT coddpto, nomdpto FROM dpto WHERE codprov = $idProvincia ORDER BY nomdpto ASC";
$result = mysqli_query($conexion, $sql);

//Traemos los datos de la base de datos con Fetch Assoc
$html = '';
while ($row = $result->fetch_assoc()) 
{
    $html .= "<option value='{$row['coddpto']}'>{$row['nomdpto']}</option>";
}
echo $html;
exit;

?>