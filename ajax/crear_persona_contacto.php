<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/PersonaContacto.php';

header('Content-Type: application/json');

// Verificar si la solicitud es POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $database = new Database();
        $db = $database->getConnection();
        
        if (!$db) {
            throw new Exception('Error de conexión a la base de datos');
        }
        
        $personaContacto = new PersonaContacto($db);
        
        // Asignar datos del POST con validación
        $personaContacto->id_cliente = $_POST['id_cliente'];
        $personaContacto->nombre_persona = $_POST['nombre_persona'];
        $personaContacto->cargo = $_POST['cargo'] ?? null;
        $personaContacto->telefono = $_POST['telefono'] ?? null;
        $personaContacto->correo = $_POST['correo'] ?? null;
        
        // Crear la persona de contacto
        $id_persona = $personaContacto->crear();
        
        if ($id_persona) {
            // Obtener los datos completos de la persona creada
            $personaCreada = $personaContacto->obtenerPorIdArray($id_persona);
            
            echo json_encode([
                'success' => true,
                'message' => 'Persona de contacto creada exitosamente.',
                'persona' => $personaCreada
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Error al crear la persona de contacto.'
            ]);
        }
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Método no permitido. Solo se aceptan solicitudes POST.'
    ]);
}