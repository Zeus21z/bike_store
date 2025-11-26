<?php
include("bd.php");
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// Solo clientes logueados pueden ver sus pedidos
if (!isset($_SESSION['usuario']) || ($_SESSION['tipo_usuario'] ?? '') !== 'cliente') {
    header("Location: index_cliente.php");
    exit;
}

$cliente_id = $_SESSION['cliente_id'] ?? 0;

// Obtener pedidos del cliente
$stmt_pedidos = $conexion->prepare("
    SELECT p.*, 
           (SELECT SUM(dp.cantidad) FROM detalles_pedido dp WHERE dp.pedido_id = p.pedido_id) as total_items,
           (SELECT SUM(dp.precio * dp.cantidad * (1 - dp.descuento/100)) FROM detalles_pedido dp WHERE dp.pedido_id = p.pedido_id) as total_monto
    FROM pedidos p 
    WHERE p.cliente_id = :cliente_id 
    ORDER BY p.fecha_pedido DESC
");
$stmt_pedidos->execute([':cliente_id' => $cliente_id]);
$pedidos = $stmt_pedidos->fetchAll(PDO::FETCH_ASSOC);
?>
<?php include("templates/header_cliente.php"); ?>

<div class="container">
    <h1 class="mb-4">Mis Pedidos</h1>
    
    <?php if (empty($pedidos)): ?>
    <div class="alert alert-info" role="alert">
        No tienes pedidos registrados. <a href="<?php echo $url_base; ?>productos_categoria.php">Ver productos</a>
    </div>
    <?php else: ?>
    
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>N° Pedido</th>
                    <th>Fecha</th>
                    <th>Items</th>
                    <th>Total</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($pedidos as $pedido): ?>
                <tr>
                    <td>#<?php echo str_pad($pedido['pedido_id'], 6, '0', STR_PAD_LEFT); ?></td>
                    <td><?php echo date('d/m/Y', strtotime($pedido['fecha_pedido'])); ?></td>
                    <td><?php echo $pedido['total_items'] ?? 0; ?> items</td>
                    <td>$<?php echo number_format($pedido['total_monto'] ?? 0, 2); ?></td>
                    <td>
                        <?php 
                        $estado_badge = [
                            'Activo' => 'primary',
                            'Pendiente' => 'warning',
                            'Entregado' => 'success',
                            'Cancelado' => 'danger'
                        ];
                        $badge_class = $estado_badge[$pedido['estado']] ?? 'secondary';
                        ?>
                        <span class="badge bg-<?php echo $badge_class; ?>"><?php echo htmlspecialchars($pedido['estado']); ?></span>
                    </td>
                    <td>
                        <a href="secciones/pedidos/detalle.php?txtID=<?php echo $pedido['pedido_id']; ?>" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-eye"></i> Ver
                        </a>
                        <?php if($pedido['estado'] === 'Entregado'): ?>
                        <a href="<?php echo $url_base; ?>factura.php?pedido_id=<?php echo $pedido['pedido_id']; ?>" class="btn btn-sm btn-outline-success">
                            <i class="bi bi-receipt"></i> Factura
                        </a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <?php endif; ?>
</div>

<?php include("templates/footer.php"); ?>
