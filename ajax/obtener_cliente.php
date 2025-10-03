<?php
// ajax/obtener_cliente.php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Cliente.php';

header('Content-Type: application/json');

$database = new Database();
$db = $database->getConnection();
$cliente = new Cliente($db);

try {
    // Obtener el ID del cliente desde la solicitud
    $id_cliente = isset($_POST['id_cliente']) ? intval($_POST['id_cliente']) : 0;

    if ($id_cliente <= 0) {
        throw new Exception('ID de cliente no válido');
    }

    // Obtener los datos del cliente
    $clienteData = $cliente->obtenerPorId($id_cliente);

    if ($clienteData) {
        echo json_encode([
            'success' => true,
            'data' => $clienteData
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'No se encontró el cliente con el ID proporcionado.'
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>