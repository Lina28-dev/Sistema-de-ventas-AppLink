/**
 * Sistema de Temas Global - JavaScript
 * Maneja el cambio entre modo oscuro y claro en todo el proyecto
 */

class ThemeSystem {
    constructor() {
        this.currentTheme = this.getStoredTheme() || this.getSystemPreference();
        this.init();
    }

    init() {
        this.createThemeButton();
        this.applyTheme(this.currentTheme);
        this.bindEvents();
        this.injectStyles();
    }

    createThemeButton() {
        // Solo crear si no existe
        if (document.querySelector('.theme-switcher')) return;

        const themeSwitcher = document.createElement('div');
        themeSwitcher.className = 'theme-switcher';
        themeSwitcher.innerHTML = `
            <button class="theme-btn" id="themeToggle" aria-label="Cambiar tema">
                <i class="fas fa-sun theme-icon light-icon"></i>
                <i class="fas fa-moon theme-icon dark-icon"></i>
            </button>
        `;
        
        document.body.appendChild(themeSwitcher);
    }

    injectStyles() {
        // Inyectar estilos adicionales si no están presentes
        const existingStyle = document.getElementById('theme-system-styles');
        if (existingStyle) return;

        const style = document.createElement('style');
        style.id = 'theme-system-styles';
        style.textContent = `
            /* Estilos específicos para componentes dinámicos */
            .theme-switcher {
                position: fixed;
                bottom: 20px;
                right: 20px;
                z-index: 1050;
            }
            
            .theme-btn {
                width: 50px;
                height: 50px;
                border-radius: 50%;
                border: none;
                background: linear-gradient(135deg, #e91e63, #00bcd4);
                color: white;
                font-size: 1rem;
                cursor: pointer;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                transition: all 0.3s ease;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            
            .theme-btn:hover {
                transform: scale(1.1);
                box-shadow: 0 6px 20px rgba(0,0,0,0.25);
            }
            
            [data-theme="dark"] .light-icon {
                display: inline-block;
            }
            
            [data-theme="dark"] .dark-icon {
                display: none;
            }
            
            [data-theme="light"] .light-icon {
                display: none;
            }
            
            [data-theme="light"] .dark-icon {
                display: inline-block;
            }
            
            @media (max-width: 768px) {
                .theme-btn {
                    width: 45px;
                    height: 45px;
                    bottom: 15px;
                    right: 15px;
                }
            }
        `;
        
        document.head.appendChild(style);
    }

    bindEvents() {
        // Event listener para el botón de tema
        document.addEventListener('click', (e) => {
            if (e.target.closest('#themeToggle')) {
                this.toggleTheme();
            }
        });

        // Escuchar cambios en el sistema
        const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
        mediaQuery.addEventListener('change', (e) => {
            if (!this.getStoredTheme()) {
                this.applyTheme(e.matches ? 'dark' : 'light');
            }
        });

        // Escuchar cambios de tema desde otras pestañas
        window.addEventListener('storage', (e) => {
            if (e.key === 'theme') {
                this.currentTheme = e.newValue || 'light';
                this.applyTheme(this.currentTheme);
            }
        });
    }

    toggleTheme() {
        this.currentTheme = this.currentTheme === 'light' ? 'dark' : 'light';
        this.applyTheme(this.currentTheme);
        this.storeTheme(this.currentTheme);
        
        // Emitir evento personalizado
        window.dispatchEvent(new CustomEvent('themeChanged', {
            detail: { theme: this.currentTheme }
        }));
    }

    applyTheme(theme) {
        document.documentElement.setAttribute('data-theme', theme);
        
        // Actualizar meta theme-color
        this.updateMetaThemeColor(theme);
        
        // Actualizar favicon si existe
        this.updateFavicon(theme);
        
        // Aplicar tema a componentes específicos
        this.applyBootstrapTheme(theme);
        
        this.currentTheme = theme;
    }

    updateMetaThemeColor(theme) {
        let metaThemeColor = document.querySelector('meta[name="theme-color"]');
        if (!metaThemeColor) {
            metaThemeColor = document.createElement('meta');
            metaThemeColor.name = 'theme-color';
            document.head.appendChild(metaThemeColor);
        }
        metaThemeColor.content = theme === 'dark' ? '#1a1a1a' : '#ffffff';
    }

    updateFavicon(theme) {
        // Si tienes diferentes favicons para temas, puedes cambiarlos aquí
        const favicon = document.querySelector('link[rel="icon"]');
        if (favicon && theme === 'dark') {
            // favicon.href = 'assets/favicon-dark.ico';
        }
    }

    applyBootstrapTheme(theme) {
        const body = document.body;
        
        if (theme === 'dark') {
            body.classList.add('bg-dark', 'text-light');
            body.classList.remove('bg-light', 'text-dark');
        } else {
            body.classList.add('bg-light', 'text-dark');
            body.classList.remove('bg-dark', 'text-light');
        }

        // Actualizar componentes Bootstrap específicos
        this.updateBootstrapComponents(theme);
    }

    updateBootstrapComponents(theme) {
        // Modales
        const modals = document.querySelectorAll('.modal-content');
        modals.forEach(modal => {
            if (theme === 'dark') {
                modal.classList.add('bg-dark', 'text-light');
                modal.classList.remove('bg-light', 'text-dark');
            } else {
                modal.classList.add('bg-light', 'text-dark');
                modal.classList.remove('bg-dark', 'text-light');
            }
        });

        // Navbars
        const navbars = document.querySelectorAll('.navbar');
        navbars.forEach(navbar => {
            if (theme === 'dark') {
                navbar.classList.add('navbar-dark', 'bg-dark');
                navbar.classList.remove('navbar-light', 'bg-light');
            } else {
                navbar.classList.add('navbar-light', 'bg-light');
                navbar.classList.remove('navbar-dark', 'bg-dark');
            }
        });

        // Cards
        const cards = document.querySelectorAll('.card');
        cards.forEach(card => {
            if (theme === 'dark') {
                card.classList.add('bg-dark', 'text-light');
                card.classList.remove('bg-light', 'text-dark');
            } else {
                card.classList.add('bg-light', 'text-dark');
                card.classList.remove('bg-dark', 'text-light');
            }
        });
    }

    getSystemPreference() {
        return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    }

    getStoredTheme() {
        return localStorage.getItem('theme');
    }

    storeTheme(theme) {
        localStorage.setItem('theme', theme);
    }

    // Método público para obtener el tema actual
    getCurrentTheme() {
        return this.currentTheme;
    }

    // Método público para forzar un tema específico
    setTheme(theme) {
        if (theme === 'dark' || theme === 'light') {
            this.currentTheme = theme;
            this.applyTheme(theme);
            this.storeTheme(theme);
        }
    }

    // Método para aplicar tema a elementos específicos
    applyThemeToElement(element, theme = this.currentTheme) {
        if (!element) return;

        if (theme === 'dark') {
            element.classList.add('bg-dark', 'text-light');
            element.classList.remove('bg-light', 'text-dark');
        } else {
            element.classList.add('bg-light', 'text-dark');
            element.classList.remove('bg-dark', 'text-light');
        }
    }
}

// Inicializar sistema de temas cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    window.themeSystem = new ThemeSystem();
    
    // Agregar clase fade-in a elementos principales
    const mainElements = document.querySelectorAll('.container, .container-fluid, main, section');
    mainElements.forEach(el => el.classList.add('fade-in'));
});

// API global para otros scripts
window.ThemeAPI = {
    toggle: () => window.themeSystem?.toggleTheme(),
    set: (theme) => window.themeSystem?.setTheme(theme),
    get: () => window.themeSystem?.getCurrentTheme(),
    applyTo: (element, theme) => window.themeSystem?.applyThemeToElement(element, theme)
};

// Función de utilidad para componentes dinámicos
function applyCurrentTheme(element) {
    if (window.themeSystem) {
        window.themeSystem.applyThemeToElement(element);
    }
}

// Exportar para uso en módulos
if (typeof module !== 'undefined' && module.exports) {
    module.exports = ThemeSystem;
}