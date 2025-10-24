/**
 * Theme Switcher - Modo Oscuro/Claro
 * Sistema de Ventas AppLink
 */

class ThemeSwitcher {
    constructor() {
        this.currentTheme = localStorage.getItem('theme') || 'light';
        this.init();
    }

    init() {
        this.applyTheme(this.currentTheme);
        this.createToggleButton();
        this.bindEvents();
    }

    createToggleButton() {
        // Crear el botón de cambio de tema
        const themeToggle = document.createElement('div');
        themeToggle.className = 'theme-toggle';
        themeToggle.innerHTML = `
            <button class="theme-btn" id="themeToggle" title="Cambiar tema">
                <i class="fas fa-sun theme-icon light-icon"></i>
                <i class="fas fa-moon theme-icon dark-icon"></i>
            </button>
        `;

        // Agregar estilos CSS
        const styles = document.createElement('style');
        styles.textContent = `
            .theme-toggle {
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 1050;
            }

            .theme-btn {
                background: var(--theme-toggle-bg, #fff);
                border: 2px solid var(--theme-toggle-border, #e91e63);
                border-radius: 50%;
                width: 50px;
                height: 50px;
                display: flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
                transition: all 0.3s ease;
                box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
                position: relative;
                overflow: hidden;
            }

            .theme-btn:hover {
                transform: scale(1.1);
                box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
            }

            .theme-icon {
                font-size: 1.2rem;
                transition: all 0.3s ease;
                position: absolute;
            }

            .light-icon {
                color: #ffd700;
                opacity: 1;
                transform: rotate(0deg);
            }

            .dark-icon {
                color: #4a90e2;
                opacity: 0;
                transform: rotate(180deg);
            }

            /* Dark theme button styles */
            [data-theme="dark"] .theme-btn {
                background: var(--theme-toggle-bg, #2d3748);
                border-color: var(--theme-toggle-border, #4a90e2);
            }

            [data-theme="dark"] .light-icon {
                opacity: 0;
                transform: rotate(180deg);
            }

            [data-theme="dark"] .dark-icon {
                opacity: 1;
                transform: rotate(0deg);
            }

            /* Responsive design */
            @media (max-width: 768px) {
                .theme-toggle {
                    top: 10px;
                    right: 10px;
                }

                .theme-btn {
                    width: 45px;
                    height: 45px;
                }

                .theme-icon {
                    font-size: 1rem;
                }
            }
        `;

        document.head.appendChild(styles);
        document.body.appendChild(themeToggle);
    }

    bindEvents() {
        document.addEventListener('click', (e) => {
            if (e.target.closest('#themeToggle')) {
                this.toggleTheme();
            }
        });

        // Escuchar cambios en el sistema
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
            if (!localStorage.getItem('theme')) {
                this.applyTheme(e.matches ? 'dark' : 'light');
            }
        });
    }

    toggleTheme() {
        this.currentTheme = this.currentTheme === 'light' ? 'dark' : 'light';
        this.applyTheme(this.currentTheme);
        localStorage.setItem('theme', this.currentTheme);
        
        // Animación de transición
        document.body.style.transition = 'all 0.3s ease';
        setTimeout(() => {
            document.body.style.transition = '';
        }, 300);
    }

    applyTheme(theme) {
        document.documentElement.setAttribute('data-theme', theme);
        this.currentTheme = theme;
        
        // Aplicar clases de Bootstrap si están disponibles
        if (theme === 'dark') {
            document.body.classList.add('bg-dark', 'text-light');
            document.body.classList.remove('bg-light', 'text-dark');
        } else {
            document.body.classList.add('bg-light', 'text-dark');
            document.body.classList.remove('bg-dark', 'text-light');
        }

        // Actualizar meta theme-color para PWA
        let metaThemeColor = document.querySelector('meta[name="theme-color"]');
        if (!metaThemeColor) {
            metaThemeColor = document.createElement('meta');
            metaThemeColor.name = 'theme-color';
            document.head.appendChild(metaThemeColor);
        }
        metaThemeColor.content = theme === 'dark' ? '#2d3748' : '#ffffff';
    }

    getCurrentTheme() {
        return this.currentTheme;
    }
}

// Inicializar automáticamente cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', () => {
    window.themeSwitcher = new ThemeSwitcher();
});

// Exportar para uso global
window.ThemeSwitcher = ThemeSwitcher;