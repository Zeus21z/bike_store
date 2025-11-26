<?php include("../../bd.php");

try {
    // Obtener lista de tiendas y productos
    $sentencia_tiendas = $conexion->prepare("SELECT * FROM tiendas ORDER BY nombre_tienda");
    $sentencia_tiendas->execute();
    $lista_tiendas = $sentencia_tiendas->fetchAll(PDO::FETCH_ASSOC);

    $sentencia_productos = $conexion->prepare("SELECT p.*, c.descripcion as categoria 
                                              FROM productos p 
                                              INNER JOIN categorias c ON p.category_id = c.category_id 
                                              ORDER BY p.product_name");
    $sentencia_productos->execute();
    $lista_productos = $sentencia_productos->fetchAll(PDO::FETCH_ASSOC);
} catch(Exception $e) {
    $mensaje = "Error al cargar datos: " . $e->getMessage();
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
        $mensaje = "Error: Todos los campos son requeridos. La cantidad debe ser un número mayor o igual a 0";
        header("Location:index.php?mensaje=".$mensaje);
        exit;
    }
    
    try {
        // Verificar si ya existe un registro para esta combinación tienda-producto
        $sentencia_verificar = $conexion->prepare("SELECT * FROM inventario 
                                                  WHERE tienda_id = :tienda_id 
                                                  AND product_id = :product_id");
        $sentencia_verificar->bindParam(":tienda_id", $tienda_id);
        $sentencia_verificar->bindParam(":product_id", $product_id);
        $sentencia_verificar->execute();
        
        if($sentencia_verificar->fetch()){
            $mensaje = "Este producto ya existe en el inventario de esta tienda. Use la opción editar para modificar la cantidad.";
            header("Location:index.php?mensaje=".$mensaje);
            exit;
        }
        
        // Preparar la inserción de datos
        $sentencia = $conexion->prepare("INSERT INTO inventario 
        (tienda_id, product_id, cantidad) 
        VALUES (:tienda_id, :product_id, :cantidad)");
        
        // Asignamos los valores
        $sentencia->bindParam(":tienda_id", $tienda_id);
        $sentencia->bindParam(":product_id", $product_id);
        $sentencia->bindParam(":cantidad", $cantidad);
        
        $sentencia->execute();
        $mensaje = "Registro agregado al inventario correctamente";
        header("Location:index.php?mensaje=".$mensaje);
    } catch(Exception $e){
        $mensaje = "Error al guardar: ".$e->getMessage();
        header("Location:index.php?mensaje=".$mensaje);
    }
}
?>
<?php include("../../templates/header.php");?>
<br>
<h2>Agregar al Inventario</h2>
<div class="card">
    <div class="card-header">Datos del Inventario</div>
    <div class="card-body">
        <form action="" method="post">
            <div class="mb-3">
                <label for="tienda_id" class="form-label">Tienda</label>
                <select class="form-select form-select-sm" name="tienda_id" id="tienda_id" required>
                    <option value="" selected disabled>Seleccione una tienda</option>
                    <?php foreach($lista_tiendas as $tienda) { ?>
                        <option value="<?php echo $tienda['tienda_id']; ?>">
                            <?php echo $tienda['nombre_tienda'] . ' - ' . $tienda['ciudad']; ?>
                        </option>
                    <?php } ?>
                </select>
            </div>
            
            <div class="mb-3">
                <label for="product_id" class="form-label">Producto</label>
                <select class="form-select form-select-sm" name="product_id" id="product_id" required>
                    <option value="" selected disabled>Seleccione un producto</option>
                    <?php foreach($lista_productos as $producto) { ?>
                        <option value="<?php echo $producto['product_id']; ?>">
                            <?php echo $producto['product_name'] . ' - $' . number_format($producto['price'], 2) . ' (' . $producto['categoria'] . ')'; ?>
                        </option>
                    <?php } ?>
                </select>
            </div>
            
            <div class="mb-3">
                <label for="cantidad" class="form-label">Cantidad en Stock</label>
                <input type="number" class="form-control" name="cantidad" id="cantidad"
                aria-describedby="helpid" placeholder="Cantidad" min="0" value="0" required>
                <small id="helpid" class="form-text text-muted">Ingrese la cantidad disponible en stock</small>
            </div>
            
            <button type="submit" class="btn btn-outline-success">Agregar al Inventario</button>
            <a name="" id="" class="btn btn-outline-primary" href="index.php" role="button">Cancelar</a>
        </form>
    </div>
</div>
<?php include("../../templates/footer.php");?>