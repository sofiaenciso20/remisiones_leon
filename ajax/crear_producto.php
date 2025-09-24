<?php
require_once '../config/database.php';
require_once '../models/Producto.php';

header('Content-Type: application/json');

$database = new Database();
$db = $database->getConnection();
$producto = new Producto($db);

if ($_POST) {
    $producto->nombre_producto = $_POST['nombre_producto'];
    
    if ($producto->crear()) {
        echo json_encode([
            'success' => true,
            'message' => 'Producto creado correctamente',
            'producto' => [
                'id_producto' => $producto->id_producto,
                'nombre_producto' => $producto->nombre_producto
            ]
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Error al crear el producto'
        ]);
    }
}
?>