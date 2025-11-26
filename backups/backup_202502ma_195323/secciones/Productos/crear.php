<?php include("../../bd.php");
if($_POST){
    //Recolectamos los datos del metodo POST
    $product_name=(isset($_POST["product_name"])?$_POST["product_name"]:"");
    //Para foto cambiamos a  $_FILES y agregamos ['name']
    $foto=(isset($_FILES["foto"]['name'])?$_FILES["foto"]['name']:"");
    $model_year=(isset($_POST["model_year"])?$_POST["model_year"]:"");
    $price=(isset($_POST["price"])?$_POST["price"]:"");
    $category_id=(isset($_POST["category_id"])?$_POST["category_id"]:"");
    // Validaciones: categoria obligatoria y existente
    if($category_id==="" || !ctype_digit((string)$category_id)){
        $mensaje="Seleccione una categoria válida";
        header("Location:index.php?mensaje=".$mensaje);
        exit;
    }
    $verificar=$conexion->prepare("SELECT 1 FROM categorias WHERE category_id=:id LIMIT 1");
    $verificar->bindParam(":id", $category_id);
    $verificar->execute();
    if(!$verificar->fetchColumn()){
        $mensaje="La categoria seleccionada no existe";
        header("Location:index.php?mensaje=".$mensaje);
        exit;
    }
    //Preparar la insercion de datos
    $sentencia=$conexion->prepare("INSERT INTO
    productos(product_id,product_name,foto,model_year,price,category_id)
    VALUES(null,:product_name,:foto,:model_year,:price,:category_id)");
    //Asignamos los valores que tienen uso de :variable
    $sentencia->bindParam(":product_name",$product_name);
    $sentencia->bindParam(":model_year",$model_year);
    $sentencia->bindParam(":price",$price);
    $sentencia->bindParam(":category_id",$category_id);
    //Adjuntamos la foto con un nombre distinto de archivo
    $fecha_=new DateTime();
    $nombreArchivo_foto=($foto!='')?$fecha_->getTimestamp()."_".$_FILES["foto"]['name']:"";
    //Creamos archivo temporal de la foto
    $tmp_foto=$_FILES["foto"]['tmp_name'];
    if($tmp_foto!=''){
        move_uploaded_file($tmp_foto,"./img/".$nombreArchivo_foto);
    }
$sentencia->bindParam(":foto",$nombreArchivo_foto);
try{
    $sentencia->execute();
    $mensaje="Registro agregado";
}catch(Exception $e){
    $mensaje="Error al guardar: ".$e->getMessage();
}
//Redirecciona a index.php
header("Location:index.php?mensaje=".$mensaje);
}
//Consulta de categorias
$sentencia=$conexion->prepare("SELECT * FROM categorias");
$sentencia->execute();
$lista_categorias=$sentencia->fetchAll(PDO::FETCH_ASSOC);
?>
<?php include("../../templates/header.php");?>
<br>
<div class="card">
    <div class="card-header">Datos del producto</div>
    <div class="card-body">
        <form action="" method="post" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="product_name" class="form-label">Producto nombre</label>
                <input type="text" class="form-control" name="product_name" id="product_name"
                aria-describedby="helpid" placeholder="Nombre del producto">
                <small id="helpid" class="form-text text-muted">Ingrese el nombre del producto</small>
            </div>
            <div class="mb-3">
                <label for="foto" class="form-label">Foto</label>
                <input type="file" class="form-control" name="foto" id="foto"
                aria-describedby="helpid" placeholder="Foto">
                <small id="helpid" class="form-text text-muted">Ingrese el nombre del archivo tipo imagen</small>
            </div>
            <div class="mb-3">
                <label for="model_year" class="form-label">Modelo año</label>
                <input type="text" class="form-control" name="model_year" id="model_year"
                aria-describedby="helpid" placeholder="Modelo año">
                <small id="helpid" class="form-text text-muted">Ingrese el año de modelo del producto</small>
            </div>
            <div class="mb-3">
                <label for="price" class="form-label">Precio</label>
                <input type="text" class="form-control" name="price" id="price"
                aria-describedby="helpid" placeholder="Precio">
                <small id="helpid" class="form-text text-muted">Ingrese el precio del producto</small>
            </div>
            <div class="mb-3">
                <label for="category_id" class="form-label">Categoria</label>
                <select class="form-select form-select-sm" name="category_id" id="category_id" required>
                    <option value="" selected disabled>Seleccione una opción</option>
                    <?php foreach($lista_categorias as $registro) { ?>
                        <option value="<?php echo $registro['category_id'] ?>">
                            <?php echo $registro['descripcion'] ?>
                        </option>
                    <?php } ?>
                </select>
            </div>
            <button type="submit" class="btn btn-outline-success">Agregar registro</button>
            <a name="" id="" class="btn btn-outline-primary" href="index.php" role="button">Cancelar</a>
        </form>
    </div>
</div>
<?php include("../../templates/footer.php");?>