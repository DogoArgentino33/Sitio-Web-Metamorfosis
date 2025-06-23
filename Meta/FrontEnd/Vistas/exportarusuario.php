<?php
include('auth.php');
include('conexion.php');

require_once __DIR__ . '/../../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

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
$formatosPermitidos = ['pdf', 'xls', 'xlsx', 'csv'];
if (!in_array($formato, $formatosPermitidos)) {
    die("Formato no soportado.");
}

// Consultar usuario
$inAtributos = implode(", ", array_map(function ($attr) {
    return preg_match('/^\w+$/', $attr) ? $attr : '';
}, $atributos));
$inAtributos = trim($inAtributos, ", ");

if (empty($inAtributos)) {
    die("No seleccionaste atributos válidos.");
}

$stmt = $conexion->prepare("SELECT $inAtributos, nom_usu FROM usuario WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    die("Usuario no encontrado.");
}
$usuario = $result->fetch_assoc();

// Función para obtener valor seguro
function valor($usuario, $key) {
    return isset($usuario[$key]) ? $usuario[$key] : '';
}

// Nombre archivo
$nomArchivo = "usuario_" . (isset($usuario['nom_usu']) ? preg_replace('/[^a-zA-Z0-9_\-]/', '_', strtolower($usuario['nom_usu'])) : "id_$id") . "_$id";

// --- PDF ---
if ($formato === 'pdf') {
    class CustomPDF extends TCPDF {
        public function Header() {
            // Logo (opcional)
            // $this->Image('ruta/logo.png', 20, 10, 20);

            // Título principal
            $this->SetFont('helvetica', 'B', 14);
            $this->SetTextColor(209, 46, 59); // Color rojo corporativo
            $this->Cell(0, 10, 'Metamorfosis - Exportación de Usuario', 0, 1, 'C');

            // Subtítulo con ID y fecha/hora
            $this->SetFont('helvetica', '', 10);
            $this->SetTextColor(0, 0, 0);
            $this->Cell(0, 5, 'ID: ' . $GLOBALS['id'] . ' | Fecha: ' . date('d-m-Y H:i'), 0, 1, 'C');
            $this->Ln(5); // Espacio después del encabezado
        }

        public function Footer() {
            $this->SetY(-15);
            $this->SetFont('helvetica', 'I', 9);
            $this->SetTextColor(120, 120, 120);
            $this->Cell(0, 10, 'Página '.$this->getAliasNumPage().' de '.$this->getAliasNbPages(), 0, 0, 'R');
        }
    }

    $pdf = new CustomPDF();
    $pdf->SetCreator('Sistema Metamorfosis');
    $pdf->SetAuthor('Metamorfosis');
    $pdf->SetTitle("Exportación Usuario ID $id");

    // Márgenes
    $pdf->SetMargins(20, 40, 20); // top 40 para dejar espacio al header
    $pdf->SetHeaderMargin(10);
    $pdf->SetFooterMargin(15);
    $pdf->SetAutoPageBreak(true, 25);

    // Tipografía
    $pdf->SetFont('helvetica', '', 11);

    // Header/Footer activos
    $pdf->setPrintHeader(true);
    $pdf->setPrintFooter(true);

    // Agrega la página
    $pdf->AddPage();

   $html = '
    <style>
        h2 {
            color: #d12e3b;
            text-align: center;
            font-family: helvetica;
            margin-bottom: 20px;
        }
        table {
            border-collapse: collapse;
            width: 100%;
            margin-top: 10px;
            font-family: helvetica;
            font-size: 11pt;
        }
        th {
            background-color: #d12e3b;
            color: #ffffff;
            padding: 8px;
            border: 1px solid #d12e3b;
            text-align: center;
        }
        td {
            background-color: #ffffff;
            color: #333333;
            padding: 8px;
            border: 1px solid #d12e3b;
            text-align: center;
        }
        tr:nth-child(even) td {
            background-color: #f9f9f9;
        }
    </style>

    <h2>Ficha del Usuario</h2>
    <table>';

    foreach ($atributos as $attr) {
    if ($attr === 'img_perfil') {
            $img = valor($usuario, 'img_perfil');
            if ($img && (file_exists($img) || filter_var($img, FILTER_VALIDATE_URL))) {
                $pdf->Image($img, '', '', 40, 40, '', '', 'T', false, 300);
                $pdf->Ln(50);
            }
        } else {
            $label = ucwords(str_replace('_', ' ', $attr));
            $html .= "<tr><th>$label</th><td>" . htmlspecialchars(valor($usuario, $attr)) . "</td></tr>";
        }
    }

    $html .= '</table>';
    $pdf->writeHTML($html, true, false, true, false, '');
    $pdf->Output("$nomArchivo.pdf", 'D');
    exit;
}

// --- Excel (XLS / XLSX) ---
if ($formato === 'xls' || $formato === 'xlsx') {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Paleta corporativa
    $rojo = 'D12E3B';
    $blanco = 'FFFFFF';

    // Título principal
    $sheet->mergeCells('A1:E1');
    $sheet->setCellValue('A1', 'METAMORFOSIS - Exportación de Usuario');
    $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16)->getColor()->setRGB($blanco);
    $sheet->getStyle('A1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($rojo);
    $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    // Subtítulo
    $sheet->mergeCells('A2:E2');
    $sheet->setCellValue('A2', 'ID: ' . $id . '    Fecha: ' . date('d-m-Y H:i'));
    $sheet->getStyle('A2')->getFont()->setItalic(true)->setSize(11);
    $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

    // Espacio antes de la tabla
    $startRow = 4;

    // Encabezados
    $col = 'A';
    foreach ($atributos as $attr) {
        $label = ucwords(str_replace('_', ' ', $attr));
        $cell = $col . $startRow;
        $sheet->setCellValue($cell, $label);
        $sheet->getStyle($cell)->getFont()->setBold(true)->getColor()->setRGB($blanco);
        $sheet->getStyle($cell)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($rojo);
        $sheet->getStyle($cell)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle($cell)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $col++;
    }

    // Datos (fila siguiente a los encabezados)
    $row = $startRow + 1;
    $col = 'A';
    $imgCol = null; // Para almacenar la columna de la imagen y excluirla de autosize

    foreach ($atributos as $attr) {
        $cell = $col . $row;
        if ($attr === 'img_perfil') {
            $imgCol = $col; // Guardamos la columna de la imagen
            $imgPath = valor($usuario, 'img_perfil');
            if ($imgPath && file_exists($imgPath)) {
                $drawing = new Drawing();
                $drawing->setPath($imgPath);

                // Solo fijamos la altura de la imagen (dejamos que el ancho se calcule automáticamente)
                $drawing->setHeight(80); // px
                $drawing->setCoordinates($cell);
                $drawing->setOffsetX(5);
                $drawing->setOffsetY(5);
                $drawing->setWorksheet($sheet);

                // Ajustamos alto de fila manualmente (en puntos)
                $sheet->getRowDimension($row)->setRowHeight(65); // esto acomoda visualmente la imagen

                // Ajustamos ancho de la columna en unidades aproximadas
                $sheet->getColumnDimension($col)->setWidth(22); // 15 ≈ 80-90 px
            } else {
                $sheet->setCellValue($cell, 'Imagen no disponible');
            }
        } else {
            $sheet->setCellValue($cell, valor($usuario, $attr));
        }
        $sheet->getStyle($cell)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle($cell)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER)->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $col++;
    }

    // Autoajuste de columnas **excepto la de la imagen**
    foreach (range('A', chr(ord($col) - 1)) as $columnID) {
        if ($columnID !== $imgCol) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }
    }

    // Pie de página profesional
    $sheet->getHeaderFooter()->setOddFooter('&LMetamorfosis&R Página &P de &N');

    // Exportación
    $writer = $formato === 'xls' ? new Xls($spreadsheet) : new Xlsx($spreadsheet);
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header("Content-Disposition: attachment;filename=\"$nomArchivo.$formato\"");
    header('Cache-Control: max-age=0');
    $writer->save('php://output');
    exit;
}

// --- EXPORTAR CSV ---
if ($formato === 'csv') {
    header('Content-Type: text/csv; charset=UTF-8');
    header("Content-Disposition: attachment;filename=\"$nomArchivo.csv\"");

    $output = fopen('php://output', 'w');

    // Separador visual
    fputcsv($output, []);
    fputcsv($output, ['==============================']);
    fputcsv($output, ['METAMORFOSIS - Exportación de Usuario']);
    fputcsv($output, ['==============================']);
    fputcsv($output, []);
    
    // Fecha y ID
    fputcsv($output, ['ID del Usuario', $id]);
    fputcsv($output, ['Fecha de Exportación', date('d-m-Y H:i')]);
    fputcsv($output, []);

    // Línea divisoria
    fputcsv($output, ['------------------------------']);

    // Cabeceras
    $cabeceras = [];
    foreach ($atributos as $attr) {
        $cabeceras[] = ucwords(str_replace('_', ' ', $attr));
    }
    fputcsv($output, $cabeceras);

    // Datos
    $fila = [];
    foreach ($atributos as $attr) {
        $fila[] = valor($usuario, $attr);
    }
    fputcsv($output, $fila);

    // Línea final y marca
    fputcsv($output, []);
    fputcsv($output, ['------------------------------']);
    fputcsv($output, ['Documento generado por Metamorfosis']);
    fputcsv($output, ['Fin del documento.']);

    fclose($output);
    exit;
}
?>