<?php include("../../bd.php");

if($_POST){
    // Recolectamos los datos del método POST
    $nombre_tienda = (isset($_POST["nombre_tienda"]) ? $_POST["nombre_tienda"] : "");
    $telefono = (isset($_POST["telefono"]) ? $_POST["telefono"] : "");
    $email = (isset($_POST["email"]) ? $_POST["email"] : "");
    $calle = (isset($_POST["calle"]) ? $_POST["calle"] : "");
    $ciudad = (isset($_POST["ciudad"]) ? $_POST["ciudad"] : "");
    $estado = (isset($_POST["estado"]) ? $_POST["estado"] : "");
    
    // Preparar la inserción de datos
    $sentencia = $conexion->prepare("INSERT INTO tiendas 
    (tienda_id, nombre_tienda, telefono, email, calle, ciudad, estado) 
    VALUES (null, :nombre_tienda, :telefono, :email, :calle, :ciudad, :estado)");
    
    // Asignamos los valores
    $sentencia->bindParam(":nombre_tienda", $nombre_tienda);
    $sentencia->bindParam(":telefono", $telefono);
    $sentencia->bindParam(":email", $email);
    $sentencia->bindParam(":calle", $calle);
    $sentencia->bindParam(":ciudad", $ciudad);
    $sentencia->bindParam(":estado", $estado);
    
    try{
        $sentencia->execute();
        $mensaje = "Tienda agregada correctamente";
        header("Location:index.php?mensaje=".$mensaje);
    } catch(Exception $e){
        $mensaje = "Error al guardar: ".$e->getMessage();
    }
}
?>
<?php include("../../templates/header.php");?>
<br>
<h2>Crear Nueva Tienda</h2>
<div class="card">
    <div class="card-header">Datos de la Tienda</div>
    <div class="card-body">
        <form action="" method="post">
            <div class="mb-3">
                <label for="nombre_tienda" class="form-label">Nombre de la Tienda</label>
                <input type="text" class="form-control" name="nombre_tienda" id="nombre_tienda"
                aria-describedby="helpid" placeholder="Nombre de la tienda" required>
                <small id="helpid" class="form-text text-muted">Ingrese el nombre de la tienda</small>
            </div>
            
            <div class="mb-3">
                <label for="telefono" class="form-label">Teléfono</label>
                <input type="text" class="form-control" name="telefono" id="telefono"
                aria-describedby="helpid" placeholder="Teléfono">
                <small id="helpid" class="form-text text-muted">Ingrese el teléfono de contacto</small>
            </div>
            
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" name="email" id="email"
                aria-describedby="helpid" placeholder="Email">
                <small id="helpid" class="form-text text-muted">Ingrese el email de contacto</small>
            </div>
            
            <div class="mb-3">
                <label for="calle" class="form-label">Calle/Dirección</label>
                <input type="text" class="form-control" name="calle" id="calle"
                aria-describedby="helpid" placeholder="Calle y número">
                <small id="helpid" class="form-text text-muted">Ingrese la dirección de la tienda</small>
            </div>
            
            <div class="mb-3">
                <label for="ciudad" class="form-label">Ciudad</label>
                <input type="text" class="form-control" name="ciudad" id="ciudad"
                aria-describedby="helpid" placeholder="Ciudad">
                <small id="helpid" class="form-text text-muted">Ingrese la ciudad</small>
            </div>
            
            <div class="mb-3">
                <label for="estado" class="form-label">Estado/Departamento</label>
                <input type="text" class="form-control" name="estado" id="estado"
                aria-describedby="helpid" placeholder="Estado o Departamento">
                <small id="helpid" class="form-text text-muted">Ingrese el estado o departamento</small>
            </div>
            
            <button type="submit" class="btn btn-outline-success">Agregar Tienda</button>
            <a name="" id="" class="btn btn-outline-primary" href="index.php" role="button">Cancelar</a>
        </form>
    </div>
</div>
<?php include("../../templates/footer.php");?>