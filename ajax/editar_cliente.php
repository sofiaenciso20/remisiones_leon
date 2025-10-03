<?php
// ajax/editar_cliente.php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Cliente.php';

header('Content-Type: application/json');

$database = new Database();
$db = $database->getConnection();
$cliente = new Cliente($db);

try {
    // Obtener los datos del formulario
    $id_cliente = isset($_POST['id_cliente']) ? intval($_POST['id_cliente']) : 0;
    $nombre_cliente = isset($_POST['nombre_cliente']) ? trim($_POST['nombre_cliente']) : '';
    $tipo_cliente = isset($_POST['tipo_cliente']) ? trim($_POST['tipo_cliente']) : '';
    $nit = isset($_POST['nit']) ? trim($_POST['nit']) : '';
    $direccion = isset($_POST['direccion']) ? trim($_POST['direccion']) : null;
    $telefono = isset($_POST['telefono']) ? trim($_POST['telefono']) : null;
    $correo = isset($_POST['correo']) ? trim($_POST['correo']) : null;

    // Validaciones
    if ($id_cliente <= 0) {
        throw new Exception('ID de cliente no válido');
    }

    if (empty($nombre_cliente)) {
        throw new Exception('El nombre del cliente es obligatorio');
    }

    if (empty($tipo_cliente)) {
        throw new Exception('El tipo de cliente es obligatorio');
    }

    if (empty($nit)) {
        throw new Exception('El NIT es obligatorio');
    }

    // Validar formato de email si se proporciona
    if (!empty($correo) && !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('El formato del correo electrónico no es válido');
    }

    // Asignar los datos al modelo
    $cliente->id_cliente = $id_cliente;
    $cliente->nombre_cliente = $nombre_cliente;
    $cliente->tipo_cliente = $tipo_cliente;
    $cliente->nit = $nit;
    $cliente->direccion = $direccion;
    $cliente->telefono = $telefono;
    $cliente->correo = $correo;

    // Intentar actualizar el cliente
    if ($cliente->actualizar()) {
        echo json_encode([
            'success' => true,
            'message' => 'Cliente actualizado correctamente.'
        ]);
    } else {
        throw new Exception('No se pudo actualizar el cliente. Verifique que los datos sean correctos.');
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>