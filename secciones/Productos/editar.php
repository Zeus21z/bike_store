<?php include("../../bd.php");
if(isset($_GET['txtID'])){
    $txtID=(isset($_GET['txtID']))?$_GET['txtID']:"";
    $sentencia=$conexion->prepare("SELECT * FROM productos WHERE product_id=:id");
    $sentencia->bindParam(":id",$txtID);
    $sentencia->execute();
    //creamos las variable
    $registro=$sentencia->fetch(PDO::FETCH_LAZY);
    $product_name=$registro["product_name"];
    $foto=$registro["foto"];
    $model_year=$registro["model_year"];
    $price=$registro["price"];
    $category_id=$registro["category_id"];
}
//El siguiente codigo copiamos del archivo crear.php
if($_POST){
    //Recolectamos los datos del metodo POST
    $txtID=(isset($_POST["txtID"])?$_POST["txtID"]:"");
    $product_name=(isset($_POST["product_name"])?$_POST["product_name"]:"");
    //Para foto cambiamos a  $_FILES y agregamos ['name']
    
    $model_year=(isset($_POST["model_year"])?$_POST["model_year"]:"");
    $price=(isset($_POST["price"])?$_POST["price"]:"");
    $category_id=(isset($_POST["category_id"])?$_POST["category_id"]:"");
    // Actualizar datos principales (sin foto)
    $sentencia=$conexion->prepare("UPDATE productos SET
    product_name=:product_name,
    model_year=:model_year,
    price=:price,
    category_id=:category_id WHERE product_id=:id");
    $sentencia->bindParam(":product_name",$product_name);
    $sentencia->bindParam(":model_year",$model_year);
    $sentencia->bindParam(":price",$price);
    $sentencia->bindParam(":category_id",$category_id);
    $sentencia->bindParam(":id",$txtID);
    $sentencia->execute();

    $foto=(isset($_FILES["foto"]['name'])?$_FILES["foto"]['name']:"");
    //Adjuntamos la foto con un nombre distinto de archivo
    $fecha_=new DateTime();
    $nombreArchivo_foto=($foto!='')?$fecha_->getTimestamp()."_".$_FILES["foto"]['name']:"";
    //Creamos archivo temporal de la foto
    $tmp_foto=$_FILES["foto"]['tmp_name'];
    if($tmp_foto!=''){
        move_uploaded_file($tmp_foto,"./img/".$nombreArchivo_foto);
        // Buscar el archivo relacionado con el producto y borrarlo
        $sentenciaFoto=$conexion->prepare("SELECT foto FROM productos WHERE product_id=:id");
        $sentenciaFoto->bindParam(":id",$txtID);
        $sentenciaFoto->execute();
        $registro_recuperado=$sentenciaFoto->fetch(PDO::FETCH_LAZY);
        if(isset($registro_recuperado["foto"]) && $registro_recuperado["foto"]!=""){
            if(file_exists("./img/".$registro_recuperado["foto"])){
                unlink("./img/".$registro_recuperado["foto"]);
            }
        }
        // Actualizar foto en BD
        $sentenciaActualizarFoto=$conexion->prepare("UPDATE productos SET foto=:foto WHERE product_id=:id");
        $sentenciaActualizarFoto->bindParam(":foto",$nombreArchivo_foto);
        $sentenciaActualizarFoto->bindParam(":id",$txtID);
        $sentenciaActualizarFoto->execute();
    }
    $mensaje="Registro actualizado";
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
<h2>Editar producto</h2>
<div class="card">
    <div class="card-header">Datos del producto</div>
    <div class="card-body">
        <form action="" method="post" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="txtID" class="form-label">ID:</label>
                <input type="text" value="<?php echo $txtID; ?>" 
                class="form-control" readonly name="txtID" id="txtID"
                aria-describedby="helpid" placeholder="ID del producto">
            </div>
            <div class="mb-3">
                <label for="product_name" class="form-label">Producto nombre</label>
                <input type="text" class="form-control" name="product_name" id="product_name"
                aria-describedby="helpid" placeholder="Nombre del producto" value="<?php echo htmlspecialchars($product_name); ?>">
                <small id="helpid" class="form-text text-muted">Ingrese el nombre del producto</small>
            </div>
            <div class="mb-3">
                <label for="foto" class="form-label">Foto</label>
                <br>
                <img width="50"
                src="img/<?php echo $foto; ?>"
                class="img-fliud rounded" alt="Producto imagen"/>
                <br><br>
                <input type="file" class="form-control" name="foto" id="foto"
                aria-describedby="helpid" placeholder="Foto">
                <small id="helpid" class="form-text text-muted">Ingrese el nombre del archivo tipo imagen</small>
            </div>
            <div class="mb-3">
                <label for="model_year" class="form-label">Modelo año</label>
                <input type="text" class="form-control" name="model_year" id="model_year"
                aria-describedby="helpid" placeholder="Modelo año" value="<?php echo htmlspecialchars($model_year); ?>">
                <small id="helpid" class="form-text text-muted">Ingrese el año de modelo del producto</small>
            </div>
            <div class="mb-3">
                <label for="price" class="form-label">Precio</label>
                <input type="text" class="form-control" name="price" id="price"
                aria-describedby="helpid" placeholder="Precio" value="<?php echo htmlspecialchars($price); ?>">
                <small id="helpid" class="form-text text-muted">Ingrese el precio del producto</small>
            </div>
            <div class="mb-3">
                <label for="category_id" class="form-label">Categoria</label>
                <select class="form-select form-select-sm" name="category_id" id="category_id">
                    <?php foreach($lista_categorias as $registro) { ?>
                        <option <?php echo ($category_id == $registro['category_id'])?"selected":""; ?>
                            value="<?php echo $registro['category_id'] ?>">
                            <?php echo $registro['descripcion'] ?>
                        </option>
                    <?php } ?>
                </select>
            </div>
            <button type="submit" class="btn btn-outline-success">Guardar cambios</button>
            <a name="" id="" class="btn btn-outline-primary" href="index.php" role="button">Cancelar</a>
        </form>
    </div>
</div>
<?php include("../../templates/footer.php");?>