<?php include("../../bd.php");
// Detectar si existe columna foto
$tieneFoto=false;
try{
    $d=$conexion->prepare("DESCRIBE clientes");
    $d->execute();
    $cols=$d->fetchAll(PDO::FETCH_ASSOC);
    foreach($cols as $col){ if($col['Field']==='foto'){ $tieneFoto=true; break; } }
}catch(Exception $e){ $tieneFoto=false; }
if($_POST){
    $nombre=(isset($_POST['nombre'])?trim($_POST['nombre']):'');
    $apellido=(isset($_POST['apellido'])?trim($_POST['apellido']):'');
    $telefono=(isset($_POST['telefono'])?trim($_POST['telefono']):'');
    $correo=(isset($_POST['correo'])?trim($_POST['correo']):'');
    $calle=(isset($_POST['calle'])?trim($_POST['calle']):'');
    $ciudad=(isset($_POST['ciudad'])?trim($_POST['ciudad']):'');
    $estado=(isset($_POST['estado'])?trim($_POST['estado']):'');
    if($tieneFoto){
        $foto=(isset($_FILES['foto']['name'])?$_FILES['foto']['name']:'');
    }
    $ins=$conexion->prepare($tieneFoto
        ? "INSERT INTO clientes(cliente_id,nombre,apellido,telefono,correo,calle,ciudad,estado,foto) VALUES(NULL,:nombre,:apellido,:telefono,:correo,:calle,:ciudad,:estado,:foto)"
        : "INSERT INTO clientes(cliente_id,nombre,apellido,telefono,correo,calle,ciudad,estado) VALUES(NULL,:nombre,:apellido,:telefono,:correo,:calle,:ciudad,:estado)");
    $ins->bindParam(":nombre", $nombre);
    $ins->bindParam(":apellido", $apellido);
    $ins->bindParam(":telefono", $telefono);
    $ins->bindParam(":correo", $correo);
    $ins->bindParam(":calle", $calle);
    $ins->bindParam(":ciudad", $ciudad);
    $ins->bindParam(":estado", $estado);
    if($tieneFoto){
        $fecha_=new DateTime();
        $nombreArchivo_foto=($foto!='')?$fecha_->getTimestamp()."_".$_FILES['foto']['name']:"";
        $tmp_foto=$_FILES['foto']['tmp_name'] ?? '';
        if($tmp_foto!=''){
            if(!is_dir("./img")) { @mkdir("./img",0777,true); }
            move_uploaded_file($tmp_foto,"./img/".$nombreArchivo_foto);
        }
        $ins->bindParam(":foto", $nombreArchivo_foto);
    }
    $ins->execute();
    header("Location:index.php?mensaje=Cliente creado");
    exit;
}
?>
<?php include("../../templates/header.php");?>
<br>
<div class="card">
    <div class="card-header">Nuevo Cliente</div>
    <div class="card-body">
        <form method="post" enctype="multipart/form-data">
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="nombre" class="form-label">Nombre</label>
                    <input type="text" class="form-control" id="nombre" name="nombre" required>
                </div>
                <div class="col-md-6">
                    <label for="apellido" class="form-label">Apellido</label>
                    <input type="text" class="form-control" id="apellido" name="apellido" required>
                </div>
                <div class="col-md-4">
                    <label for="telefono" class="form-label">Teléfono</label>
                    <input type="text" class="form-control" id="telefono" name="telefono">
                </div>
                <?php if($tieneFoto){ ?>
                <div class="col-md-8">
                    <label for="foto" class="form-label">Foto</label>
                    <input type="file" class="form-control" id="foto" name="foto">
                </div>
                <?php } ?>
                <div class="col-md-8">
                    <label for="correo" class="form-label">Correo</label>
                    <input type="email" class="form-control" id="correo" name="correo">
                </div>
                <div class="col-md-4">
                    <label for="calle" class="form-label">Calle</label>
                    <input type="text" class="form-control" id="calle" name="calle">
                </div>
                <div class="col-md-4">
                    <label for="ciudad" class="form-label">Ciudad</label>
                    <input type="text" class="form-control" id="ciudad" name="ciudad">
                </div>
                <div class="col-md-4">
                    <label for="estado" class="form-label">Depa</label>
                    <input type="text" class="form-control" id="estado" name="estado">
                </div>
            </div>
            <br>
            <button class="btn btn-outline-success" type="submit">Guardar</button>
            <a class="btn btn-outline-primary" href="index.php">Cancelar</a>
        </form>
    </div>
</div>
<?php include("../../templates/footer.php");?>

