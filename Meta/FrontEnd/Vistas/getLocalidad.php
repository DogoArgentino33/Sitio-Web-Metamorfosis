<?php
//Funcion complementaria del select anidado
include 'conexion.php';

//Traemos el dato
$idMunicipio = isset($_POST['codmun']) ? intval($_POST['codmun']) : 0;

//Hacemos la consulta
$sql = "SELECT codloc, nomloc FROM localidades WHERE codmun = $idMunicipio ORDER BY nomloc ASC";
$result = mysqli_query($conexion, $sql);

//Traemos los datos de la base de datos con Fetch Assoc
$html = '';
while ($row = $result->fetch_assoc()) 
{
    $html .= "<option value='{$row['codloc']}'>{$row['nomloc']}</option>";
}
echo $html;
exit;

?>