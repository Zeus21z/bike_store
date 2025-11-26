<?php include("../../bd.php");

// Crear categoría
if (isset($_POST['descripcion']) && $_POST['descripcion'] !== '') {
    $descripcion = trim($_POST['descripcion']);
    $stmt = $conexion->prepare("INSERT INTO categorias (descripcion) VALUES (:d)");
    try{
        $stmt->bindParam(":d", $descripcion);
        $stmt->execute();
        $msg = "Categoría creada";
    } catch(Exception $e){
        $msg = "Error: ".$e->getMessage();
    }
    header("Location: categorias.php?mensaje=".urlencode($msg));
    exit;
}

// Eliminar categoría
if (isset($_GET['del'])) {
    $id = $_GET['del'];
    try{
        $del = $conexion->prepare("DELETE FROM categorias WHERE category_id=:id");
        $del->bindParam(":id", $id);
        $del->execute();
        $msg = "Categoría eliminada";
    }catch(Exception $e){
        $msg = "No se puede eliminar: ".$e->getMessage();
    }
    header("Location: categorias.php?mensaje=".urlencode($msg));
    exit;
}

// Listado
$lista = $conexion->query("SELECT * FROM categorias ORDER BY category_id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<?php include("../../templates/header.php");?>
<br>
<h2>Categorías</h2>
<div class="row g-3">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">Nueva categoría</div>
            <div class="card-body">
                <form method="post">
                    <div class="mb-3">
                        <label for="descripcion" class="form-label">Descripción</label>
                        <input type="text" class="form-control" id="descripcion" name="descripcion" placeholder="Ej: Autos" required>
                    </div>
                    <button class="btn btn-outline-success" type="submit">Guardar</button>
                    <a class="btn btn-outline-primary" href="../Productos/" role="button">Volver a Productos</a>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Listado</span>
                <a class="btn btn-sm btn-outline-secondary" href="categorias.php">Refrescar</a>
            </div>
            <div class="card-body">
                <div class="table-responsive-sm">
                    <table class="table table-primary table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Descripción</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($lista as $c) { ?>
                                <tr>
                                    <td><?php echo $c['category_id']; ?></td>
                                    <td><?php echo htmlspecialchars($c['descripcion']); ?></td>
                                    <td>
                                        <a class="btn btn-sm btn-outline-danger" href="categorias.php?del=<?php echo $c['category_id']; ?>" onclick="return confirm('¿Eliminar categoría?');">Eliminar</a>
                                    </td>
                                </tr>
                            <?php } ?>
                            <?php if(empty($lista)) { ?>
                                <tr><td colspan="3" class="text-center">Sin registros</td></tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include("../../templates/footer.php");?>

