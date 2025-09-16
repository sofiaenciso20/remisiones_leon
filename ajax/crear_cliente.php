<?php
// filepath: c:\xampp\htdocs\remisiones\ajax\crear_cliente.php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Cliente.php';

// Establecer cabeceras para respuesta JSON
header('Content-Type: application/json; charset=utf-8');

// Habilitar reporte de errores para depuración
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Verificar que sea una solicitud POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Método no permitido. Se esperaba una solicitud POST.'
    ]);
    exit;
}

// Obtener el contenido raw de la solicitud (para cuando se envía como JSON)
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Si no se pudo decodificar como JSON, usar $_POST
if (json_last_error() !== JSON_ERROR_NONE) {
    $data = $_POST;
}

// Validar campos obligatorios
$campos_obligatorios = ['nombre_cliente', 'tipo_cliente', 'nit'];
$campos_faltantes = [];

foreach ($campos_obligatorios as $campo) {
    if (empty($data[$campo])) {
        $campos_faltantes[] = $campo;
    }
}

if (!empty($campos_faltantes)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Faltan campos obligatorios: ' . implode(', ', $campos_faltantes)
    ]);
    exit;
}

try {
    $database = new Database();
    $db = $database->getConnection();
    $cliente = new Cliente($db);

    // Asignar valores
    $cliente->nombre_cliente = trim($data['nombre_cliente']);
    $cliente->tipo_cliente = trim($data['tipo_cliente']);
    $cliente->nit = trim($data['nit']);
    $cliente->direccion = isset($data['direccion']) ? trim($data['direccion']) : '';
    $cliente->telefono = isset($data['telefono']) ? trim($data['telefono']) : '';
    $cliente->correo = isset($data['correo']) ? trim($data['correo']) : '';

    $id_cliente = $cliente->crear();

    if ($id_cliente) {
        $clienteCreado = $cliente->obtenerPorId($id_cliente);
        http_response_code(201);
        echo json_encode([
            'success' => true,
            'message' => 'Cliente creado correctamente',
            'cliente' => $clienteCreado
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Error al crear el cliente en la base de datos'
        ]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
exit;
?>