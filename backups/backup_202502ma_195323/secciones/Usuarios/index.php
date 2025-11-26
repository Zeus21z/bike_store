<?php include("../../bd.php");
// Detectar nombre de la tabla y su clave primaria
$tabla = "usuarios";

// Obtener metadatos de columnas
$sentenciaCols = $conexion->prepare("DESCRIBE $tabla");
$sentenciaCols->execute();
$columnas = $sentenciaCols->fetchAll(PDO::FETCH_ASSOC);

$columnaPk = null;
foreach ($columnas as $col) {
    if (isset($col["Key"]) && $col["Key"] === "PRI") {
        $columnaPk = $col["Field"];
        break;
    }
}

// Eliminar registro si se recibe ID
if ($columnaPk && isset($_GET['txtID'])) {
    $txtID = $_GET['txtID'];
    $sentencia = $conexion->prepare("DELETE FROM $tabla WHERE $columnaPk = :id");
    $sentencia->bindParam(":id", $txtID);
    $sentencia->execute();
    $mensaje = "Registro eliminado";
    header("Location:index.php?mensaje=".$mensaje);
    exit;
}

// Listar registros
$sentencia = $conexion->prepare("SELECT * FROM $tabla");
$sentencia->execute();
$lista_usuarios = $sentencia->fetchAll(PDO::FETCH_ASSOC);
?>
<?php include("../../templates/header.php");?>
<br>
<h2>Listado de Usuarios</h2>
<div class="card">
    <div class="card-header">
        <a name="" id="" class="btn btn-outline-primary" href="crear.php" role="button">
            Nuevo
        </a>
        <a name="" id="" class="btn btn-outline-primary" href="../../index.php" role="button">
            Inicio
        </a>

        
    </div>
    <div class="card-body">
        <div class="table-responsive-sm">
            <table class="table table-primary table-striped align-middle">
                <thead>
                    <tr>
                        <?php foreach ($columnas as $col) { ?>
                            <th scope="col"><?php echo htmlspecialchars($col['Field']); ?></th>
                        <?php } ?>
                        <th scope="col">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($lista_usuarios as $registro) { ?>
                    <tr class="">
                        <?php foreach ($columnas as $col) { $f = $col['Field']; $esPassword = (bool)preg_match('/pass|contra|password/i', $f); ?>
                            <td><?php echo $esPassword ? '*******' : htmlspecialchars(isset($registro[$f]) ? (string)$registro[$f] : ""); ?></td>
                        <?php } ?>
                        <td>
                            <?php if ($columnaPk) { $idVal = $registro[$columnaPk]; ?>
                                <a class="btn btn-outline-primary btn-sm" href="editar.php?txtID=<?php echo urlencode($idVal); ?>" role="button">Editar</a>
                                <a class="btn btn-outline-danger btn-sm" href="index.php?txtID=<?php echo urlencode($idVal); ?>" role="button" onclick="return confirm('¿Eliminar registro?');">Eliminar</a>
                            <?php } else { ?>
                                <span class="text-muted">Sin PK</span>
                            <?php } ?>
                        </td>
                    </tr>
                    <?php } ?>
                    <?php if (empty($lista_usuarios)) { ?>
                        <tr><td colspan="<?php echo count($columnas)+1; ?>" class="text-center">Sin registros</td></tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include("../../templates/footer.php");?>

