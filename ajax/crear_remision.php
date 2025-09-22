<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Remision.php';
require_once __DIR__ . '/../models/ItemRemisionado.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Conexión a la base de datos
        $database = new Database();
        $db = $database->getConnection();
        
        if (!$db) {
            throw new Exception('Error de conexión a la base de datos');
        }

        // Validaciones de campos obligatorios
        if (empty($_POST['id_cliente'])) {
            throw new Exception('El campo id_cliente es obligatorio');
        }

        // Crear instancia de modelos
        $remision = new Remision($db);
        $itemRemisionado = new ItemRemisionado($db);

        // Asignar valores a la remisión
        $remision->fecha_emision   = date('Y-m-d');
        $remision->id_cliente      = (int) $_POST['id_cliente'];
        $remision->id_persona      = !empty($_POST['id_persona']) ? (int) $_POST['id_persona'] : null;
        $remision->id_usuario      = 1; // Usuario fijo por ahora
        $remision->observaciones   = $_POST['observaciones'] ?? null;
        $remision->id_estado       = 1; // Estado inicial

        // Debug interno
        error_log("Datos recibidos en crear_remision.php: " . print_r($_POST, true));

        // Crear remisión
        $id_remision = $remision->crear();

        if ($id_remision) {
            $items_success = true;

            // Procesar los ítems si existen
            if (!empty($_POST['items'])) {
                $items = json_decode($_POST['items'], true);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new Exception('Formato inválido en items (JSON incorrecto)');
                }

                if ($items && is_array($items) && count($items) > 0) {
                    $items_success = $itemRemisionado->crearItems($id_remision, $items);
                }
            }

            echo json_encode([
                'success' => true,
                'message' => 'Remisión creada correctamente',
                'id_remision' => $id_remision,
                'numero_remision' => $remision->numero_remision,
                'items_success' => $items_success
            ]);
        } else {
            // Error en la creación de la remisión
            $error_info = $db->errorInfo();
            error_log("Error al crear remisión: " . print_r($error_info, true));

            echo json_encode([
                'success' => false,
                'message' => 'Error al crear la remisión',
                'debug' => $error_info
            ]);
        }

    } catch (Exception $e) {
        // Manejo de excepciones
        error_log("Excepción en crear_remision.php: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());

        echo json_encode([
            'success' => false,
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]);
    }
} else {
    // Método no permitido
    echo json_encode([
        'success' => false,
        'message' => 'Método no permitido. Usa POST'
    ]);
}
