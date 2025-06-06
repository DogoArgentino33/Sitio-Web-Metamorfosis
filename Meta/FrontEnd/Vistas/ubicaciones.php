<?php
$nivel = $_GET['nivel'] ?? '';
$padres = json_decode($_GET['padres'] ?? '[]', true);
$data = json_decode(file_get_contents('datos_ubicaciones.json'), true);

function obtenerOpciones($data, $nivel, $padres) {
    foreach ($padres as $p) {
        $data = $data[$p] ?? [];
    }

    if ($nivel === 'localidad') {
        $opciones = $data;
    } else {
        $opciones = array_keys($data);
    }

    echo '<option value="">Seleccione</option>';
    foreach ($opciones as $op) {
        echo "<option value=\"$op\">$op</option>";
    }
}

obtenerOpciones($data, $nivel, $padres);
