// Mostrar/ocultar formulario
function mostrarFormulario() {
    const form = document.getElementById('ingresar_cliente');
    form.style.display = form.style.display === 'none' ? 'block' : 'none';
}

// Validación del formulario
document.getElementById('form-cliente').addEventListener('submit', function(e) {
    const cc = document.getElementById('numero_identificacion').value;
    
    if (!/^\d{6,12}$/.test(cc)) {
        e.preventDefault();
        alert('Número de identificación inválido');
    }
    
    // Otras validaciones...
});

// Cargar mensajes de éxito/error
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    
    if (urlParams.has('exito')) {
        alert('Cliente registrado con éxito');
    }
    
    if (urlParams.has('error')) {
        alert('Error al registrar cliente');
    }
});