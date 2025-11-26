<?php include("../../bd.php");

if($_POST){
    // Recolectamos los datos del método POST
    $descripcion=(isset($_POST["descripcion"])?trim($_POST["descripcion"]):"");
    
    // Validaciones
    if(empty($descripcion)){
        $mensaje="La descripción es obligatoria";
        header("Location:index.php?mensaje=".$mensaje);
        exit;
    }
    
    // Preparar la inserción de datos
    $sentencia=$conexion->prepare("INSERT INTO categorias(category_id, descripcion) VALUES(NULL, :descripcion)");
    
    // Asignamos los valores que tienen uso de :variable
    $sentencia->bindParam(":descripcion", $descripcion);
    
    // Ejecutar la inserción
    $sentencia->execute();
    
    $mensaje="Categoría creada exitosamente";
    header("Location:index.php?mensaje=".$mensaje);
    exit;
}
?>
<?php include("../../templates/header.php");?>
<br>
<h2>Crear Nueva Categoría</h2>
<div class="card">
    <div class="card-header">Datos de la Categoría</div>
    <div class="card-body">
        <form method="post">
            <div class="mb-3">
                <label for="descripcion" class="form-label">Descripción</label>
                <input type="text" class="form-control" id="descripcion" name="descripcion" placeholder="Ingrese la descripción de la categoría" required>
            </div>
            <button class="btn btn-outline-success" type="submit">Guardar</button>
            <a class="btn btn-outline-primary" href="index.php">Cancelar</a>
        </form>
    </div>
</div>
<?php include("../../templates/footer.php");?>
