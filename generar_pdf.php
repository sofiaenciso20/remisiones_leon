<?php
require_once 'models/Remision.php';
require_once 'models/ItemRemisionado.php';
require_once 'lib/fpdf/fpdf.php';

if (!isset($_GET['id'])) {
    die('ID de remisión no especificado');
}

$remision = new Remision();
$itemRemisionado = new ItemRemisionado();

$datos = $remision->obtenerPorId($_GET['id']);
$items = $itemRemisionado->obtenerPorRemision($_GET['id']);

if (!$datos) {
    die('Remisión no encontrada');
}

class RemisionPDF extends FPDF {
    function Header() {
        // Logo y encabezado
        $this->SetFont('Arial', 'B', 16);
        $this->Cell(0, 10, 'LEON GRAFICAS S.A.S', 0, 1, 'R');
        $this->SetFont('Arial', '', 8);
        $this->Cell(0, 5, 'CARRERA 15 No. 12-51', 0, 1, 'R');
        $this->Cell(0, 5, 'TEL: 123-456-7890', 0, 1, 'R');
        $this->Ln(5);
    }
    
    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Pagina ' . $this->PageNo(), 0, 0, 'C');
    }
}

// Crear PDF en formato A5 (148 x 210 mm)
$pdf = new RemisionPDF('P', 'mm', array(148, 210));
$pdf->AddPage();
$pdf->SetMargins(10, 10, 10);

// Título REMISION
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 8, 'REMISION', 0, 1, 'C');
$pdf->Ln(3);

// Número de remisión
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(30, 6, 'No.', 0, 0);
$pdf->Cell(40, 6, $datos['numero_remision'], 1, 1);
$pdf->Ln(2);

// Fecha
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(30, 6, 'FECHA:', 0, 0);
$pdf->Cell(40, 6, date('d/m/Y', strtotime($datos['fecha_emision'])), 1, 1);
$pdf->Ln(3);

// Datos del cliente
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(20, 6, 'SENOR(ES):', 0, 0);
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(108, 6, utf8_decode($datos['nombre_cliente']), 1, 1);

$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(20, 6, 'DIRECCION:', 0, 0);
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(108, 6, utf8_decode($datos['direccion'] ?? ''), 1, 1);

$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(20, 6, 'TELEFONO:', 0, 0);
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(50, 6, $datos['telefono'] ?? '', 1, 0);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(15, 6, 'NIT:', 0, 0);
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(43, 6, $datos['nit'], 1, 1);

$pdf->Ln(5);

// Encabezado de la tabla de items
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(15, 8, 'CANT.', 1, 0, 'C');
$pdf->Cell(98, 8, 'DETALLE', 1, 0, 'C');
$pdf->Cell(15, 8, 'V. UNITARIO', 1, 1, 'C');

// Items
$pdf->SetFont('Arial', '', 9);
$y_position = $pdf->GetY();
$max_items_per_page = 12; // Ajustar según el espacio disponible

foreach ($items as $index => $item) {
    if ($index >= $max_items_per_page) break;
    
    $pdf->Cell(15, 6, $item['cantidad'], 1, 0, 'C');
    $pdf->Cell(98, 6, utf8_decode($item['descripcion']), 1, 0);
    $pdf->Cell(15, 6, '', 1, 1, 'C'); // Valor unitario vacío
}

// Rellenar filas vacías si hay menos items
$items_restantes = $max_items_per_page - count($items);
for ($i = 0; $i < $items_restantes; $i++) {
    $pdf->Cell(15, 6, '', 1, 0, 'C');
    $pdf->Cell(98, 6, '', 1, 0);
    $pdf->Cell(15, 6, '', 1, 1, 'C');
}

// Total
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(113, 6, 'TOTAL $', 1, 0, 'R');
$pdf->Cell(15, 6, '', 1, 1, 'C');

$pdf->Ln(5);

// Observaciones
if (!empty($datos['observaciones'])) {
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(0, 6, 'OBSERVACIONES:', 0, 1);
    $pdf->SetFont('Arial', '', 9);
    $pdf->MultiCell(0, 5, utf8_decode($datos['observaciones']), 1);
}

// Firma
$pdf->Ln(10);
$pdf->SetFont('Arial', '', 9);
$pdf->Cell(64, 6, 'ENTREGADO POR:', 0, 0);
$pdf->Cell(64, 6, 'RECIBIDO POR:', 0, 1);
$pdf->Ln(15);
$pdf->Cell(64, 6, '________________________', 0, 0);
$pdf->Cell(64, 6, '________________________', 0, 1);

// Generar el PDF
$filename = 'Remision_' . $datos['numero_remision'] . '.pdf';
$pdf->Output('D', $filename);
?>
