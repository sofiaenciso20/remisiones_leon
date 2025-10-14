<?php
require_once 'config/database.php';
require_once 'models/Cliente.php';
require_once 'models/Remision.php';
require_once 'models/PersonaContacto.php';
require_once 'models/Producto.php';

// Manejar acciones
$action = $_GET['action'] ?? 'nueva_remision';

switch ($action) {
    case 'inventario':
        include 'views/inventario.php';
        exit;
    case 'nueva_remision':
    default:
        // Continuar con el código existente para nueva remisión
        break;
}

// Crear conexión a la base de datos
$database = new Database();
$db = $database->getConnection();

// Crear instancias de los modelos pasando la conexión
$cliente = new Cliente($db);
$remision = new Remision($db);
$personaContacto = new PersonaContacto($db);
$producto = new Producto($db);

$siguiente_numero = $remision->generarNumeroRemision();

include 'views/layout/header.php';
?>

<style>
/* Estilos personalizados para mejorar la estética y responsividad */
.card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}

.form-group label {
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.btn-agregar-item {
    margin-bottom: 1rem;
}

.item-row {
    background-color: #f8f9fa;
    padding: 1rem;
    margin-bottom: 1rem;
    border-radius: 0.375rem;
    border: 1px solid #dee2e6;
}

.total-general-container {
    background-color: #e9ecef;
    padding: 1rem;
    border-radius: 0.375rem;
    margin-top: 1rem;
}

.select2-container {
    width: 100% !important;
}

@media (max-width: 768px) {
    .card-body {
        padding: 1rem;
    }
    
    .item-row .row > div {
        margin-bottom: 0.75rem;
    }
    
    .item-row .btn-danger {
        width: 100%;
        margin-top: 0.5rem;
    }
    
    .input-group-append .btn {
        margin-top: 0.5rem;
        width: 100%;
    }
    
    .modal-dialog {
        margin: 0.5rem;
    }
    
    .producto-search-container {
        flex-direction: column;
    }
    
    .btn-nuevo-producto {
        margin-top: 0.5rem;
        width: 100%;
    }
}

/* Mejoras visuales para inputs y selects */
.form-control, .select2-container .select2-selection--single {
    border-radius: 0.375rem;
    border: 1px solid #ced4da;
    transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}

.form-control:focus, .select2-container--focus .select2-selection--single {
    border-color: #80bdff;
    outline: 0;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

/* Estilo para los botones */
.btn {
    border-radius: 0.375rem;
    font-weight: 500;
}

.btn-primary {
    background-color: #007bff;
    border-color: #007bff;
}

.btn-success {
    background-color: #28a745;
    border-color: #28a745;
}

/* Estilo para el total general */
#total-general {
    font-size: 1.1rem;
    padding: 0.5rem;
    border-radius: 0.375rem;
}

.producto-search-container {
    display: flex;
    gap: 0.5rem;
}

.select2-producto {
    flex: 1;
}
</style>

<!-- Content Header -->
<div class="content-header bg-light py-3">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h1 class="m-0 text-dark"><i class="fas fa-file-invoice mr-2"></i>Nueva Remisión</h1>
            </div>
            <div class="col-md-6">
                <ol class="breadcrumb justify-content-md-end mb-0">
                    <li class="breadcrumb-item"><a href="index.php">Inicio</a></li>
                    <li class="breadcrumb-item active">Nueva Remisión</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Main content -->
<div class="content py-4">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-white py-3">
                        <h3 class="card-title mb-0">
                            <i class="fas fa-file-invoice text-primary mr-2"></i> Formulario de Remisión
                        </h3>
                    </div>
                    <form id="formRemision">
                        <div class="card-body p-4">
                            <div class="row mb-4">
                                <div class="col-md-6 col-lg-4 mb-3">
                                    <div class="form-group">
                                        <label for="numero_remision" class="form-label">Número de Remisión</label>
                                        <input type="text" class="form-control form-control-lg" id="numero_remision" name="numero_remision" 
                                               value="<?php echo $siguiente_numero; ?>" readonly>
                                    </div>
                                </div>
                                <div class="col-md-6 col-lg-4 mb-3">
                                    <div class="form-group">
                                        <label for="fecha_emision" class="form-label">Fecha de Emisión *</label>
                                        <input type="date" class="form-control form-control-lg" id="fecha_emision" name="fecha_emision" 
                                               value="<?php echo date('Y-m-d'); ?>" required>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-lg-8 mb-3">
                                    <div class="form-group">
                                        <label for="cliente" class="form-label">Cliente *</label>
                                        <div class="input-group">
                                            <select class="form-control select2" id="cliente" name="id_cliente" required style="width: 100%;">
                                                <option value="">Seleccione un cliente...</option>
                                            </select>
                                            <div class="input-group-append">
                                                <button type="button" class="btn btn-success ml-2" data-toggle="modal" data-target="#modalCliente">
                                                    <i class="fas fa-plus mr-1"></i> Nuevo
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-4 mb-3">
                                    <div class="form-group">
                                        <label for="persona_contacto" class="form-label">Persona de Contacto</label>
                                        <div class="input-group">
                                            <select class="form-control" id="persona_contacto" name="id_persona" style="width: 100%;">
                                                <option value="">Seleccione...</option>
                                            </select>
                                            <div class="input-group-append">
                                                <button type="button" class="btn btn-success ml-2" onclick="abrirModalPersonaContacto()">
                                                    <i class="fas fa-plus mr-1"></i> Nueva
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group mb-4">
                                <label for="observaciones" class="form-label">Observaciones</label>
                                <textarea class="form-control" id="observaciones" name="observaciones" rows="3" 
                                          placeholder="Ingrese observaciones adicionales..."></textarea>
                            </div>

                            <hr class="my-4">

                            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-3">
                                <h5 class="mb-2 mb-md-0"><i class="fas fa-list mr-2"></i> Items de la Remisión</h5>
                                <button type="button" class="btn btn-success btn-agregar-item" onclick="agregarItem()">
                                    <i class="fas fa-plus mr-1"></i> Agregar Item
                                </button>
                            </div>

                            <div id="items-container" class="mb-4">
                                <!-- Los items se agregarán aquí dinámicamente -->
                            </div>
                            
                            <div class="total-general-container">
                                <div class="row align-items-center">
                                    <div class="col-md-8 text-md-right text-center mb-2 mb-md-0">
                                        <h5 class="font-weight-bold mb-0">TOTAL GENERAL:</h5>
                                    </div>
                                    <div class="col-md-4 text-center">
                                        <div class="alert alert-success py-2 font-weight-bold mb-0" id="total-general">$0.00</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card-footer bg-white py-3">
                            <div class="d-flex flex-column flex-md-row justify-content-end">
                                <button type="button" class="btn btn-secondary mb-2 mb-md-0 mr-md-2" onclick="limpiarFormulario()">
                                    <i class="fas fa-broom mr-1"></i> Limpiar
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save mr-1"></i> Guardar Remisión
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para crear cliente -->
<div class="modal fade" id="modalCliente" tabindex="-1" role="dialog" aria-labelledby="modalClienteLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h4 class="modal-title" id="modalClienteLabel">
                    <i class="fas fa-user-plus mr-2"></i> Nuevo Cliente
                </h4>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formCliente">
                <div class="modal-body p-4">
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <div class="form-group">
                                <label for="nombre_cliente" class="form-label">Nombre del Cliente *</label>
                                <input type="text" class="form-control" id="nombre_cliente" name="nombre_cliente" required>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="form-group">
                                <label for="tipo_cliente" class="form-label">Tipo *</label>
                                <select class="form-control" id="tipo_cliente" name="tipo_cliente" required>
                                    <option value="persona">Persona</option>
                                    <option value="empresa">Empresa</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="form-group">
                                <label for="nit" class="form-label">NIT/Cédula *</label>
                                <input type="text" class="form-control" id="nit" name="nit" required>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-group">
                                <label for="telefono_cliente" class="form-label">Teléfono</label>
                                <input type="text" class="form-control" id="telefono_cliente" name="telefono">
                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <label for="direccion" class="form-label">Dirección</label>
                        <input type="text" class="form-control" id="direccion" name="direccion">
                    </div>

                    <div class="form-group mb-3">
                        <label for="correo" class="form-label">Correo Electrónico</label>
                        <input type="email" class="form-control" id="correo" name="correo">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save mr-1"></i> Guardar Cliente
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para crear persona de contacto -->
<div class="modal fade" id="modalPersonaContacto" tabindex="-1" role="dialog" aria-labelledby="modalPersonaContactoLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h4 class="modal-title" id="modalPersonaContactoLabel">
                    <i class="fas fa-user-plus mr-2"></i> Nueva Persona de Contacto
                </h4>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formPersonaContacto">
                <div class="modal-body p-4">
                    <input type="hidden" id="cliente_persona_contacto" name="id_cliente">
                    
                    <div class="form-group mb-3">
                        <label for="nombre_persona" class="form-label">Nombre Completo *</label>
                        <input type="text" class="form-control" id="nombre_persona" name="nombre_persona" required>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="form-group">
                                <label for="cargo_persona" class="form-label">Cargo</label>
                                <input type="text" class="form-control" id="cargo_persona" name="cargo">
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-group">
                                <label for="telefono_persona" class="form-label">Teléfono</label>
                                <input type="text" class="form-control" id="telefono_persona" name="telefono">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group mb-3">
                        <label for="correo_persona" class="form-label">Correo Electrónico</label>
                        <input type="email" class="form-control" id="correo_persona" name="correo">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save mr-1"></i> Guardar Persona
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para crear producto -->
<div class="modal fade" id="modalProducto" tabindex="-1" role="dialog" aria-labelledby="modalProductoLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h4 class="modal-title" id="modalProductoLabel">
                    <i class="fas fa-box mr-2"></i> Nuevo Producto
                </h4>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formProducto">
                <div class="modal-body p-4">
                    <input type="hidden" id="item_index_producto" name="item_index">
                    
                    <div class="form-group mb-3">
                        <label for="nombre_producto" class="form-label">Nombre del Producto *</label>
                        <input type="text" class="form-control" id="nombre_producto" name="nombre_producto" required 
                               placeholder="Ingrese el nombre del producto">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save mr-1"></i> Guardar Producto
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
        width: '100%',
        dropdownParent: $('#formRemision'),
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
            // Establecer el cliente en el modal de persona de contacto
            $('#cliente_persona_contacto').val(clienteId);
        } else {
            $('#persona_contacto').empty().append('<option value="">Seleccione...</option>');
            $('#cliente_persona_contacto').val('');
        }
    });

    // Manejar envío del formulario de remisión
    $('#formRemision').on('submit', function(e) {
        e.preventDefault();
        
        const items = [];
        let itemsValidos = true;
        
        $('.item-row').each(function() {
            const productoId = $(this).find('.id-producto').val();
            const descripcion = $(this).find('.descripcion').val();
            const cantidad = $(this).find('.cantidad').val();
            const valorUnitario = $(this).find('.valor-unitario').val();
            
            if (!descripcion || !cantidad) {
                itemsValidos = false;
                return;
            }
            
            items.push({
                id_producto: productoId || null,
                descripcion: descripcion,
                cantidad: parseInt(cantidad),
                valor_unitario: parseFloat(valorUnitario) || 0
            });
        });

        if (!itemsValidos) {
            Swal.fire('Error', 'Todos los items deben tener al menos descripción y cantidad', 'error');
            return;
        }

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
                        cancelButtonText: 'Crear Nueva',
                        customClass: {
                            confirmButton: 'btn btn-primary',
                            cancelButton: 'btn btn-secondary'
                        },
                        buttonsStyling: false
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

    // Manejar envío del formulario de persona de contacto
    $('#formPersonaContacto').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: 'ajax/crear_persona_contacto.php',
            method: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
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
                console.error("Error al crear persona de contacto:", error);
                Swal.fire('Error', 'Error al crear la persona de contacto', 'error');
            }
        });
    });

    // Manejar envío del formulario de producto
    $('#formProducto').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: 'ajax/crear_producto.php',
            method: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#modalProducto').modal('hide');
                    $('#formProducto')[0].reset();
                    
                    const nuevoProducto = response.producto;
                    const itemIndex = $('#item_index_producto').val();
                    
                    // Agregar el nuevo producto al select2 correspondiente
                    if (itemIndex) {
                        const $select = $(`#producto-${itemIndex}`);
                        const newOption = new Option(nuevoProducto.nombre_producto, nuevoProducto.id_producto, true, true);
                        $select.append(newOption).trigger('change');
                        
                        // Actualizar el campo de descripción
                        $(`#descripcion-${itemIndex}`).val(nuevoProducto.nombre_producto);
                        $(`#id_producto-${itemIndex}`).val(nuevoProducto.id_producto);
                    }
                    
                    Swal.fire('¡Éxito!', 'Producto creado correctamente', 'success');
                } else {
                    Swal.fire('Error', response.message || 'Error al crear el producto', 'error');
                }
            },
            error: function(xhr, status, error) {
                console.error("Error al crear producto:", error);
                Swal.fire('Error', 'Error al crear el producto', 'error');
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
            <div class="row align-items-end">
                <div class="col-md-5 col-lg-6 mb-2">
                    <div class="form-group">
                        <label for="producto-${contadorItems}">Producto *</label>
                        <div class="producto-search-container">
                            <select class="form-control select2-producto" id="producto-${contadorItems}" 
                                    data-item-index="${contadorItems}" style="width: 100%;">
                                <option value="">Buscar producto...</option>
                            </select>
                            <button type="button" class="btn btn-outline-secondary btn-nuevo-producto" 
                                    onclick="abrirModalProducto(${contadorItems})" title="Nuevo producto">
                                <i class="fas fa-plus"></i> Nuevo
                            </button>
                        </div>
                        <input type="hidden" class="id-producto" id="id_producto-${contadorItems}" name="id_producto-${contadorItems}">
                        <input type="hidden" class="descripcion" id="descripcion-${contadorItems}" name="descripcion-${contadorItems}">
                    </div>
                </div>
                <div class="col-6 col-md-3 col-lg-2 mb-2">
                    <div class="form-group">
                        <label for="cantidad-${contadorItems}">Cantidad *</label>
                        <input type="number" class="form-control cantidad" id="cantidad-${contadorItems}" 
                               name="cantidad-${contadorItems}" min="1" value="1" required onchange="calcularTotalItem(${contadorItems})">
                    </div>
                </div>
                <div class="col-6 col-md-2 col-lg-2 mb-2">
                    <div class="form-group">
                        <label for="valor-unitario-${contadorItems}">Valor Unitario</label>
                        <input type="number" class="form-control valor-unitario" id="valor-unitario-${contadorItems}" 
                               name="valor-unitario-${contadorItems}" min="0" step="0.01" placeholder="0.00" onchange="calcularTotalItem(${contadorItems})">
                    </div>
                </div>
                <div class="col-6 col-md-2 col-lg-1 mb-2">
                    <div class="form-group">
                        <label for="total-item-${contadorItems}">Total</label>
                        <input type="text" class="form-control total-item text-center" id="total-item-${contadorItems}" 
                               readonly placeholder="$0.00">
                    </div>
                </div>
                <div class="col-6 col-md-1 col-lg-1 mb-2">
                    <div class="form-group">
                        <label class="d-block d-md-none">&nbsp;</label>
                        <button type="button" class="btn btn-danger btn-block" onclick="eliminarItem(${contadorItems})" title="Eliminar item">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    $('#items-container').append(itemHtml);
    
    // Inicializar Select2 para el producto
    $(`#producto-${contadorItems}`).select2({
        placeholder: 'Buscar producto...',
        allowClear: true,
        width: '100%',
        ajax: {
            url: 'ajax/buscar_productos.php',
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
    }).on('change', function() {
        const itemIndex = $(this).data('item-index');
        const selectedProduct = $(this).select2('data')[0];
        
        if (selectedProduct && selectedProduct.id) {
            $(`#id_producto-${itemIndex}`).val(selectedProduct.id);
            $(`#descripcion-${itemIndex}`).val(selectedProduct.text);
        } else {
            $(`#id_producto-${itemIndex}`).val('');
            $(`#descripcion-${itemIndex}`).val('');
        }
    });
    
    if (contadorItems === 1) {
        agregarFilaTotalGeneral();
    }
}

function calcularTotalItem(itemId) {
    const cantidad = parseFloat($(`#cantidad-${itemId}`).val()) || 0;
    const valorUnitario = parseFloat($(`#valor-unitario-${itemId}`).val()) || 0;
    const total = cantidad * valorUnitario;
    
    $(`#total-item-${itemId}`).val(total > 0 ? `$${total.toLocaleString('es-CO', {minimumFractionDigits: 2})}` : '$0.00');
    
    calcularTotalGeneral();
}

function calcularTotalGeneral() {
    let totalGeneral = 0;
    
    $('.item-row').each(function() {
        const cantidad = parseFloat($(this).find('.cantidad').val()) || 0;
        const valorUnitario = parseFloat($(this).find('.valor-unitario').val()) || 0;
        totalGeneral += cantidad * valorUnitario;
    });
    
    $('#total-general').text(totalGeneral > 0 ? `$${totalGeneral.toLocaleString('es-CO', {minimumFractionDigits: 2})}` : '$0.00');
}

function agregarFilaTotalGeneral() {
    // Ya está incluida en el HTML principal
}

function eliminarItem(id) {
    if ($('.item-row').length > 1) {
        $(`#item-${id}`).remove();
        calcularTotalGeneral();
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

function cargarPersonasContacto(clienteId) {
    $.ajax({
        url: 'ajax/obtener_personas_contacto.php',
        method: 'POST',
        data: { id_cliente: clienteId },
        dataType: 'json',
        success: function(personas) {
            $('#persona_contacto').empty().append('<option value="">Seleccione...</option>');
            
            if (Array.isArray(personas) && personas.length > 0) {
                personas.forEach(function(persona) {
                    $('#persona_contacto').append(`<option value="${persona.id_persona}">${persona.nombre_persona}</option>`);
                });
            }
        },
        error: function(xhr, status, error) {
            console.error("Error al cargar personas de contacto:", error);
            $('#persona_contacto').empty().append('<option value="">Seleccione...</option>');
        }
    });
}

function cargarSiguienteNumero() {
    $.ajax({
        url: 'ajax/obtener_siguiente_numero.php',
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                $('#numero_remision').val(response.siguiente_numero);
            } else {
                $('#numero_remision').val(1);
            }
        },
        error: function(xhr, status, error) {
            console.error("Error al obtener siguiente número:", error);
            $('#numero_remision').val(1);
        }
    });
}

function abrirModalPersonaContacto() {
    const clienteId = $('#cliente').val();
    if (!clienteId) {
        Swal.fire('Advertencia', 'Debe seleccionar un cliente primero', 'warning');
        return;
    }
    $('#cliente_persona_contacto').val(clienteId);
    $('#modalPersonaContacto').modal('show');
}

function abrirModalProducto(itemIndex) {
    $('#item_index_producto').val(itemIndex);
    $('#modalProducto').modal('show');
}
</script>

<?php include 'views/layout/footer.php'; ?>