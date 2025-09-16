<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Remision.php';
require_once __DIR__ . '/../models/ItemRemisionado.php';

// Habilitar reporte de errores para depuración
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Crear conexión a la base de datos
    $database = new Database();
    $db = $database->getConnection();

    // Instanciar modelos con la conexión
    $remision = new Remision($db);
    $itemRemisionado = new ItemRemisionado($db);

    // Asignar valores
    $remision->numero_remision = $_POST['numero_remision'];
    $remision->id_cliente = $_POST['id_cliente'];
    $remision->id_persona = !empty($_POST['id_persona']) ? $_POST['id_persona'] : null;
    $remision->id_usuario = 1; // Por ahora usuario fijo
    $remision->observaciones = $_POST['observaciones'] ?? null;
    $remision->id_estado = 1; // Estado por defecto

    $id_remision = $remision->crear();

    if ($id_remision) {
        $items = json_decode($_POST['items'], true);
        $allItemsCreated = true;
        
        // Crear cada item individualmente
        foreach ($items as $item) {
            // Crear una nueva instancia para cada item
            $itemObj = new ItemRemisionado($db);
            $itemObj->id_remision = $id_remision;
            $itemObj->descripcion = $item['descripcion'];
            $itemObj->cantidad = $item['cantidad'];
            
            if (!$itemObj->crear()) {
                $allItemsCreated = false;
                error_log("Error al crear item: " . $item['descripcion']);
                break;
            }
        }

        if ($allItemsCreated) {
            echo json_encode([
                'success' => true,
                'message' => 'Remisión creada correctamente',
                'id_remision' => $id_remision
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Error al crear los items de la remisión'
            ]);
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Error al crear la remisión'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Método no permitido'
    ]);
}
?>