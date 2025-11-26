<?php include("../../bd.php");

// Eliminar tienda
if(isset($_GET['txtID'])){
    $txtID = (isset($_GET['txtID'])) ? $_GET['txtID'] : "";
    
    // Verificar si la tienda tiene inventario antes de eliminar
    $sentencia_inventario = $conexion->prepare("SELECT COUNT(*) FROM inventario WHERE tienda_id = :id");
    $sentencia_inventario->bindParam(":id", $txtID);
    $sentencia_inventario->execute();
    $tiene_inventario = $sentencia_inventario->fetchColumn();
    
    if($tiene_inventario > 0){
        $mensaje = "No se puede eliminar la tienda porque tiene productos en inventario";
        header("Location:index.php?mensaje=".$mensaje);
        exit;
    }
    
    // Eliminar la tienda
    $sentencia = $conexion->prepare("DELETE FROM tiendas WHERE tienda_id = :id");
    $sentencia->bindParam(":id", $txtID);
    $sentencia->execute();
    
    $mensaje = "Registro eliminado";
    header("Location:index.php?mensaje=".$mensaje);
}

// Consulta tiendas para mostrar
$sentencia = $conexion->prepare("SELECT * FROM tiendas");
$sentencia->execute();
$lista_tiendas = $sentencia->fetchAll(PDO::FETCH_ASSOC);

?>
<?php include("../../templates/header.php");?>
<br>
<h2>Listado de Tiendas</h2>
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <a name="" id="" class="btn btn-outline-primary" href="crear.php" role="button">
                Nueva Tienda
            </a>
            <a name="" id="" class="btn btn-outline-primary" href="../../index.php" role="button">
                Inicio
            </a>
        </div>
    </div>
    <div class="card-body">
        <?php if(isset($_GET['mensaje'])) { ?>
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <?php echo $_GET['mensaje']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php } ?>
        
        <div class="table-responsive-sm">
            <table class="table table-primary">
                <thead>
                    <tr>
                        <th scope="col">ID</th>
                        <th scope="col">Nombre Tienda</th>
                        <th scope="col">Teléfono</th>
                        <th scope="col">Email</th>
                        <th scope="col">Dirección</th>
                        <th scope="col">Ciudad</th>
                        <th scope="col">Estado</th>
                        <th scope="col">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($lista_tiendas as $registro) { ?>
                    <tr class="">
                        <td scope="row"><?php echo $registro['tienda_id']; ?></td>
                        <td><?php echo $registro['nombre_tienda']; ?></td>
                        <td><?php echo $registro['telefono']; ?></td>
                        <td><?php echo $registro['email']; ?></td>
                        <td><?php echo $registro['calle']; ?></td>
                        <td><?php echo $registro['ciudad']; ?></td>
                        <td><?php echo $registro['estado']; ?></td>
                        <td>
                            <a class="btn btn-outline-primary" href="editar.php?txtID=<?php echo $registro['tienda_id']; ?>" role="button">Editar</a>
                          
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include("../../templates/footer.php");?>