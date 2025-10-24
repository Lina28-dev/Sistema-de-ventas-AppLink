<?php
/**
 * Template del Sidebar para el Sistema AppLink
 * Incluir en todas las páginas del dashboard
 */

// Verificar autenticación
if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
    header('Location: /Sistema-de-ventas-AppLink-main/public/');
    exit;
}

// Obtener información del usuario actual
$user_role = $_SESSION['user_role'] ?? 'cliente';
$user_nick = $_SESSION['user_nick'] ?? 'Usuario';
$user_name = $_SESSION['user_name'] ?? 'Usuario';
$is_admin = $_SESSION['is_admin'] ?? false;
$is_medium = $_SESSION['is_medium'] ?? false;

function getUserType() {
    global $user_role;
    switch($user_role) {
        case 'administrador': return 'Administrador';
        case 'empleado': return 'Empleado';
        case 'cliente': return 'Cliente';
        default: return 'Usuario';
    }
}

function getUserIcon() {
    global $user_role;
    switch($user_role) {
        case 'administrador': return 'fas fa-crown text-warning';
        case 'empleado': return 'fas fa-user-tie text-info';
        case 'cliente': return 'fas fa-user text-success';
        default: return 'fas fa-user text-secondary';
    }
}

function hasPermission($section) {
    global $user_role;
    switch($section) {
        case 'dashboard': return true;
        case 'ventas': return in_array($user_role, ['administrador', 'empleado']);
        case 'pedidos': return true;
        case 'inventario': return ($user_role === 'administrador');
        case 'reportes': return in_array($user_role, ['administrador', 'empleado']);
        case 'usuarios': return ($user_role === 'administrador');
        default: return false;
    }
}

// Definir elementos del menú
$menu_items = [
    [
        'title' => 'Dashboard',
        'url' => '/Sistema-de-ventas-AppLink-main/public/dashboard',
        'icon' => 'fas fa-home',
        'permission' => 'dashboard'
    ],
    [
        'title' => 'Ventas',
        'url' => '/Sistema-de-ventas-AppLink-main/public/ventas',
        'icon' => 'fas fa-shopping-cart',
        'permission' => 'ventas'
    ],
    [
        'title' => 'Pedidos',
        'url' => '/Sistema-de-ventas-AppLink-main/public/pedidos',
        'icon' => 'fas fa-clipboard-list',
        'permission' => 'pedidos'
    ],
    [
        'title' => 'Inventario',
        'url' => '/Sistema-de-ventas-AppLink-main/public/inventario',
        'icon' => 'fas fa-boxes',
        'permission' => 'inventario'
    ],
    [
        'title' => 'Reportes',
        'url' => '/Sistema-de-ventas-AppLink-main/public/reportes',
        'icon' => 'fas fa-chart-bar',
        'permission' => 'reportes'
    ],
    [
        'title' => 'Usuarios',
        'url' => '/Sistema-de-ventas-AppLink-main/public/usuarios',
        'icon' => 'fas fa-users',
        'permission' => 'usuarios'
    ]
];
?>

<!-- Sidebar -->
<nav class="col-md-2 d-md-block sidebar">
    <div class="position-sticky pt-3">
        <div class="text-center mb-4">
            <img src="/Sistema-de-ventas-AppLink-main/public/assets/images/logo.jpg" alt="logo" class="img-fluid mb-2" style="max-height:80px;object-fit:contain;">
            <div class="user-info mt-3">
                <div class="role-badge mb-2" style="background: linear-gradient(135deg, #e91e63, #ff4081); color: white; padding: 0.4rem 0.8rem; border-radius: 15px; font-size: 0.8rem;">
                    <i class="<?php echo getUserIcon(); ?> me-1"></i>
                    <?php echo getUserType(); ?>
                </div>
                <small class="text-white-50"><?php echo htmlspecialchars($user_name); ?></small>
            </div>
        </div>
        <ul class="nav flex-column">
            <?php foreach ($menu_items as $item): ?>
                <?php if (hasPermission($item['permission'])): ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo (strpos($_SERVER['REQUEST_URI'], $item['url']) !== false) ? 'active' : ''; ?>" href="<?php echo $item['url']; ?>">
                            <i class="<?php echo $item['icon']; ?>"></i> <?php echo $item['title']; ?>
                        </a>
                    </li>
                <?php endif; ?>
            <?php endforeach; ?>
            <li class="nav-item mt-4">
                <a class="nav-link text-danger" href="/Sistema-de-ventas-AppLink-main/public/logout">
                    <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                </a>
            </li>
        </ul>
    </div>
</nav>