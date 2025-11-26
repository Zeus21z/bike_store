<?php include("../../bd.php");

if(isset($_GET['txtID'])){
    $txtID = (isset($_GET['txtID'])) ? $_GET['txtID'] : "";
    $sentencia = $conexion->prepare("SELECT * FROM tiendas WHERE tienda_id = :id");
    $sentencia->bindParam(":id", $txtID);
    $sentencia->execute();
    
    $registro = $sentencia->fetch(PDO::FETCH_LAZY);
    $nombre_tienda = $registro["nombre_tienda"];
    $telefono = $registro["telefono"];
    $email = $registro["email"];
    $calle = $registro["calle"];
    $ciudad = $registro["ciudad"];
    $estado = $registro["estado"];
}

if($_POST){
    // Recolectamos los datos del método POST
    $txtID = (isset($_POST["txtID"]) ? $_POST["txtID"] : "");
    $nombre_tienda = (isset($_POST["nombre_tienda"]) ? $_POST["nombre_tienda"] : "");
    $telefono = (isset($_POST["telefono"]) ? $_POST["telefono"] : "");
    $email = (isset($_POST["email"]) ? $_POST["email"] : "");
    $calle = (isset($_POST["calle"]) ? $_POST["calle"] : "");
    $ciudad = (isset($_POST["ciudad"]) ? $_POST["ciudad"] : "");
    $estado = (isset($_POST["estado"]) ? $_POST["estado"] : "");
    
    // Actualizar datos
    $sentencia = $conexion->prepare("UPDATE tiendas SET
    nombre_tienda = :nombre_tienda,
    telefono = :telefono,
    email = :email,
    calle = :calle,
    ciudad = :ciudad,
    estado = :estado 
    WHERE tienda_id = :id");
    
    $sentencia->bindParam(":nombre_tienda", $nombre_tienda);
    $sentencia->bindParam(":telefono", $telefono);
    $sentencia->bindParam(":email", $email);
    $sentencia->bindParam(":calle", $calle);
    $sentencia->bindParam(":ciudad", $ciudad);
    $sentencia->bindParam(":estado", $estado);
    $sentencia->bindParam(":id", $txtID);
    
    $sentencia->execute();
    $mensaje = "Tienda actualizada correctamente";
    header("Location:index.php?mensaje=".$mensaje);
}
?>
<?php include("../../templates/header.php");?>
<br>
<h2>Editar Tienda</h2>
<div class="card">
    <div class="card-header">Datos de la Tienda</div>
    <div class="card-body">
        <form action="" method="post">
            <div class="mb-3">
                <label for="txtID" class="form-label">ID:</label>
                <input type="text" value="<?php echo $txtID; ?>" 
                class="form-control" readonly name="txtID" id="txtID"
                aria-describedby="helpid" placeholder="ID de la tienda">
            </div>
            
            <div class="mb-3">
                <label for="nombre_tienda" class="form-label">Nombre de la Tienda</label>
                <input type="text" class="form-control" name="nombre_tienda" id="nombre_tienda"
                aria-describedby="helpid" placeholder="Nombre de la tienda" 
                value="<?php echo htmlspecialchars($nombre_tienda); ?>" required>
            </div>
            
            <div class="mb-3">
                <label for="telefono" class="form-label">Teléfono</label>
                <input type="text" class="form-control" name="telefono" id="telefono"
                aria-describedby="helpid" placeholder="Teléfono"
                value="<?php echo htmlspecialchars($telefono); ?>">
            </div>
            
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" name="email" id="email"
                aria-describedby="helpid" placeholder="Email"
                value="<?php echo htmlspecialchars($email); ?>">
            </div>
            
            <div class="mb-3">
                <label for="calle" class="form-label">Calle/Dirección</label>
                <input type="text" class="form-control" name="calle" id="calle"
                aria-describedby="helpid" placeholder="Calle y número"
                value="<?php echo htmlspecialchars($calle); ?>">
            </div>
            
            <div class="mb-3">
                <label for="ciudad" class="form-label">Ciudad</label>
                <input type="text" class="form-control" name="ciudad" id="ciudad"
                aria-describedby="helpid" placeholder="Ciudad"
                value="<?php echo htmlspecialchars($ciudad); ?>">
            </div>
            
            <div class="mb-3">
                <label for="estado" class="form-label">Estado/Departamento</label>
                <input type="text" class="form-control" name="estado" id="estado"
                aria-describedby="helpid" placeholder="Estado o Departamento"
                value="<?php echo htmlspecialchars($estado); ?>">
            </div>
            
            <button type="submit" class="btn btn-outline-success">Guardar cambios</button>
            <a name="" id="" class="btn btn-outline-primary" href="index.php" role="button">Cancelar</a>
        </form>
    </div>
</div>
<?php include("../../templates/footer.php");?>