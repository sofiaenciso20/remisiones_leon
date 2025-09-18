<?php

require_once __DIR__ . '/../models/PersonaContacto.php';
require_once __DIR__ . '/../models/Cliente.php';

header('Content-Type: application/json; charset=utf-8');

// Solo aceptar POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

// Permitir también JSON body
$raw = file_get_contents('php://input');
$json = json_decode($raw, true);
if (json_last_error() === JSON_ERROR_NONE && is_array($json)) {
    $data = $json;
} else {
    $data = $_POST;
}

try {
    $database = new Database();
    $db = $database->getConnection();

    // Validar campos requeridos
    $nombre = isset($data['nombre_persona']) ? trim($data['nombre_persona']) : '';
    $idCliente = isset($data['id_cliente']) ? (int)$data['id_cliente'] : 0;
    if ($nombre === '' || $idCliente <= 0) {
        http_response_code(400);
        throw new Exception('El nombre y el cliente son obligatorios.');
    }

    // Validar existencia de cliente
    $clienteModel = new Cliente($db);
    if (!$clienteModel->obtenerPorId($idCliente)) {
        http_response_code(404);
        throw new Exception('El cliente indicado no existe.');
    }

    // Normalizar opcionales (evitar null en htmlspecialchars)
    $cargo = isset($data['cargo']) ? trim($data['cargo']) : '';
    $telefono = isset($data['telefono']) ? trim($data['telefono']) : '';
    $correo = isset($data['correo']) ? trim($data['correo']) : '';

    // Validar correo si viene
    if ($correo !== '' && !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        throw new Exception('El formato del correo no es válido.');
    }

    // Evitar duplicados (mismo nombre para mismo cliente)
    $stmtDup = $db->prepare("SELECT id_persona, cargo FROM personas_contacto WHERE id_cliente = :idc AND nombre_persona = :nom LIMIT 1");
    $stmtDup->bindParam(':idc', $idCliente, PDO::PARAM_INT);
    $stmtDup->bindParam(':nom', $nombre, PDO::PARAM_STR);
    $stmtDup->execute();
    $dupRow = $stmtDup->fetch(PDO::FETCH_ASSOC);
    if ($dupRow) {
        echo json_encode([
            'success' => true,
            'message' => 'Ya existía la persona de contacto, se reutiliza.',
            'persona' => [
                'id_persona' => $dupRow['id_persona'],
                'nombre_persona' => $nombre,
                'cargo' => $dupRow['cargo']
            ],
            'duplicado' => true
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $personaContacto = new PersonaContacto($db);
    $personaContacto->nombre_persona = $nombre;
    $personaContacto->cargo = $cargo;
    $personaContacto->telefono = $telefono;
    $personaContacto->correo = $correo;
    $personaContacto->id_cliente = $idCliente;

    if ($personaContacto->crear()) {
        http_response_code(201);
        echo json_encode([
            'success' => true,
            'message' => 'Persona de contacto creada correctamente.',
            'persona' => [
                'id_persona' => $personaContacto->id_persona,
                'nombre_persona' => $personaContacto->nombre_persona,
                'cargo' => $personaContacto->cargo
            ]
        ], JSON_UNESCAPED_UNICODE);
    } else {
        http_response_code(500);
        throw new Exception('Error al crear la persona de contacto en la base de datos.');
    }
} catch (Exception $e) {
    if (http_response_code() < 400) {
        http_response_code(500);
    }
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}