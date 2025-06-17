<?php
include('auth.php');
include('conexion.php');

require_once __DIR__ . '/../../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
//use TCPDF;

// Validar inputs
if (
    !isset($_POST['id'], $_POST['atributos'], $_POST['formato']) ||
    !is_numeric($_POST['id']) ||
    !is_array($_POST['atributos']) ||
    empty($_POST['formato'])
) {
    die("Parámetros inválidos.");
}

$id = intval($_POST['id']);
$atributos = $_POST['atributos'];
$formato = strtolower($_POST['formato']);

// Validar formato permitido
$formatsPermitidos = ['pdf', 'xls', 'xlsx', 'csv'];
if (!in_array($formato, $formatsPermitidos)) {
    die("Formato no soportado.");
}

// Consultar usuario
$inAtributos = implode(", ", array_map(function($attr) {
    return preg_match('/^\w+$/', $attr) ? $attr : '';
}, $atributos));
$inAtributos = trim($inAtributos, ", ");

if (empty($inAtributos)) {
    die("No seleccionaste atributos válidos.");
}

$stmt = $conexion->prepare("SELECT $inAtributos FROM usuario WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    die("Usuario no encontrado.");
}
$usuario = $result->fetch_assoc();

// Función para obtener valor seguro para exportar (por si hay NULL)
function valor($usuario, $key) {
    return isset($usuario[$key]) ? $usuario[$key] : '';
}

// --- EXPORTAR PDF ---
if ($formato === 'pdf') {
    $pdf = new TCPDF();
    $pdf->SetCreator('Tu Sistema');
    $pdf->SetAuthor('Metamorfosis');
    $pdf->SetTitle('Exportación Usuario ID ' . $id);

    // Encabezado con título, fecha y hora
    $fechaHora = date('d-m-Y H:i:s');
    $pdf->setHeaderData('', 0, "Exportación Usuario ID $id", "Fecha: $fechaHora");

    // Pie de página con número de páginas (TCPDF lo hace automáticamente si llamas SetFooter)
    $pdf->setFooterFont(Array('helvetica', '', 8));
    $pdf->setPrintHeader(true);
    $pdf->setPrintFooter(true);
    $pdf->AddPage();

    $html = '<h2>Información del Usuario</h2><table border="1" cellpadding="5">';

    foreach ($atributos as $attr) {
        if ($attr === 'img_perfil') {
            // Insertar imagen si existe archivo o URL
            $img = valor($usuario, 'img_perfil');
            if ($img && (file_exists($img) || filter_var($img, FILTER_VALIDATE_URL))) {
                $pdf->Image($img, '', '', 40, 40, '', '', 'T', false, 300);
                $pdf->Ln(45);
            }
        } else {
            $label = ucfirst(str_replace('_', ' ', $attr));
            $html .= "<tr><td><strong>$label</strong></td><td>" . htmlspecialchars(valor($usuario, $attr)) . "</td></tr>";
        }
    }
    $html .= '</table>';
    $pdf->writeHTML($html, true, false, true, false, '');

    // Salida
    $pdf->Output("usuario_$id.pdf", 'D'); // Descarga
    exit;
}

// --- EXPORTAR EXCEL (XLS/XLSX) ---
if ($formato === 'xls' || $formato === 'xlsx') {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Encabezado personalizado (Título, fecha, hora)
    $sheet->setCellValue('A1', 'Exportación Usuario ID ' . $id);
    $sheet->setCellValue('A2', 'Fecha: ' . date('d-m-Y H:i:s'));
    $sheet->mergeCells('A1:C1');
    $sheet->mergeCells('A2:C2');

    // Nombres columnas (fila 4)
    $col = 'A';
    foreach ($atributos as $attr) {
        $label = ucfirst(str_replace('_', ' ', $attr));
        $sheet->setCellValue($col . '4', $label);
        $col++;
    }

    // Datos usuario (fila 5)
    $col = 'A';
    foreach ($atributos as $attr) {
        if ($attr === 'img_perfil') {
            // Insertar imagen si es local
            $imgPath = valor($usuario, 'img_perfil');
            if ($imgPath && file_exists($imgPath)) {
                $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
                $drawing->setPath($imgPath);
                $drawing->setHeight(80);
                $drawing->setCoordinates($col . '5');
                $drawing->setWorksheet($sheet);
            } else {
                $sheet->setCellValue($col . '5', 'Imagen no disponible');
            }
        } else {
            $sheet->setCellValue($col . '5', valor($usuario, $attr));
        }
        $col++;
    }

    // Pie de página con número de página (solo para Excel)
    $sheet->getHeaderFooter()->setOddFooter('&RPágina &P de &N');

    // Generar y enviar archivo
    if ($formato === 'xls') {
        $writer = new Xls($spreadsheet);
        $filename = "usuario_$id.xls";
    } else {
        $writer = new Xlsx($spreadsheet);
        $filename = "usuario_$id.xlsx";
    }

    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="'.$filename.'"');
    header('Cache-Control: max-age=0');

    $writer->save('php://output');
    exit;
}

// --- EXPORTAR CSV ---
if ($formato === 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment;filename="usuario_' . $id . '.csv"');

    $output = fopen('php://output', 'w');

    // Cabecera CSV
    $cabeceras = [];
    foreach ($atributos as $attr) {
        $cabeceras[] = ucfirst(str_replace('_', ' ', $attr));
    }
    fputcsv($output, $cabeceras);

    // Datos
    $fila = [];
    foreach ($atributos as $attr) {
        if ($attr === 'img_perfil') {
            $fila[] = valor($usuario, 'img_perfil');
        } else {
            $fila[] = valor($usuario, $attr);
        }
    }
    fputcsv($output, $fila);

    fclose($output);
    exit;
}

?>
