<?php
// ajax/editar_persona_contacto.php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/PersonaContacto.php';

header('Content-Type: application/json');

// Establecer el tipo de contenido y charset
header('Content-Type: application/json; charset=utf-8');

$database = new Database();
$db = $database->getConnection();
$personaContacto = new PersonaContacto($db);

try {
    // Obtener los datos del formulario
    $id_persona = isset($_POST['id_persona']) ? intval($_POST['id_persona']) : 0;
    $nombre_persona = isset($_POST['nombre_persona']) ? trim($_POST['nombre_persona']) : '';
    $cargo = isset($_POST['cargo']) ? trim($_POST['cargo']) : null;
    $telefono = isset($_POST['telefono']) ? trim($_POST['telefono']) : null;
    $correo = isset($_POST['correo']) ? trim($_POST['correo']) : null;
    $id_cliente = isset($_POST['id_cliente']) ? intval($_POST['id_cliente']) : 0;

    // Log para debugging
    error_log("Datos recibidos - ID: $id_persona, Nombre: $nombre_persona, Cliente: $id_cliente");

    // Validaciones
    if ($id_persona <= 0) {
        throw new Exception('ID de persona de contacto no válido');
    }

    if (empty($nombre_persona)) {
        throw new Exception('El nombre de la persona es obligatorio');
    }

    if ($id_cliente <= 0) {
        throw new Exception('Debe seleccionar un cliente válido');
    }

    // Validar formato de email si se proporciona
    if (!empty($correo) && !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('El formato del correo electrónico no es válido');
    }

    // Asignar los datos al modelo
    $personaContacto->id_persona = $id_persona;
    $personaContacto->nombre_persona = $nombre_persona;
    $personaContacto->cargo = $cargo;
    $personaContacto->telefono = $telefono;
    $personaContacto->correo = $correo;
    $personaContacto->id_cliente = $id_cliente;

    // Log antes de actualizar
    error_log("Intentando actualizar persona ID: $id_persona");

    // Intentar actualizar la persona de contacto
    if ($personaContacto->actualizar()) {
        error_log("Actualización exitosa para persona ID: $id_persona");
        echo json_encode([
            'success' => true,
            'message' => 'Persona de contacto actualizada correctamente.'
        ]);
    } else {
        error_log("Error en actualización para persona ID: $id_persona");
        throw new Exception('No se pudo actualizar la persona de contacto. Verifique que los datos sean correctos.');
    }
} catch (Exception $e) {
    error_log("Error en editar_persona_contacto.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>