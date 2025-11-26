<?php include("../../bd.php");
//Envio de parametros en la URL o en el metodo GET
if(isset($_GET['txtID'])){
    $txtID=(isset($_GET['txtID']))?$_GET['txtID']:"";
    //Buscar el archivo relacionado con el producto
    $sentencia=$conexion->prepare("SELECT foto FROM productos WHERE product_id=:id");
    $sentencia->bindParam(":id",$txtID);
    $sentencia->execute();
    $registro_recuperado=$sentencia->fetch(PDO::FETCH_LAZY);
    //Buscar el archivo para eliminarlo
    if(isset($registro_recuperado["foto"]) && $registro_recuperado["foto"]!=""){
        if(file_exists("./img/".$registro_recuperado["foto"])){
            unlink("./img/".$registro_recuperado["foto"]);
        }
    }
//Elimina los datos del producto
$sentencia=$conexion->prepare("DELETE FROM productos WHERE product_id=:id");
$sentencia->bindParam(":id",$txtID);
$sentencia->execute();
$mensaje="Registro eliminado";
header("Location:index.php?mensaje=".$mensaje);
}
//Consulta productos para mostrar como unico registro
$sentencia=$conexion->prepare("SELECT *,
(SELECT descripcion FROM categorias WHERE categorias.category_id=productos.category_id limit 1) 
as categoria FROM productos");
$sentencia->execute();
$lista_productos=$sentencia->fetchAll(PDO::FETCH_ASSOC);
//print_r($lista_productos);
?>
<?php include("../../templates/header.php");?>
<br>
<h2>Listado de Productos</h2>
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <a name="" id="" class="btn btn-outline-primary" href="crear.php" role="button">
                Nuevo
            </a>
            <a name="" id="" class="btn btn-outline-primary" href="../../index.php" role="button">
            Inicio
        </a>
        </div>
        
    </div>
    <div class="card-body">
        <div class="table-responsive-sm">
            <table class="table table-primary">
                <thead>
                    <tr>
                        <th scope="col">ID</th>
                        <th scope="col">Producto</th>
                        <th scope="col">Foto</th>
                        <th scope="col">Modelo año</th>
                        <th scope="col">Precio</th>
                        <th scope="col">Categoria</th>
                        <th scope="col">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($lista_productos as $registro) { ?>
                    <tr class="">
                        <td scope="row"><?php echo $registro['product_id']; ?></td>
                        <td><?php echo $registro['product_name']; ?></td>
                        <td>
                            <?php if(!empty($registro['foto'])): ?>
                            <img width="50" height="50" style="object-fit: contain;"
                            src="img/<?php echo $registro['foto']; ?>"
                            class="img-fluid rounded" alt="Producto imagen"/>
                            <?php else: ?>
                            <i class="bi bi-image" style="font-size: 24px; color: #ccc;"></i>
                            <?php endif; ?>
                        </td>
                        <td><?php echo $registro['model_year']; ?></td>
                        <td><?php echo $registro['price']; ?></td>
                        <td><?php echo $registro['categoria']; ?></td>
                        <td><a class="btn btn-outline-primary" href="editar.php?txtID=<?php echo $registro['product_id']; ?>" role="button">Editar</a>
                            <a class="btn btn-outline-danger" href="index.php?txtID=<?php echo $registro['product_id']; ?>" role="button">Eliminar</a>
                    </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include("../../templates/footer.php");?>