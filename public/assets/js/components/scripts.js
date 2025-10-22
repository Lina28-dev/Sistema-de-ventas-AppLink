
// Variables globales
var subtotal = 0;

// ==============================================
// FUNCIONES BÁSICAS DE MANIPULACIÓN DEL DOM
// ==============================================

/**
 * Alterna la visibilidad de un elemento
 * @param {HTMLElement} e - Elemento a mostrar/ocultar
 * @param {number} time - Tiempo de retardo en ms (opcional)
 */
function toggle(e, time = 0) {
    let element = window.getComputedStyle(e);
    let elementVisibility = element.getPropertyValue('display');
    
    if (elementVisibility === 'none' || e.style.display === 'none') {
        mostrar(e, time);
    } else {
        ocultar(e, time);
    }
    
    function ocultar(elemento, tiempo) {
        setTimeout(() => elemento.style.display = "none", tiempo);
    }
    
    function mostrar(elemento, tiempo) {
        setTimeout(() => elemento.style.display = "block", tiempo);
    }
}

/**
 * Realiza peticiones AJAX a la base de datos
 * @param {string} peticion - Tipo de petición (GET/POST)
 * @param {string} url - URL del endpoint
 * @param {HTMLElement|null} elemento - Elemento donde insertar la respuesta (opcional)
 */
function consultarDb(peticion, url, elemento = null) {
    var conexion_ajax = new XMLHttpRequest();
    conexion_ajax.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            if (elemento) {
                elemento.innerHTML = this.responseText;
            }
            // Puedes agregar aquí manejo adicional de la respuesta
        }
    };
    conexion_ajax.open(peticion, url, true);
    conexion_ajax.send();
}

// ==============================================
// FUNCIONES ESPECÍFICAS PARA CLIENTES
// ==============================================

// Muestra el formulario para nuevo cliente
function mostrarFormularioCliente() {
    const formulario = document.getElementById('ingresar_cliente');
    toggle(formulario);
    document.getElementById('formulario-cliente').reset();
    document.getElementById('campo-descuento').style.display = 'none';
    window.scrollTo({
        top: formulario.offsetTop,
        behavior: 'smooth'
    });
}

// Oculta el formulario de cliente
function ocultarFormulario() {
    toggle(document.getElementById('ingresar_cliente'));
}

// Valida el formulario antes de enviar
function validarFormularioCliente() {
    // Validación de identificación
    const tipoID = document.getElementById('tipo_identificacion').value;
    const numeroID = document.getElementById('numero_identificacion').value;
    
    if (!/^\d+$/.test(numeroID)) {
        mostrarError('El número de identificación solo puede contener dígitos');
        return false;
    }

    const validaciones = {
        'CC': { min: 8, max: 10, msg: 'La cédula debe tener 8 o 10 dígitos' },
        'CE': { min: 6, max: 20, msg: 'Mínimo 6 dígitos para cédula extranjería' },
        'TI': { min: 6, max: 12, msg: 'Tarjeta de identidad: 6-12 dígitos' },
        'PA': { min: 6, max: 20, msg: 'Pasaporte: mínimo 6 caracteres' },
        'NIT': { min: 9, max: 15, msg: 'NIT: 9-15 dígitos' }
    };

    if (tipoID && validaciones[tipoID]) {
        const { min, max, msg } = validaciones[tipoID];
        if (numeroID.length < min || numeroID.length > max) {
            mostrarError(msg);
            return false;
        }
    }

    // Validar teléfono
    const telefono = document.getElementById('telefono').value;
    if (!/^\d{7,15}$/.test(telefono)) {
        mostrarError('El teléfono debe contener entre 7 y 15 dígitos');
        return false;
    }

    // Validar email si se proporciona
    const email = document.getElementById('email').value;
    if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        mostrarError('Por favor ingrese un email válido');
        return false;
    }

    return true;
}

// Muestra mensajes de error
function mostrarError(mensaje) {
    const errorContainer = document.getElementById('error-message');
    if (!errorContainer) {
        // Crear contenedor si no existe
        const div = document.createElement('div');
        div.id = 'error-message';
        div.className = 'alert alert-danger';
        div.textContent = mensaje;
        document.querySelector('.container').prepend(div);
        setTimeout(() => div.remove(), 5000);
    } else {
        errorContainer.textContent = mensaje;
    }
}

// ==============================================
// FUNCIONES DE BÚSQUEDA Y FILTRADO
// ==============================================

// Busca clientes según el criterio seleccionado
function buscarClientes(tipo) {
    const busqueda = document.getElementById('busqueda-cliente').value.trim();
    const tablaBody = document.querySelector('#tabla-clientes tbody');
    
    if (busqueda.length === 0 && tipo !== 'todos') {
        mostrarError('Ingrese un término de búsqueda');
        return;
    }

    consultarDb(
        'GET',
        `buscar_clientes.php?tipo=${tipo}&q=${encodeURIComponent(busqueda)}`,
        tablaBody
    );
}

// ==============================================
// FUNCIONES PARA MANEJO DE EVENTOS
// ==============================================

// Configura los event listeners cuando el DOM está listo
document.addEventListener('DOMContentLoaded', function() {
    // Mostrar/ocultar campo de descuento para revendedores
    document.querySelectorAll('input[name="revendedora"]').forEach(radio => {
        radio.addEventListener('change', function() {
            document.getElementById('campo-descuento').style.display = 
                this.value === '1' ? 'block' : 'none';
        });
    }); 

    // Configurar botones de búsqueda
    document.querySelectorAll('.botones-busqueda button').forEach(btn => {
        btn.addEventListener('click', function() {
            const tipo = this.getAttribute('onclick').match(/'(.*?)'/)[1];
            buscarClientes(tipo);
        });
    });
});

// Muestra mensajes de éxito
function mostrarMensajeExito(mensaje) {
    const successContainer = document.getElementById('success-message');
    if (!successContainer) {
        const div = document.createElement('div');
        div.id = 'success-message';
        div.className = 'alert alert-success';
        div.textContent = mensaje;
        document.querySelector('.container').prepend(div);
        setTimeout(() => div.remove(), 5000);
    }
}

// ==============================================
// FUNCIONES ADICIONALES PARA CLIENTES
// ==============================================

// Prepara el formulario para editar un cliente
function editarCliente(id) {
    consultarDb(
        'GET',
        `obtener_cliente.php?id=${id}`,
        document.getElementById('formulario-cliente')
    );
    mostrarFormularioCliente();
    document.getElementById('formulario-cliente').scrollIntoView({
        behavior: 'smooth'
    });
}

// Muestra el historial de compras de un cliente
function verHistorial(id) {
    const historialContainer = document.getElementById('historial-container');
    if (!historialContainer) {
        const div = document.createElement('div');
        div.id = 'historial-container';
        document.body.appendChild(div);
    }
    
    consultarDb(
        'GET',
        `historial_compras.php?cliente_id=${id}`,
        document.getElementById('historial-container')
    );
    toggle(document.getElementById('historial-container'));
}