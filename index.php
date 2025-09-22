<?php
require_once 'config/database.php';
require_once 'models/Cliente.php';
require_once 'models/Remision.php';
require_once 'models/PersonaContacto.php';

// Crear conexión a la base de datos
$database = new Database();
$db = $database->getConnection();

// Crear instancias de los modelos pasando la conexión
$cliente = new Cliente($db);
$remision = new Remision($db);
$personaContacto = new PersonaContacto($db);

$siguiente_numero = $remision->generarNumeroRemision();

include 'views/layout/header.php';
?>

<!-- Content Header -->
<div class="content-header">
    <div class="container">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Nueva Remisión</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item active">Nueva Remisión</li>
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
                            <i class="fas fa-file-invoice"></i> Formulario de Remisión
                        </h3>
                    </div>
                    <form id="formRemision">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="numero_remision">Número de Remisión</label>
                                        <input type="text" class="form-control" id="numero_remision" name="numero_remision" 
                                               value="<?php echo $siguiente_numero; ?>" readonly>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="fecha_emision">Fecha de Emisión</label>
                                        <input type="date" class="form-control" id="fecha_emision" name="fecha_emision" 
                                               value="<?php echo date('Y-m-d'); ?>" required>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-8">
                                    <div class="form-group">
                                        <label for="cliente">Cliente *</label>
                                        <div class="input-group">
                                            <select class="form-control select2" id="cliente" name="id_cliente" required>
                                                <option value="">Seleccione un cliente...</option>
                                            </select>
                                            <div class="input-group-append">
                                                <button type="button" class="btn btn-success" data-toggle="modal" data-target="#modalCliente">
                                                    <i class="fas fa-plus"></i> Nuevo
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="persona_contacto">Persona de Contacto</label>
                                        <div class="input-group">
                                            <select class="form-control" id="persona_contacto" name="id_persona">
                                                <option value="">Seleccione...</option>
                                            </select>
                                            <div class="input-group-append">
                                                <button type="button" class="btn btn-success btn-sm" onclick="abrirModalPersonaContacto()">
                                                    <i class="fas fa-plus"></i> Nueva
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="observaciones">Observaciones</label>
                                <textarea class="form-control" id="observaciones" name="observaciones" rows="3" 
                                          placeholder="Ingrese observaciones adicionales..."></textarea>
                            </div>

                            <hr>

                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5><i class="fas fa-list"></i> Items de la Remisión</h5>
                                <button type="button" class="btn btn-success btn-agregar-item" onclick="agregarItem()">
                                    <i class="fas fa-plus"></i> Agregar Item
                                </button>
                            </div>

                            <div id="items-container">
                                <!-- Los items se agregarán aquí dinámicamente -->
                            </div>
                        </div>

                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Guardar Remisión
                            </button>
                            <button type="button" class="btn btn-secondary ml-2" onclick="limpiarFormulario()">
                                <i class="fas fa-broom"></i> Limpiar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para crear cliente -->
<div class="modal fade" id="modalCliente" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">
                    <i class="fas fa-user-plus"></i> Nuevo Cliente
                </h4>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="formCliente">
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
                                    <option value="persona">Persona</option>
                                    <option value="empresa">Empresa</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nit">NIT/Cédula *</label>
                                <input type="text" class="form-control" id="nit" name="nit" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="telefono_cliente">Teléfono</label>
                                <input type="text" class="form-control" id="telefono_cliente" name="telefono">
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
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> Guardar Cliente
                    </button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para crear persona de contacto -->
<div class="modal fade" id="modalPersonaContacto" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">
                    <i class="fas fa-user-plus"></i> Nueva Persona de Contacto
                </h4>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="formPersonaContacto">
                <div class="modal-body">
                    <input type="hidden" id="cliente_persona_contacto" name="id_cliente">
                    
                    <div class="form-group">
                        <label for="nombre_persona">Nombre Completo *</label>
                        <input type="text" class="form-control" id="nombre_persona" name="nombre_persona" required>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="cargo_persona">Cargo</label>
                                <input type="text" class="form-control" id="cargo_persona" name="cargo">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="telefono_persona">Teléfono</label>
                                <input type="text" class="form-control" id="telefono_persona" name="telefono">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="correo_persona">Correo Electrónico</label>
                        <input type="email" class="form-control" id="correo_persona" name="correo">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> Guardar Persona
                    </button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    cargarSiguienteNumero();
    
    // Inicializar Select2 para búsqueda de clientes
    $('#cliente').select2({
        placeholder: 'Buscar cliente...',
        allowClear: true,
        ajax: {
            url: 'ajax/buscar_clientes.php',
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                    termino: params.term
                };
            },
            processResults: function (data) {
                return {
                    results: data
                };
            },
            cache: true
        }
    });

    $('#cliente').on('change', function() {
        const clienteId = $(this).val();
        console.log('[v0] Cliente seleccionado:', clienteId);
        if (clienteId) {
            cargarPersonasContacto(clienteId);
        } else {
            $('#persona_contacto').empty().append('<option value="">Seleccione...</option>');
        }
    });

    // Manejar envío del formulario de remisión
    $('#formRemision').on('submit', function(e) {
        e.preventDefault();
        
        const items = [];
        $('.item-row').each(function() {
            const descripcion = $(this).find('.descripcion').val();
            const cantidad = $(this).find('.cantidad').val();
            
            if (descripcion && cantidad) {
                items.push({
                    descripcion: descripcion,
                    cantidad: parseInt(cantidad)
                });
            }
        });

        if (items.length === 0) {
            Swal.fire('Error', 'Debe agregar al menos un item a la remisión', 'error');
            return;
        }

        const formData = new FormData(this);
        formData.append('items', JSON.stringify(items));

        console.log('[v0] Enviando datos del formulario...');
        console.log('[v0] Items:', items);

        $.ajax({
            url: 'ajax/crear_remision.php',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                console.log('[v0] Respuesta del servidor:', response);
                if (response.success) {
                    Swal.fire({
                        title: '¡Éxito!',
                        text: `Remisión #${response.numero_remision} creada correctamente`,
                        icon: 'success',
                        showCancelButton: true,
                        confirmButtonText: 'Imprimir PDF',
                        cancelButtonText: 'Crear Nueva'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.open(`generar_pdf.php?id=${response.id_remision}`, '_blank');
                        }
                        limpiarFormulario();
                        cargarSiguienteNumero();
                    });
                } else {
                    console.log('[v0] Error en respuesta:', response);
                    Swal.fire('Error', response.message, 'error');
                }
            },
            error: function(xhr, status, error) {
                console.log('[v0] Error AJAX:', error);
                console.log('[v0] Respuesta completa:', xhr.responseText);
                Swal.fire('Error', 'Error al procesar la solicitud', 'error');
            }
        });
    });

    // Manejar envío del formulario de cliente
    $('#formCliente').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: 'ajax/crear_cliente.php',
            method: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#modalCliente').modal('hide');
                    $('#formCliente')[0].reset();
                    
                    // Agregar el nuevo cliente al select
                    const newOption = new Option(response.cliente.nombre_cliente, response.cliente.id_cliente, true, true);
                    $('#cliente').append(newOption).trigger('change');
                    
                    Swal.fire('¡Éxito!', 'Cliente creado correctamente', 'success');
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            },
            error: function() {
                Swal.fire('Error', 'Error al crear el cliente', 'error');
            }
        });
    });

    $('#formPersonaContacto').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: 'ajax/crear_persona_contacto.php',
            method: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                console.log('[v0] Respuesta crear persona:', response);
                if (response.success) {
                    $('#modalPersonaContacto').modal('hide');
                    $('#formPersonaContacto')[0].reset();
                    
                    const nuevaPersona = response.persona;
                    $('#persona_contacto').append(`<option value="${nuevaPersona.id_persona}" selected>${nuevaPersona.nombre_persona}</option>`);
                    
                    Swal.fire('¡Éxito!', 'Persona de contacto creada correctamente', 'success');
                } else {
                    Swal.fire('Error', response.message || 'Error al crear la persona de contacto', 'error');
                }
            },
            error: function(xhr, status, error) {
                console.log('[v0] Error AJAX crear persona:', xhr.responseText);
                Swal.fire('Error', 'Error al crear la persona de contacto', 'error');
            }
        });
    });

    agregarItem();
});

let contadorItems = 0;

function agregarItem() {
    contadorItems++;
    const itemHtml = `
        <div class="item-row" id="item-${contadorItems}">
            <div class="row">
                <div class="col-md-8">
                    <div class="form-group">
                        <label for="descripcion-${contadorItems}">Descripción *</label>
                        <input type="text" class="form-control descripcion" id="descripcion-${contadorItems}" 
                               name="descripcion-${contadorItems}" placeholder="Descripción del item..." required>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="cantidad-${contadorItems}">Cantidad *</label>
                        <input type="number" class="form-control cantidad" id="cantidad-${contadorItems}" 
                               name="cantidad-${contadorItems}" min="1" value="1" required>
                    </div>
                </div>
                <div class="col-md-1">
                    <div class="form-group">
                        <label>&nbsp;</label>
                        <button type="button" class="btn btn-danger btn-block" onclick="eliminarItem(${contadorItems})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    $('#items-container').append(itemHtml);
}

function eliminarItem(id) {
    if ($('.item-row').length > 1) {
        $(`#item-${id}`).remove();
    } else {
        Swal.fire('Advertencia', 'Debe mantener al menos un item', 'warning');
    }
}

function limpiarFormulario() {
    $('#formRemision')[0].reset();
    $('#cliente').val(null).trigger('change');
    $('#persona_contacto').empty().append('<option value="">Seleccione...</option>');
    $('#items-container').empty();
    contadorItems = 0;
    agregarItem();
    
    cargarSiguienteNumero();
}

// Función para abrir modal de persona de contacto
function abrirModalPersonaContacto() {
    const clienteId = $('#cliente').val();
    if (!clienteId) {
        Swal.fire('Advertencia', 'Primero debe seleccionar un cliente', 'warning');
        return;
    }
    
    $('#cliente_persona_contacto').val(clienteId);
    $('#modalPersonaContacto').modal('show');
}

function cargarPersonasContacto(clienteId) {
    console.log('[v0] Cargando personas de contacto para cliente:', clienteId);
    
    $.ajax({
        url: 'ajax/obtener_personas_contacto.php',
        method: 'POST',
        data: { id_cliente: clienteId },
        dataType: 'json',
        success: function(response) {
            console.log('[v0] Respuesta completa:', response);
            $('#persona_contacto').empty().append('<option value="">Seleccione...</option>');
            
            if (response.success && response.data && response.data.length > 0) {
                response.data.forEach(function(persona) {
                    console.log('[v0] Agregando persona:', persona);
                    $('#persona_contacto').append(`<option value="${persona.id_persona}">${persona.nombre_persona}</option>`);
                });
            } else {
                console.log('[v0] No se encontraron personas de contacto');
            }
        },
        error: function(xhr, status, error) {
            console.log('[v0] Error al cargar personas:', error);
            console.log('[v0] Respuesta del servidor:', xhr.responseText);
            $('#persona_contacto').empty().append('<option value="">Seleccione...</option>');
        }
    });
}

function cargarSiguienteNumero() {
    console.log('[v0] Cargando siguiente número de remisión...');
    
    $.ajax({
        url: 'ajax/obtener_siguiente_numero.php',
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            console.log('[v0] Respuesta número remisión:', response);
            if (response.success) {
                $('#numero_remision').val(response.siguiente_numero);
                console.log('[v0] Número de remisión cargado:', response.siguiente_numero);
            } else {
                console.log('[v0] Error al obtener número:', response.message);
                $('#numero_remision').val(1);
            }
        },
        error: function(xhr, status, error) {
            console.log('[v0] Error AJAX obtener número:', error);
            console.log('[v0] Respuesta del servidor:', xhr.responseText);
            $('#numero_remision').val(1);
        }
    });
}
</script>

<?php include 'views/layout/footer.php'; ?>
