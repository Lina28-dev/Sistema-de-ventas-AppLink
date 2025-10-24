// Login Form Handler - AppLink Sistema de Ventas
document.addEventListener('DOMContentLoaded', function() {
    console.log('Inicializando sistema de login...');
    
    // Variables globales
    const loginAlert = document.getElementById('loginAlert');
    const loginForm = document.getElementById('loginFormModal');
    
    if (!loginForm) {
        console.error('Formulario de login no encontrado!');
        return;
    }
    
    // Inicializar Tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.forEach(function (tooltipTriggerEl) {
        new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Mostrar el modal si hay un error al cargar la página desde PHP
    if (loginAlert && loginAlert.style.display === 'block') {
        var loginModal = new bootstrap.Modal(document.getElementById('loginModal'));
        loginModal.show();
    }
    
    // MANEJO DEL FORMULARIO DE LOGIN
    loginForm.addEventListener('submit', function(e) {
        e.preventDefault(); // CRÍTICO: Prevenir envío normal del formulario
        console.log('Formulario enviado via AJAX');
        
        // Validación personalizada del rol
        const rolSelect = document.getElementById('rol');
        const nick = document.getElementById('nick').value.trim();
        const password = document.getElementById('password').value.trim();
        
        console.log('Datos del formulario:', { nick, password, rol: rolSelect.value });
        
        // Validar campos requeridos
        if (!nick || !password || !rolSelect.value) {
            this.classList.add('was-validated');
            if (!rolSelect.value) {
                rolSelect.setCustomValidity('Debe seleccionar un tipo de usuario');
            }
            console.log('Validación fallida: campos vacíos');
            return;
        } else {
            rolSelect.setCustomValidity('');
        }
        
        // Validación de Bootstrap 5
        if (!this.checkValidity()) {
            this.classList.add('was-validated');
            console.log('Validación fallida: checkValidity');
            return;
        }

        const formData = new FormData(this);
        if (loginAlert) loginAlert.style.display = 'none';
        
        // Mostrar indicador de carga
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Verificando...';
        submitBtn.disabled = true;

        console.log('Enviando petición fetch a:', this.action);
        
        // Enviar con fetch
        fetch(this.action, {
            method: 'POST',
            body: formData
        })
        .then(response => {
            console.log('Respuesta recibida:', response.status);
            if (!response.ok) {
                throw new Error('Error en la respuesta del servidor');
            }
            return response.json();
        })
        .then(data => {
            console.log('Datos JSON recibidos:', data);
            
            if (data.success) {
                console.log('Login exitoso, redirigiendo...');
                
                // Mostrar mensaje de éxito
                const roleName = data.user?.rol ? data.user.rol.charAt(0).toUpperCase() + data.user.rol.slice(1) : 'Usuario';
                if (loginAlert) {
                    loginAlert.className = 'alert alert-success';
                    loginAlert.innerHTML = `<i class="fas fa-check-circle me-2"></i>¡Bienvenido ${data.user?.nombre || ''}! Redirigiendo...`;
                    loginAlert.style.display = 'block';
                }
                
                // Redirigir inmediatamente
                console.log('Redirigiendo a:', data.redirect);
                setTimeout(() => {
                    window.location.href = data.redirect || '/Sistema-de-ventas-AppLink-main/public/dashboard';
                }, 500);
            } else {
                console.log('Login fallido:', data.message);
                
                // Mostrar error
                if (loginAlert) {
                    loginAlert.className = 'alert alert-danger';
                    loginAlert.innerHTML = `<i class="fas fa-exclamation-circle me-2"></i>${data.message || 'Error desconocido'}`;
                    loginAlert.style.display = 'block';
                }
                this.classList.remove('was-validated');
            }
        })
        .catch(error => {
            console.error('Error en la petición:', error);
            
            // Error de conexión
            if (loginAlert) {
                loginAlert.className = 'alert alert-danger';
                loginAlert.innerHTML = '<i class="fas fa-exclamation-triangle me-2"></i>Error de conexión. Verifique su red.';
                loginAlert.style.display = 'block';
            }
        })
        .finally(() => {
            // Restaurar botón
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        });
    });

    // Validación en tiempo real del campo de rol
    const rolSelect = document.getElementById('rol');
    if (rolSelect) {
        rolSelect.addEventListener('change', function() {
            if (this.value) {
                this.setCustomValidity('');
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
            } else {
                this.setCustomValidity('Debe seleccionar un tipo de usuario');
                this.classList.remove('is-valid');
                this.classList.add('is-invalid');
            }
        });
    }
});