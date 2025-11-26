<?php include("../../bd.php");

// Eliminar categoría por GET
if (isset($_GET['txtID'])) {
    $txtID = $_GET['txtID'];
    $sentencia = $conexion->prepare("DELETE FROM categorias WHERE category_id=:id");
    $sentencia->bindParam(":id", $txtID);
    $sentencia->execute();
    $mensaje = "Registro eliminado";
    header("Location:index.php?mensaje=".$mensaje);
    exit;
}

// Listado
$sentencia=$conexion->prepare("SELECT * FROM categorias ORDER BY category_id DESC");
$sentencia->execute();
$lista_categorias=$sentencia->fetchAll(PDO::FETCH_ASSOC);
?>
<?php include("../../templates/header.php");?>
<br>

<?php if(isset($_GET['mensaje'])) { ?>
    <div class="alert alert-success" role="alert">
        <?php echo htmlspecialchars($_GET['mensaje']); ?>
    </div>
<?php } ?>

<h2>Listado de Categorías</h2>
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <a class="btn btn-outline-primary" href="crear.php">Nuevo</a>
        </div>
        <div>
            <a class="btn btn-outline-primary" href="../Productos/">Volver a Productos</a>
            <a class="btn btn-outline-primary" href="../../index.php">Inicio</a>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive-sm">
            <table class="table table-primary table-striped align-middle">
                <thead>
                    <tr>
                        <th scope="col">ID</th>
                        <th scope="col">Descripción</th>
                        <th scope="col">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($lista_categorias as $registro) { ?>
                    <tr class="">
                        <td scope="row"><?php echo $registro['category_id']; ?></td>
                        <td><?php echo htmlspecialchars($registro['descripcion']); ?></td>
                        <td>
                            <a class="btn btn-outline-primary btn-sm" href="editar.php?txtID=<?php echo $registro['category_id']; ?>" role="button">Editar</a>
                            <a class="btn btn-outline-danger btn-sm" href="index.php?txtID=<?php echo $registro['category_id']; ?>" role="button" onclick="return confirm('¿Eliminar registro?');">Eliminar</a>
                        </td>
                    </tr>
                    <?php } ?>
                    <?php if (empty($lista_categorias)) { ?>
                        <tr><td colspan="3" class="text-center">Sin registros</td></tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include("../../templates/footer.php");?>


