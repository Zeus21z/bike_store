<?php include("../../bd.php");
// Detectar si existe columna foto
$tieneFoto=false;
try{
    $d=$conexion->prepare("DESCRIBE clientes");
    $d->execute();
    $cols=$d->fetchAll(PDO::FETCH_ASSOC);
    foreach($cols as $col){ if($col['Field']==='foto'){ $tieneFoto=true; break; } }
}catch(Exception $e){ $tieneFoto=false; }
$txtID = isset($_GET['txtID'])?$_GET['txtID']:'';
if($txtID!=='' && $_SERVER['REQUEST_METHOD']!== 'POST'){
    $stmt=$conexion->prepare("SELECT * FROM clientes WHERE cliente_id=:id");
    $stmt->bindParam(":id", $txtID);
    $stmt->execute();
    $cliente=$stmt->fetch(PDO::FETCH_ASSOC);
    if(!$cliente){ header("Location:index.php?mensaje=Cliente no encontrado"); exit; }
}
if($_POST){
    $txtID=(isset($_POST['txtID'])?$_POST['txtID']:'');
    $nombre=(isset($_POST['nombre'])?trim($_POST['nombre']):'');
    $apellido=(isset($_POST['apellido'])?trim($_POST['apellido']):'');
    $telefono=(isset($_POST['telefono'])?trim($_POST['telefono']):'');
    $correo=(isset($_POST['correo'])?trim($_POST['correo']):'');
    $calle=(isset($_POST['calle'])?trim($_POST['calle']):'');
    $ciudad=(isset($_POST['ciudad'])?trim($_POST['ciudad']):'');
    $estado=(isset($_POST['estado'])?trim($_POST['estado']):'');
    $upd=$conexion->prepare("UPDATE clientes SET nombre=:nombre, apellido=:apellido, telefono=:telefono, correo=:correo, calle=:calle, ciudad=:ciudad, estado=:estado WHERE cliente_id=:id");
    $upd->bindParam(":nombre", $nombre);
    $upd->bindParam(":apellido", $apellido);
    $upd->bindParam(":telefono", $telefono);
    $upd->bindParam(":correo", $correo);
    $upd->bindParam(":calle", $calle);
    $upd->bindParam(":ciudad", $ciudad);
    $upd->bindParam(":estado", $estado);
    $upd->bindParam(":id", $txtID);
    $upd->execute();
    if($tieneFoto && isset($_FILES['foto']['name']) && $_FILES['foto']['name']!==''){
        $foto=$_FILES['foto']['name'];
        $fecha_=new DateTime();
        $nombreArchivo_foto=$fecha_->getTimestamp()."_".$foto;
        $tmp_foto=$_FILES['foto']['tmp_name'];
        if($tmp_foto!=''){
            if(!is_dir("./img")) { @mkdir("./img",0777,true); }
            move_uploaded_file($tmp_foto,"./img/".$nombreArchivo_foto);
        }
        // borrar anterior si existe
        $q=$conexion->prepare("SELECT foto FROM clientes WHERE cliente_id=:id");
        $q->bindParam(":id", $txtID);
        $q->execute();
        $old=$q->fetch(PDO::FETCH_ASSOC);
        if($old && !empty($old['foto']) && file_exists("./img/".$old['foto'])){ @unlink("./img/".$old['foto']); }
        $u2=$conexion->prepare("UPDATE clientes SET foto=:f WHERE cliente_id=:id");
        $u2->bindParam(":f", $nombreArchivo_foto);
        $u2->bindParam(":id", $txtID);
        $u2->execute();
    }
    header("Location:index.php?mensaje=Cliente actualizado");
    exit;
}
?>
<?php include("../../templates/header.php");?>
<br>
<h2>Editar Cliente</h2>
<div class="card">
    <div class="card-header">Datos del cliente</div>
    <div class="card-body">
        <form method="post" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="txtID" class="form-label">ID</label>
                <input type="text" class="form-control" id="txtID" name="txtID" value="<?php echo htmlspecialchars($txtID); ?>" readonly>
            </div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="nombre" class="form-label">Nombre</label>
                    <input type="text" class="form-control" id="nombre" name="nombre" value="<?php echo htmlspecialchars($cliente['nombre'] ?? ''); ?>" required>
                </div>
                <div class="col-md-6">
                    <label for="apellido" class="form-label">Apellido</label>
                    <input type="text" class="form-control" id="apellido" name="apellido" value="<?php echo htmlspecialchars($cliente['apellido'] ?? ''); ?>" required>
                </div>
                <div class="col-md-4">
                    <label for="telefono" class="form-label">Teléfono</label>
                    <input type="text" class="form-control" id="telefono" name="telefono" value="<?php echo htmlspecialchars($cliente['telefono'] ?? ''); ?>">
                </div>
                <div class="col-md-8">
                    <label for="correo" class="form-label">Correo</label>
                    <input type="email" class="form-control" id="correo" name="correo" value="<?php echo htmlspecialchars($cliente['correo'] ?? ''); ?>">
                </div>
                <?php if($tieneFoto){ ?>
                <div class="col-md-12">
                    <label for="foto" class="form-label">Foto</label>
                    <br>
                    <?php if(!empty($cliente['foto'])){ ?>
                        <img src="img/<?php echo htmlspecialchars($cliente['foto']); ?>" alt="Cliente" width="60" class="img-fluid rounded">
                        <br><br>
                    <?php } ?>
                    <input type="file" class="form-control" id="foto" name="foto">
                </div>
                <?php } ?>
                <div class="col-md-4">
                    <label for="calle" class="form-label">Calle</label>
                    <input type="text" class="form-control" id="calle" name="calle" value="<?php echo htmlspecialchars($cliente['calle'] ?? ''); ?>">
                </div>
                <div class="col-md-4">
                    <label for="ciudad" class="form-label">Ciudad</label>
                    <input type="text" class="form-control" id="ciudad" name="ciudad" value="<?php echo htmlspecialchars($cliente['ciudad'] ?? ''); ?>">
                </div>
                <div class="col-md-4">
                    <label for="estado" class="form-label">Depa</label>
                    <input type="text" class="form-control" id="estado" name="estado" value="<?php echo htmlspecialchars($cliente['estado'] ?? ''); ?>">
                </div>
            </div>
            <br>
            <button class="btn btn-outline-success" type="submit">Guardar cambios</button>
            <a class="btn btn-outline-primary" href="index.php">Cancelar</a>
        </form>
    </div>
</div>
<?php include("../../templates/footer.php");?>

