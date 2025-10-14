<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Remision.php';
require_once __DIR__ . '/../models/ItemRemisionado.php';
require_once __DIR__ . '/../models/Producto.php';
require_once __DIR__ . '/../models/MovimientoInventario.php';

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

        if (empty($_POST['numero_remision'])) {
            throw new Exception('El campo numero_remision es obligatorio');
        }

        $db->beginTransaction();

        // Crear instancia de modelos
        $remision = new Remision($db);
        $itemRemisionado = new ItemRemisionado($db);
        $productoModel = new Producto($db);
        $movimientoInventario = new MovimientoInventario($db);

        // Validar stock antes de crear la remisión
        if (!empty($_POST['items'])) {
            $items = json_decode($_POST['items'], true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Formato inválido en items (JSON incorrecto)');
            }

            if ($items && is_array($items) && count($items) > 0) {
                foreach ($items as $item) {
                    // Solo verificar productos con id_producto (que están en el catálogo)
                    if (!empty($item['id_producto']) && !empty($item['cantidad'])) {
                        // Obtener información del producto
                        $producto = $productoModel->obtenerPorId($item['id_producto']);
                        
                        if ($producto && $producto['maneja_inventario']) {
                            if ($producto['stock_actual'] < $item['cantidad']) {
                                throw new Exception('Stock insuficiente para el producto: ' . $producto['nombre_producto'] . '. Stock actual: ' . $producto['stock_actual'] . ', cantidad solicitada: ' . $item['cantidad']);
                            }
                        }
                    }
                }
            }
        }

        // Asignar valores a la remisión
        $remision->numero_remision = (int) $_POST['numero_remision'];
        $remision->fecha_emision   = $_POST['fecha_emision'];
        $remision->id_cliente      = (int) $_POST['id_cliente'];
        $remision->id_persona      = !empty($_POST['id_persona']) ? (int) $_POST['id_persona'] : null;
        $remision->id_usuario      = 1; // Usuario fijo por ahora
        $remision->observaciones   = $_POST['observaciones'] ?? null;
        $remision->id_estado       = !empty($_POST['id_estado']) ? (int) $_POST['id_estado'] : 1; // Estado inicial: Pendiente

        // Crear remisión
        $id_remision = $remision->crear();

        if ($id_remision) {
            $items_success = true;
            $items_procesados = 0;
            $items_con_inventario = 0;

            // Procesar los ítems si existen
            if (!empty($_POST['items'])) {
                $items = json_decode($_POST['items'], true);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new Exception('Formato inválido en items (JSON incorrecto)');
                }

                if ($items && is_array($items) && count($items) > 0) {
                    foreach ($items as $item) {
                        // Validar item
                        if (empty($item['descripcion']) || empty($item['cantidad'])) {
                            continue; // Saltar item inválido
                        }

                        // Crear instancia de ItemRemisionado para cada item
                        $itemObj = new ItemRemisionado($db);
                        $itemObj->id_remision = $id_remision;
                        $itemObj->id_producto = !empty($item['id_producto']) ? (int) $item['id_producto'] : null;
                        $itemObj->descripcion = $item['descripcion'];
                        $itemObj->cantidad = (int) $item['cantidad'];
                        $itemObj->valor_unitario = !empty($item['valor_unitario']) ? (float) $item['valor_unitario'] : 0.00;

                        // Insertar item
                        if ($itemObj->crear()) {
                            $items_procesados++;

                            // ACTUALIZACIÓN DE INVENTARIO - IMPLEMENTACIÓN SOLICITADA
                            if (!empty($item['id_producto'])) {
                                // Verificar si el producto maneja inventario
                                $query_producto = "SELECT maneja_inventario, stock_actual FROM productos WHERE id_producto = ?";
                                $stmt = $db->prepare($query_producto);
                                $stmt->execute([$item['id_producto']]);
                                $producto = $stmt->fetch(PDO::FETCH_ASSOC);

                                if ($producto && $producto['maneja_inventario']) {
                                    $stock_anterior = $producto['stock_actual'];
                                    $stock_nuevo = $stock_anterior - $item['cantidad'];

                                    // Actualizar stock del producto
                                    $update_stock = "UPDATE productos SET stock_actual = ? WHERE id_producto = ?";
                                    $stmt = $db->prepare($update_stock);
                                    $stmt->execute([$stock_nuevo, $item['id_producto']]);

                                    // Registrar movimiento de salida
                                    $movimiento = new MovimientoInventario($db);
                                    $movimiento->id_producto = $item['id_producto'];
                                    $movimiento->tipo_movimiento = 'salida';
                                    $movimiento->cantidad = $item['cantidad'];
                                    $movimiento->stock_anterior = $stock_anterior;
                                    $movimiento->stock_nuevo = $stock_nuevo;
                                    $movimiento->motivo = 'remision';
                                    $movimiento->id_remision = $id_remision;
                                    $movimiento->id_usuario = 1; // Usuario fijo por ahora
                                    $movimiento->observaciones = "Remisión #" . $remision->numero_remision;
                                    
                                    if ($movimiento->crear()) {
                                        $items_con_inventario++;
                                    }
                                }
                            }
                        }
                    }
                    
                    $items_success = ($items_procesados > 0);
                }
            }

            $db->commit();

            echo json_encode([
                'success' => true,
                'message' => 'Remisión creada correctamente',
                'id_remision' => $id_remision,
                'numero_remision' => $remision->numero_remision,
                'items_procesados' => $items_procesados,
                'items_con_inventario' => $items_con_inventario,
                'items_success' => $items_success
            ]);
        } else {
            $db->rollBack();
            
            // Error en la creación de la remisión
            $error_info = $db->errorInfo();

            echo json_encode([
                'success' => false,
                'message' => 'Error al crear la remisión',
                'debug' => $error_info
            ]);
        }

    } catch (Exception $e) {
        if ($db && $db->inTransaction()) {
            $db->rollBack();
        }
        
        // Manejo de excepciones
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
} else {
    // Método no permitido
    echo json_encode([
        'success' => false,
        'message' => 'Método no permitido. Usa POST'
    ]);
}
?>