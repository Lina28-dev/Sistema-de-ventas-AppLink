<?php
// La sesión ya está iniciada en index.php
if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
    header("Location: /Sistema-de-ventas-AppLink-main/public/");
    exit();
}
include(__DIR__ . '/partials/header.php');
include(__DIR__ . '/partials/navegador.php');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Inventario</title>
    <link rel="stylesheet" href="css/contenido.css">
</head>
<body>
    <div class="content-container">
        <h2>Gestión de Inventario</h2>
        
        <div class="tabs">
            <button class="tab-link active" onclick="openTab(event, 'alta')">Alta de Producto</button>
            <button class="tab-link" onclick="openTab(event, 'baja')">Baja de Producto</button>
            <button class="tab-link" onclick="openTab(event, 'modificar')">Modificar Existencias</button>
        </div>
        
        <!-- Alta de Producto -->
        <div id="alta" class="tab-content" style="display: block;">
            <h3>Agregar Nuevo Producto</h3>
            <form action="" method="POST">
                <div class="form-group">
                    <label for="codigo">Código:</label>
                    <input type="text" id="codigo" name="codigo" required>
                </div>
                
                <div class="form-group">
                    <label for="nombre">Nombre del Producto:</label>
                    <input type="text" id="nombre" name="nombre" required>
                </div>
                
                <div class="form-group">
                    <label for="descripcion">Descripción:</label>
                    <textarea id="descripcion" name="descripcion" rows="3"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="categoria">Categoría:</label>
                    <select id="categoria" name="categoria">
                        <option value="">Seleccionar categoría</option>
                        <option value="Ropa">Ropa</option>
                        <option value="Accesorios">Accesorios</option>
                        <option value="Calzado">Calzado</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="stock">Stock Inicial:</label>
                    <input type="number" id="stock" name="stock" min="0" required>
                </div>
                
                <div class="form-group">
                    <label for="precio">Precio:</label>
                    <input type="number" id="precio" name="precio" min="0" step="0.01" required>
                </div>
                
                <button type="submit" name="agregar">Agregar Producto</button>
            </form>
        </div>
        
        <!-- Baja de Producto -->
        <div id="baja" class="tab-content" style="display: none;">
            <h3>Dar de Baja un Producto</h3>
            <form action="" method="POST">
                <div class="form-group">
                    <label for="codigo_baja">Código del Producto:</label>
                    <input type="text" id="codigo_baja" name="codigo_baja" required>
                </div>
                
                <div class="form-group">
                    <label for="motivo">Motivo de Baja:</label>
                    <select id="motivo" name="motivo">
                        <option value="Descontinuado">Descontinuado</option>
                        <option value="Defectuoso">Defectuoso</option>
                        <option value="Otro">Otro</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="comentario">Comentarios:</label>
                    <textarea id="comentario" name="comentario" rows="3"></textarea>
                </div>
                
                <button type="submit" name="baja">Dar de Baja</button>
            </form>
        </div>
        
        <!-- Modificar Existencias -->
        <div id="modificar" class="tab-content" style="display: none;">
            <h3>Modificar Existencias</h3>
            <form action="" method="POST">
                <div class="form-group">
                    <label for="codigo_mod">Código del Producto:</label>
                    <input type="text" id="codigo_mod" name="codigo_mod" required>
                </div>
                
                <div class="form-group">
                    <label for="cantidad">Cantidad a Modificar:</label>
                    <input type="number" id="cantidad" name="cantidad" required>
                </div>
                
                <div class="form-group">
                    <label for="tipo_mov">Tipo de Movimiento:</label>
                    <select id="tipo_mov" name="tipo_mov">
                        <option value="Entrada">Entrada</option>
                        <option value="Salida">Salida</option>
                    </select>
                </div>
                
                <button type="submit" name="modificar">Actualizar Stock</button>
            </form>
        </div>
    </div>

    <script>
        let infoPagina = document.getElementById('infoPagina');
        infoPagina.innerHTML = 'Gestión de Inventario';
        let infoGeneral = document.getElementById('infoGeneralText');
        infoGeneral.innerHTML = "Administra el inventario de productos";
        
        // Función para cambiar entre pestañas
        function openTab(evt, tabName) {
            let i, tabContent, tabLinks;
            
            // Ocultar todas las pestañas
            tabContent = document.getElementsByClassName("tab-content");
            for (i = 0; i < tabContent.length; i++) {
                tabContent[i].style.display = "none";
            }
            
            // Desactivar todos los botones
            tabLinks = document.getElementsByClassName("tab-link");
            for (i = 0; i < tabLinks.length; i++) {
                tabLinks[i].className = tabLinks[i].className.replace(" active", "");
            }
            
            // Mostrar la pestaña actual y activar el botón
            document.getElementById(tabName).style.display = "block";
            evt.currentTarget.className += " active";
        }
    </script>
    
    <style>
        /* Estilos para las pestañas */
        .tabs {
            overflow: hidden;
            border: 1px solid #ccc;
            background-color: #f1f1f1;
            border-radius: 5px 5px 0 0;
        }
        
        .tab-link {
            background-color: inherit;
            float: left;
            border: none;
            outline: none;
            cursor: pointer;
            padding: 14px 16px;
            transition: 0.3s;
            font-size: 16px;
        }
        
        .tab-link:hover {
            background-color: #ddd;
        }
        
        .tab-link.active {
            background-color: #E91E63;
            color: white;
        }
        
        .tab-content {
            padding: 20px;
            border: 1px solid #ccc;
            border-top: none;
            border-radius: 0 0 5px 5px;
        }
    </style>
</body>
</html>
