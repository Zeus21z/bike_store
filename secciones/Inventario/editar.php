<?php include("../../bd.php");

if(isset($_GET['tienda_id']) && isset($_GET['product_id'])){
    $tienda_id = $_GET['tienda_id'];
    $product_id = $_GET['product_id'];
    
    // Validar que los IDs sean numéricos
    if(!is_numeric($tienda_id) || !is_numeric($product_id)){
        $mensaje = "Error: IDs inválidos";
        header("Location:index.php?mensaje=".$mensaje);
        exit;
    }
    
    try {
        $sentencia = $conexion->prepare("SELECT i.*, t.nombre_tienda, p.product_name 
                                        FROM inventario i
                                        INNER JOIN tiendas t ON i.tienda_id = t.tienda_id
                                        INNER JOIN productos p ON i.product_id = p.product_id
                                        WHERE i.tienda_id = :tienda_id AND i.product_id = :product_id");
        $sentencia->bindParam(":tienda_id", $tienda_id);
        $sentencia->bindParam(":product_id", $product_id);
        $sentencia->execute();
        
        $registro = $sentencia->fetch(PDO::FETCH_LAZY);
        
        if(!$registro){
            $mensaje = "Error: No se encontró el registro en el inventario";
            header("Location:index.php?mensaje=".$mensaje);
            exit;
        }
        
        $cantidad = $registro["cantidad"];
        $nombre_tienda = $registro["nombre_tienda"];
        $product_name = $registro["product_name"];
    } catch(Exception $e) {
        $mensaje = "Error al obtener datos: " . $e->getMessage();
        header("Location:index.php?mensaje=".$mensaje);
        exit;
    }
} else {
    $mensaje = "Error: Parámetros requeridos no encontrados";
    header("Location:index.php?mensaje=".$mensaje);
    exit;
}

if($_POST){
    // Recolectamos los datos del método POST
    $tienda_id = (isset($_POST["tienda_id"]) ? $_POST["tienda_id"] : "");
    $product_id = (isset($_POST["product_id"]) ? $_POST["product_id"] : "");
    $cantidad = (isset($_POST["cantidad"]) ? $_POST["cantidad"] : "");
    
    // Validar datos de entrada
    if(empty($tienda_id) || empty($product_id) || !is_numeric($cantidad) || $cantidad < 0){
        $mensaje = "Error: Datos inválidos. La cantidad debe ser un número mayor o igual a 0";
        header("Location:index.php?mensaje=".$mensaje);
        exit;
    }
    
    try {
        // Actualizar datos
        $sentencia = $conexion->prepare("UPDATE inventario SET
        cantidad = :cantidad 
        WHERE tienda_id = :tienda_id AND product_id = :product_id");
        
        $sentencia->bindParam(":cantidad", $cantidad);
        $sentencia->bindParam(":tienda_id", $tienda_id);
        $sentencia->bindParam(":product_id", $product_id);
        
        $sentencia->execute();
        
        if($sentencia->rowCount() > 0){
            $mensaje = "Stock actualizado correctamente";
        } else {
            $mensaje = "No se realizaron cambios en el inventario";
        }
        header("Location:index.php?mensaje=".$mensaje);
    } catch(Exception $e) {
        $mensaje = "Error al actualizar: " . $e->getMessage();
        header("Location:index.php?mensaje=".$mensaje);
    }
}
?>
<?php include("../../templates/header.php");?>
<br>
<h2>Editar Stock</h2>
<div class="card">
    <div class="card-header">Datos del Inventario</div>
    <div class="card-body">
        <form action="" method="post">
            <div class="mb-3">
                <label class="form-label">Tienda:</label>
                <input type="text" class="form-control" value="<?php echo htmlspecialchars($nombre_tienda); ?>" readonly>
                <input type="hidden" name="tienda_id" value="<?php echo $tienda_id; ?>">
            </div>
            
            <div class="mb-3">
                <label class="form-label">Producto:</label>
                <input type="text" class="form-control" value="<?php echo htmlspecialchars($product_name); ?>" readonly>
                <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
            </div>
            
            <div class="mb-3">
                <label for="cantidad" class="form-label">Cantidad en Stock</label>
                <input type="number" class="form-control" name="cantidad" id="cantidad"
                aria-describedby="helpid" placeholder="Cantidad" min="0" 
                value="<?php echo htmlspecialchars($cantidad); ?>" required>
                <small id="helpid" class="form-text text-muted">Modifique la cantidad disponible en stock</small>
            </div>
            
            <button type="submit" class="btn btn-outline-success">Actualizar Stock</button>
            <a name="" id="" class="btn btn-outline-primary" href="index.php" role="button">Cancelar</a>
        </form>
    </div>
</div>
<?php include("../../templates/footer.php");?>