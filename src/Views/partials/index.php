<?php
session_start();
if (!isset($_SESSION['usuario_nombre'])) {
    header("Location: index.php");
    exit();
}
include('header.php');
include('navegador.php');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ventas</title>
    <link rel="stylesheet" href="css/contenido.css">
</head>
<body>
    <div class="content-container">
        <h2>Sistema de Ventas</h2>
        
        <div class="tabs">
            <button class="tab-link active" onclick="openTab(event, 'nueva')">Nueva Venta</button>
            <button class="tab-link" onclick="openTab(event, 'historial')">Historial de Ventas</button>
            <button class="tab-link" onclick="openTab(event, 'reportes')">Reportes</button>
        </div>
        
        <!-- Nueva Venta -->
        <div id="nueva" class="tab-content" style="display: block;">
            <h3>Registrar Nueva Venta</h3>
            
            <div class="venta-container">
                <div class="cliente-section">
                    <h4>Datos del Cliente</h4>
                    <div class="form-group">
                        <label for="cliente">Buscar Cliente:</label>
                        <div class="search-flex">
                            <input type="text" id="cliente" name="cliente" placeholder="DNI o nombre del cliente">
                            <button type="button" id="buscarCliente">Buscar</button>
                            <button type="button" onclick="window.location.href='clientes.php?nuevo=1'">Nuevo Cliente</button>
                        </div>
                    </div>
                    
                    <div id="datos-cliente" class="cliente-info">
                        <p><strong>Cliente:</strong> <span id="cliente-nombre">No seleccionado</span></p>
                        <p><strong>DNI/ID:</strong> <span id="cliente-id">-</span></p>
                        <p><strong>Teléfono:</strong> <span id="cliente-telefono">-</span></p>
                    </div>
                </div>
                
                <div class="productos-section">
                    <h4>Agregar Productos</h4>
                    <div class="form-group">
                        <label for="producto">Buscar Producto:</label>
                        <div class="search-flex">
                            <input type="text" id="producto" name="producto" placeholder="Código o nombre del producto">
                            <button type="button" id="buscarProducto">Buscar</button>
                        </div>
                    </div>
                    
                    <div class="producto-results" id="producto-results" style="display: none;">
                        <table>
                            <thead>
                                <tr>
                                    <th>Código</th>
                                    <th>Nombre</th>
                                    <th>Precio</th>
                                    <th>Stock</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody id="productos-tbody">
                                <!-- Aquí se cargarán los resultados de productos -->
                                <tr>
                                    <td>001</td>
                                    <td>Producto Ejemplo</td>
                                    <td>$19.99</td>
                                    <td>50</td>
                                    <td><button type="button" onclick="agregarAlCarrito('001', 'Producto Ejemplo', 19.99)">Agregar</button></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <h4>Carrito de Compra</h4>
                    <table>
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Producto</th>
                                <th>Precio</th>
                                <th>Cantidad</th>
                                <th>Subtotal</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody id="carrito-tbody">
                            <!-- Aquí se cargarán los productos del carrito -->
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="4" class="text-right"><strong>Total:</strong></td>
                                <td id="total-venta">$0.00</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                    
                    <div class="form-actions">
                        <div class="form-group">
                            <label for="metodo_pago">Método de Pago:</label>
                            <select id="metodo_pago" name="metodo_pago">
                                <option value="Efectivo">Efectivo</option>
                                <option value="Tarjeta">Tarjeta de Crédito/Débito</option>
                                <option value="Transferencia">Transferencia Bancaria</option>
                            </select>
                        </div>
                        
                        <button type="button" id="finalizar-venta" class="btn-finalizar">Finalizar Venta</button>
