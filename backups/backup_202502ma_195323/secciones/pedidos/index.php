<?php include("../../bd.php");
// Anular pedido si se solicita
if (isset($_GET['entregar'])) {
    $pid = $_GET['entregar'];
    $upd = $conexion->prepare("UPDATE pedidos SET estado='Entregado' WHERE pedido_id=:id");
    $upd->bindParam(":id", $pid);
    $upd->execute();
    header("Location: index.php?mensaje=Pedido entregado");
    exit;
}
// Activar pedido si se solicita
if (isset($_GET['pendiente'])) {
    $pid = $_GET['pendiente'];
    $upd = $conexion->prepare("UPDATE pedidos SET estado='Pendiente' WHERE pedido_id=:id");
    $upd->bindParam(":id", $pid);
    $upd->execute();
    header("Location: index.php?mensaje=Pedido marcado como pendiente");
    exit;
}

// Listar pedidos con información de clientes, usuarios y productos
$sql = "SELECT p.pedido_id, p.fecha_pedido, p.estado, p.usuario_id,
               c.nombre as cliente_nombre, c.apellido as cliente_apellido,
               u.usuario as usuario_nombre,
               GROUP_CONCAT(DISTINCT pr.product_name ORDER BY pr.product_name SEPARATOR ', ') as productos
        FROM pedidos p
        LEFT JOIN clientes c ON c.cliente_id = p.cliente_id
        LEFT JOIN usuarios u ON u.user_id = p.usuario_id
        LEFT JOIN detalles_pedido dp ON dp.pedido_id = p.pedido_id
        LEFT JOIN productos pr ON pr.product_id = dp.producto_id
        GROUP BY p.pedido_id, p.fecha_pedido, p.estado, p.usuario_id, c.nombre, c.apellido, u.usuario
        ORDER BY p.pedido_id ASC";
$sentencia=$conexion->prepare($sql);
$sentencia->execute();
$lista_pedidos=$sentencia->fetchAll(PDO::FETCH_ASSOC);
?>
<?php include("../../templates/header.php");?>
<br>
<?php if(isset($_GET['mensaje'])) { ?>
    <div class="alert alert-success" role="alert">
        <?php echo htmlspecialchars($_GET['mensaje']); ?>
    </div>
<?php } ?>
<h2>Listado de Pedidos</h2>
<div class="card">
    
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <a class="btn btn-outline-primary" href="crear.php">Nuevo</a>
        </div>
        <div>
            <a class="btn btn-outline-primary" href="../../index.php">Inicio</a>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive-sm">
            <table class="table table-primary table-striped align-middle">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Fecha de pedido</th>
                        <th>Cliente</th>
                        <th>Productos</th>
                        <th>Estado</th>
                        <th>Usuario</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($lista_pedidos as $p) { ?>
                    <tr>
                        <td><?php echo $p['pedido_id']; ?></td>
                        <td><?php echo htmlspecialchars($p['fecha_pedido']); ?></td>
                        <td><?php echo htmlspecialchars(($p['cliente_nombre'] ?? '') . ' ' . ($p['cliente_apellido'] ?? '')); ?></td>
                        <td>
                            <?php if (!empty($p['productos'])) { ?>
                                <span class="badge bg-info"><?php echo htmlspecialchars($p['productos']); ?></span>
                            <?php } else { ?>
                                <span class="text-muted">Sin productos</span>
                            <?php } ?>
                        </td>
                        <td class="text-uppercase"><?php echo htmlspecialchars($p['estado']); ?></td>
                        <td>
                            <?php echo htmlspecialchars($p['usuario_nombre'] ?? 'N/A'); ?>
                        </td>
                        <td class="d-flex gap-2">
                            <a class="btn btn-sm btn-outline-primary" href="detalle.php?txtID=<?php echo $p['pedido_id']; ?>">Pedido Detalle</a>
                            <?php if (strtoupper($p['estado'])!== 'ENTREGADO') { ?>
                                <a class="btn btn-sm btn-outline-success" href="index.php?entregar=<?php echo $p['pedido_id']; ?>" onclick="return confirm('¿Marcar como entregado?');">Entregar</a>
                            <?php } else { ?>
                                <a class="btn btn-sm btn-outline-warning" href="index.php?pendiente=<?php echo $p['pedido_id']; ?>" onclick="return confirm('¿Marcar como pendiente?');">Pendiente</a>
                            <?php } ?>
                        </td>
                    </tr>
                    <?php } ?>
                    <?php if (empty($lista_pedidos)) { ?>
                        <tr><td colspan="7" class="text-center">Sin registros</td></tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include("../../templates/footer.php");?>

