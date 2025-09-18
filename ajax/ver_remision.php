<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Remision.php';
require_once __DIR__ . '/../models/ItemRemisionado.php';

if (isset($_POST['id_remision'])) {
    // Crear conexión a la base de datos
    $database = new Database();
    $db = $database->getConnection();
    
    // Instanciar los modelos pasando la conexión (esto es lo que faltaba)
    $remision = new Remision($db);
    $itemRemisionado = new ItemRemisionado($db);
    
    $datos = $remision->obtenerPorId($_POST['id_remision']);
    $items = $itemRemisionado->obtenerPorRemision($_POST['id_remision']);
    
    if ($datos) {
        ?>
        <div class="row">
            <div class="col-md-6">
                <strong>Número de Remisión:</strong> <?php echo $datos['numero_remision'] ?? 'N/A'; ?><br>
                <strong>Fecha:</strong> <?php echo isset($datos['fecha_emision']) ? date('d/m/Y', strtotime($datos['fecha_emision'])) : 'N/A'; ?><br>
                <strong>Cliente:</strong> <?php echo htmlspecialchars($datos['nombre_cliente'] ?? 'N/A'); ?><br>
                <strong>NIT:</strong> <?php echo htmlspecialchars($datos['nit'] ?? 'N/A'); ?>
            </div>
            <div class="col-md-6">
                <strong>Dirección:</strong> <?php echo htmlspecialchars($datos['direccion'] ?? 'N/A'); ?><br>
                <strong>Teléfono:</strong> <?php echo htmlspecialchars($datos['telefono'] ?? 'N/A'); ?><br>
                <strong>Persona de Contacto:</strong> <?php echo htmlspecialchars($datos['nombre_persona'] ?? 'N/A'); ?>
            </div>
        </div>
        
        <hr>
        
        <h5>Items:</h5>
        <div class="table-responsive">
            <table class="table table-sm table-bordered">
                <thead>
                    <tr>
                        <th>Descripción</th>
                        <th width="100">Cantidad</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($items)): ?>
                        <?php foreach ($items as $item): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['descripcion'] ?? 'N/A'); ?></td>
                                <td class="text-center"><?php echo $item['cantidad'] ?? 0; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="2" class="text-center text-muted">No hay items registrados</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <?php if (!empty($datos['observaciones'])): ?>
            <hr>
            <h5>Observaciones:</h5>
            <p><?php echo nl2br(htmlspecialchars($datos['observaciones'])); ?></p>
        <?php endif; ?>
        
        <hr>
        <div class="text-center">
            <a href="generar_pdf.php?id=<?php echo $datos['id_remision'] ?? 0; ?>" 
               target="_blank" class="btn btn-danger">
                <i class="fas fa-file-pdf"></i> Descargar PDF
            </a>
        </div>
        <?php
    } else {
        echo '<p class="text-center text-muted">No se encontraron datos de la remisión.</p>';
    }
} else {
    echo '<p class="text-center text-danger">Error: ID de remisión no proporcionado.</p>';
}
?>