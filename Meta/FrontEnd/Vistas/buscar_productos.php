<?php 
include('auth.php'); 
include('conexion.php'); 

$buscar = isset($_GET['buscar']) ? '%' . $conexion->real_escape_string($_GET['buscar']) . '%' : '%';
$pagina = isset($_GET['pagina']) ? max(1, intval($_GET['pagina'])) : 1;
$por_pagina = 10;
$offset = ($pagina - 1) * $por_pagina;

// Conteo total para paginación
$sql_total = "
    SELECT COUNT(DISTINCT p.id) as total
    FROM producto p
    LEFT JOIN producto_categoria pc ON pc.id_producto = p.id
    LEFT JOIN categoria c ON c.id = pc.id_categoria
    LEFT JOIN producto_talla pt ON pt.id_producto = p.id
    LEFT JOIN talla t ON t.id = pt.id_talla
    LEFT JOIN producto_tematica ptem ON ptem.id_producto = p.id
    LEFT JOIN tematica tm ON tm.id = ptem.id_tematica
    WHERE p.eliminado = 0
    AND (
        p.nombre LIKE ? OR 
        c.nombre_cat LIKE ? OR 
        t.talla LIKE ? OR 
        tm.nombre_tema LIKE ?
    )
";

$stmt_total = $conexion->prepare($sql_total);
$stmt_total->bind_param("ssss", $buscar, $buscar, $buscar, $buscar);
$stmt_total->execute();
$result_total = $stmt_total->get_result();
$total_registros = $result_total->fetch_assoc()['total'];
$total_paginas = ceil($total_registros / $por_pagina);

// Consulta con paginación
$sql = "
    SELECT 
        p.id,
        p.nombre,
        p.tipo,
        p.unidades_disponibles,
        p.precio,
        GROUP_CONCAT(DISTINCT c.nombre_cat SEPARATOR ', ') AS categorias,
        GROUP_CONCAT(DISTINCT t.talla SEPARATOR ', ') AS tallas,
        GROUP_CONCAT(DISTINCT tm.nombre_tema SEPARATOR ', ') AS tematicas,
        (
            SELECT ip.img 
            FROM img_producto ip 
            WHERE ip.id_producto = p.id 
            AND (ip.eliminado IS NULL OR ip.eliminado = 0)
            LIMIT 1
        ) AS imagen
    FROM producto p
    LEFT JOIN producto_categoria pc ON pc.id_producto = p.id
    LEFT JOIN categoria c ON c.id = pc.id_categoria
    LEFT JOIN producto_talla pt ON pt.id_producto = p.id
    LEFT JOIN talla t ON t.id = pt.id_talla
    LEFT JOIN producto_tematica ptem ON ptem.id_producto = p.id
    LEFT JOIN tematica tm ON tm.id = ptem.id_tematica
    WHERE p.eliminado = 0
    AND (
        p.nombre LIKE ? OR 
        c.nombre_cat LIKE ? OR 
        t.talla LIKE ? OR 
        tm.nombre_tema LIKE ?
    )
    GROUP BY p.id
    ORDER BY p.id ASC
    LIMIT ? OFFSET ?
";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("ssssii", $buscar, $buscar, $buscar, $buscar, $por_pagina, $offset);
$stmt->execute();
$result = $stmt->get_result();

$productos = [];

while ($producto = $result->fetch_assoc()) {
    $productos[] = $producto;
}

header('Content-Type: application/json');
echo json_encode([
    'productos' => $productos,
    'total_paginas' => $total_paginas,
    'pagina_actual' => $pagina
]);
?>