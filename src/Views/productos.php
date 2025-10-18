<?php
namespace App\Views;

// La sesión ya está iniciada en index.php
if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
    header('Location: /Sistema-de-ventas-AppLink-main/public/');
    exit;
}

// Incluir modelos necesarios
require_once __DIR__ . '/../Models/Producto.php';
require_once __DIR__ . '/../Utils/CSRFToken.php';

use App\Models\Producto;
use App\Utils\CSRFToken;

// Obtener productos con paginación
$pagina = (int)($_GET['pagina'] ?? 1);
$limite = 20;
$busqueda = $_GET['buscar'] ?? '';

try {
    if ($busqueda) {
        $productos = Producto::buscar($busqueda, null, $limite);
        $totalProductos = count($productos);
    } else {
        $producto = new Producto();
        $productos = $producto->all(['activo' => 1], $limite);
        $totalProductos = count($productos);
    }
    
    // Obtener productos con stock bajo
    $stockBajo = Producto::getStockBajo();
    
} catch (Exception $e) {
    $error = "Error al cargar productos: " . $e->getMessage();
    $productos = [];
    $stockBajo = [];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Productos - Sistema de Ventas AppLink</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/Sistema-de-ventas-AppLink-main/public/css/base.css">
    <style>
        .producto-card {
            transition: transform 0.3s ease;
        }
        .producto-card:hover {
            transform: translateY(-5px);
        }
        .stock-badge {
            position: absolute;
            top: 10px;
            right: 10px;
        }
        .precio-badge {
            font-size: 1.2em;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 sidebar p-3" style="min-height: 100vh; background: linear-gradient(180deg, #343a40 0%, #212529 100%); color: white;">
                <?php 
                    $activePage = 'productos';
                    include __DIR__ . '/partials/sidebar.php';
                ?>
            </div>

            <!-- Contenido principal -->
            <div class="col-md-10 main-content" style="padding: 20px;">
                <div class="px-4">
                    <!-- Header -->
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h1 class="fw-bold">Gestión de Productos</h1>
                            <p class="text-muted">Administra tu inventario de productos</p>
                        </div>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#productoModal">
                            <i class="fas fa-plus"></i> Nuevo Producto
                        </button>
                    </div>

                    <!-- Alertas -->
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($stockBajo)): ?>
                        <div class="alert alert-warning alert-dismissible fade show">
                            <i class="fas fa-exclamation-triangle"></i> 
                            <strong>¡Atención!</strong> Tienes <?php echo count($stockBajo); ?> producto(s) con stock bajo.
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Métricas -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card border-0 shadow-sm">
                                <div class="card-body text-center">
                                    <h6 class="mb-1">Total Productos</h6>
                                    <span class="fs-2 fw-bold text-primary"><?php echo $totalProductos; ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-0 shadow-sm">
                                <div class="card-body text-center">
                                    <h6 class="mb-1">Stock Bajo</h6>
                                    <span class="fs-2 fw-bold text-warning"><?php echo count($stockBajo); ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-0 shadow-sm">
                                <div class="card-body text-center">
                                    <h6 class="mb-1">Activos</h6>
                                    <span class="fs-2 fw-bold text-success"><?php echo count(array_filter($productos, function($p) { return $p['activo']; })); ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-0 shadow-sm">
                                <div class="card-body text-center">
                                    <h6 class="mb-1">Categorías</h6>
                                    <span class="fs-2 fw-bold text-info">
                                        <?php 
                                        $categorias = array_unique(array_column($productos, 'id_categoria'));
                                        echo count(array_filter($categorias));
                                        ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Búsqueda y filtros -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <form method="GET" class="row g-3">
                                <div class="col-md-6">
                                    <input type="search" name="buscar" class="form-control" 
                                           placeholder="Buscar productos por nombre, código o descripción..." 
                                           value="<?php echo htmlspecialchars($busqueda); ?>">
                                </div>
                                <div class="col-md-3">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search"></i> Buscar
                                    </button>
                                    <a href="?" class="btn btn-outline-secondary">
                                        <i class="fas fa-times"></i> Limpiar
                                    </a>
                                </div>
                                <div class="col-md-3 text-end">
                                    <button type="button" class="btn btn-success">
                                        <i class="fas fa-file-excel"></i> Exportar
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Grid de productos -->
                    <div class="row">
                        <?php if (empty($productos)): ?>
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body text-center py-5">
                                        <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                                        <h4 class="text-muted">No hay productos</h4>
                                        <p class="text-muted">
                                            <?php echo $busqueda ? 'No se encontraron productos para tu búsqueda.' : 'Aún no has agregado productos.'; ?>
                                        </p>
                                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#productoModal">
                                            <i class="fas fa-plus"></i> Agregar Primer Producto
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <?php foreach ($productos as $producto): ?>
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card producto-card h-100 position-relative">
                                        <!-- Badge de stock -->
                                        <div class="stock-badge">
                                            <?php 
                                            if ($producto['stock_actual'] <= 0) {
                                                echo '<span class="badge bg-danger">Sin Stock</span>';
                                            } elseif ($producto['stock_actual'] <= $producto['stock_minimo']) {
                                                echo '<span class="badge bg-warning text-dark">Stock Bajo</span>';
                                            } else {
                                                echo '<span class="badge bg-success">En Stock</span>';
                                            }
                                            ?>
                                        </div>

                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <small class="text-muted">Código: <?php echo htmlspecialchars($producto['codigo'] ?? 'N/A'); ?></small>
                                            </div>
                                            
                                            <h5 class="card-title"><?php echo htmlspecialchars($producto['nombre']); ?></h5>
                                            
                                            <?php if ($producto['descripcion']): ?>
                                                <p class="card-text text-muted small">
                                                    <?php echo htmlspecialchars(substr($producto['descripcion'], 0, 100)) . (strlen($producto['descripcion']) > 100 ? '...' : ''); ?>
                                                </p>
                                            <?php endif; ?>

                                            <div class="row text-center mb-3">
                                                <div class="col-6">
                                                    <div class="precio-badge text-primary">
                                                        $<?php echo number_format($producto['precio'], 2); ?>
                                                    </div>
                                                    <small class="text-muted">Precio</small>
                                                </div>
                                                <div class="col-6">
                                                    <div class="fw-bold">
                                                        <?php echo $producto['stock_actual']; ?>
                                                    </div>
                                                    <small class="text-muted">Stock</small>
                                                </div>
                                            </div>

                                            <?php if ($producto['talle'] || $producto['color']): ?>
                                                <div class="mb-2">
                                                    <?php if ($producto['talle']): ?>
                                                        <span class="badge bg-light text-dark">Talle: <?php echo htmlspecialchars($producto['talle']); ?></span>
                                                    <?php endif; ?>
                                                    <?php if ($producto['color']): ?>
                                                        <span class="badge bg-light text-dark">Color: <?php echo htmlspecialchars($producto['color']); ?></span>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>

                                        <div class="card-footer bg-transparent">
                                            <div class="btn-group w-100" role="group">
                                                <button class="btn btn-outline-primary btn-sm" onclick="editarProducto(<?php echo htmlspecialchars(json_encode($producto)); ?>)" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-outline-success btn-sm" onclick="verDetalles(<?php echo $producto['id_producto']; ?>)" title="Ver detalles">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn btn-outline-warning btn-sm" onclick="ajustarStock(<?php echo $producto['id_producto']; ?>)" title="Ajustar stock">
                                                    <i class="fas fa-boxes"></i>
                                                </button>
                                                <button class="btn btn-outline-danger btn-sm" onclick="confirmarEliminar(<?php echo $producto['id_producto']; ?>)" title="Eliminar">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Producto -->
    <div class="modal fade" id="productoModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="productoForm" method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo CSRFToken::generate(); ?>">
                    <input type="hidden" name="action" value="crear">
                    <input type="hidden" name="id_producto" id="productId">

                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTitle">Nuevo Producto</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="codigo" class="form-label">Código</label>
                                <input type="text" class="form-control" id="codigo" name="codigo" placeholder="Se genera automáticamente">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="nombre" class="form-label">Nombre *</label>
                                <input type="text" class="form-control" id="nombre" name="nombre" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="descripcion" class="form-label">Descripción</label>
                            <textarea class="form-control" id="descripcion" name="descripcion" rows="3"></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="precio" class="form-label">Precio *</label>
                                <input type="number" class="form-control" id="precio" name="precio" step="0.01" min="0" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="precio_costo" class="form-label">Precio Costo</label>
                                <input type="number" class="form-control" id="precio_costo" name="precio_costo" step="0.01" min="0">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="stock_actual" class="form-label">Stock Inicial</label>
                                <input type="number" class="form-control" id="stock_actual" name="stock_actual" min="0" value="0">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="talle" class="form-label">Talle</label>
                                <input type="text" class="form-control" id="talle" name="talle">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="color" class="form-label">Color</label>
                                <input type="text" class="form-control" id="color" name="color">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="stock_minimo" class="form-label">Stock Mínimo</label>
                                <input type="number" class="form-control" id="stock_minimo" name="stock_minimo" min="0" value="5">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="marca" class="form-label">Marca</label>
                                <input type="text" class="form-control" id="marca" name="marca">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="modelo" class="form-label">Modelo</label>
                                <input type="text" class="form-control" id="modelo" name="modelo">
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar Producto</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editarProducto(producto) {
            document.getElementById('modalTitle').textContent = 'Editar Producto';
            document.getElementById('productoForm').action.value = 'actualizar';
            
            // Llenar formulario con datos del producto
            Object.keys(producto).forEach(key => {
                const field = document.getElementById(key);
                if (field) {
                    field.value = producto[key] || '';
                }
            });
            
            new bootstrap.Modal(document.getElementById('productoModal')).show();
        }

        function verDetalles(id) {
            // Implementar vista de detalles
            alert('Función de detalles en desarrollo para producto ID: ' + id);
        }

        function ajustarStock(id) {
            // Implementar ajuste de stock
            const cantidad = prompt('Ingrese la cantidad a ajustar (negativo para reducir):');
            if (cantidad !== null) {
                alert('Función de ajuste de stock en desarrollo para producto ID: ' + id + ', cantidad: ' + cantidad);
            }
        }

        function confirmarEliminar(id) {
            if (confirm('¿Estás seguro de que deseas eliminar este producto?')) {
                alert('Función de eliminación en desarrollo para producto ID: ' + id);
            }
        }

        // Resetear formulario al cerrar modal
        document.getElementById('productoModal').addEventListener('hidden.bs.modal', function () {
            document.getElementById('productoForm').reset();
            document.getElementById('modalTitle').textContent = 'Nuevo Producto';
            document.getElementById('productoForm').action.value = 'crear';
        });
    </script>
</body>
</html>