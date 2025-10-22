<?php
// Sidebar Lilipink centralizado
?>
<nav class="col-md-2 sidebar p-3">
    <h4 class="text-white text-center mb-4">
        <img src="/Sistema-de-ventas-AppLink-main/public/assets/images/logo.jpg" class="img-fluid" style="max-width: 120px;">
    </h4>
    <ul class="nav flex-column">
        <li class="nav-item"><a class="nav-link<?php echo ($activePage=='dashboard')?' active':''; ?>" href="/Sistema-de-ventas-AppLink-main/public/dashboard"><i class="fas fa-home"></i> Dashboard</a></li>
        <li class="nav-item"><a class="nav-link<?php echo ($activePage=='ventas')?' active':''; ?>" href="/Sistema-de-ventas-AppLink-main/public/ventas"><i class="fas fa-shopping-cart"></i> Ventas</a></li>
        <li class="nav-item"><a class="nav-link<?php echo ($activePage=='clientes')?' active':''; ?>" href="/Sistema-de-ventas-AppLink-main/public/clientes"><i class="fas fa-users"></i> Clientes</a></li>
        <li class="nav-item"><a class="nav-link<?php echo ($activePage=='pedidos')?' active':''; ?>" href="/Sistema-de-ventas-AppLink-main/public/pedidos"><i class="fas fa-box"></i> Pedidos</a></li>
        <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
        <li class="nav-item"><a class="nav-link<?php echo ($activePage=='usuarios')?' active':''; ?>" href="/Sistema-de-ventas-AppLink-main/public/usuarios"><i class="fas fa-user-cog"></i> Usuarios</a></li>
        <?php endif; ?>
        <li class="nav-item mt-5"><a class="nav-link text-danger" href="/Sistema-de-ventas-AppLink-main/public/logout"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a></li>
    </ul>
</nav>

