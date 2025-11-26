<?php include("../../bd.php");
$tabla = "usuarios";

// Obtener columnas y PK
$sentenciaCols = $conexion->prepare("DESCRIBE $tabla");
$sentenciaCols->execute();
$columnas = $sentenciaCols->fetchAll(PDO::FETCH_ASSOC);
$columnaPk = null;
foreach ($columnas as $col) { if ($col['Key'] === 'PRI') { $columnaPk = $col['Field']; break; } }

if (!$columnaPk) { die('No hay clave primaria en la tabla usuarios'); }

if (isset($_GET['txtID'])) {
    $txtID = $_GET['txtID'];
    $sentencia = $conexion->prepare("SELECT * FROM $tabla WHERE $columnaPk = :id LIMIT 1");
    $sentencia->bindParam(":id", $txtID);
    $sentencia->execute();
    $registro = $sentencia->fetch(PDO::FETCH_ASSOC);
}

if ($_POST) {
    $txtID = isset($_POST['txtID']) ? $_POST['txtID'] : null;
    $sets = [];
    $valores = [];
    foreach ($columnas as $col) {
        $campo = $col['Field'];
        if ($campo === $columnaPk) { continue; }
        if (array_key_exists($campo, $_POST)) {
            // Si es password y viene vacío, no actualizar
            $esPassword = (bool)preg_match('/pass|contra|password/i', $campo);
            if ($esPassword && $_POST[$campo] === '') { continue; }
            $sets[] = "$campo = :$campo";
            $valores[":".$campo] = $_POST[$campo];
        }
    }
    if (!empty($sets) && $txtID !== null) {
        $sql = "UPDATE $tabla SET ".implode(", ", $sets)." WHERE $columnaPk = :id";
        $sentencia = $conexion->prepare($sql);
        foreach ($valores as $k => $v) { $sentencia->bindValue($k, $v); }
        $sentencia->bindValue(":id", $txtID);
        $sentencia->execute();
    }
    $mensaje = "Registro actualizado";
    header("Location:index.php?mensaje=".$mensaje);
    exit;
}
?>
<?php include("../../templates/header.php");?>
<br>
<h2>Editar usuario</h2>
<div class="card">
    <div class="card-header">Datos del usuario</div>
    <div class="card-body">
        <form action="" method="post">
            <div class="mb-3">
                <label for="txtID" class="form-label">ID:</label>
                <input type="text" value="<?php echo htmlspecialchars(isset($registro[$columnaPk])?$registro[$columnaPk]: (isset($txtID)?$txtID:'')); ?>" class="form-control" readonly name="txtID" id="txtID">
            </div>
            <?php foreach ($columnas as $col) { $campo = $col['Field']; if ($campo === $columnaPk) { continue; }
                $esPassword = (bool)preg_match('/pass|contra|password/i', $campo);
                $tipo = $esPassword ? 'password' : 'text';
                $valor = $esPassword ? '' : (isset($registro[$campo])?$registro[$campo]: '');
                $placeholder = $esPassword ? '*******' : ('Ingrese '.$campo);
            ?>
                <div class="mb-3">
                    <label for="<?php echo $campo; ?>" class="form-label"><?php echo ucfirst(str_replace('_',' ', $campo)); ?></label>
                    <input type="<?php echo $tipo; ?>" class="form-control" name="<?php echo $campo; ?>" id="<?php echo $campo; ?>" value="<?php echo htmlspecialchars($valor); ?>" placeholder="<?php echo $placeholder; ?>">
                </div>
            <?php } ?>
            <button type="submit" class="btn btn-outline-success">Guardar cambios</button>
            <a class="btn btn-outline-primary" href="index.php" role="button">Cancelar</a>
        </form>
    </div>
</div>
<?php include("../../templates/footer.php");?>

