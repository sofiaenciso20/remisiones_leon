<?php
require_once '../models/Remision.php';
require_once '../models/ItemRemisionado.php';

if (isset($_POST['id_remision'])) {
    $remision = new Remision();
    $itemRemisionado = new ItemRemisionado();
    
    $datos = $remision->obtenerPorId($_POST['id_remision']);
    $items = $itemRemisionado->obtenerPorRemision($_POST['id_remision']);
    
    if ($datos) {
        ?>
        <div class="row">
            <div class="col-md-6">
                <strong>Número de Remisión:</strong> <?php echo $datos['numero_remision']; ?><br>
                <strong>Fecha:</strong> <?php echo date('d/m/Y', strtotime($datos['fecha_emision'])); ?><br>
                <strong>Cliente:</strong> <?php echo htmlspecialchars($datos['nombre_cliente']); ?><br>
                <strong>NIT:</strong> <?php echo htmlspecialchars($datos['nit']); ?>
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
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['descripcion']); ?></td>
                            <td class="text-center"><?php echo $item['cantidad']; ?></td>
                        </tr>
                    <?php endforeach; ?>
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
            <a href="generar_pdf.php?id=<?php echo $datos['id_remision']; ?>" 
               target="_blank" class="btn btn-danger">
                <i class="fas fa-file-pdf"></i> Descargar PDF
            </a>
        </div>
        <?php
    } else {
        echo '<p class="text-center text-muted">No se encontraron datos de la remisión.</p>';
    }
}
?>
