<?php include("../../bd.php");

if(!isset($_GET['tienda_id']) || !is_numeric($_GET['tienda_id'])){
    $mensaje = "Error: ID de tienda inválido";
    header("Location:index.php?mensaje=".$mensaje);
    exit;
}

$tienda_id = $_GET['tienda_id'];

try {
    // Obtener información de la tienda
    $sentencia_tienda = $conexion->prepare("SELECT * FROM tiendas WHERE tienda_id = :tienda_id");
    $sentencia_tienda->bindParam(":tienda_id", $tienda_id);
    $sentencia_tienda->execute();
    $tienda = $sentencia_tienda->fetch(PDO::FETCH_ASSOC);
    
    if(!$tienda){
        $mensaje = "Error: Tienda no encontrada";
        header("Location:index.php?mensaje=".$mensaje);
        exit;
    }
    
    // Obtener productos del inventario de esta tienda
    $sentencia_inventario = $conexion->prepare("SELECT 
        i.tienda_id,
        i.product_id,
        i.cantidad,
        t.nombre_tienda,
        t.ciudad,
        p.product_name,
        p.foto
    FROM inventario i
    INNER JOIN tiendas t ON i.tienda_id = t.tienda_id
    INNER JOIN productos p ON i.product_id = p.product_id
    WHERE i.tienda_id = :tienda_id
    ORDER BY p.product_name");
    $sentencia_inventario->bindParam(":tienda_id", $tienda_id);
    $sentencia_inventario->execute();
    $lista_inventario = $sentencia_inventario->fetchAll(PDO::FETCH_ASSOC);
    
} catch(Exception $e) {
    $mensaje = "Error al cargar datos: " . $e->getMessage();
    header("Location:index.php?mensaje=".$mensaje);
    exit;
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

<h2>Inventario por Tienda - <?php echo htmlspecialchars($tienda['nombre_tienda']); ?></h2>
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <a class="btn btn-outline-primary" href="crear.php">Nuevo</a>
        </div>
        <div>
            <a class="btn btn-outline-primary" href="index.php">Volver al Inventario</a>
            <a class="btn btn-outline-primary" href="../../index.php">Inicio</a>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive-sm">
            <table class="table table-primary table-striped align-middle">
                <thead>
                    <tr>
                        <th scope="col">Tienda</th>
                        <th scope="col">Producto</th>
                        <th scope="col">Stock</th>
                        <th scope="col">Estado</th>
                        <th scope="col">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($lista_inventario as $registro) { ?>
                    <tr class="">
                        <td><?php echo htmlspecialchars($registro['nombre_tienda'] . ' - ' . $registro['ciudad']); ?></td>
                        <td>
                            <?php echo htmlspecialchars($registro['product_name']); ?>
                        </td>
                        <td>
                            <span class="badge <?php echo ($registro['cantidad'] > 10) ? 'bg-success' : (($registro['cantidad'] > 0) ? 'bg-warning' : 'bg-danger'); ?>">
                                <?php echo $registro['cantidad']; ?> unidades
                            </span>
                        </td>
                        <td>
                            <?php if($registro['cantidad'] > 10) { ?>
                                <span class="text-success">✓ Stock Alto</span>
                            <?php } elseif($registro['cantidad'] > 0) { ?>
                                <span class="text-warning">⚠ Stock Bajo</span>
                            <?php } else { ?>
                                <span class="text-danger">✗ Sin Stock</span>
                            <?php } ?>
                        </td>
                        <td>
                            <a class="btn btn-outline-primary btn-sm" href="editar.php?tienda_id=<?php echo $registro['tienda_id']; ?>&product_id=<?php echo $registro['product_id']; ?>" role="button">Editar</a>
                        </td>
                    </tr>
                    <?php } ?>
                    <?php if (empty($lista_inventario)) { ?>
                        <tr><td colspan="5" class="text-center">No hay registros en el inventario de esta tienda</td></tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include("../../templates/footer.php");?>
