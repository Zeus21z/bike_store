<?php include("../../bd.php");
$txtID = isset($_GET['txtID'])?$_GET['txtID']:'';
if($txtID!=='' && $_SERVER['REQUEST_METHOD']!== 'POST'){
    $stmt=$conexion->prepare("SELECT * FROM pedidos WHERE pedido_id=:id");
    $stmt->bindParam(":id", $txtID);
    $stmt->execute();
    $pedido=$stmt->fetch(PDO::FETCH_ASSOC);
    if(!$pedido){ header("Location:index.php?mensaje=Pedido no encontrado"); exit; }
}
if($_POST){
    $txtID=(isset($_POST['txtID'])?$_POST['txtID']:'');
    $cliente_id=(isset($_POST['cliente_id'])?$_POST['cliente_id']:'');
    $fecha_pedido=(isset($_POST['fecha_pedido'])?$_POST['fecha_pedido']:'');
    $usuario_id=(isset($_POST['usuario_id'])?$_POST['usuario_id']:'');
    $estado=(isset($_POST['estado'])?$_POST['estado']:'Activo');
    $upd=$conexion->prepare("UPDATE pedidos SET cliente_id=:cliente_id, fecha_pedido=:fecha_pedido, usuario_id=:usuario_id, estado=:estado WHERE pedido_id=:id");
    $upd->bindParam(":cliente_id", $cliente_id);
    $upd->bindParam(":fecha_pedido", $fecha_pedido);
    $upd->bindParam(":usuario_id", $usuario_id);
    $upd->bindParam(":estado", $estado);
    $upd->bindParam(":id", $txtID);
    $upd->execute();
    header("Location:index.php?mensaje=Pedido actualizado");
    exit;
}
?>
<?php include("../../templates/header.php");?>
<br>
<h2>Editar Pedido</h2>
<div class="card">
    <div class="card-header">Datos del pedido</div>
    <div class="card-body">
        <form method="post">
            <div class="mb-3">
                <label for="txtID" class="form-label">ID</label>
                <input type="text" class="form-control" id="txtID" name="txtID" value="<?php echo htmlspecialchars($txtID); ?>" readonly>
            </div>
            <div class="mb-3">
                <label for="fecha_pedido" class="form-label">Fecha pedido</label>
                <input type="date" class="form-control" id="fecha_pedido" name="fecha_pedido" value="<?php echo isset($pedido['fecha_pedido'])?htmlspecialchars($pedido['fecha_pedido']):''; ?>" required>
            </div>
            <div class="mb-3">
                <label for="cliente_id" class="form-label">Cliente ID</label>
                <input type="number" class="form-control" id="cliente_id" name="cliente_id" value="<?php echo isset($pedido['cliente_id'])?htmlspecialchars($pedido['cliente_id']):''; ?>" required>
            </div>
            <div class="mb-3">
                <label for="usuario_id" class="form-label">Usuario ID</label>
                <input type="number" class="form-control" id="usuario_id" name="usuario_id" value="<?php echo isset($pedido['usuario_id'])?htmlspecialchars($pedido['usuario_id']):''; ?>" required>
            </div>
            <div class="mb-3">
                <label for="estado" class="form-label">Estado</label>
                <select class="form-select" id="estado" name="estado">
                    <?php $estadoVal = isset($pedido['estado'])?$pedido['estado']:'Activo'; ?>
                    <option value="Activo" <?php echo $estadoVal==='Activo'?'selected':''; ?>>Activo</option>
                    <option value="Anulado" <?php echo $estadoVal==='Anulado'?'selected':''; ?>>Anulado</option>
                </select>
            </div>
            <button class="btn btn-outline-success" type="submit">Guardar cambios</button>
            <a class="btn btn-outline-primary" href="index.php">Cancelar</a>
        </form>
    </div>
</div>
<?php include("../../templates/footer.php");?>

