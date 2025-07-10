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

$sql = "
SELECT 
    p.id,
    p.nombre,
    p.apellido,
    p.dni,
    p.fec_nac,
    p.genero,
    p.img,
    p.calle,
    p.altura,
    p.barrio,
    pr.nomprov AS nomprov,
    d.nomdpto AS nomdpto,
    m.nommun AS nommun,
    l.nomloc AS nomloc,
    p.lat,
    p.lng
FROM persona p
LEFT JOIN localidades l ON p.localidad = l.codloc
LEFT JOIN municipio m ON l.codmun = m.codmun
LEFT JOIN dpto d ON m.coddpto = d.coddpto
LEFT JOIN provincias pr ON d.codprov = pr.codprov
WHERE p.id = ?
";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    die("Persona no encontrado.");
}
$persona = $result->fetch_assoc();

// Función para obtener valor seguro
function valor($persona, $key) {
    return isset($persona[$key]) ? $persona[$key] : '';
}

// Nombre archivo
$nomArchivo = "persona_" . (isset($persona['nombre']) ? preg_replace('/[^a-zA-Z0-9_\-]/', '_', strtolower($persona['nombre'])) : "id_$id") . "_$id";

// --- PDF ---
if ($formato === 'pdf') {
    class CustomPDF extends TCPDF {
        public function Header() {
            // Logo (opcional)
            // $this->Image('ruta/logo.png', 20, 10, 20);

            // Título principal
            $this->SetFont('helvetica', 'B', 14);
            $this->SetTextColor(209, 46, 59); // Color rojo corporativo
            $this->Cell(0, 10, 'Metamorfosis - Exportación de Persona', 0, 1, 'C');

            // Subtítulo con ID y fecha/hora
            $this->SetFont('helvetica', '', 10);
            $this->SetTextColor(0, 0, 0);
            $this->Cell(0, 5, 'Fecha: ' . date('d-m-Y') . ' | Hora: ' . date('H:i'), 0, 1, 'C');
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
    $pdf->SetTitle("Exportación Persona ID $id");

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

    // Diccionario de nombres amigables
    $nombresCampos = [
        'nombre' => 'Nombre(s)',
        'apellido' => 'Apellido(s)',
        'dni' => 'DNI',
        'fec_nac' => 'Fecha de Nacimiento',
        'genero' => 'Género',
        'img' => 'Imagen de Perfil',
        'calle' => 'Calle',
        'altura' => 'Altura',
        'barrio' => 'Barrio',
        'nomprov' => 'Provincia',
        'nomdpto' => 'Departamento',
        'nommun' => 'Municipio',
        'nomloc' => 'Localidad',
        'lat' => 'Latitud',
        'lng' => 'Longitud'
    ];

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
            font-size: 12pt; /* tamaño aumentado */
        }
        th {
            background-color: #d12e3b;
            color: #ffffff;
            padding: 10px; /* padding aumentado */
            border: 1px solid #d12e3b;
            text-align: center;
            vertical-align: middle;
            width: 35%;
        }
        td {
            background-color: #ffffff;
            color: #333333;
            padding: 10px;
            border: 1px solid #d12e3b;
            text-align: center;
            vertical-align: middle;
        }
        tr:nth-child(even) td {
            background-color: #f9f9f9;
        }
    </style>

    <h2>Ficha de Persona</h2>
    <table>';

    // --- Mostrar imagen primero ---
    if (in_array('img', $atributos)) {
        $imgPath = valor($persona, 'img');
        $label = isset($nombresCampos['img']) ? $nombresCampos['img'] : 'Imagen';

        if ($imgPath && (file_exists($imgPath) || filter_var($imgPath, FILTER_VALIDATE_URL))) {
            if (filter_var($imgPath, FILTER_VALIDATE_URL)) {
                $imgData = file_get_contents($imgPath);
                $tempImg = tempnam(sys_get_temp_dir(), 'img_') . '.jpg';
                file_put_contents($tempImg, $imgData);
                $imgPath = $tempImg;
            }

            $html .= "<tr><th>$label</th><td><img src=\"$imgPath\" width=\"80\" height=\"80\" /></td></tr>";

            if (isset($tempImg)) {
                register_shutdown_function(function () use ($tempImg) {
                    if (file_exists($tempImg)) {
                        unlink($tempImg);
                    }
                });
            }
        } else {
            $html .= "<tr><th>$label</th><td>Imagen no disponible</td></tr>";
        }
    }

    // --- Mostrar el resto de los atributos ---
    foreach ($atributos as $attr) {
        if ($attr === 'img') continue; // ya se mostró

        $label = isset($nombresCampos[$attr]) ? $nombresCampos[$attr] : ucwords(str_replace('_', ' ', $attr));
        $valorCampo = valor($persona, $attr);
        $valorCampo = htmlspecialchars($valorCampo); // Sanear para evitar HTML no deseado
        $html .= "<tr><th>$label</th><td>$valorCampo</td></tr>";
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
    $sheet->setCellValue('A1', 'METAMORFOSIS - Exportación de Persona');
    $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16)->getColor()->setRGB($blanco);
    $sheet->getStyle('A1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($rojo);
    $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    // Subtítulo con Fecha y Hora (sin ID)
    $sheet->mergeCells('A2:E2');
    $fecha = date('d-m-Y');
    $hora = date('H:i');
    $sheet->setCellValue('A2', 'Fecha: ' . $fecha . ' | Hora: ' . $hora);
    $sheet->getStyle('A2')->getFont()->setItalic(true)->setSize(11);
    $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

    // Espacio antes de la tabla
    $startRow = 4;

    // Nombres amigables para encabezados
    $nombresCampos = [
        'nombre' => 'Nombre(s)',
        'apellido' => 'Apellido(s)',
        'fec_nac' => 'Fecha de Nacimiento',
        'img' => 'Imagen de Perfil',
        'lat' => 'Latitud',
        'lng' => 'Longitud'
    ];

    // Reorganizar atributos: img_perfil al principio
    $atributosOrdenados = $atributos;
    if (($key = array_search('img', $atributosOrdenados)) !== false) {
        unset($atributosOrdenados[$key]);
        array_unshift($atributosOrdenados, 'img');
    }

    // Encabezados
    $col = 'A';
    foreach ($atributosOrdenados as $attr) {
        $label = isset($nombresCampos[$attr]) ? $nombresCampos[$attr] : ucwords(str_replace('_', ' ', $attr));
        $cell = $col . $startRow;
        $sheet->setCellValue($cell, $label);
        $sheet->getStyle($cell)->getFont()->setBold(true)->getColor()->setRGB($blanco);
        $sheet->getStyle($cell)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($rojo);
        $sheet->getStyle($cell)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle($cell)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $col++;
    }

    // Datos (fila siguiente a los encabezados)
    $row = $startRow + 1;
    $col = 'A';
    $imgCol = null;

    foreach ($atributosOrdenados as $attr) {
        $cell = $col . $row;

        if ($attr === 'img') {
            $imgCol = $col;
            $imgPath = valor($persona, 'img');

            if ($imgPath && file_exists($imgPath)) {
                $drawing = new Drawing();
                $drawing->setPath($imgPath);
                $drawing->setHeight(80);
                $drawing->setCoordinates($cell);
                $drawing->setOffsetX(5);
                $drawing->setOffsetY(5);
                $drawing->setWorksheet($sheet);

                $sheet->getRowDimension($row)->setRowHeight(65);
                $sheet->getColumnDimension($col)->setWidth(22);
            } else {
                $sheet->setCellValue($cell, 'Imagen no disponible');
            }
        } else {
            $sheet->setCellValue($cell, 'Datos no disponibles');
        }

        $sheet->getStyle($cell)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle($cell)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $col++;
    }

    // Autoajuste de columnas excepto imagen
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

?>
