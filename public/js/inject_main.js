// Archivo inject_main.js para evitar errores 404
console.log('inject_main.js cargado correctamente');

// Funciones básicas para evitar errores
window.logoutFunctions = {
    clearSession: function() {
        if (typeof(Storage) !== "undefined") {
            localStorage.clear();
            sessionStorage.clear();
        }
    },
    redirect: function() {
        window.location.href = '/Sistema-de-ventas-AppLink-main/public/';
    }
};

// Log para debug
console.log('✅ inject_main.js inicializado correctamente');