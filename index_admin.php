<?php include("bd.php");
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// Verificar que sea administrador
if (!isset($_SESSION['usuario']) || ($_SESSION['tipo_usuario'] ?? '') !== 'admin') {
    header("Location: login.php");
    exit;
}
?>
<?php include("templates/header.php");?>

<div class="p-5 mb-4 bg-light rounded-3">
    <div class="container py-5">
        <div class="row align-items-center g-4">
            <div class="col-lg-7">
                <h1 class="display-5 fw-bold">Dashboard Administrador</h1>
                <p class="fs-5 text-secondary mb-4">
                    Bienvenido: <?php echo htmlspecialchars($_SESSION['usuario']); ?>
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Estadísticas -->
<div class="row mb-5">
    <?php
    // Total de productos
    $stmt_productos = $conexion->query("SELECT COUNT(*) as total FROM productos");
    $total_productos = $stmt_productos->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Total de clientes
    $stmt_clientes = $conexion->query("SELECT COUNT(*) as total FROM clientes");
    $total_clientes = $stmt_clientes->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Total de pedidos
    $stmt_pedidos = $conexion->query("SELECT COUNT(*) as total FROM pedidos");
    $total_pedidos = $stmt_pedidos->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Total de ventas
    $stmt_ventas = $conexion->query("SELECT SUM(monto_total) as total FROM pagos WHERE estado = 'Completado'");
    $total_ventas = $stmt_ventas->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    
    // Pedidos pendientes
    $stmt_pendientes = $conexion->query("SELECT COUNT(*) as total FROM pedidos WHERE estado = 'Pendiente'");
    $total_pendientes = $stmt_pendientes->fetch(PDO::FETCH_ASSOC)['total'];
    ?>
    
    <div class="col-md-4 mb-4">
        <div class="card text-white bg-primary">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4><?php echo $total_productos; ?></h4>
                        <p class="mb-0">Productos</p>
                    </div>
                    <i class="bi bi-box-seam" style="font-size: 48px; opacity: 0.5;"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-4">
        <div class="card text-white bg-success">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4><?php echo $total_clientes; ?></h4>
                        <p class="mb-0">Clientes</p>
                    </div>
                    <i class="bi bi-people" style="font-size: 48px; opacity: 0.5;"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-4">
        <div class="card text-white bg-info">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4><?php echo $total_pedidos; ?></h4>
                        <p class="mb-0">Pedidos</p>
                    </div>
                    <i class="bi bi-cart-check" style="font-size: 48px; opacity: 0.5;"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 mb-4">
        <div class="card text-white bg-warning">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4>$<?php echo number_format($total_ventas, 2); ?></h4>
                        <p class="mb-0">Total Ventas</p>
                    </div>
                    <i class="bi bi-currency-dollar" style="font-size: 48px; opacity: 0.5;"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 mb-4">
        <div class="card text-white bg-danger">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4><?php echo $total_pendientes; ?></h4>
                        <p class="mb-0">Pedidos Pendientes</p>
                    </div>
                    <i class="bi bi-hourglass-split" style="font-size: 48px; opacity: 0.5;"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Módulos de mantenimiento -->
<div class="card">
    <div class="card-header">
        <h4>Módulos de Mantenimiento</h4>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4 mb-3">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-tags" style="font-size: 48px; color: #0066cc;"></i>
                        <h5 class="mt-3">Categorías</h5>
                        <p class="text-muted">Gestionar categorías de productos</p>
                        <a href="secciones/categorias/index.php" class="btn btn-primary">Acceder</a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 mb-3">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-box" style="font-size: 48px; color: #0066cc;"></i>
                        <h5 class="mt-3">Productos</h5>
                        <p class="text-muted">Gestionar inventario de productos</p>
                        <a href="secciones/Productos/index.php" class="btn btn-primary">Acceder</a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 mb-3">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-people" style="font-size: 48px; color: #0066cc;"></i>
                        <h5 class="mt-3">Clientes</h5>
                        <p class="text-muted">Gestionar información de clientes</p>
                        <a href="secciones/clientes/index.php" class="btn btn-primary">Acceder</a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 mb-3">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-cart" style="font-size: 48px; color: #0066cc;"></i>
                        <h5 class="mt-3">Pedidos</h5>
                        <p class="text-muted">Gestionar pedidos y órdenes</p>
                        <a href="secciones/pedidos/index.php" class="btn btn-primary">Acceder</a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 mb-3">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-shop" style="font-size: 48px; color: #0066cc;"></i>
                        <h5 class="mt-3">Tiendas</h5>
                        <p class="text-muted">Gestionar ubicaciones de tiendas</p>
                        <a href="secciones/tiendas/index.php" class="btn btn-primary">Acceder</a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 mb-3">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-clipboard-data" style="font-size: 48px; color: #0066cc;"></i>
                        <h5 class="mt-3">Inventario</h5>
                        <p class="text-muted">Control de inventario</p>
                        <a href="secciones/Inventario/index.php" class="btn btn-primary">Acceder</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include("templates/footer.php");?>
