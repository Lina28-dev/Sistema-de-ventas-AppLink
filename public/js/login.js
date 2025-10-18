window.addEventListener('load', function() {
    // Inicializar el modal
    var loginModal = new bootstrap.Modal(document.getElementById('loginModal'));

    // Manejar el botón de inicio de sesión en la barra de navegación
    document.getElementById('loginBtn').onclick = function(e) {
        e.preventDefault();
        loginModal.show();
    };

    // Manejar el botón "Comenzar Ahora"
    document.getElementById('startBtn').onclick = function(e) {
        e.preventDefault();
        loginModal.show();
    };

    // Validación del formulario
    var forms = document.querySelectorAll('.needs-validation');
    Array.prototype.slice.call(forms).forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });
});