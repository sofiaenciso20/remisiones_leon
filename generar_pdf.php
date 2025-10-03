<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/models/Remision.php';
require_once __DIR__ . '/models/ItemRemisionado.php';
require_once __DIR__ . '/lib/fpdf/fpdf.php';

// Validar parámetros requeridos
if (!isset($_GET['id']) || empty($_GET['id'])) {
    http_response_code(400);
    die('<h1>Error</h1><p>ID de remisión no especificado</p>');
}

try {
    $database = new Database();
    $db = $database->getConnection();
    
    if (!$db) {
        throw new Exception('Error de conexión a la base de datos');
    }
    
    $remision = new Remision($db);
    $itemRemisionado = new ItemRemisionado($db);
    
    $datos = $remision->obtenerPorId($_GET['id']);
    $items = $itemRemisionado->obtenerPorRemision($_GET['id']);
    
    if (!$datos) {
        http_response_code(404);
        die('<h1>Error</h1><p>Remisión no encontrada</p>');
    }
} catch (Exception $e) {
    error_log("Error al generar PDF: " . $e->getMessage());
    http_response_code(500);
    die('<h1>Error</h1><p>Error al generar el PDF: ' . htmlspecialchars($e->getMessage()) . '</p>');
}

class RemisionPDF extends FPDF {
    public $datos;
    
    function __construct($datos) {
        parent::__construct('L', 'mm', 'A5');
        $this->datos = $datos;
    }

    function Header() {
        $x = 10;
        $y = 10;
        $w = $this->w - 20;
        $h = 30;

        $this->Rect($x, $y, $w, $h);
        $divisor_x = $x + $w * 0.6;
        $this->Line($divisor_x, $y, $divisor_x, $y + $h);

        $this->SetXY($x + 3, $y + 3);
        $this->SetFont('Arial', 'B', 10);
        $this->Cell(28, 4, utf8_decode('REMISIÓN N°: '), 0, 0);
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(15, 4, utf8_decode($this->datos['numero_remision']), 0, 1);
        
        $this->SetX($x + 3);
        $this->SetFont('Arial', 'B', 9);
        $this->Cell(14, 4, utf8_decode('FECHA: '), 0, 0);
        $this->SetFont('Arial', '', 9);
        $this->Cell(20, 4, utf8_decode(date('d/m/Y', strtotime($this->datos['fecha_emision']))), 0, 1);
        
        $this->SetX($x + 3);
        $this->SetFont('Arial', 'B', 9);
        $this->Cell(17, 4, utf8_decode('CLIENTE: '), 0, 0);
        $this->SetFont('Arial', '', 9);
        $this->Cell(0, 4, utf8_decode($this->datos['nombre_cliente']), 0, 1);
        
        $this->SetX($x + 3);
        $this->SetFont('Arial', 'B', 9);
        $this->Cell(20, 4, utf8_decode('DIRECCIÓN: '), 0, 0);
        $this->SetFont('Arial', '', 9);
        $this->MultiCell($divisor_x - $x - 23, 4, utf8_decode($this->datos['direccion'] ?? 'No especificada'), 0, 'L');
        
        $this->SetX($x + 3);
        $this->SetFont('Arial', 'B', 9);
        $this->Cell(20, 4, utf8_decode('TELÉFONO: '), 0, 0);
        $this->SetFont('Arial', '', 9);
        $this->Cell(25, 4, utf8_decode($this->datos['telefono'] ?? 'No especificado'), 0, 0);
        
        $this->SetFont('Arial', 'B', 9);
        $this->Cell(10, 4, 'NIT: ', 0, 0);
        $this->SetFont('Arial', '', 9);
        $this->Cell(0, 4, utf8_decode($this->datos['nit']), 0, 1);

        // Información de persona de contacto (RECIBE)
    if (!empty($this->datos['nombre_persona'])) {
            $this->SetX($x + 3);
            $this->SetFont('Arial', 'B', 9);
            $this->Cell(15, 4, utf8_decode('RECIBE: '), 0, 0);
            $this->SetFont('Arial', '', 9);
            
            // Usar telefono_persona (no el teléfono del cliente)
            $telefono_persona = isset($this->datos['telefono_persona']) ? $this->datos['telefono_persona'] : '';
            $recibe_info = $this->datos['nombre_persona'];
            if (!empty($telefono_persona)) {
                $recibe_info .= ' - Tel: ' . $telefono_persona;
            }
            
            $this->Cell(0, 4, utf8_decode($recibe_info), 0, 1);
        }

        // Información derecha (empresa)
        $logoPath = __DIR__ . '/img/logo.png';
        $logoWidth = 25;
        $logoX = $divisor_x + 3;
        $logoY = $y + 7;
        
        if (file_exists($logoPath)) {
            $this->Image($logoPath, $logoX, $logoY, $logoWidth);
        }
        
        $textoX = $logoX + $logoWidth + 3;
        $textoY = $y + 10;
        
        $this->SetXY($textoX, $textoY);
        $this->SetFont('Arial', 'B', 9);
        $this->Cell(0, 4, utf8_decode('LEON GRÁFICAS S.A.S'), 0, 1);
        
        $this->SetX($textoX);
        $this->SetFont('Arial', '', 8);
        $this->Cell(0, 3, utf8_decode('NIT: 809012539-4'), 0, 1);
        
        $this->SetX($textoX);
        $this->Cell(0, 3, utf8_decode('TEL: 318-578-0327'), 0, 1);

        // REDUCIR espacio después del header a aproximadamente 1 cm (10mm)
        $this->Ln(10);
    }

    function Footer() {
        $this->SetY(-35);
        
        $ancho_util = $this->w - 20;
        $y_firmas = $this->GetY();
        
        $this->Rect(10, $y_firmas, $ancho_util, 25);
        
        $this->SetFont('Arial', '', 8);
        $mitad = $ancho_util / 2;
        
        $this->SetY($y_firmas + 3);
        $this->SetX(15);
        $this->Cell($mitad - 10, 5, utf8_decode('LEON GRÁFICAS S.A.S'), 0, 1, 'L');
        
        $this->SetX(15);
        $this->Ln(8);
        $this->SetX(15);
        $this->Cell($mitad - 10, 5, utf8_decode('_________________________'), 0, 1, 'L');
        
        $this->SetX(15);
        $this->SetFont('Arial', '', 7);
        $this->Cell($mitad - 10, 4, utf8_decode('Administrador'), 0, 0, 'L');
        
        $this->SetY($y_firmas + 3);
        $this->SetX($mitad + 10);
        $nombre_persona = !empty($this->datos['nombre_persona']) ? $this->datos['nombre_persona'] : 'CLIENTE';
        $this->Cell($mitad - 10, 5, utf8_decode("FIRMA {$nombre_persona}"), 0, 1, 'L');
        
        $this->SetX($mitad + 10);
        $this->Ln(8);
        $this->SetX($mitad + 10);
        $this->Cell($mitad - 10, 5, utf8_decode('_________________________'), 0, 1, 'L');
        
        $this->SetX($mitad + 10);
        $this->SetFont('Arial', '', 7);
        $this->Cell($mitad - 10, 4, utf8_decode('NIT O CC.'), 0, 0, 'L');
    }
}

$pdf = new RemisionPDF($datos);
$pdf->SetMargins(10, 42, 10); // Ajustar margen superior
$pdf->SetAutoPageBreak(true, 40);
$pdf->AddPage();

$ancho_util = $pdf->GetPageWidth() - 20;

// ELIMINAR el espacio adicional entre el cuadro de información y la tabla
// Solo dejar el espacio del Ln(10) del header que es aproximadamente 1 cm
// $pdf->Ln(8); // ESTA LÍNEA HA SIDO ELIMINADA

if (count($items) > 0) {
    $col1 = 15;
    $col3 = 25;
    $col4 = 25;
    $col2 = $ancho_util - ($col1 + $col3 + $col4);

    $pdf->SetFont('Arial', 'B', 9);
    $pdf->Cell($col1, 7, utf8_decode('CANT.'), 1, 0, 'C');
    $pdf->Cell($col2, 7, utf8_decode('DESCRIPCIÓN'), 1, 0, 'C');
    $pdf->Cell($col3, 7, utf8_decode('V. UNITARIO'), 1, 0, 'C');
    $pdf->Cell($col4, 7, utf8_decode('TOTAL'), 1, 1, 'C');

    $pdf->SetFont('Arial', '', 8);
    $total_general = 0;
    
    foreach ($items as $item) {
        $valor = isset($item['valor_unitario']) ? $item['valor_unitario'] : 0;
        $total_item = $item['cantidad'] * $valor;
        $total_general += $total_item;

        $pdf->Cell($col1, 6, utf8_decode($item['cantidad']), 1, 0, 'C');
        $pdf->Cell($col2, 6, utf8_decode(substr($item['descripcion'], 0, 70)), 1, 0, 'L');
        $pdf->Cell($col3, 6, '$ ' . number_format($valor, 0, ',', '.'), 1, 0, 'R');
        $pdf->Cell($col4, 6, '$ ' . number_format($total_item, 0, ',', '.'), 1, 1, 'R');
    }

    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell($col1 + $col2 + $col3, 7, utf8_decode('TOTAL GENERAL'), 1, 0, 'R');
    $pdf->Cell($col4, 7, '$ ' . number_format($total_general, 0, ',', '.'), 1, 1, 'R');
} else {
    $pdf->SetFont('Arial', 'I', 9);
    $pdf->Cell(0, 10, utf8_decode('No hay items registrados en esta remisión'), 0, 1, 'C');
}

$pdf->Ln(5);

if (!empty($datos['observaciones'])) {
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->Cell(0, 5, utf8_decode('OBSERVACIONES:'), 0, 1);
    $pdf->SetFont('Arial', '', 7);
    
    $observaciones = wordwrap(utf8_decode($datos['observaciones']), 100, "\n", true);
    $pdf->MultiCell(0, 4, $observaciones, 1);
}

$filename = 'Remision_' . $datos['numero_remision'] . '.pdf';
$pdf->Output('I', $filename);
?>