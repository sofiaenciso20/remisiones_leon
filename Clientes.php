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

// Ordenar los clientes por ID de menor a mayor
usort($clientes, function($a, $b) {
    return $a['id_cliente'] - $b['id_cliente'];
});

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

<!-- Modal Ver Cliente -->
<div class="modal fade" id="modalVerCliente" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Detalles del Cliente</h4>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><strong>ID:</strong></label>
                            <p id="ver_id_cliente" class="form-control-plaintext"></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><strong>Nombre:</strong></label>
                            <p id="ver_nombre_cliente" class="form-control-plaintext"></p>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><strong>Tipo:</strong></label>
                            <p id="ver_tipo_cliente" class="form-control-plaintext"></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><strong>NIT:</strong></label>
                            <p id="ver_nit" class="form-control-plaintext"></p>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><strong>Teléfono:</strong></label>
                            <p id="ver_telefono_cliente" class="form-control-plaintext"></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><strong>Correo Electrónico:</strong></label>
                            <p id="ver_correo_cliente" class="form-control-plaintext"></p>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label><strong>Dirección:</strong></label>
                    <p id="ver_direccion" class="form-control-plaintext"></p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Editar Cliente -->
<div class="modal fade" id="modalEditarCliente" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Editar Cliente</h4>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="formEditarCliente">
                <input type="hidden" id="editar_id_cliente" name="id_cliente">
                <div class="modal-body">
                    <div id="alertContainerEditarCliente"></div>
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="editar_nombre_cliente">Nombre del Cliente *</label>
                                <input type="text" class="form-control" id="editar_nombre_cliente" name="nombre_cliente" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="editar_tipo_cliente">Tipo *</label>
                                <select class="form-control" id="editar_tipo_cliente" name="tipo_cliente" required>
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
                                <label for="editar_nit">NIT *</label>
                                <input type="text" class="form-control" id="editar_nit" name="nit" required>
                                <small class="form-text text-muted">El NIT debe ser único para cada cliente.</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editar_telefono">Teléfono</label>
                                <input type="text" class="form-control" id="editar_telefono" name="telefono">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="editar_direccion">Dirección</label>
                        <input type="text" class="form-control" id="editar_direccion" name="direccion">
                    </div>
                    <div class="form-group">
                        <label for="editar_correo">Correo Electrónico</label>
                        <input type="email" class="form-control" id="editar_correo" name="correo">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="btnEditarCliente">Actualizar Cliente</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/views/layout/footer.php'; ?>

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

// Función para validar el formulario de edición
function validarFormularioEditarCliente() {
    const nombre = $('#editar_nombre_cliente').val().trim();
    const tipo = $('#editar_tipo_cliente').val();
    const nit = $('#editar_nit').val().trim();
    
    if (!nombre) {
        mostrarAlertaEditarCliente('El nombre del cliente es obligatorio', 'danger');
        return false;
    }
    
    if (!tipo) {
        mostrarAlertaEditarCliente('Debe seleccionar un tipo de cliente', 'danger');
        return false;
    }
    
    if (!nit) {
        mostrarAlertaEditarCliente('El NIT es obligatorio', 'danger');
        return false;
    }
    
    // Si todo está bien, limpiar alertas y retornar true
    $('#alertContainerEditarCliente').empty();
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

// Función para mostrar alertas en el modal de edición
function mostrarAlertaEditarCliente(mensaje, tipo) {
    $('#alertContainerEditarCliente').html(
        `<div class="alert alert-${tipo} alert-dismissible fade show" role="alert">
            ${mensaje}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>`
    );
}

// Función para ver los detalles de un cliente
function verCliente(id) {
    console.log("Solicitando datos para cliente ID:", id);
    
    $.ajax({
        url: 'ajax/obtener_cliente.php',
        type: 'POST',
        data: { id_cliente: id },
        dataType: 'json',
        success: function(response) {
            console.log("Respuesta recibida:", response);
            
            if (response.success) {
                const cliente = response.data;
                console.log("Datos del cliente:", cliente);
                
                // Llenar los campos del modal de ver
                $('#ver_id_cliente').text(cliente.id_cliente || 'N/A');
                $('#ver_nombre_cliente').text(cliente.nombre_cliente || 'N/A');
                $('#ver_tipo_cliente').text(cliente.tipo_cliente ? cliente.tipo_cliente.charAt(0).toUpperCase() + cliente.tipo_cliente.slice(1) : '-');
                $('#ver_nit').text(cliente.nit || '-');
                $('#ver_telefono_cliente').text(cliente.telefono || '-');
                $('#ver_correo_cliente').text(cliente.correo || '-');
                $('#ver_direccion').text(cliente.direccion || '-');
                
                // Mostrar el modal
                $('#modalVerCliente').modal('show');
            } else {
                console.error("Error en respuesta:", response.message);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: response.message || 'Error al cargar los datos del cliente'
                });
            }
        },
        error: function(xhr, status, error) {
            console.error("Error en la solicitud AJAX:", error);
            console.error("Status:", status);
            console.error("Respuesta completa:", xhr.responseText);
            
            Swal.fire({
                icon: 'error',
                title: 'Error de conexión',
                text: 'Error al cargar los datos del cliente: ' + error
            });
        }
    });
}

// Función para editar un cliente
function editarCliente(id) {
    console.log("Editando cliente ID:", id);
    
    $.ajax({
        url: 'ajax/obtener_cliente.php',
        type: 'POST',
        data: { id_cliente: id },
        dataType: 'json',
        success: function(response) {
            console.log("Respuesta para edición:", response);
            
            if (response.success) {
                const cliente = response.data;
                
                // Llenar los campos del formulario de edición
                $('#editar_id_cliente').val(cliente.id_cliente);
                $('#editar_nombre_cliente').val(cliente.nombre_cliente || '');
                $('#editar_tipo_cliente').val(cliente.tipo_cliente || '');
                $('#editar_nit').val(cliente.nit || '');
                $('#editar_telefono').val(cliente.telefono || '');
                $('#editar_direccion').val(cliente.direccion || '');
                $('#editar_correo').val(cliente.correo || '');
                
                // Limpiar alertas
                $('#alertContainerEditarCliente').empty();
                
                // Mostrar el modal
                $('#modalEditarCliente').modal('show');
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: response.message || 'Error al cargar los datos del cliente'
                });
            }
        },
        error: function(xhr, status, error) {
            console.error("Error en la solicitud de edición:", error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Error al cargar los datos del cliente: ' + error
            });
        }
    });
}

// Cuando el documento esté listo
$(document).ready(function() {
    console.log("Documento listo, inicializando DataTable...");
    
    // Inicializar DataTable con paginación cada 10 registros
    $('#tablaClientes').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
        },
        "responsive": true,
        "autoWidth": false,
        "pageLength": 10, // Mostrar 10 registros por página
        "lengthMenu": [10, 25, 50, 100], // Opciones de cantidad de registros por página
        "order": [[0, "asc"]], // Ordenar por la primera columna (ID) de forma ascendente
        "drawCallback": function(settings) {
            // Actualizar información de paginación
            const api = this.api();
            const pageInfo = api.page.info();
            console.log(`Página ${pageInfo.page + 1} de ${pageInfo.pages} páginas`);
        }
    });

    // Manejar envío del formulario de crear cliente
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

    // Manejar envío del formulario de edición
    $('#formEditarCliente').on('submit', function(e) {
        console.log("Formulario de edición de cliente enviado...");
        
        // Prevenir el envío tradicional del formulario
        e.preventDefault();
        
        // Validar campos requeridos
        if (!validarFormularioEditarCliente()) {
            console.log("Validación fallida");
            return;
        }
        
        console.log("Enviando datos de edición por AJAX...");
        
        // Mostrar indicador de carga
        $('#btnEditarCliente').html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Procesando...').prop('disabled', true);
        
        // Obtener los datos del formulario
        const formData = $(this).serialize();
        console.log("Datos a enviar:", formData);
        
        // Enviar datos por AJAX
        $.ajax({
            url: 'ajax/editar_cliente.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                console.log("Respuesta recibida:", response);
                $('#btnEditarCliente').html('Actualizar Cliente').prop('disabled', false);
                
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Éxito!',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        $('#modalEditarCliente').modal('hide');
                        // Recargar la página para ver los cambios
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
                $('#btnEditarCliente').html('Actualizar Cliente').prop('disabled', false);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error al procesar la solicitud: ' + error
                });
            }
        });
    });
});
</script>