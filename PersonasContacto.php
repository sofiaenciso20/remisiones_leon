<?php
// filepath: c:\xampp\htdocs\remisiones\PersonasContacto.php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/models/PersonaContacto.php';
require_once __DIR__ . '/models/Cliente.php';

$database = new Database();
$db = $database->getConnection();
$personaContacto = new PersonaContacto($db);
$cliente = new Cliente($db);

// Obtener todas las personas de contacto ORDENADAS por ID de menor a mayor
$stmt = $personaContacto->obtenerTodos();
$personas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Ordenar las personas por ID de menor a mayor
usort($personas, function($a, $b) {
    return $a['id_persona'] - $b['id_persona'];
});

// Obtener todos los clientes para el dropdown
$stmtClientes = $cliente->obtenerTodos();
$clientes = $stmtClientes->fetchAll(PDO::FETCH_ASSOC);

// Incluir cabecera
include __DIR__ . '/views/layout/header.php';
?>

<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Gestión de Personas de Contacto</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="index.php">Inicio</a></li>
                    <li class="breadcrumb-item active">Personas de Contacto</li>
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
                        <h3 class="card-title">Lista de Personas de Contacto</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalCrearPersonaContacto">
                                <i class="fas fa-plus"></i> Nueva Persona de Contacto
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="tablaPersonasContacto" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nombre</th>
                                        <th>Cargo</th>
                                        <th>Teléfono</th>
                                        <th>Correo</th>
                                        <th>Cliente</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($personas) > 0): ?>
                                        <?php foreach ($personas as $persona): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($persona['id_persona']); ?></td>
                                            <td><?php echo htmlspecialchars($persona['nombre_persona']); ?></td>
                                            <td><?php echo htmlspecialchars($persona['cargo'] ?? '-'); ?></td>
                                            <td><?php echo htmlspecialchars($persona['telefono'] ?? '-'); ?></td>
                                            <td><?php echo htmlspecialchars($persona['correo'] ?? '-'); ?></td>
                                            <td>
                                                <?php 
                                                // Buscar el nombre del cliente asociado
                                                $nombreCliente = '-';
                                                foreach ($clientes as $cli) {
                                                    if ($cli['id_cliente'] == $persona['id_cliente']) {
                                                        $nombreCliente = htmlspecialchars($cli['nombre_cliente']);
                                                        break;
                                                    }
                                                }
                                                echo $nombreCliente;
                                                ?>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-info" onclick="verPersonaContacto(<?php echo $persona['id_persona']; ?>)">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn btn-sm btn-warning" onclick="editarPersonaContacto(<?php echo $persona['id_persona']; ?>)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" class="text-center">No hay personas de contacto registradas</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Modal Crear Persona de Contacto -->
<div class="modal fade" id="modalCrearPersonaContacto" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Crear Nueva Persona de Contacto</h4>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="formCrearPersonaContacto">
                <div class="modal-body">
                    <div id="alertContainerPersona"></div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nombre_persona">Nombre de la Persona *</label>
                                <input type="text" class="form-control" id="nombre_persona" name="nombre_persona" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="cargo">Cargo</label>
                                <input type="text" class="form-control" id="cargo" name="cargo">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="telefono">Teléfono</label>
                                <input type="text" class="form-control" id="telefono" name="telefono">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="correo">Correo Electrónico</label>
                                <input type="email" class="form-control" id="correo" name="correo">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="id_cliente">Cliente *</label>
                        <select class="form-control" id="id_cliente" name="id_cliente" required>
                            <option value="">Seleccionar Cliente...</option>
                            <?php foreach ($clientes as $clienteOption): ?>
                                <option value="<?php echo $clienteOption['id_cliente']; ?>">
                                    <?php echo htmlspecialchars($clienteOption['nombre_cliente']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="btnCrearPersonaContacto">Crear Persona de Contacto</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Ver Persona de Contacto -->
<div class="modal fade" id="modalVerPersonaContacto" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Detalles de Persona de Contacto</h4>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><strong>ID:</strong></label>
                            <p id="ver_id_persona" class="form-control-plaintext"></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><strong>Nombre:</strong></label>
                            <p id="ver_nombre_persona" class="form-control-plaintext"></p>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><strong>Cargo:</strong></label>
                            <p id="ver_cargo" class="form-control-plaintext"></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><strong>Teléfono:</strong></label>
                            <p id="ver_telefono" class="form-control-plaintext"></p>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><strong>Correo Electrónico:</strong></label>
                            <p id="ver_correo" class="form-control-plaintext"></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><strong>Cliente:</strong></label>
                            <p id="ver_cliente" class="form-control-plaintext"></p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Editar Persona de Contacto -->
<div class="modal fade" id="modalEditarPersonaContacto" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Editar Persona de Contacto</h4>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="formEditarPersonaContacto">
                <input type="hidden" id="editar_id_persona" name="id_persona">
                <div class="modal-body">
                    <div id="alertContainerEditarPersona"></div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editar_nombre_persona">Nombre de la Persona *</label>
                                <input type="text" class="form-control" id="editar_nombre_persona" name="nombre_persona" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editar_cargo">Cargo</label>
                                <input type="text" class="form-control" id="editar_cargo" name="cargo">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editar_telefono">Teléfono</label>
                                <input type="text" class="form-control" id="editar_telefono" name="telefono">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="editar_correo">Correo Electrónico</label>
                                <input type="email" class="form-control" id="editar_correo" name="correo">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="editar_id_cliente">Cliente *</label>
                        <select class="form-control" id="editar_id_cliente" name="id_cliente" required>
                            <option value="">Seleccionar Cliente...</option>
                            <?php foreach ($clientes as $clienteOption): ?>
                                <option value="<?php echo $clienteOption['id_cliente']; ?>">
                                    <?php echo htmlspecialchars($clienteOption['nombre_cliente']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="btnEditarPersonaContacto">Actualizar Persona de Contacto</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/views/layout/footer.php'; ?>

<script>
// Función para validar el formulario de persona de contacto
function validarFormularioPersona() {
    const nombre = $('#nombre_persona').val().trim();
    const idCliente = $('#id_cliente').val();
    
    if (!nombre) {
        mostrarAlertaPersona('El nombre de la persona es obligatorio', 'danger');
        return false;
    }
    
    if (!idCliente) {
        mostrarAlertaPersona('Debe seleccionar un cliente', 'danger');
        return false;
    }
    
    // Si todo está bien, limpiar alertas y retornar true
    $('#alertContainerPersona').empty();
    return true;
}

// Función para validar el formulario de edición
function validarFormularioEditarPersona() {
    const nombre = $('#editar_nombre_persona').val().trim();
    const idCliente = $('#editar_id_cliente').val();
    
    if (!nombre) {
        mostrarAlertaEditarPersona('El nombre de la persona es obligatorio', 'danger');
        return false;
    }
    
    if (!idCliente) {
        mostrarAlertaEditarPersona('Debe seleccionar un cliente', 'danger');
        return false;
    }
    
    // Si todo está bien, limpiar alertas y retornar true
    $('#alertContainerEditarPersona').empty();
    return true;
}

// Función para mostrar alertas en el modal de persona
function mostrarAlertaPersona(mensaje, tipo) {
    $('#alertContainerPersona').html(
        `<div class="alert alert-${tipo} alert-dismissible fade show" role="alert">
            ${mensaje}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>`
    );
}

// Función para mostrar alertas en el modal de edición
function mostrarAlertaEditarPersona(mensaje, tipo) {
    $('#alertContainerEditarPersona').html(
        `<div class="alert alert-${tipo} alert-dismissible fade show" role="alert">
            ${mensaje}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>`
    );
}

// Función para ver los detalles de una persona de contacto - VERSIÓN CORREGIDA
function verPersonaContacto(id) {
    console.log("Solicitando datos para persona ID:", id);
    
    $.ajax({
        url: 'ajax/obtener_persona_contacto.php',
        type: 'POST',
        data: { id_persona: id },
        dataType: 'json',
        success: function(response) {
            console.log("Respuesta recibida:", response);
            
            if (response.success) {
                const persona = response.data;
                console.log("Datos de persona:", persona);
                
                // Llenar los campos del modal de ver
                $('#ver_id_persona').text(persona.id_persona || 'N/A');
                $('#ver_nombre_persona').text(persona.nombre_persona || 'N/A');
                $('#ver_cargo').text(persona.cargo || '-');
                $('#ver_telefono').text(persona.telefono || '-');
                $('#ver_correo').text(persona.correo || '-');
                $('#ver_cliente').text(persona.nombre_cliente || '-');
                
                // Mostrar el modal
                $('#modalVerPersonaContacto').modal('show');
            } else {
                console.error("Error en respuesta:", response.message);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: response.message || 'Error al cargar los datos de la persona de contacto'
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
                text: 'Error al cargar los datos de la persona de contacto: ' + error
            });
        }
    });
}

// Función para editar una persona de contacto - VERSIÓN CORREGIDA
function editarPersonaContacto(id) {
    console.log("Editando persona ID:", id);
    
    $.ajax({
        url: 'ajax/obtener_persona_contacto.php',
        type: 'POST',
        data: { id_persona: id },
        dataType: 'json',
        success: function(response) {
            console.log("Respuesta para edición:", response);
            
            if (response.success) {
                const persona = response.data;
                
                // Llenar los campos del formulario de edición
                $('#editar_id_persona').val(persona.id_persona);
                $('#editar_nombre_persona').val(persona.nombre_persona || '');
                $('#editar_cargo').val(persona.cargo || '');
                $('#editar_telefono').val(persona.telefono || '');
                $('#editar_correo').val(persona.correo || '');
                $('#editar_id_cliente').val(persona.id_cliente);
                
                // Limpiar alertas
                $('#alertContainerEditarPersona').empty();
                
                // Mostrar el modal
                $('#modalEditarPersonaContacto').modal('show');
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: response.message || 'Error al cargar los datos de la persona de contacto'
                });
            }
        },
        error: function(xhr, status, error) {
            console.error("Error en la solicitud de edición:", error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Error al cargar los datos de la persona de contacto: ' + error
            });
        }
    });
}

// Cuando el documento esté listo
$(document).ready(function() {
    console.log("Documento listo, inicializando DataTable para personas de contacto...");
    
    // Inicializar DataTable con paginación cada 10 registros
    $('#tablaPersonasContacto').DataTable({
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

    // Manejar envío del formulario de persona de contacto
    $('#formCrearPersonaContacto').on('submit', function(e) {
        console.log("Formulario de persona de contacto enviado, previniendo comportamiento por defecto...");
        
        // Prevenir el envío tradicional del formulario
        e.preventDefault();
        
        // Validar campos requeridos
        if (!validarFormularioPersona()) {
            console.log("Validación fallida");
            return;
        }
        
        console.log("Enviando datos por AJAX...");
        
        // Mostrar indicador de carga
        $('#btnCrearPersonaContacto').html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Procesando...').prop('disabled', true);
        
        // Obtener los datos del formulario
        const formData = $(this).serialize();
        console.log("Datos a enviar:", formData);
        
        // Enviar datos por AJAX
        $.ajax({
            url: 'ajax/crear_persona_contacto.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                console.log("Respuesta recibida:", response);
                $('#btnCrearPersonaContacto').html('Crear Persona de Contacto').prop('disabled', false);
                
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Éxito!',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        $('#modalCrearPersonaContacto').modal('hide');
                        // Limpiar formulario
                        $('#formCrearPersonaContacto')[0].reset();
                        $('#alertContainerPersona').empty();
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
                $('#btnCrearPersonaContacto').html('Crear Persona de Contacto').prop('disabled', false);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error al procesar la solicitud: ' + error
                });
            }
        });
    });

    // Manejar envío del formulario de edición
    $('#formEditarPersonaContacto').on('submit', function(e) {
        console.log("Formulario de edición de persona de contacto enviado...");
        
        // Prevenir el envío tradicional del formulario
        e.preventDefault();
        
        // Validar campos requeridos
        if (!validarFormularioEditarPersona()) {
            console.log("Validación fallida");
            return;
        }
        
        console.log("Enviando datos de edición por AJAX...");
        
        // Mostrar indicador de carga
        $('#btnEditarPersonaContacto').html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Procesando...').prop('disabled', true);
        
        // Obtener los datos del formulario
        const formData = $(this).serialize();
        console.log("Datos a enviar:", formData);
        
        // Enviar datos por AJAX
        $.ajax({
            url: 'ajax/editar_persona_contacto.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                console.log("Respuesta recibida:", response);
                $('#btnEditarPersonaContacto').html('Actualizar Persona de Contacto').prop('disabled', false);
                
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Éxito!',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        $('#modalEditarPersonaContacto').modal('hide');
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
                $('#btnEditarPersonaContacto').html('Actualizar Persona de Contacto').prop('disabled', false);
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