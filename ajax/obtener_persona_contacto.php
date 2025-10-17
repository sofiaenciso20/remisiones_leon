<?php
// ajax/obtener_persona_contacto.php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/PersonaContacto.php';
require_once __DIR__ . '/../models/Cliente.php';

header('Content-Type: application/json');

$database = new Database();
$db = $database->getConnection();
$personaContacto = new PersonaContacto($db);
$cliente = new Cliente($db);

try {
    // Obtener el ID de la persona de contacto desde la solicitud
    $id_persona = isset($_POST['id_persona']) ? intval($_POST['id_persona']) : 0;

    if ($id_persona <= 0) {
        throw new Exception('ID de persona de contacto no válido');
    }

    // Obtener los datos de la persona de contacto usando obtenerPorIdArray
    $persona = $personaContacto->obtenerPorIdArray($id_persona);

    if ($persona) {
        // Obtener el nombre del cliente asociado
        $clienteData = $cliente->obtenerPorId($persona['id_cliente']);
        $nombre_cliente = '';
        
        if ($clienteData && isset($clienteData['nombre_cliente'])) {
            $nombre_cliente = $clienteData['nombre_cliente'];
        } else {
            $nombre_cliente = 'Cliente no encontrado';
        }

        echo json_encode([
            'success' => true,
            'data' => [
                'id_persona' => $persona['id_persona'],
                'nombre_persona' => $persona['nombre_persona'],
                'cargo' => $persona['cargo'] ?? '',
                'telefono' => $persona['telefono'] ?? '',
                'correo' => $persona['correo'] ?? '',
                'id_cliente' => $persona['id_cliente'],
                'nombre_cliente' => $nombre_cliente
            ],
            'persona' => [
                'id_persona' => $persona['id_persona'],
                'nombre_persona' => $persona['nombre_persona']
            ]
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'No se encontró la persona de contacto con el ID proporcionado.'
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
