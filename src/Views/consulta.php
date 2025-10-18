<?php
// La sesión ya está iniciada en index.php
if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
    header("Location: /gestor-ventas-lilipink-main/public/");
    exit();
}
include(__DIR__ . '/partials/header.php');
include(__DIR__ . '/partials/navegador.php');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Consulta de Stock</title>
    <link rel="stylesheet" href="css/contenido.css">
</head>
<body>
    <div class="content-container">
        <h2>Consulta de Stock de Productos</h2>
        
        <form action="" method="GET">
            <div class="search-box">
                <input type="search" name="buscar" placeholder="Buscar producto..." value="<?php echo isset($_GET['buscar']) ? htmlspecialchars($_GET['buscar']) : ''; ?>">
                <button type="submit">Buscar</button>
            </div>
        </form>
        
        <table>
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th>Categoría</th>
                    <th>Stock</th>
                    <th>Precio</th>
                </tr>
            </thead>
            <tbody>
                <!-- Aquí iría el código PHP para mostrar los resultados de la base de datos -->
                <tr>
                    <td>001</td>
                    <td>Ejemplo Producto</td>
                    <td>Descripción del producto</td>
                    <td>Ropa</td>
                    <td>25</td>
                    <td>$19.99</td>
                </tr>
                <!-- Más filas de muestra -->
                <tr>
                    <td>002</td>
                    <td>Otro Producto</td>
                    <td>Descripción del producto</td>
                    <td>Accesorios</td>
                    <td>10</td>
                    <td>$24.99</td>
                </tr>
            </tbody>
        </table>
    </div>

    <script>
        let infoPagina = document.getElementById('infoPagina');
        infoPagina.innerHTML = 'Consulta de Stock';
        let infoGeneral = document.getElementById('infoGeneralText');
        infoGeneral.innerHTML = "Consulta la disponibilidad de productos en inventario";
    </script>
</body>
</html>