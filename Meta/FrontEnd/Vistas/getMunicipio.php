<?php
//Funcion complementaria del select anidado
include 'conexion.php';

//Traemos el dato
$idDepartamento = isset($_POST['coddpto']) ? intval($_POST['coddpto']) : 0;

//Hacemos la consulta
$sql = "SELECT codmun, nommun FROM municipio WHERE coddpto = $idDepartamento ORDER BY nommun ASC";
$result = mysqli_query($conexion, $sql);

//Traemos los datos de la base de datos con Fetch Assoc
$html = '';
while ($row = $result->fetch_assoc()) 
{
    $html .= "<option value='{$row['codmun']}'>{$row['nommun']}</option>";
}
echo $html;
exit;

?>