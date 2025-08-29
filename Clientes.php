<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/models/Cliente.php';

$database = new Database();
$db = $database->getConnection();
$cliente = new Cliente($db);

// Obtener todos los clientes
$stmt = $cliente->obtenerTodos();
$clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/views/layout/header.php';
?>

<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Gestión de Clientes</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="index.php">Inicio</a></li>
                    <li class="breadcrumb-item active">Clientes</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Lista de Clientes</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalCrearCliente">
                                <i class="fas fa-plus"></i> Nuevo Cliente
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="tablaClientes" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nombre</th>
                                        <th>Tipo</th>
                                        <th>NIT</th>
                                        <th>Teléfono</th>
                                        <th>Correo</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($clientes as $cli): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($cli['id_cliente']); ?></td>
                                        <td><?php echo htmlspecialchars($cli['nombre_cliente']); ?></td>
                                        <td>
                                            <span class="badge badge-<?php echo $cli['tipo_cliente'] == 'empresa' ? 'primary' : 'secondary'; ?>">
                                                <?php echo ucfirst($cli['tipo_cliente']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($cli['nit']); ?></td>
                                        <td><?php echo htmlspecialchars($cli['telefono'] ?? '-'); ?></td>
                                        <td><?php echo htmlspecialchars($cli['correo'] ?? '-'); ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-info" onclick="verCliente(<?php echo $cli['id_cliente']; ?>)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-sm btn-warning" onclick="editarCliente(<?php echo $cli['id_cliente']; ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Modal Crear Cliente -->
<div class="modal fade" id="modalCrearCliente" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Crear Nuevo Cliente</h4>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="formCrearCliente">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="nombre_cliente">Nombre del Cliente *</label>
                                <input type="text" class="form-control" id="nombre_cliente" name="nombre_cliente" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="tipo_cliente">Tipo *</label>
                                <select class="form-control" id="tipo_cliente" name="tipo_cliente" required>
                                    <option value="">Seleccionar...</option>
                                    <option value="empresa">Empresa</option>
                                    <option value="persona">Persona</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nit">NIT *</label>
                                <input type="text" class="form-control" id="nit" name="nit" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="telefono">Teléfono</label>
                                <input type="text" class="form-control" id="telefono" name="telefono">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="direccion">Dirección</label>
                        <input type="text" class="form-control" id="direccion" name="direccion">
                    </div>
                    <div class="form-group">
                        <label for="correo">Correo Electrónico</label>
                        <input type="email" class="form-control" id="correo" name="correo">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Crear Cliente</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/views/layout/footer.php'; ?>

<script>
$(document).ready(function() {
    // Inicializar DataTable
    $('#tablaClientes').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
        },
        "responsive": true,
        "autoWidth": false
    });

    // Manejar envío del formulario
    $('#formCrearCliente').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: 'ajax/crear_cliente.php',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Éxito!',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message
                    });
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error al procesar la solicitud'
                });
            }
        });
    });
});

function verCliente(id) {
    // Implementar vista de cliente
    Swal.fire({
        icon: 'info',
        title: 'Ver Cliente',
        text: 'Funcionalidad en desarrollo'
    });
}

function editarCliente(id) {
    // Implementar edición de cliente
    Swal.fire({
        icon: 'info',
        title: 'Editar Cliente',
        text: 'Funcionalidad en desarrollo'
    });
}
</script>