<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/models/Remision.php';
require_once __DIR__ . '/models/ItemRemisionado.php';
require_once __DIR__ . '/lib/fpdf/fpdf.php';

if (!isset($_GET['id'])) {
    die('ID de remisión no especificado');
}

$database = new Database();
$db = $database->getConnection();

$remision = new Remision($db);
$itemRemisionado = new ItemRemisionado($db);

$datos = $remision->obtenerPorId($_GET['id']);
$items = $itemRemisionado->obtenerPorRemision($_GET['id']);

if (!$datos) {
    die('Remisión no encontrada');
}

class RemisionPDF extends FPDF {
    function Header() {
        $this->SetFont('Arial', 'B', 14);
        $this->Cell(0, 8, 'LEON GRAFICAS S.A.S', 0, 1, 'R');
        $this->SetFont('Arial', '', 8);
        $this->Cell(0, 5, 'Calle 14 No. 6-50', 0, 1, 'R');
        $this->Cell(0, 5, 'TEL: 318-578-0327', 0, 1, 'R');
        $this->Ln(2);
    }
    
    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Pagina ' . $this->PageNo(), 0, 0, 'C');
    }
}

// Crear PDF en formato Media Carta (140 × 216 mm)
$pdf = new RemisionPDF('P', 'mm', array(140, 216));
$pdf->SetMargins(10, 10, 10);
$pdf->AddPage();

// =============================
// ENCABEZADO DEL DOCUMENTO
// =============================
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 8, 'REMISION', 0, 1, 'C');
$pdf->Ln(1);

// Número de remisión
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(20, 6, 'No.', 0, 0);
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(40, 6, $datos['numero_remision'], 1, 1);

// Fecha
$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell(20, 6, 'FECHA:', 0, 0);
$pdf->SetFont('Arial', '', 9);
$pdf->Cell(40, 6, date('d/m/Y', strtotime($datos['fecha_emision'])), 1, 1);
$pdf->Ln(2);

// =============================
// DATOS DEL CLIENTE
// =============================
$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell(25, 6, 'SENOR(ES):', 0, 0);
$pdf->SetFont('Arial', '', 9);
$pdf->Cell(90, 6, utf8_decode($datos['nombre_cliente']), 1, 1);

$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell(25, 6, 'DIRECCION:', 0, 0);
$pdf->SetFont('Arial', '', 9);
$pdf->Cell(90, 6, utf8_decode($datos['direccion'] ?? ''), 1, 1);

$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell(25, 6, 'TELEFONO:', 0, 0);
$pdf->SetFont('Arial', '', 9);
$pdf->Cell(40, 6, $datos['telefono'] ?? '', 1, 0);
$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell(10, 6, 'NIT:', 0, 0);
$pdf->SetFont('Arial', '', 9);
$pdf->Cell(40, 6, $datos['nit'], 1, 1);

$pdf->Ln(4);

// =============================
// TABLA DE ITEMS
// =============================
$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell(18, 7, 'CANT.', 1, 0, 'C');
$pdf->Cell(72, 7, 'DETALLE', 1, 0, 'C');
$pdf->Cell(30, 7, 'V. UNITARIO', 1, 1, 'C');

$pdf->SetFont('Arial', '', 8);

$total = 0;
$max_items_per_page = 10; // reducido para asegurar que quepa en 1 hoja

foreach ($items as $index => $item) {
    if ($index >= $max_items_per_page) break;

    $valor = isset($item['valor_unitario']) ? $item['valor_unitario'] : 0;
    $total += ($item['cantidad'] * $valor);

    $pdf->Cell(18, 6, $item['cantidad'], 1, 0, 'C');
    $pdf->Cell(72, 6, utf8_decode($item['descripcion']), 1, 0);
    $pdf->Cell(30, 6, number_format($valor, 0, ',', '.'), 1, 1, 'R');
}

// Rellenar filas vacías
$items_restantes = $max_items_per_page - count($items);
for ($i = 0; $i < $items_restantes; $i++) {
    $pdf->Cell(18, 6, '', 1, 0);
    $pdf->Cell(72, 6, '', 1, 0);
    $pdf->Cell(30, 6, '', 1, 1);
}

// =============================
// TOTAL
// =============================
$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell(90, 6, 'TOTAL $', 1, 0, 'R');
$pdf->Cell(30, 6, number_format($total, 0, ',', '.'), 1, 1, 'R');

$pdf->Ln(4);

// =============================
// OBSERVACIONES
// =============================
if (!empty($datos['observaciones'])) {
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->Cell(0, 6, 'OBSERVACIONES:', 0, 1);
    $pdf->SetFont('Arial', '', 8);
    $pdf->MultiCell(0, 5, utf8_decode($datos['observaciones']), 1);
}

// =============================
// FIRMAS
// =============================
$pdf->Ln(8);
$pdf->SetFont('Arial', '', 9);
$pdf->Cell(60, 6, 'LEON GRAFICAS S.A.S:', 0, 0);
$pdf->Cell(60, 6, 'FIRMA Y SELLO CLIENTE:', 0, 1);
$pdf->Ln(12);
$pdf->Cell(60, 6, '________________________', 0, 0);
$pdf->Cell(60, 6, '________________________', 0, 1);

// =============================
// GENERAR PDF
// =============================
$filename = 'Remision_' . $datos['numero_remision'] . '.pdf';
$pdf->Output('I', $filename); // I = ver en navegador, D = descargar directo
?>
