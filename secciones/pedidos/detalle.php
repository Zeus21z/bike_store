<?php include("../../bd.php");
$txtID = isset($_GET['txtID'])?$_GET['txtID']:'';
if($txtID===''){ header("Location:index.php?mensaje=ID requerido"); exit; }

// Cabecera del pedido
$stmt=$conexion->prepare("SELECT p.*, c.nombre, c.apellido, u.usuario FROM pedidos p INNER JOIN clientes c ON c.cliente_id=p.cliente_id INNER JOIN usuarios u ON u.user_id=p.usuario_id WHERE p.pedido_id=:id");
$stmt->bindParam(":id", $txtID);
$stmt->execute();
$pedido=$stmt->fetch(PDO::FETCH_ASSOC);
if(!$pedido){ header("Location:index.php?mensaje=Pedido no encontrado"); exit; }

// Obtener solo los detalles del pedido específico
$stmt_detalles = $conexion->prepare("SELECT d.*, pr.product_name FROM detalles_pedido d INNER JOIN productos pr ON pr.product_id=d.producto_id WHERE d.pedido_id=:pedido_id");
$stmt_detalles->bindParam(":pedido_id", $txtID);
$stmt_detalles->execute();
$items = $stmt_detalles->fetchAll(PDO::FETCH_ASSOC);

// Calcular total del pedido
$total_pedido = 0;
foreach($items as $item) {
    $cantidad = isset($item['cantidad']) ? (float)$item['cantidad'] : 0;
    $precio = isset($item['precio']) ? (float)$item['precio'] : 0;
    $descuento = isset($item['descuento']) ? (float)$item['descuento'] : 0;
    $subtotal = ($precio * $cantidad) - $descuento;
    $total_pedido += $subtotal;
}

?>
<?php include("../../templates/header.php");?>
<br>
<h2>Detalle del Pedido #<?php echo htmlspecialchars($txtID); ?></h2>
<div class="card">
    <div class="card-header">
        <div class="row">
            <div class="col-md-6">
                <strong>ID Pedido:</strong> #<?php echo htmlspecialchars($txtID); ?><br>
                <strong>Cliente:</strong> <?php echo htmlspecialchars($pedido['nombre'] . ' ' . $pedido['apellido']); ?><br>
                <strong>Fecha:</strong> <?php echo htmlspecialchars($pedido['fecha_pedido']); ?>
            </div>
            <div class="col-md-6 text-end">
                <strong>Estado:</strong> <span class="badge bg-primary"><?php echo htmlspecialchars($pedido['estado']); ?></span><br>
                <strong>Atendió:</strong> <?php echo htmlspecialchars($pedido['usuario']); ?>
            </div>
        </div>
        <div class="mt-2 text-end">
            <a class="btn btn-sm btn-outline-secondary" href="index.php">Volver a Pedidos</a>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive-sm">
            <table class="table table-primary table-striped align-middle">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th class="text-center">Cantidad</th>
                        <th class="text-end">Precio Unitario</th>
                        <th class="text-end">Descuento</th>
                        <th class="text-end">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($items as $it){ 
                        $cantidad = isset($it['cantidad']) ? (float)$it['cantidad'] : 0;
                        $precio = isset($it['precio']) ? (float)$it['precio'] : 0;
                        $descuento = isset($it['descuento']) ? (float)$it['descuento'] : 0;
                        $subtotal = ($precio * $cantidad) - $descuento;
                    ?>
                        <tr>
                            <td><?php echo htmlspecialchars($it['product_name'] ?? ''); ?></td>
                            <td class="text-center"><?php echo $it['cantidad'] ?? ''; ?></td>
                            <td class="text-end"><?php echo isset($it['precio']) ? '$' . number_format((float)$it['precio'], 2) : ''; ?></td>
                            <td class="text-end"><?php echo isset($it['descuento']) ? '$' . number_format((float)$it['descuento'], 2) : '$0.00'; ?></td>
                            <td class="text-end"><strong><?php echo '$' . number_format($subtotal, 2); ?></strong></td>
                        </tr>
                    <?php } ?>
                    <?php if(empty($items)){ ?>
                        <tr><td colspan="5" class="text-center">No hay items en este pedido</td></tr>
                    <?php } ?>
                    <?php if(!empty($items)){ ?>
                        <tr class="table-success">
                            <td colspan="4" class="text-end"><strong>TOTAL DEL PEDIDO</strong></td>
                            <td class="text-end"><strong><?php echo '$' . number_format($total_pedido, 2); ?></strong></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include("../../templates/footer.php");?>