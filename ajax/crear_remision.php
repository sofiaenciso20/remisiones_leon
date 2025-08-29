<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Remision.php';
require_once __DIR__ . '/../models/ItemRemisionado.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Crear conexión a la base de datos
    $database = new Database();
    $db = $database->getConnection();

    // Instanciar modelos con la conexión
    $remision = new Remision($db);
    $itemRemisionado = new ItemRemisionado($db);

    $remision->numero_remision = $_POST['numero_remision'];
    $remision->id_cliente = $_POST['id_cliente'];
    $remision->id_persona = !empty($_POST['id_persona']) ? $_POST['id_persona'] : null;
    $remision->id_usuario = 1; // Por ahora usuario fijo
    $remision->observaciones = $_POST['observaciones'] ?? null;
    $remision->id_estado = 1; // Estado por defecto

    $id_remision = $remision->crear();

    if ($id_remision) {
        $items = json_decode($_POST['items'], true);
        $itemRemisionado->crearItems($id_remision, $items);

        echo json_encode([
            'success' => true,
            'message' => 'Remisión creada correctamente',
            'id_remision' => $id_remision
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Error al crear la remisión'
        ]);
    }
}
?>
