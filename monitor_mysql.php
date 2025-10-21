<?php
/**
 * Monitor de Estado MySQL - Sistema de Ventas AppLink
 * Detecta problemas antes de que ocurran
 */

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitor MySQL - Sistema AppLink</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .status-ok { color: #28a745; background: #d4edda; border-left: 4px solid #28a745; padding: 10px; margin: 10px 0; }
        .status-warning { color: #856404; background: #fff3cd; border-left: 4px solid #ffc107; padding: 10px; margin: 10px 0; }
        .status-error { color: #721c24; background: #f8d7da; border-left: 4px solid #dc3545; padding: 10px; margin: 10px 0; }
        .metric { display: inline-block; margin: 10px; padding: 15px; background: #f8f9fa; border-radius: 5px; min-width: 150px; text-align: center; }
        .metric-value { font-size: 24px; font-weight: bold; color: #007bff; }
        .metric-label { font-size: 12px; color: #6c757d; text-transform: uppercase; }
        .recommendations { background: #e7f3ff; padding: 15px; border-radius: 5px; margin: 20px 0; }
        .btn { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; text-decoration: none; display: inline-block; }
        .btn:hover { background: #0056b3; }
        .btn-success { background: #28a745; }
        .btn-warning { background: #ffc107; color: #212529; }
        .btn-danger { background: #dc3545; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Monitor de Estado MySQL</h1>
        <p><strong>Sistema:</strong> <?= $_SERVER['HTTP_HOST'] ?> | <strong>Hora:</strong> <?= date('Y-m-d H:i:s') ?></p>
        
        <?php
        $status = [];
        $recommendations = [];
        
        // 1. Test de conexión básica
        try {
            $pdo = new PDO("mysql:host=localhost", "root", "");
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $status['connection'] = ['status' => 'ok', 'message' => 'Conexión MySQL exitosa'];
            
            // 2. Verificar base de datos
            $stmt = $pdo->query("SHOW DATABASES LIKE 'fs_clientes'");
            if ($stmt->rowCount() > 0) {
                $status['database'] = ['status' => 'ok', 'message' => 'Base de datos fs_clientes encontrada'];
                
                // 3. Conectar a la base específica y obtener métricas
                $pdo_db = new PDO("mysql:host=localhost;dbname=fs_clientes", "root", "");
                
                // Contar registros
                $users = $pdo_db->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();
                $products = $pdo_db->query("SELECT COUNT(*) FROM fs_productos")->fetchColumn();
                $clients = $pdo_db->query("SELECT COUNT(*) FROM fs_clientes")->fetchColumn();
                
                $status['data'] = ['status' => 'ok', 'message' => "Datos: $users usuarios, $products productos, $clients clientes"];
                
            } else {
                $status['database'] = ['status' => 'error', 'message' => 'Base de datos fs_clientes no encontrada'];
                $recommendations[] = "Ejecutar el script de migración de base de datos";
            }
            
        } catch (PDOException $e) {
            $status['connection'] = ['status' => 'error', 'message' => 'Error MySQL: ' . $e->getMessage()];
            $recommendations[] = "Verificar que MySQL esté iniciado en XAMPP Control Panel";
            $recommendations[] = "Ejecutar mantenimiento_mysql.bat si persisten los errores";
        }
        
        // 4. Verificar archivos problemáticos
        $data_dir = 'C:\\xampp\\mysql\\data\\';
        $problematic_files = 0;
        if (is_dir($data_dir)) {
            $files = glob($data_dir . '*relay-bin*');
            $files = array_merge($files, glob($data_dir . 'DESKTOP*'));
            $files = array_merge($files, glob($data_dir . '*master*'));
            $problematic_files = count($files);
        }
        
        if ($problematic_files > 0) {
            $status['files'] = ['status' => 'warning', 'message' => "$problematic_files archivos problemáticos detectados"];
            $recommendations[] = "Ejecutar limpieza automática con mantenimiento_mysql.bat";
        } else {
            $status['files'] = ['status' => 'ok', 'message' => 'No se detectaron archivos problemáticos'];
        }
        
        // 5. Verificar espacio en disco
        $free_space = disk_free_space('C:');
        $total_space = disk_total_space('C:');
        $used_percent = (($total_space - $free_space) / $total_space) * 100;
        
        if ($used_percent > 90) {
            $status['disk'] = ['status' => 'error', 'message' => 'Espacio en disco crítico: ' . round($used_percent, 1) . '%'];
            $recommendations[] = "Liberar espacio en disco urgentemente";
        } elseif ($used_percent > 80) {
            $status['disk'] = ['status' => 'warning', 'message' => 'Espacio en disco bajo: ' . round($used_percent, 1) . '%'];
            $recommendations[] = "Considerar liberar espacio en disco";
        } else {
            $status['disk'] = ['status' => 'ok', 'message' => 'Espacio en disco OK: ' . round($used_percent, 1) . '% usado'];
        }
        
        // Mostrar estado general
        $overall_status = 'ok';
        foreach ($status as $check) {
            if ($check['status'] == 'error') {
                $overall_status = 'error';
                break;
            } elseif ($check['status'] == 'warning' && $overall_status != 'error') {
                $overall_status = 'warning';
            }
        }
        ?>
        
        <div class="status-<?= $overall_status ?>">
            <h3>
                <?php if ($overall_status == 'ok'): ?>
                    ✅ Sistema funcionando correctamente
                <?php elseif ($overall_status == 'warning'): ?>
                    ⚠️ Sistema con advertencias - Revisar recomendaciones
                <?php else: ?>
                    ❌ Sistema con errores críticos
                <?php endif; ?>
            </h3>
        </div>
        
        <h3>📊 Métricas del Sistema</h3>
        <div>
            <?php if (isset($users)): ?>
            <div class="metric">
                <div class="metric-value"><?= $users ?></div>
                <div class="metric-label">Usuarios</div>
            </div>
            <div class="metric">
                <div class="metric-value"><?= $products ?></div>
                <div class="metric-label">Productos</div>
            </div>
            <div class="metric">
                <div class="metric-value"><?= $clients ?></div>
                <div class="metric-label">Clientes</div>
            </div>
            <?php endif; ?>
            <div class="metric">
                <div class="metric-value"><?= round($used_percent, 1) ?>%</div>
                <div class="metric-label">Disco Usado</div>
            </div>
            <div class="metric">
                <div class="metric-value"><?= $problematic_files ?></div>
                <div class="metric-label">Archivos Problemáticos</div>
            </div>
        </div>
        
        <h3>🔍 Detalles de Verificación</h3>
        <?php foreach ($status as $key => $check): ?>
        <div class="status-<?= $check['status'] ?>">
            <strong><?= ucfirst($key) ?>:</strong> <?= $check['message'] ?>
        </div>
        <?php endforeach; ?>
        
        <?php if (!empty($recommendations)): ?>
        <div class="recommendations">
            <h3>💡 Recomendaciones</h3>
            <ul>
                <?php foreach ($recommendations as $rec): ?>
                <li><?= $rec ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>
        
        <h3>🛠️ Herramientas de Mantenimiento</h3>
        <a href="mantenimiento_mysql.bat" class="btn btn-warning" download>⬇️ Descargar Script de Limpieza</a>
        <a href="test_connection.php" class="btn">🔗 Test de Conexión Simple</a>
        <a href="public/" class="btn btn-success">🚀 Ir al Sistema</a>
        
        <div style="margin-top: 30px; padding: 15px; background: #f8f9fa; border-radius: 5px; font-size: 12px; color: #6c757d;">
            <strong>Consejo:</strong> Ejecuta este monitor una vez por semana para detectar problemas antes de que ocurran.
            <br><strong>Automatización:</strong> Programa mantenimiento_mysql.bat para ejecutarse cada domingo.
        </div>
    </div>
</body>
</html>