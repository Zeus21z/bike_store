<?php include("../../bd.php");
// Inicializar variables por si no hay datos
$txtID = isset($txtID) ? $txtID : "";
$descripcion = isset($descripcion) ? $descripcion : "";

if(isset($_GET['txtID'])){
    $txtID=(isset($_GET['txtID']))?$_GET['txtID']:"";
    $sentencia=$conexion->prepare("SELECT * FROM categorias WHERE category_id=:id");
    $sentencia->bindParam(":id",$txtID);
    $sentencia->execute();
    $registro=$sentencia->fetch(PDO::FETCH_LAZY);
    if($registro){
        $descripcion=$registro["descripcion"];
    } else {
        header("Location:index.php?mensaje=Categoria no encontrada");
        exit;
    }
}
if($_POST){
    $txtID=(isset($_POST["txtID"]) ? $_POST["txtID"] : "");
    $descripcion=(isset($_POST["descripcion"]) ? trim($_POST["descripcion"]) : "");
    $sentencia=$conexion->prepare("UPDATE categorias SET descripcion=:descripcion WHERE category_id=:id");
    $sentencia->bindParam(":descripcion", $descripcion);
    $sentencia->bindParam(":id", $txtID);
    $sentencia->execute();
    $mensaje="Registro actualizado";
    header("Location:index.php?mensaje=".$mensaje);
    exit;
}
?>
<?php include("../../templates/header.php");?>
<br>
<h2>Editar categoría</h2>
<div class="card">
    <div class="card-header">Datos de la categoría</div>
    <div class="card-body">
        <form action="" method="post">
            <div class="mb-3">
                <label for="txtID" class="form-label">ID:</label>
                <input type="text" value="<?php echo $txtID; ?>" class="form-control" readonly name="txtID" id="txtID">
            </div>
            <div class="mb-3">
                <label for="descripcion" class="form-label">Descripción</label>
                <input type="text" class="form-control" name="descripcion" id="descripcion" value="<?php echo htmlspecialchars($descripcion); ?>" placeholder="Ej: Autos">
            </div>
            <button type="submit" class="btn btn-outline-success">Guardar cambios</button>
            <a class="btn btn-outline-primary" href="index.php" role="button">Cancelar</a>
        </form>
    </div>
</div>
<?php include("../../templates/footer.php");?>

