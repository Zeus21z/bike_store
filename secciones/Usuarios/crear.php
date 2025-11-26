<?php include("../../bd.php");
$tabla = "usuarios";

// Obtener columnas para generar formulario dinámico
$sentenciaCols = $conexion->prepare("DESCRIBE $tabla");
$sentenciaCols->execute();
$columnas = $sentenciaCols->fetchAll(PDO::FETCH_ASSOC);

// Detectar PK y columnas auto_increment para excluirlas del formulario
$columnaPk = null;
$excluir = [];
foreach ($columnas as $col) {
    if ($col['Key'] === 'PRI') { $columnaPk = $col['Field']; }
    if (stripos($col['Extra'], 'auto_increment') !== false) { $excluir[$col['Field']] = true; }
}

if ($_POST) {
    $campos = [];
    $placeholders = [];
    $valores = [];
    foreach ($columnas as $col) {
        $campo = $col['Field'];
        if (isset($excluir[$campo])) { continue; }
        $campos[] = $campo;
        $placeholders[] = ":".$campo;
        $valores[":".$campo] = isset($_POST[$campo]) ? $_POST[$campo] : null;
    }
    if (!empty($campos)) {
        $sql = "INSERT INTO $tabla (".implode(",", $campos).") VALUES (".implode(",", $placeholders).")";
        $sentencia = $conexion->prepare($sql);
        foreach ($valores as $k => $v) { $sentencia->bindValue($k, $v); }
        $sentencia->execute();
    }
    $mensaje = "Registro agregado";
    header("Location:index.php?mensaje=".$mensaje);
    exit;
}
?>
<?php include("../../templates/header.php");?>
<br>
<h2>Crear usuario</h2>
<div class="card">
    <div class="card-header">Datos del usuario</div>
    <div class="card-body">
        <form action="" method="post">
            <?php foreach ($columnas as $col) { $campo = $col['Field']; if (isset($excluir[$campo])) { continue; }
                $esPassword = (bool)preg_match('/pass|contra|password/i', $campo);
                $tipo = $esPassword ? 'password' : 'text';
            ?>
                <div class="mb-3">
                    <label for="<?php echo $campo; ?>" class="form-label"><?php echo ucfirst(str_replace('_',' ', $campo)); ?></label>
                    <input type="<?php echo $tipo; ?>" class="form-control" name="<?php echo $campo; ?>" id="<?php echo $campo; ?>" placeholder="<?php echo $esPassword ? '*******' : ('Ingrese '.$campo); ?>">
                </div>
            <?php } ?>
            <button type="submit" class="btn btn-outline-success">Guardar</button>
            <a class="btn btn-outline-primary" href="index.php" role="button">Cancelar</a>
        </form>
    </div>
</div>
<?php include("../../templates/footer.php");?>

