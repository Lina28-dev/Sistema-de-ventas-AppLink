// Sistema de Ventas - JavaScript Principal
// Variables globales
let carrito = [];
let ventas = [];
let clienteSeleccionado = null;
let productosCache = [];

// Configuración
const API_BASE_URL = '/Sistema-de-ventas-AppLink-main/src/Controllers';

// Inicialización
window.addEventListener('DOMContentLoaded', function() {
    inicializarSistema();
    configurarEventos();
    cargarEstadisticas();
    // Cargar productos disponibles automáticamente después de 1 segundo
    setTimeout(function() {
        cargarProductosDisponibles();
    }, 1000);
});

function inicializarSistema() {
    // Toggle sidebar en móviles
    const mobileToggle = document.getElementById('mobileToggle');
    const sidebar = document.querySelector('.sidebar');
    
    if (mobileToggle && sidebar) {
        mobileToggle.addEventListener('click', function() {
            sidebar.classList.toggle('show');
        });
        
        document.addEventListener('click', function(e) {
            if (window.innerWidth <= 768) {
                if (!sidebar.contains(e.target) && !mobileToggle.contains(e.target)) {
                    sidebar.classList.remove('show');
                }
            }
        });
    }
    
    // Inicializar tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.forEach(function (tooltipTriggerEl) {
        new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Confirmación logout
    var logoutLink = document.querySelector('a[href$="logout"]');
    if (logoutLink) {
        logoutLink.addEventListener('click', function(e) {
            if (!confirm('¿Seguro que deseas cerrar sesión?')) {
                e.preventDefault();
            }
        });
    }
}

function configurarEventos() {
    // Evento para método de pago
    const metodoPago = document.getElementById('metodoPago');
    if (metodoPago) {
        metodoPago.addEventListener('change', function() {
            const campoEfectivo = document.getElementById('campoEfectivo');
            if (campoEfectivo) {
                if (this.value === 'efectivo') {
                    campoEfectivo.style.display = 'block';
                } else {
                    campoEfectivo.style.display = 'none';
                    const efectivoRecibido = document.getElementById('efectivoRecibido');
                    const cambioTexto = document.getElementById('cambioTexto');
                    if (efectivoRecibido) efectivoRecibido.value = '';
                    if (cambioTexto) cambioTexto.textContent = '';
                }
            }
        });
    }
}

// ===== FUNCIONES DE PRODUCTOS =====
async function buscarProducto() {
    const termino = document.getElementById('buscarProducto').value.trim();
    const container = document.getElementById('productosLista');
    
    if (!termino) {
        mostrarMensajeEstado('Ingresa un término de búsqueda', 'warning');
        return;
    }
    
    // Mostrar estado de carga
    container.innerHTML = `
        <div class="col-12 text-center text-muted py-4">
            <i class="fas fa-spinner fa-spin fa-3x mb-3"></i>
            <p>Buscando productos...</p>
            <small>Consultando base de datos</small>
        </div>
    `;
    
    try {
        const response = await fetch(`${API_BASE_URL}/ProductoController.php?accion=buscar&termino=${encodeURIComponent(termino)}&limite=20`);
        const data = await response.json();
        
        if (data.success && data.data.length > 0) {
            mostrarProductos(data.data);
            mostrarMensajeEstado(`Se encontraron ${data.total} productos`, 'success');
        } else {
            container.innerHTML = `
                <div class="col-12 text-center text-muted py-4">
                    <i class="fas fa-search fa-3x mb-3"></i>
                    <p>No se encontraron productos</p>
                    <small>Intenta con otro término de búsqueda</small>
                </div>
            `;
            mostrarMensajeEstado('No se encontraron productos', 'info');
        }
    } catch (error) {
        console.error('Error al buscar productos:', error);
        container.innerHTML = `
            <div class="col-12 text-center text-danger py-4">
                <i class="fas fa-exclamation-triangle fa-3x mb-3"></i>
                <p>Error al buscar productos</p>
                <small>Verifica la conexión con el servidor</small>
            </div>
        `;
        mostrarToast('Error al buscar productos', 'danger');
    }
}

async function cargarTodosProductos() {
    const container = document.getElementById('productosLista');
    
    container.innerHTML = `
        <div class="col-12 text-center text-muted py-4">
            <i class="fas fa-spinner fa-spin fa-3x mb-3"></i>
            <p>Cargando productos...</p>
        </div>
    `;
    
    try {
        const response = await fetch(`${API_BASE_URL}/ProductoController.php?accion=listar&limite=50`);
        const data = await response.json();
        
        if (data.success && data.data.length > 0) {
            mostrarProductos(data.data);
            mostrarMensajeEstado(`Mostrando ${data.data.length} productos`, 'success');
        } else {
            container.innerHTML = `
                <div class="col-12 text-center text-muted py-4">
                    <i class="fas fa-box-open fa-3x mb-3"></i>
                    <p>No hay productos disponibles</p>
                    <small>Agrega productos al inventario</small>
                </div>
            `;
        }
    } catch (error) {
        console.error('Error al cargar productos:', error);
        mostrarToast('Error al cargar productos', 'danger');
    }
}

function mostrarProductos(productos) {
    const container = document.getElementById('productosLista');
    
    const html = productos.map(producto => {
        const stockBadgeClass = producto.stock > 10 ? 'bg-success' : producto.stock > 0 ? 'bg-warning' : 'bg-danger';
        const stockText = producto.stock > 0 ? 'Stock: ' + producto.stock : 'Sin stock';
        const isDisabled = producto.stock <= 0;
        const onclickAttr = !isDisabled ? 'onclick="agregarAlCarrito(' + producto.id + ', \'' + producto.nombre.replace(/'/g, "\\'") + '\', ' + producto.precio + ', ' + producto.stock + ')"' : '';
        
        let imagenHtml = '<div class="product-placeholder"><i class="fas fa-tshirt fa-3x text-muted"></i></div>';
        if (producto.imagen) {
            imagenHtml = '<img src="' + producto.imagen + '" alt="' + producto.nombre + '" class="product-image">';
        }
        
        let detallesHtml = '';
        if (producto.color || producto.talle) {
            detallesHtml = '<div class="row text-center mb-2">';
            if (producto.color) {
                detallesHtml += '<div class="col-6"><small class="text-muted"><i class="fas fa-palette"></i> ' + producto.color + '</small></div>';
            }
            if (producto.talle) {
                detallesHtml += '<div class="col-6"><small class="text-muted"><i class="fas fa-ruler"></i> ' + producto.talle + '</small></div>';
            }
            detallesHtml += '</div>';
        }
        
        let botonAgregar = '';
        if (!isDisabled) {
            botonAgregar = '<div class="position-absolute" style="bottom: 15px; right: 15px;"><div class="btn btn-success btn-sm rounded-circle" style="width: 35px; height: 35px; display: flex; align-items: center; justify-content: center;"><i class="fas fa-plus"></i></div></div>';
        }
        
        return '<div class="col-md-4 col-sm-6 mb-3">' +
            '<div class="card product-card h-100 ' + (isDisabled ? 'opacity-75' : '') + '" ' + onclickAttr + '>' +
                '<div class="card-body text-center p-3 position-relative">' +
                    '<span class="badge ' + stockBadgeClass + ' stock-badge">' + stockText + '</span>' +
                    imagenHtml +
                    '<div class="mb-2">' +
                        '<span class="badge bg-secondary">' + producto.codigo + '</span>' +
                        (producto.categoria ? '<span class="badge bg-info ms-1">' + producto.categoria + '</span>' : '') +
                    '</div>' +
                    '<h6 class="card-title fw-bold">' + producto.nombre + '</h6>' +
                    (producto.descripcion ? '<p class="card-text small text-muted mb-2">' + producto.descripcion + '</p>' : '') +
                    detallesHtml +
                    '<div class="price-section text-center">' +
                        '<h5 class="mb-1">$' + formatearPrecio(producto.precio) + '</h5>' +
                        '<small class="opacity-75">' + (isDisabled ? 'No disponible' : 'Clic para agregar') + '</small>' +
                    '</div>' +
                    botonAgregar +
                '</div>' +
            '</div>' +
        '</div>';
    }).join('');
    
    container.innerHTML = html;
}

// Función para cargar productos disponibles
async function cargarProductosDisponibles() {
    const container = document.getElementById('productosLista');
    
    // Mostrar estado de carga
    container.innerHTML = '<div class="col-12 text-center text-muted py-4">' +
        '<i class="fas fa-spinner fa-spin fa-3x mb-3"></i>' +
        '<p>Cargando productos disponibles...</p>' +
        '<small>Consultando inventario</small>' +
        '</div>';
    
    try {
        const response = await fetch(API_BASE_URL + '/ProductoController.php?accion=listar&disponibles=1&limite=50');
        const data = await response.json();
        
        if (data.success && data.data.length > 0) {
            mostrarProductos(data.data);
            mostrarMensajeEstado(`Se encontraron ${data.data.length} productos disponibles`, 'success');
        } else {
            container.innerHTML = '<div class="col-12 text-center text-muted py-4">' +
                '<i class="fas fa-box-open fa-3x mb-3"></i>' +
                '<p>No hay productos disponibles</p>' +
                '<small>Revisa el inventario o contacta al administrador</small>' +
                '</div>';
            mostrarMensajeEstado('No se encontraron productos disponibles', 'warning');
        }
    } catch (error) {
        console.error('Error al cargar productos:', error);
        container.innerHTML = '<div class="col-12 text-center text-danger py-4">' +
            '<i class="fas fa-exclamation-triangle fa-3x mb-3"></i>' +
            '<p>Error al cargar productos</p>' +
            '<small>Verifica tu conexión e intenta nuevamente</small>' +
            '<div class="mt-3">' +
                '<button class="btn btn-outline-danger" onclick="cargarProductosDisponibles()">' +
                    '<i class="fas fa-redo"></i> Reintentar' +
                '</button>' +
            '</div>' +
            '</div>';
        mostrarError('Error al cargar productos disponibles');
    }
}

// ===== FUNCIONES DE CLIENTES =====
async function buscarCliente() {
    const termino = document.getElementById('buscarCliente').value.trim();
    const resultados = document.getElementById('resultadosCliente');
    
    if (!termino) {
        if (resultados) resultados.style.display = 'none';
        return;
    }
    
    try {
        const response = await fetch(`${API_BASE_URL}/ClienteControllerAPI.php?accion=buscar&termino=${encodeURIComponent(termino)}&limite=5`);
        const data = await response.json();
        
        if (data.success && data.data.length > 0) {
            const html = data.data.map(cliente => {
                const nombreCompleto = cliente.nombres && cliente.apellidos 
                    ? cliente.nombres + ' ' + cliente.apellidos 
                    : cliente.nombre;
                return `
                <div class="list-group-item list-group-item-action" onclick="seleccionarCliente(${JSON.stringify(cliente).replace(/"/g, '&quot;')})">
                    <div class="d-flex justify-content-between">
                        <h6 class="mb-1">${nombreCompleto}</h6>
                        <small class="text-success">${cliente.descuento}% desc.</small>
                    </div>
                    <p class="mb-1 small">${cliente.identificacion} | ${cliente.telefono || 'Sin teléfono'}</p>
                    <small class="text-muted">${cliente.ciudad || 'Sin ciudad'}</small>
                </div>
                `;
            }).join('');
            
            if (resultados) {
                resultados.innerHTML = `<div class="list-group">${html}</div>`;
                resultados.style.display = 'block';
            }
        } else {
            if (resultados) {
                resultados.innerHTML = '<div class="alert alert-info">No se encontraron clientes</div>';
                resultados.style.display = 'block';
            }
        }
    } catch (error) {
        console.error('Error al buscar cliente:', error);
        mostrarToast('Error al buscar cliente', 'danger');
    }
}

function seleccionarCliente(cliente) {
    clienteSeleccionado = cliente;
    const clienteNombre = document.getElementById('clienteNombre');
    const clienteDetalles = document.getElementById('clienteDetalles');
    const buscarCliente = document.getElementById('buscarCliente');
    const resultados = document.getElementById('resultadosCliente');
    
    const nombreCompleto = cliente.nombres && cliente.apellidos 
        ? cliente.nombres + ' ' + cliente.apellidos 
        : cliente.nombre;
    
    if (clienteNombre) clienteNombre.textContent = nombreCompleto;
    if (clienteDetalles) clienteDetalles.textContent = `${cliente.identificacion} | Descuento: ${cliente.descuento}%`;
    if (buscarCliente) buscarCliente.value = nombreCompleto;
    if (resultados) resultados.style.display = 'none';
    
    // Actualizar totales si hay productos en el carrito
    if (carrito.length > 0) {
        calcularTotal();
    }
}

function limpiarCliente() {
    clienteSeleccionado = null;
    const clienteNombre = document.getElementById('clienteNombre');
    const clienteDetalles = document.getElementById('clienteDetalles');
    const buscarCliente = document.getElementById('buscarCliente');
    const resultados = document.getElementById('resultadosCliente');
    
    if (clienteNombre) clienteNombre.textContent = 'Cliente General';
    if (clienteDetalles) clienteDetalles.textContent = 'Sin descuentos especiales';
    if (buscarCliente) buscarCliente.value = '';
    if (resultados) resultados.style.display = 'none';
    
    if (carrito.length > 0) {
        calcularTotal();
    }
}

// ===== FUNCIONES DEL CARRITO =====
function agregarAlCarrito(id, nombre, precio, stock) {
    if (stock <= 0) {
        mostrarToast('Producto sin stock disponible', 'warning');
        return;
    }
    
    const itemExistente = carrito.find(item => item.id === id);
    if (itemExistente) {
        if (itemExistente.cantidad < stock) {
            itemExistente.cantidad++;
            mostrarToast(`${nombre} - Cantidad actualizada`, 'info');
        } else {
            mostrarToast('No hay suficiente stock disponible', 'warning');
            return;
        }
    } else {
        carrito.push({ id, nombre, precio, cantidad: 1, stock });
        mostrarToast(`${nombre} agregado al carrito`, 'success');
    }
    
    actualizarCarrito();
}

function actualizarCarrito() {
    const container = document.getElementById("carritoItems");
    const btnFinalizar = document.getElementById("btnFinalizar");
    const btnLimpiarCarrito = document.getElementById("btnLimpiarCarrito");
    
    if (carrito.length === 0) {
        container.innerHTML = `
            <div class="text-center text-muted py-4">
                <i class="fas fa-shopping-cart fa-3x mb-2"></i>
                <p>El carrito está vacío</p>
                <small>Agrega productos para comenzar la venta</small>
            </div>
        `;
        if (btnFinalizar) btnFinalizar.disabled = true;
        if (btnLimpiarCarrito) btnLimpiarCarrito.style.display = 'none';
    } else {
        let html = '';
        carrito.forEach(item => {
            const subtotal = item.precio * item.cantidad;
            html += `
                <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                    <div class="flex-grow-1">
                        <div class="fw-bold">${item.nombre}</div>
                        <small class="text-muted">$${formatearPrecio(item.precio)} × ${item.cantidad}</small>
                    </div>
                    <div class="text-end">
                        <div class="btn-group btn-group-sm mb-1">
                            <button class="btn btn-outline-secondary" onclick="cambiarCantidad(${item.id}, -1)" title="Quitar uno">
                                <i class="fas fa-minus"></i>
                            </button>
                            <button class="btn btn-outline-secondary" disabled>${item.cantidad}</button>
                            <button class="btn btn-outline-secondary" onclick="cambiarCantidad(${item.id}, 1)" title="Agregar uno" ${item.cantidad >= item.stock ? 'disabled' : ''}>
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                        <div>
                            <strong>$${formatearPrecio(subtotal)}</strong>
                            <button class="btn btn-sm btn-outline-danger ms-1" onclick="eliminarDelCarrito(${item.id})" title="Eliminar">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;
        });
        container.innerHTML = html;
        if (btnFinalizar) btnFinalizar.disabled = false;
        if (btnLimpiarCarrito) btnLimpiarCarrito.style.display = 'inline-block';
    }
    
    calcularTotal();
}

function cambiarCantidad(id, cambio) {
    const item = carrito.find(i => i.id === id);
    if (item) {
        const nuevaCantidad = item.cantidad + cambio;
        if (nuevaCantidad <= 0) {
            eliminarDelCarrito(id);
        } else if (nuevaCantidad > item.stock) {
            mostrarToast('No hay suficiente stock disponible', 'warning');
        } else {
            item.cantidad = nuevaCantidad;
            actualizarCarrito();
        }
    }
}

function eliminarDelCarrito(id) {
    const item = carrito.find(i => i.id === id);
    if (item && confirm(`¿Eliminar ${item.nombre} del carrito?`)) {
        carrito = carrito.filter(item => item.id !== id);
        actualizarCarrito();
        mostrarToast('Producto eliminado del carrito', 'info');
    }
}

function limpiarCarrito() {
    if (carrito.length > 0 && confirm('¿Limpiar todo el carrito?')) {
        carrito = [];
        actualizarCarrito();
        mostrarToast('Carrito limpiado', 'info');
    }
}

function calcularTotal() {
    const subtotal = carrito.reduce((sum, item) => sum + (item.precio * item.cantidad), 0);
    let descuento = 0;
    let total = subtotal;
    
    // Aplicar descuento del cliente
    if (clienteSeleccionado && clienteSeleccionado.descuento > 0) {
        descuento = subtotal * (clienteSeleccionado.descuento / 100);
        total = subtotal - descuento;
        
        const descuentoRow = document.getElementById('descuentoRow');
        const porcentajeDescuento = document.getElementById('porcentajeDescuento');
        const montoDescuento = document.getElementById('montoDescuento');
        
        if (descuentoRow) descuentoRow.style.display = 'flex';
        if (porcentajeDescuento) porcentajeDescuento.textContent = clienteSeleccionado.descuento;
        if (montoDescuento) montoDescuento.textContent = '$' + formatearPrecio(descuento);
    } else {
        const descuentoRow = document.getElementById('descuentoRow');
        if (descuentoRow) descuentoRow.style.display = 'none';
    }
    
    const subtotalEl = document.getElementById("subtotal");
    const totalEl = document.getElementById("total");
    const cantidadItemsEl = document.getElementById("cantidadItems");
    
    if (subtotalEl) subtotalEl.textContent = "$" + formatearPrecio(subtotal);
    if (totalEl) totalEl.textContent = "$" + formatearPrecio(total);
    if (cantidadItemsEl) cantidadItemsEl.textContent = carrito.reduce((sum, item) => sum + item.cantidad, 0);
}

function calcularCambio() {
    const efectivoRecibidoEl = document.getElementById('efectivoRecibido');
    const cambioTextoEl = document.getElementById('cambioTexto');
    
    if (!efectivoRecibidoEl || !cambioTextoEl) return;
    
    const efectivoRecibido = parseFloat(efectivoRecibidoEl.value) || 0;
    const total = carrito.reduce((sum, item) => sum + (item.precio * item.cantidad), 0);
    const descuento = clienteSeleccionado ? total * (clienteSeleccionado.descuento / 100) : 0;
    const totalFinal = total - descuento;
    
    const cambio = efectivoRecibido - totalFinal;
    
    if (efectivoRecibido > 0) {
        if (cambio >= 0) {
            cambioTextoEl.textContent = `Cambio: $${formatearPrecio(cambio)}`;
            cambioTextoEl.className = 'text-success';
        } else {
            cambioTextoEl.textContent = `Falta: $${formatearPrecio(Math.abs(cambio))}`;
            cambioTextoEl.className = 'text-danger';
        }
    } else {
        cambioTextoEl.textContent = '';
    }
}

// ===== FUNCIONES DE VENTA =====
async function finalizarVenta() {
    if (carrito.length === 0) {
        mostrarToast('El carrito está vacío', 'warning');
        return;
    }
    
    const metodoPago = document.getElementById("metodoPago").value;
    const subtotal = carrito.reduce((sum, item) => sum + (item.precio * item.cantidad), 0);
    const descuento = clienteSeleccionado ? subtotal * (clienteSeleccionado.descuento / 100) : 0;
    const total = subtotal - descuento;
    
    // Validar efectivo recibido si es necesario
    if (metodoPago === 'efectivo') {
        const efectivoRecibidoEl = document.getElementById('efectivoRecibido');
        const efectivoRecibido = parseFloat(efectivoRecibidoEl ? efectivoRecibidoEl.value : '0') || 0;
        if (efectivoRecibido < total) {
            mostrarToast('El efectivo recibido es insuficiente', 'warning');
            return;
        }
    }
    
    const ventaData = {
        cliente_id: clienteSeleccionado ? clienteSeleccionado.id : null,
        cliente_nombre: clienteSeleccionado ? clienteSeleccionado.nombre : 'Cliente General',
        items: carrito,
        subtotal: subtotal,
        descuento: descuento,
        total: total,
        metodo_pago: metodoPago,
        fecha: new Date().toISOString()
    };
    
    try {
        // Mostrar loading
        const btnFinalizar = document.getElementById('btnFinalizar');
        const originalText = btnFinalizar.innerHTML;
        btnFinalizar.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Procesando...';
        btnFinalizar.disabled = true;
        
        // Simular venta (aquí se enviaría a VentaControllerDashboard.php)
        const venta = {
            id: ventas.length + 1,
            ...ventaData,
            fecha: new Date().toLocaleString()
        };
        
        ventas.push(venta);
        
        // Mostrar confirmación
        mostrarToast('¡Venta procesada exitosamente!', 'success');
        
        // Limpiar formulario
        carrito = [];
        limpiarCliente();
        actualizarCarrito();
        cargarEstadisticas();
        
        // Resetear método de pago
        const metodoPagoEl = document.getElementById('metodoPago');
        const campoEfectivo = document.getElementById('campoEfectivo');
        const efectivoRecibido = document.getElementById('efectivoRecibido');
        const cambioTexto = document.getElementById('cambioTexto');
        
        if (metodoPagoEl) metodoPagoEl.value = 'efectivo';
        if (campoEfectivo) campoEfectivo.style.display = 'none';
        if (efectivoRecibido) efectivoRecibido.value = '';
        if (cambioTexto) cambioTexto.textContent = '';
        
        // Restaurar botón
        btnFinalizar.innerHTML = originalText;
        btnFinalizar.disabled = false;
        
        // Focus en búsqueda de productos
        const buscarProducto = document.getElementById('buscarProducto');
        if (buscarProducto) {
            buscarProducto.value = '';
            buscarProducto.focus();
        }
        
    } catch (error) {
        console.error('Error al procesar venta:', error);
        mostrarToast('Error al procesar la venta', 'danger');
        
        // Restaurar botón
        const btnFinalizar = document.getElementById('btnFinalizar');
        if (btnFinalizar) {
            btnFinalizar.innerHTML = '<i class="fas fa-check-circle"></i> Finalizar Venta';
            btnFinalizar.disabled = false;
        }
    }
}

// ===== FUNCIONES DE ESTADÍSTICAS =====
function cargarEstadisticas() {
    const hoy = new Date().toDateString();
    const totalHoy = ventas.filter(v => {
        return new Date(v.fecha).toDateString() === hoy;
    }).reduce((sum, v) => sum + v.total, 0);
    
    const totalMes = ventas.reduce((sum, v) => sum + v.total, 0);
    const transacciones = ventas.length;
    const promedio = transacciones > 0 ? totalMes / transacciones : 0;
    
    const ventasHoyEl = document.getElementById("ventasHoy");
    const ventasMesEl = document.getElementById("ventasMes");
    const transaccionesEl = document.getElementById("transacciones");
    const ticketPromedioEl = document.getElementById("ticketPromedio");
    
    if (ventasHoyEl) ventasHoyEl.textContent = "$" + formatearPrecio(totalHoy);
    if (ventasMesEl) ventasMesEl.textContent = "$" + formatearPrecio(totalMes);
    if (transaccionesEl) transaccionesEl.textContent = transacciones;
    if (ticketPromedioEl) ticketPromedioEl.textContent = "$" + formatearPrecio(promedio);
}

// ===== FUNCIONES AUXILIARES =====
function formatearPrecio(precio) {
    return new Intl.NumberFormat('es-CO').format(precio);
}

function mostrarMensajeEstado(mensaje, tipo) {
    const estadoBusqueda = document.getElementById('estadoBusqueda');
    const mensajeEstado = document.getElementById('mensajeEstado');
    
    if (mensajeEstado) mensajeEstado.textContent = mensaje;
    if (estadoBusqueda) {
        estadoBusqueda.className = `alert alert-${tipo} d-block`;
        
        setTimeout(() => {
            estadoBusqueda.classList.add('d-none');
        }, 3000);
    }
}

function mostrarToast(mensaje, tipo = 'success') {
    const toastEl = tipo === 'danger' ? document.getElementById('errorToast') : document.getElementById('ventasToast');
    
    if (toastEl) {
        if (tipo === 'danger') {
            const errorMessage = document.getElementById('errorMessage');
            if (errorMessage) errorMessage.textContent = mensaje;
        } else {
            const toastBody = toastEl.querySelector('.toast-body');
            if (toastBody) toastBody.textContent = mensaje;
        }
        
        const toast = new bootstrap.Toast(toastEl);
        toast.show();
    }
}

// Filtrar por categoría
function filtrarPorCategoria() {
    const filtroCategoria = document.getElementById('filtroCategoria');
    const buscarProducto = document.getElementById('buscarProducto');
    
    if (filtroCategoria && buscarProducto) {
        const categoria = filtroCategoria.value;
        if (categoria) {
            buscarProducto.value = categoria;
            buscarProducto();
        } else {
            cargarTodosProductos();
        }
    }
}