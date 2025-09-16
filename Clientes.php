<?php
// filepath: c:\xampp\htdocs\remisiones\Clientes.php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/models/Cliente.php';

$database = new Database();
$db = $database->getConnection();
$cliente = new Cliente($db);

// Obtener todos los clientes
$stmt = $cliente->obtenerTodos();
$clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Incluir cabecera
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
                    <div id="alertContainer"></div>
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
                                <small class="form-text text-muted">El NIT debe ser único para cada cliente.</small>
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
                    <button type="submit" class="btn btn-primary" id="btnCrearCliente">Crear Cliente</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/views/layout/footer.php'; ?>

<!-- Asegurarse de que jQuery esté cargado antes de este script -->
<script>
// Función para validar el formulario
function validarFormulario() {
    const nombre = $('#nombre_cliente').val().trim();
    const tipo = $('#tipo_cliente').val();
    const nit = $('#nit').val().trim();
    
    if (!nombre) {
        mostrarAlerta('El nombre del cliente es obligatorio', 'danger');
        return false;
    }
    
    if (!tipo) {
        mostrarAlerta('Debe seleccionar un tipo de cliente', 'danger');
        return false;
    }
    
    if (!nit) {
        mostrarAlerta('El NIT es obligatorio', 'danger');
        return false;
    }
    
    // Si todo está bien, limpiar alertas y retornar true
    $('#alertContainer').empty();
    return true;
}

// Función para mostrar alertas
function mostrarAlerta(mensaje, tipo) {
    $('#alertContainer').html(
        `<div class="alert alert-${tipo} alert-dismissible fade show" role="alert">
            ${mensaje}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>`
    );
}

// Cuando el documento esté listo
$(document).ready(function() {
    console.log("Documento listo, inicializando DataTable...");
    
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
        console.log("Formulario enviado, previniendo comportamiento por defecto...");
        
        // Prevenir el envío tradicional del formulario
        e.preventDefault();
        
        // Validar campos requeridos
        if (!validarFormulario()) {
            console.log("Validación fallida");
            return;
        }
        
        console.log("Enviando datos por AJAX...");
        
        // Mostrar indicador de carga
        $('#btnCrearCliente').html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Procesando...').prop('disabled', true);
        
        // Obtener los datos del formulario
        const formData = $(this).serialize();
        console.log("Datos a enviar:", formData);
        
        // Enviar datos por AJAX
        $.ajax({
            url: 'ajax/crear_cliente.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                console.log("Respuesta recibida:", response);
                $('#btnCrearCliente').html('Crear Cliente').prop('disabled', false);
                
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Éxito!',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        $('#modalCrearCliente').modal('hide');
                        // Limpiar formulario
                        $('#formCrearCliente')[0].reset();
                        $('#alertContainer').empty();
                        // Recargar la página para ver el nuevo cliente
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
            error: function(xhr, status, error) {
                console.error("Error en la solicitud:", error);
                $('#btnCrearCliente').html('Crear Cliente').prop('disabled', false);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error al procesar la solicitud: ' + error
                });
            }
        });
    });
});

function verCliente(id) {
    Swal.fire({
        icon: 'info',
        title: 'Ver Cliente',
        text: 'Funcionalidad en desarrollo. Cliente ID: ' + id
    });
}

function editarCliente(id) {
    Swal.fire({
        icon: 'info',
        title: 'Editar Cliente',
        text: 'Funcionalidad en desarrollo. Cliente ID: ' + id
    });
}
</script>