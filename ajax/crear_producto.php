<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Producto.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $database = new Database();
        $db = $database->getConnection();
        
        if (!$db) {
            throw new Exception('Error de conexión a la base de datos');
        }
        
        // Validar campo obligatorio
        if (empty($_POST['nombre_producto'])) {
            throw new Exception('El nombre del producto es obligatorio');
        }
        
        $producto = new Producto($db);
        $producto->nombre_producto = trim($_POST['nombre_producto']);
        
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
            throw new Exception('Error al crear el producto en la base de datos');
        }
    } catch (Exception $e) {
        error_log("Error en crear_producto.php: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Método no permitido. Usa POST'
    ]);
}
?>