<?php
/**
 * Dashboard de Auditoría - Sistema de Ventas AppLink
 * Visualización de toda la actividad registrada automáticamente
 */

require_once __DIR__ . '/config/app.php';

$config = include __DIR__ . '/config/app.php';

try {
    $pdo = new PDO(
        "mysql:host={$config['db']['host']};dbname={$config['db']['name']};charset={$config['db']['charset']}", 
        $config['db']['user'], 
        $config['db']['pass']
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Obtener métricas del día
    $metricas_hoy = $pdo->query("
        SELECT * FROM metricas_diarias 
        WHERE fecha = CURDATE()
    ")->fetch(PDO::FETCH_ASSOC);
    
    if (!$metricas_hoy) {
        $metricas_hoy = [
            'usuarios_nuevos' => 0,
            'clientes_nuevos' => 0,
            'productos_nuevos' => 0,
            'ventas_realizadas' => 0,
            'logins_exitosos' => 0,
            'logins_fallidos' => 0
        ];
    }
    
    // Obtener actividad reciente
    $actividad_reciente = $pdo->query("
        SELECT 
            tabla,
            accion,
            registro_id,
            usuario_nombre,
            observaciones,
            fecha_hora,
            CASE 
                WHEN tabla = 'usuarios' THEN '👤'
                WHEN tabla = 'fs_clientes' THEN '🏢'
                WHEN tabla = 'fs_productos' THEN '📦'
                WHEN tabla = 'fs_ventas' THEN '💰'
                ELSE '📝'
            END as icono
        FROM auditoria_general 
        ORDER BY fecha_hora DESC 
        LIMIT 20
    ")->fetchAll(PDO::FETCH_ASSOC);
    
    // Métricas de los últimos 7 días
    $metricas_semana = $pdo->query("
        SELECT 
            fecha,
            usuarios_nuevos,
            clientes_nuevos,
            productos_nuevos,
            logins_exitosos
        FROM metricas_diarias 
        WHERE fecha >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
        ORDER BY fecha ASC
    ")->fetchAll(PDO::FETCH_ASSOC);
    
    // Contadores generales
    $total_registros = $pdo->query("SELECT COUNT(*) FROM auditoria_general")->fetchColumn();
    $registros_hoy = $pdo->query("SELECT COUNT(*) FROM auditoria_general WHERE DATE(fecha_hora) = CURDATE()")->fetchColumn();
    
} catch (PDOException $e) {
    $error_message = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard de Auditoría - Sistema AppLink</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container { 
            max-width: 1200px; 
            margin: 0 auto; 
            background: white; 
            border-radius: 15px; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        .header { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white; 
            padding: 30px; 
            text-align: center; 
        }
        .header h1 { font-size: 2.5em; margin-bottom: 10px; }
        .header p { font-size: 1.1em; opacity: 0.9; }
        
        .metrics-grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); 
            gap: 20px; 
            padding: 30px; 
        }
        .metric-card { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white; 
            padding: 25px; 
            border-radius: 15px; 
            text-align: center; 
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        .metric-card:hover { transform: translateY(-5px); }
        .metric-number { font-size: 2.5em; font-weight: bold; margin-bottom: 10px; }
        .metric-label { font-size: 0.9em; opacity: 0.9; text-transform: uppercase; letter-spacing: 1px; }
        
        .section { padding: 0 30px 30px; }
        .section h2 { 
            color: #333; 
            margin-bottom: 20px; 
            padding-bottom: 10px; 
            border-bottom: 2px solid #667eea;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .activity-list { 
            background: #f8f9fa; 
            border-radius: 10px; 
            max-height: 400px; 
            overflow-y: auto; 
        }
        .activity-item { 
            padding: 15px 20px; 
            border-bottom: 1px solid #dee2e6; 
            display: flex; 
            align-items: center; 
            gap: 15px;
            transition: background 0.3s ease;
        }
        .activity-item:hover { background: #e9ecef; }
        .activity-item:last-child { border-bottom: none; }
        
        .activity-icon { 
            font-size: 1.5em; 
            width: 40px; 
            height: 40px; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            background: white; 
            border-radius: 50%; 
            box-shadow: 0 2px 5px rgba(0,0,0,0.1); 
        }
        .activity-details { flex: 1; }
        .activity-title { font-weight: bold; color: #333; margin-bottom: 5px; }
        .activity-description { color: #666; font-size: 0.9em; }
        .activity-time { color: #999; font-size: 0.8em; white-space: nowrap; }
        
        .chart-container { 
            background: #f8f9fa; 
            padding: 20px; 
            border-radius: 10px; 
            margin-bottom: 20px; 
        }
        
        .no-data { 
            text-align: center; 
            padding: 40px; 
            color: #666; 
        }
        
        .actions { 
            background: #f8f9fa; 
            padding: 20px 30px; 
            text-align: center; 
        }
        .btn { 
            background: #667eea; 
            color: white; 
            padding: 12px 24px; 
            border: none; 
            border-radius: 25px; 
            text-decoration: none; 
            display: inline-block; 
            margin: 5px; 
            transition: all 0.3s ease;
            font-weight: 500;
        }
        .btn:hover { 
            background: #764ba2; 
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        .btn-success { background: #28a745; }
        .btn-success:hover { background: #218838; }
        .btn-info { background: #17a2b8; }
        .btn-info:hover { background: #138496; }
        
        .error-message { 
            background: #f8d7da; 
            color: #721c24; 
            padding: 20px; 
            margin: 20px; 
            border-radius: 10px; 
            border-left: 5px solid #dc3545; 
        }
        
        @media (max-width: 768px) {
            .metrics-grid { grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); }
            .activity-item { flex-direction: column; text-align: center; }
            .activity-time { margin-top: 10px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📊 Dashboard de Auditoría</h1>
            <p>Sistema de Seguimiento Automático - Toda la actividad registrada en tiempo real</p>
            <p><strong>Última actualización:</strong> <?= date('d/m/Y H:i:s') ?></p>
        </div>

        <?php if (isset($error_message)): ?>
        <div class="error-message">
            <h3>❌ Error de Conexión</h3>
            <p><strong>Error:</strong> <?= htmlspecialchars($error_message) ?></p>
            <p><strong>Solución:</strong> 
                <a href="instalar_auditoria.php" style="color: #721c24; text-decoration: underline;">
                    Ejecutar instalación del sistema de auditoría
                </a>
            </p>
        </div>
        <?php else: ?>

        <!-- MÉTRICAS DEL DÍA -->
        <div class="metrics-grid">
            <div class="metric-card">
                <div class="metric-number"><?= $metricas_hoy['usuarios_nuevos'] ?></div>
                <div class="metric-label">Usuarios Nuevos Hoy</div>
            </div>
            <div class="metric-card">
                <div class="metric-number"><?= $metricas_hoy['clientes_nuevos'] ?></div>
                <div class="metric-label">Clientes Nuevos Hoy</div>
            </div>
            <div class="metric-card">
                <div class="metric-number"><?= $metricas_hoy['productos_nuevos'] ?></div>
                <div class="metric-label">Productos Nuevos Hoy</div>
            </div>
            <div class="metric-card">
                <div class="metric-number"><?= $metricas_hoy['logins_exitosos'] ?></div>
                <div class="metric-label">Logins Exitosos Hoy</div>
            </div>
            <div class="metric-card">
                <div class="metric-number"><?= $registros_hoy ?></div>
                <div class="metric-label">Actividades Hoy</div>
            </div>
            <div class="metric-card">
                <div class="metric-number"><?= $total_registros ?></div>
                <div class="metric-label">Total Registros</div>
            </div>
        </div>

        <!-- ACTIVIDAD RECIENTE -->
        <div class="section">
            <h2>🕒 Actividad Reciente</h2>
            <?php if (empty($actividad_reciente)): ?>
            <div class="no-data">
                <h3>📝 No hay actividad registrada aún</h3>
                <p>Las nuevas actividades aparecerán aquí automáticamente</p>
            </div>
            <?php else: ?>
            <div class="activity-list">
                <?php foreach ($actividad_reciente as $actividad): ?>
                <div class="activity-item">
                    <div class="activity-icon"><?= $actividad['icono'] ?></div>
                    <div class="activity-details">
                        <div class="activity-title">
                            <?= ucfirst($actividad['accion']) ?> en <?= $actividad['tabla'] ?> 
                            (ID: <?= $actividad['registro_id'] ?>)
                        </div>
                        <div class="activity-description">
                            <?= htmlspecialchars($actividad['observaciones']) ?>
                        </div>
                        <div style="margin-top: 5px; font-size: 0.8em; color: #999;">
                            Por: <?= htmlspecialchars($actividad['usuario_nombre']) ?>
                        </div>
                    </div>
                    <div class="activity-time">
                        <?= date('d/m/Y H:i', strtotime($actividad['fecha_hora'])) ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- GRÁFICO DE TENDENCIAS -->
        <div class="section">
            <h2>📈 Tendencias de los Últimos 7 Días</h2>
            <?php if (empty($metricas_semana)): ?>
            <div class="no-data">
                <h3>📊 No hay datos históricos disponibles</h3>
                <p>Los datos se acumularán automáticamente día a día</p>
            </div>
            <?php else: ?>
            <div class="chart-container">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #667eea; color: white;">
                            <th style="padding: 10px; text-align: left;">Fecha</th>
                            <th style="padding: 10px; text-align: center;">👤 Usuarios</th>
                            <th style="padding: 10px; text-align: center;">🏢 Clientes</th>
                            <th style="padding: 10px; text-align: center;">📦 Productos</th>
                            <th style="padding: 10px; text-align: center;">🔑 Logins</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($metricas_semana as $dia): ?>
                        <tr style="border-bottom: 1px solid #dee2e6;">
                            <td style="padding: 10px; font-weight: bold;">
                                <?= date('d/m/Y', strtotime($dia['fecha'])) ?>
                            </td>
                            <td style="padding: 10px; text-align: center;"><?= $dia['usuarios_nuevos'] ?></td>
                            <td style="padding: 10px; text-align: center;"><?= $dia['clientes_nuevos'] ?></td>
                            <td style="padding: 10px; text-align: center;"><?= $dia['productos_nuevos'] ?></td>
                            <td style="padding: 10px; text-align: center;"><?= $dia['logins_exitosos'] ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>

        <?php endif; ?>

        <!-- ACCIONES -->
        <div class="actions">
            <a href="instalar_auditoria.php" class="btn">🔧 Reinstalar Sistema</a>
            <a href="reportes_auditoria.php" class="btn btn-info">📋 Ver Reportes Detallados</a>
            <a href="public/" class="btn btn-success">🚀 Ir al Sistema Principal</a>
            
            <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #dee2e6; color: #666; font-size: 0.9em;">
                <p><strong>Sistema Automático:</strong> Los datos se actualizan en tiempo real sin intervención manual</p>
                <p><strong>Refresh:</strong> <a href="javascript:location.reload()" style="color: #667eea;">Actualizar página</a> para ver los últimos cambios</p>
            </div>
        </div>
    </div>

    <script>
        // Auto-refresh cada 30 segundos
        setTimeout(function() {
            location.reload();
        }, 30000);
        
        // Mostrar notificación si hay nueva actividad
        if (<?= $registros_hoy ?> > 0) {
            console.log('✅ Sistema de auditoría activo - <?= $registros_hoy ?> registros hoy');
        }
    </script>
</body>
</html>