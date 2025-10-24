<?php
/**
 * Script para recrear datos de demostración
 */

// Iniciar sesión si no está activa
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Verificar autenticación
if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

// Obtener configuración de base de datos
$config = require __DIR__ . '/../../config/app.php';

try {
    $pdo = new PDO(
        "mysql:host={$config['db']['host']};dbname={$config['db']['name']};charset={$config['db']['charset']}",
        $config['db']['user'],
        $config['db']['pass'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
    
    // Limpiar datos existentes
    $pdo->exec("DELETE FROM fs_productos WHERE codigo IN ('BRA001', 'BRA002', 'BRA003', 'PAN001', 'PIJ001', 'CAM001')");
    $pdo->exec("DELETE FROM fs_clientes WHERE identificacion IN ('1234567890', '0987654321', '1122334455', '5566778899', '9988776655')");
    
    // Crear productos demo
    crearProductosDemo($pdo);
    
    // Intentar alterar tabla de clientes para agregar campos nombres y apellidos
    try {
        $pdo->exec("ALTER TABLE fs_clientes ADD COLUMN nombres VARCHAR(255) AFTER id_cliente");
        $pdo->exec("ALTER TABLE fs_clientes ADD COLUMN apellidos VARCHAR(255) AFTER nombres");
    } catch (Exception $e) {
        // Los campos pueden ya existir
        echo "Info: Campos nombres/apellidos pueden ya existir\n";
    }
    
    // Crear clientes demo
    crearClientesDemo($pdo);
    
    echo json_encode(['success' => true, 'message' => 'Datos recreados exitosamente']);
    
} catch (PDOException $e) {
    echo json_encode(['error' => 'Error: ' . $e->getMessage()]);
}

function crearProductosDemo($pdo) {
    $productosDemo = [
        [
            'codigo' => 'BRA001',
            'nombre' => 'Brasier Push Up Encaje',
            'descripcion' => 'Brasier push up con encaje delicado, realza y da forma natural',
            'color' => 'Negro',
            'talle' => '34B',
            'categoria' => 'Lencería',
            'marca' => 'Lilipink',
            'cantidad' => 15,
            'precio' => 65990,
            'precio_costo' => 35000
        ],
        [
            'codigo' => 'BRA002', 
            'nombre' => 'Brasier Deportivo',
            'descripcion' => 'Brasier deportivo con soporte medio, ideal para ejercicio',
            'color' => 'Rosa',
            'talle' => '32A',
            'categoria' => 'Deportivo',
            'marca' => 'Lilipink',
            'cantidad' => 12,
            'precio' => 45990,
            'precio_costo' => 25000
        ],
        [
            'codigo' => 'BRA003',
            'nombre' => 'Brasier Sin Copas',
            'descripcion' => 'Brasier sin copas, cómodo y natural para uso diario',
            'color' => 'Blanco',
            'talle' => '36C',
            'categoria' => 'Lencería',
            'marca' => 'Lilipink',
            'cantidad' => 8,
            'precio' => 39990,
            'precio_costo' => 22000
        ],
        [
            'codigo' => 'PAN001',
            'nombre' => 'Panty Invisible Clásico',
            'descripcion' => 'Panty invisible sin costuras, perfecto bajo cualquier prenda',
            'color' => 'Nude',
            'talle' => 'M',
            'categoria' => 'Lencería',
            'marca' => 'Lilipink',
            'cantidad' => 25,
            'precio' => 24990,
            'precio_costo' => 12000
        ],
        [
            'codigo' => 'PIJ001',
            'nombre' => 'Pijama Short Algodón',
            'descripcion' => 'Pijama de short en algodón suave, ideal para el descanso',
            'color' => 'Azul',
            'talle' => 'L',
            'categoria' => 'Pijamas',
            'marca' => 'Lilipink',
            'cantidad' => 10,
            'precio' => 79990,
            'precio_costo' => 45000
        ],
        [
            'codigo' => 'CAM001',
            'nombre' => 'Camiseta Manga Corta',
            'descripcion' => 'Camiseta básica de manga corta en algodón',
            'color' => 'Blanco',
            'talle' => 'M',
            'categoria' => 'Casual',
            'marca' => 'Lilipink',
            'cantidad' => 18,
            'precio' => 29990,
            'precio_costo' => 15000
        ]
    ];
    
    foreach ($productosDemo as $producto) {
        $stmt = $pdo->prepare("
            INSERT INTO fs_productos 
            (codigo, nombre, descripcion, color, talle, categoria, marca, cantidad, precio, precio_costo, estado) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'activo')
        ");
        
        $stmt->execute([
            $producto['codigo'],
            $producto['nombre'],
            $producto['descripcion'],
            $producto['color'],
            $producto['talle'],
            $producto['categoria'],
            $producto['marca'],
            $producto['cantidad'],
            $producto['precio'],
            $producto['precio_costo']
        ]);
    }
}

function crearClientesDemo($pdo) {
    $clientesDemo = [
        [
            'nombres' => 'María Elena',
            'apellidos' => 'González Rodríguez',
            'identificacion' => '1234567890',
            'telefono' => '3001234567',
            'email' => 'maria.gonzalez@email.com',
            'ciudad' => 'Bogotá',
            'descuento' => 5.00
        ],
        [
            'nombres' => 'Ana Sofía',
            'apellidos' => 'Martínez López',
            'identificacion' => '0987654321',
            'telefono' => '3109876543',
            'email' => 'ana.martinez@email.com',
            'ciudad' => 'Medellín',
            'descuento' => 10.00
        ],
        [
            'nombres' => 'Carolina',
            'apellidos' => 'Pérez García',
            'identificacion' => '1122334455',
            'telefono' => '3201122334',
            'email' => 'carolina.perez@email.com',
            'ciudad' => 'Cali',
            'descuento' => 0.00
        ],
        [
            'nombres' => 'Daniela',
            'apellidos' => 'Ramírez Silva',
            'identificacion' => '5566778899',
            'telefono' => '3155566778',
            'email' => 'daniela.ramirez@email.com',
            'ciudad' => 'Barranquilla',
            'descuento' => 15.00
        ],
        [
            'nombres' => 'Lucía Isabel',
            'apellidos' => 'Torres Moreno',
            'identificacion' => '9988776655',
            'telefono' => '3179988776',
            'email' => 'lucia.torres@email.com',
            'ciudad' => 'Cartagena',
            'descuento' => 8.00
        ]
    ];
    
    foreach ($clientesDemo as $cliente) {
        // Primero intentar insertar en tabla con nombres/apellidos
        try {
            $stmt = $pdo->prepare("
                INSERT INTO fs_clientes 
                (nombres, apellidos, nombre, identificacion, telefono, email, ciudad, descuento, estado) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'activo')
            ");
            
            $stmt->execute([
                $cliente['nombres'],
                $cliente['apellidos'],
                $cliente['nombres'] . ' ' . $cliente['apellidos'],
                $cliente['identificacion'],
                $cliente['telefono'],
                $cliente['email'],
                $cliente['ciudad'],
                $cliente['descuento']
            ]);
        } catch (Exception $e) {
            // Si falla, insertar solo con nombre completo
            $stmt = $pdo->prepare("
                INSERT INTO fs_clientes 
                (nombre, identificacion, telefono, email, ciudad, descuento, estado) 
                VALUES (?, ?, ?, ?, ?, ?, 'activo')
            ");
            
            $stmt->execute([
                $cliente['nombres'] . ' ' . $cliente['apellidos'],
                $cliente['identificacion'],
                $cliente['telefono'],
                $cliente['email'],
                $cliente['ciudad'],
                $cliente['descuento']
            ]);
        }
    }
}
?>