<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/PersonaContacto.php';

header('Content-Type: application/json');

if (isset($_POST['id_cliente'])) {
    try {
        error_log("[DEBUG] Obteniendo personas para cliente: " . $_POST['id_cliente']);
        
        $database = new Database();
        $db = $database->getConnection();
        
        if (!$db) {
            throw new Exception('Error de conexión a la base de datos');
        }
        
        $personaContacto = new PersonaContacto($db);
        $personas = $personaContacto->obtenerPorCliente($_POST['id_cliente']);
        
        error_log("[DEBUG] Personas encontradas: " . count($personas));
        
        echo json_encode([
            'success' => true,
            'data' => $personas
        ]);
    } catch (Exception $e) {
        error_log("[ERROR] Error al obtener personas: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage(),
            'data' => []
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'error' => 'ID de cliente no proporcionado',
        'data' => []
    ]);
}
?>
