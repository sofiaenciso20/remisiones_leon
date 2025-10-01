<?php
// listar_remisiones.php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/models/Remision.php';

// Crear conexión a la base de datos
$database = new Database();
$db = $database->getConnection();

// Instanciar el modelo Remision pasando la conexión
$remision = new Remision($db);

$termino = $_GET['buscar'] ?? '';
$fecha_inicio = $_GET['fecha_inicio'] ?? '';
$fecha_fin = $_GET['fecha_fin'] ?? '';
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$por_pagina = 10;

$stmt = $remision->buscarRemisiones($termino, $fecha_inicio, $fecha_fin);
$remisiones = $stmt->fetchAll(PDO::FETCH_ASSOC);
$total = count($remisiones);

$total_remisiones = $remision->contarRemisiones($termino);
$total_paginas = ceil($total_remisiones / $por_pagina);

include 'views/layout/header.php';
?>

<!-- Content Header -->
<div class="content-header">
    <div class="container">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Listado de Remisiones</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="index.php">Inicio</a></li>
                    <li class="breadcrumb-item active">Listado de Remisiones</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Main content -->
<div class="content">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-list"></i> Remisiones Registradas
                        </h3>
                        <div class="card-tools">
                            <a href="index.php" class="btn btn-success btn-sm">
                                <i class="fas fa-plus"></i> Nueva Remisión
                            </a>
                        </div>
                    </div>
                    
                    <div class="card-body">
                        <!-- Buscador -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <form method="GET" class="form-inline">
                                    <div class="input-group">
                                        <input type="text" class="form-control" name="buscar" 
                                               placeholder="Buscar por número o cliente..." 
                                               value="<?php echo htmlspecialchars($termino); ?>">
                                        <div class="input-group-append">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-search"></i> Buscar
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <div class="col-md-6 text-right">
                                <small class="text-muted">
                                    Mostrando <?php echo count($remisiones); ?> de <?php echo $total_remisiones; ?> remisiones
                                </small>
                            </div>
                        </div>

                        <!-- Tabla de remisiones -->
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Número</th>
                                        <th>Fecha</th>
                                        <th>Cliente</th>
                                        <th>NIT</th>
                                        <th>Persona Contacto</th>
                                        <th>Teléfono Contacto</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($remisiones)): ?>
                                        <tr>
                                            <td colspan="7" class="text-center">
                                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                                <p class="text-muted">No se encontraron remisiones</p>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($remisiones as $rem): ?>
                                            <tr>
                                                <td>
                                                    <strong><?php echo $rem['numero_remision'] ?? 'N/A'; ?></strong>
                                                </td>
                                                <td>
                                                    <?php echo isset($rem['fecha_emision']) ? date('d/m/Y', strtotime($rem['fecha_emision'])) : 'N/A'; ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($rem['nombre_cliente'] ?? 'N/A'); ?></td>
                                                <td><?php echo htmlspecialchars($rem['nit'] ?? 'N/A'); ?></td>
                                                <td><?php echo htmlspecialchars($rem['nombre_persona'] ?? '-'); ?></td>
                                                <td><?php echo htmlspecialchars($rem['telefono_persona'] ?? '-'); ?></td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <button type="button" class="btn btn-info btn-sm" 
                                                                onclick="verRemision(<?php echo $rem['id_remision'] ?? 0; ?>)">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                        <a href="generar_pdf.php?id=<?php echo $rem['id_remision'] ?? 0; ?>" 
                                                           target="_blank" class="btn btn-danger btn-sm">
                                                            <i class="fas fa-file-pdf"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Paginación -->
                        <?php if ($total_paginas > 1): ?>
                            <nav aria-label="Paginación">
                                <ul class="pagination justify-content-center">
                                    <?php if ($pagina > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?pagina=<?php echo $pagina - 1; ?>&buscar=<?php echo urlencode($termino); ?>">
                                                <i class="fas fa-chevron-left"></i>
                                            </a>
                                        </li>
                                    <?php endif; ?>

                                    <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                                        <li class="page-item <?php echo $i == $pagina ? 'active' : ''; ?>">
                                            <a class="page-link" href="?pagina=<?php echo $i; ?>&buscar=<?php echo urlencode($termino); ?>">
                                                <?php echo $i; ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>

                                    <?php if ($pagina < $total_paginas): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?pagina=<?php echo $pagina + 1; ?>&buscar=<?php echo urlencode($termino); ?>">
                                                <i class="fas fa-chevron-right"></i>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para ver detalles de remisión -->
<div class="modal fade" id="modalVerRemision" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">
                    <i class="fas fa-file-invoice"></i> Detalles de Remisión
                </h4>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="contenidoRemision">
                <!-- El contenido se cargará aquí -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times"></i> Cerrar
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.observaciones-responsive {
    max-height: 200px;
    overflow-y: auto;
    word-wrap: break-word;
    white-space: pre-wrap;
    background-color: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    padding: 10px;
    font-size: 14px;
    line-height: 1.4;
}

.detalles-remision .row {
    margin-bottom: 15px;
}

.detalles-remision .col-md-6 {
    margin-bottom: 10px;
}
</style>

<script>
function verRemision(id) {
    if (id === 0) {
        Swal.fire('Error', 'ID de remisión no válido', 'error');
        return;
    }
    
    $.ajax({
        url: 'ajax/ver_remision.php',
        method: 'POST',
        data: { id_remision: id },
        success: function(response) {
            $('#contenidoRemision').html(response);
            $('#modalVerRemision').modal('show');
        },
        error: function() {
            Swal.fire('Error', 'Error al cargar los detalles de la remisión', 'error');
        }
    });
}
</script>

<?php include 'views/layout/footer.php'; ?>