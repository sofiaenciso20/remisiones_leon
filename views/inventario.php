<?php
// views/inventario.php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Producto.php';
require_once __DIR__ . '/../models/MovimientoInventario.php';

session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: ../index.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

$productoModel = new Producto($db);
$productos = $productoModel->obtenerTodos();
?>

<?php include __DIR__ . '/layout/header.php'; ?>

<style>
    .stock-bajo { background-color: #ffe6e6; }
    .stock-medio { background-color: #fff9e6; }
    .stock-ok { background-color: #e6ffe6; }
    .table-hover tbody tr:hover { background-color: rgba(0,0,0,.075); }
    .badge-stock { font-size: 0.8em; }
</style>

<!-- Content Header -->
<div class="content-header bg-light py-3">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h1 class="m-0 text-dark"><i class="fas fa-boxes mr-2"></i>Gestión de Inventario</h1>
            </div>
            <div class="col-md-6">
                <ol class="breadcrumb justify-content-md-end mb-0">
                    <li class="breadcrumb-item"><a href="index.php">Inicio</a></li>
                    <li class="breadcrumb-item active">Inventario</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Main content -->
<div class="content py-4">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-boxes me-2"></i>Inventario de Productos</h2>
            <div>
                <button class="btn btn-success" data-toggle="modal" data-target="#nuevoProductoModal">
                    <i class="fas fa-plus mr-1"></i> Nuevo Producto
                </button>
                <a href="index.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left mr-1"></i> Volver al Inicio
                </a>
            </div>
        </div>

        <!-- Alertas -->
        <?php if (isset($_SESSION['mensaje'])): ?>
            <div class="alert alert-<?php echo $_SESSION['tipo_mensaje']; ?> alert-dismissible fade show" role="alert">
                <?php echo $_SESSION['mensaje']; ?>
                <button type="button" class="close" data-dismiss="alert">
                    <span>&times;</span>
                </button>
            </div>
            <?php unset($_SESSION['mensaje'], $_SESSION['tipo_mensaje']); ?>
        <?php endif; ?>

        <?php if (empty($productos)): ?>
            <div class="alert alert-warning">
                <h4>No se encontraron productos</h4>
                <button class="btn btn-primary" data-toggle="modal" data-target="#nuevoProductoModal">
                    <i class="fas fa-plus mr-1"></i> Crear Primer Producto
                </button>
            </div>
        <?php else: ?>

        <!-- Filtros y tabla de productos -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <input type="text" class="form-control" id="buscarProducto" placeholder="Buscar producto...">
                    </div>
                    <div class="col-md-3">
                        <select class="form-control" id="filtroInventario">
                            <option value="todos">Todos los productos</option>
                            <option value="con-inventario">Con inventario</option>
                            <option value="sin-inventario">Sin inventario</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-control" id="filtroStock">
                            <option value="todos">Todo el stock</option>
                            <option value="bajo">Stock bajo</option>
                            <option value="medio">Stock medio</option>
                            <option value="ok">Stock ok</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla de productos -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-striped" id="tablaInventario">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Producto</th>
                                <th>Maneja Inventario</th>
                                <th>Stock Actual</th>
                                <th>Stock Mínimo</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($productos as $producto): 
                                $stock = $producto['stock_actual'] ?? 0;
                                $stock_minimo = $producto['stock_minimo'] ?? 0;
                                $maneja_inventario = $producto['maneja_inventario'] ?? 0;
                                
                                if ($maneja_inventario) {
                                    if ($stock <= $stock_minimo) {
                                        $clase_stock = 'stock-bajo';
                                        $estado = '<span class="badge bg-danger badge-stock">Bajo</span>';
                                    } elseif ($stock <= ($stock_minimo + 10)) {
                                        $clase_stock = 'stock-medio';
                                        $estado = '<span class="badge bg-warning badge-stock">Medio</span>';
                                    } else {
                                        $clase_stock = 'stock-ok';
                                        $estado = '<span class="badge bg-success badge-stock">Ok</span>';
                                    }
                                } else {
                                    $clase_stock = '';
                                    $estado = '<span class="badge bg-secondary badge-stock">No aplica</span>';
                                }
                            ?>
                            <tr class="<?php echo $clase_stock; ?>" 
                                data-inventario="<?php echo $maneja_inventario ? 'con-inventario' : 'sin-inventario'; ?>" 
                                data-stock="<?php echo $maneja_inventario ? ($stock <= $stock_minimo ? 'bajo' : ($stock <= ($stock_minimo + 10) ? 'medio' : 'ok')) : 'na'; ?>">
                                <td><?php echo $producto['id_producto']; ?></td>
                                <td><?php echo htmlspecialchars($producto['nombre_producto']); ?></td>
                                <td>
                                    <?php if ($maneja_inventario): ?>
                                        <span class="badge bg-success">Sí</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">No</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?php echo $stock; ?></strong>
                                    <small class="text-muted"> unidades</small>
                                </td>
                                <td>
                                    <?php if ($maneja_inventario): ?>
                                        <strong><?php echo $stock_minimo; ?></strong>
                                        <small class="text-muted"> unidades</small>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $estado; ?></td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <?php if ($maneja_inventario): ?>
                                            <button class="btn btn-outline-primary btn-entrada" 
                                                    data-id="<?php echo $producto['id_producto']; ?>"
                                                    data-nombre="<?php echo htmlspecialchars($producto['nombre_producto']); ?>">
                                                <i class="fas fa-arrow-down"></i> Entrada
                                            </button>
                                            <button class="btn btn-outline-warning btn-salida"
                                                    data-id="<?php echo $producto['id_producto']; ?>"
                                                    data-nombre="<?php echo htmlspecialchars($producto['nombre_producto']); ?>"
                                                    data-stock="<?php echo $stock; ?>">
                                                <i class="fas fa-arrow-up"></i> Salida
                                            </button>
                                        <?php endif; ?>
                                        <button class="btn btn-outline-info btn-historial"
                                                data-id="<?php echo $producto['id_producto']; ?>"
                                                data-nombre="<?php echo htmlspecialchars($producto['nombre_producto']); ?>">
                                            <i class="fas fa-history"></i> Historial
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Nuevo Producto -->
<div class="modal fade" id="nuevoProductoModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="formNuevoProducto">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="fas fa-plus me-2"></i>Nuevo Producto</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="nombre_producto" class="form-label">Nombre del Producto *</label>
                        <input type="text" class="form-control" id="nombre_producto" name="nombre_producto" required>
                    </div>
                    <div class="form-group form-check">
                        <input type="checkbox" class="form-check-input" id="maneja_inventario" name="maneja_inventario" value="1" checked>
                        <label class="form-check-label" for="maneja_inventario">Maneja inventario</label>
                    </div>
                    <div class="form-group">
                        <label for="stock_inicial" class="form-label">Stock Inicial</label>
                        <input type="number" class="form-control" id="stock_inicial" name="stock_inicial" value="0" min="0" required>
                    </div>
                    <div class="form-group">
                        <label for="stock_minimo" class="form-label">Stock Mínimo</label>
                        <input type="number" class="form-control" id="stock_minimo" name="stock_minimo" value="0" min="0" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">Crear Producto</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Entrada -->
<div class="modal fade" id="entradaModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="formEntrada">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-arrow-down me-2"></i>Entrada de Inventario</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="entrada_id_producto" name="id_producto">
                    <div class="form-group">
                        <label class="form-label">Producto</label>
                        <input type="text" class="form-control" id="entrada_nombre_producto" readonly>
                    </div>
                    <div class="form-group">
                        <label for="cantidad_entrada" class="form-label">Cantidad *</label>
                        <input type="number" class="form-control" id="cantidad_entrada" name="cantidad" min="1" required>
                    </div>
                    <div class="form-group">
                        <label for="observaciones_entrada" class="form-label">Observaciones</label>
                        <textarea class="form-control" id="observaciones_entrada" name="observaciones" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Registrar Entrada</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Salida -->
<div class="modal fade" id="salidaModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="formSalida">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title"><i class="fas fa-arrow-up me-2"></i>Salida de Inventario</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="salida_id_producto" name="id_producto">
                    <div class="form-group">
                        <label class="form-label">Producto</label>
                        <input type="text" class="form-control" id="salida_nombre_producto" readonly>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Stock Actual</label>
                        <input type="text" class="form-control" id="salida_stock_actual" readonly>
                    </div>
                    <div class="form-group">
                        <label for="cantidad_salida" class="form-label">Cantidad *</label>
                        <input type="number" class="form-control" id="cantidad_salida" name="cantidad" min="1" required>
                    </div>
                    <div class="form-group">
                        <label for="observaciones_salida" class="form-label">Observaciones</label>
                        <textarea class="form-control" id="observaciones_salida" name="observaciones" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning">Registrar Salida</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Historial -->
<div class="modal fade" id="historialModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="fas fa-history me-2"></i>Historial de Movimientos</h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="contenido-historial">
                    <!-- El contenido se carga via AJAX -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Filtros
    $('#buscarProducto').on('input', filtrarTabla);
    $('#filtroInventario, #filtroStock').on('change', filtrarTabla);

    function filtrarTabla() {
        const termino = $('#buscarProducto').val().toLowerCase();
        const filtroInventario = $('#filtroInventario').val();
        const filtroStock = $('#filtroStock').val();

        $('#tablaInventario tbody tr').each(function() {
            const nombre = $(this).find('td:eq(1)').text().toLowerCase();
            const inventario = $(this).data('inventario');
            const stock = $(this).data('stock');

            const coincideNombre = nombre.includes(termino);
            const coincideInventario = filtroInventario === 'todos' || inventario === filtroInventario;
            const coincideStock = filtroStock === 'todos' || stock === filtroStock;

            if (coincideNombre && coincideInventario && coincideStock) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    }

    // Modal de entrada
    $('.btn-entrada').on('click', function() {
        const id = $(this).data('id');
        const nombre = $(this).data('nombre');
        
        $('#entrada_id_producto').val(id);
        $('#entrada_nombre_producto').val(nombre);
        $('#entradaModal').modal('show');
    });

    // Modal de salida
    $('.btn-salida').on('click', function() {
        const id = $(this).data('id');
        const nombre = $(this).data('nombre');
        const stock = $(this).data('stock');
        
        $('#salida_id_producto').val(id);
        $('#salida_nombre_producto').val(nombre);
        $('#salida_stock_actual').val(stock + ' unidades');
        $('#cantidad_salida').attr('max', stock);
        $('#salidaModal').modal('show');
    });

    // Modal de historial
    $('.btn-historial').on('click', function() {
        const id = $(this).data('id');
        const nombre = $(this).data('nombre');
        
        $('#historialModal .modal-title').text('Historial de Movimientos - ' + nombre);
        
        $.ajax({
            url: 'ajax/obtener_movimientos.php',
            type: 'GET',
            data: { id_producto: id },
            success: function(response) {
                $('#contenido-historial').html(response);
            },
            error: function() {
                $('#contenido-historial').html('<div class="alert alert-danger">Error al cargar el historial</div>');
            }
        });
        
        $('#historialModal').modal('show');
    });

    // Manejar envío del formulario de nuevo producto
    $('#formNuevoProducto').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            url: 'ajax/crear_producto.php',
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                const data = JSON.parse(response);
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            },
            error: function() {
                alert('Error al crear el producto');
            }
        });
    });

    // Manejar envío del formulario de entrada
    $('#formEntrada').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            url: 'ajax/registrar_entrada.php',
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                const data = JSON.parse(response);
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            },
            error: function() {
                alert('Error al registrar la entrada');
            }
        });
    });

    // Manejar envío del formulario de salida
    $('#formSalida').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            url: 'ajax/registrar_salida.php',
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                const data = JSON.parse(response);
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            },
            error: function() {
                alert('Error al registrar la salida');
            }
        });
    });
});
</script>

<?php include __DIR__ . '/layout/footer.php'; ?>