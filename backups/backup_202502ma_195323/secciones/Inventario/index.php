<?php include("../../bd.php");

// Listado de inventario agrupado por tienda con total de stock
try {
    $sentencia = $conexion->prepare("SELECT 
        i.tienda_id,
        t.nombre_tienda,
        t.ciudad,
        SUM(i.cantidad) as total_stock,
        COUNT(i.product_id) as total_productos
    FROM inventario i
    INNER JOIN tiendas t ON i.tienda_id = t.tienda_id
    GROUP BY i.tienda_id, t.nombre_tienda, t.ciudad
    ORDER BY t.nombre_tienda");
    $sentencia->execute();
    $lista_inventario = $sentencia->fetchAll(PDO::FETCH_ASSOC);
} catch(Exception $e) {
    $mensaje = "Error al cargar inventario: " . $e->getMessage();
    $lista_inventario = [];
}
?>
<?php include("../../templates/header.php");?>
<br>

<?php if(isset($_GET['mensaje'])) { 
    $alert_class = (strpos($_GET['mensaje'], 'Error') === 0) ? 'alert-danger' : 'alert-success';
?>
    <div class="alert <?php echo $alert_class; ?>" role="alert">
        <?php echo htmlspecialchars($_GET['mensaje']); ?>
    </div>
<?php } ?>

<h2>Inventario por Tienda</h2>
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <a class="btn btn-outline-primary" href="crear.php">Nuevo</a>
        </div>
        <div>
            <a class="btn btn-outline-primary" href="../tiendas/">Volver a Tiendas</a>
            <a class="btn btn-outline-primary" href="../../index.php">Inicio</a>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive-sm">
            <table class="table table-primary table-striped align-middle">
                <thead>
                    <tr>
                        <th scope="col">Tienda</th>
                        <th scope="col">Total Stock</th>
                        <th scope="col">Productos</th>
                        <th scope="col">Estado</th>
                        <th scope="col">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($lista_inventario as $registro) { ?>
                    <tr class="">
                        <td><?php echo htmlspecialchars($registro['nombre_tienda'] . ' - ' . $registro['ciudad']); ?></td>
                        <td>
                            <span class="badge <?php echo ($registro['total_stock'] > 50) ? 'bg-success' : (($registro['total_stock'] > 0) ? 'bg-warning' : 'bg-danger'); ?>">
                                <?php echo $registro['total_stock']; ?> unidades
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-info">
                                <?php echo $registro['total_productos']; ?> productos
                            </span>
                        </td>
                        <td>
                            <?php if($registro['total_stock'] > 50) { ?>
                                <span class="text-success">✓ Stock Alto</span>
                            <?php } elseif($registro['total_stock'] > 0) { ?>
                                <span class="text-warning">⚠ Stock Bajo</span>
                            <?php } else { ?>
                                <span class="text-danger">✗ Sin Stock</span>
                            <?php } ?>
                        </td>
                        <td>
                            <a class="btn btn-outline-info btn-sm" href="detalle_inventario.php?tienda_id=<?php echo $registro['tienda_id']; ?>" role="button">Ver Detalles</a>
                        </td>
                    </tr>
                    <?php } ?>
                    <?php if (empty($lista_inventario)) { ?>
                        <tr><td colspan="5" class="text-center">No hay tiendas con inventario registrado</td></tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include("../../templates/footer.php");?>