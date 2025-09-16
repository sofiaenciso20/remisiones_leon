<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/PersonaContacto.php';

header('Content-Type: application/json');

try {
    // Obtener conexión a la base de datos
    $database = new Database();
    $db = $database->getConnection();

    // Crear instancia del modelo PersonaContacto
    $personaContacto = new PersonaContacto($db);

    // Obtener datos del POST
    $data = $_POST;

    // Validar datos requeridos
    if (empty($data['nombre_persona']) || empty($data['id_cliente'])) {
        throw new Exception('El nombre y el cliente son campos obligatorios.');
    }

    // Asignar valores
    $personaContacto->nombre_persona = $data['nombre_persona'];
    $personaContacto->cargo = $data['cargo'] ?? null;
    $personaContacto->telefono = $data['telefono'] ?? null;
    $personaContacto->correo = $data['correo'] ?? null;
    $personaContacto->id_cliente = $data['id_cliente'];

    // Crear la persona de contacto
    if ($personaContacto->crear()) {
        echo json_encode([
            'success' => true,
            'message' => 'Persona de contacto creada correctamente.',
            'persona' => [
                'id_persona' => $personaContacto->id_persona,
                'nombre_persona' => $personaContacto->nombre_persona,
                'cargo' => $personaContacto->cargo
            ]
        ]);
    } else {
        throw new Exception('Error al crear la persona de contacto en la base de datos.');
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}