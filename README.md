# Gestor de Ventas AppLink

Sistema de gestión de ventas y stock para tiendas de ropa y accesorios.
Desarrollado con tecnologías modernas y buenas prácticas de programación.

## 🚀 Tecnologías

- **Backend:** PHP 7.4+
- **Base de datos:** MySQL 5.7+
- **Frontend:** HTML5, CSS3, JavaScript
- **Dependencias:** Composer, PHPUnit, Monolog
- **Servidor:** Apache/Nginx

## ✨ Características

- Sistema MVC estructurado
- Autoloading de clases con Composer
- Manejo centralizado de errores y logging
- Configuración basada en variables de entorno
- Sistema de migraciones de base de datos
- Validación y sanitización de datos
- Control de inventario en tiempo real
- Gestión de clientes y pedidos
- Reportes y estadísticas
- Autenticación segura

## � Instalación

1. Clonar el repositorio:
```bash
git clone https://github.com/Lina28-dev/gestor-ventas-lilipink.git
cd gestor-ventas-lilipink
```

2. Instalar dependencias:
```bash
composer install
```

3. Configurar el entorno:
```bash
cp .env.example .env
# Editar el archivo .env con tus configuraciones
```

4. Ejecutar migraciones:
```bash
php database/migrate.php
```

5. Configurar permisos:
```bash
chmod 755 -R storage/logs
chmod 755 -R storage/cache
```

## 🏗️ Estructura del Proyecto

```
gestor-ventas-lilipink/
├── config/           # Configuración de la aplicación
├── database/        # Migraciones y seeders
├── public/          # Archivos públicos (index.php, assets)
├── src/            # Código fuente
│   ├── Controllers/ # Controladores MVC
│   ├── Models/      # Modelos de datos
│   ├── Views/       # Vistas y templates
│   └── Utils/       # Clases utilitarias
├── storage/        # Archivos generados
│   ├── logs/       # Registros del sistema
│   └── cache/      # Caché de la aplicación
└── tests/          # Tests unitarios
```

## 🔒 Seguridad

- Protección contra CSRF
- Sanitización de entradas
- Validación de datos
- Control de sesiones
- Registro de actividades

## 📚 Documentación

La documentación completa está disponible en la [wiki del proyecto](https://github.com/Lina28-dev/gestor-ventas-lilipink/wiki).

## 🤝 Contribuir

1. Fork el proyecto
2. Crea tu rama de características (`git checkout -b feature/nueva-caracteristica`)
3. Commit tus cambios (`git commit -am 'Agrega nueva característica'`)
4. Push a la rama (`git push origin feature/nueva-caracteristica`)
5. Abre un Pull Request

## 📩 Contacto

Para consultas o colaboraciones:
- Email: lina.oviedomm28@gmail.com
- GitHub: [@Lina28-dev](https://github.com/Lina28-dev)

## 📄 Licencia

Este proyecto está bajo la Licencia MIT. Ver el archivo [LICENSE](LICENSE) para más detalles.

