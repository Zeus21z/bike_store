<?php include("../../bd.php");
// Detectar si existe columna foto
$tieneFoto = false;
try{
    $d=$conexion->prepare("DESCRIBE clientes");
    $d->execute();
    $cols=$d->fetchAll(PDO::FETCH_ASSOC);
    foreach($cols as $col){ if($col['Field']==='foto'){ $tieneFoto=true; break; } }
}catch(Exception $e){ $tieneFoto=false; }
// Eliminar cliente
if (isset($_GET['txtID'])) {
    $txtID = $_GET['txtID'];
    $sentencia = $conexion->prepare("DELETE FROM clientes WHERE cliente_id=:id");
    $sentencia->bindParam(":id", $txtID);
    $sentencia->execute();
    $mensaje = "Registro eliminado";
    header("Location:index.php?mensaje=".$mensaje);
    exit;
}

// Listado de clientes
$sentencia=$conexion->prepare("SELECT * FROM clientes ORDER BY cliente_id ASC");
$sentencia->execute();
$lista_clientes=$sentencia->fetchAll(PDO::FETCH_ASSOC);
?>
<?php include("../../templates/header.php");?>
<br>
<h2>Listado de Clientes</h2>
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <a class="btn btn-outline-primary" href="crear.php">Nuevo</a>
        </div>
        <div class="d-flex gap-2">
            <a class="btn btn-outline-success" href="generar_pdf.php" target="_blank">
                <i class="bi bi-printer"></i> Imprimir PDF
            </a>
            <a class="btn btn-outline-secondary" href="../../index.php">Inicio</a>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive-sm">
            <table class="table table-primary table-striped align-middle">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombres y Apellidos</th>
                        <?php if($tieneFoto){ ?>
                        <th style="width:70px">Foto</th>
                        <?php } ?>
                        <th>Telefono</th>
                        <th>Correo</th>
                        <th>Calle</th>
                        <th>Ciudad</th>
                        <th>Depa</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($lista_clientes as $c) { ?>
                    <tr>
                        <td><?php echo $c['cliente_id']; ?></td>
                        <td><?php echo htmlspecialchars(($c['nombre'] ?? '').' '.($c['apellido'] ?? '')); ?></td>
                        <?php if($tieneFoto){ ?>
                        <td class="text-center">
                            <?php if(!empty($c['foto'])){ ?>
                                <img src="img/<?php echo htmlspecialchars($c['foto']); ?>" alt="Cliente" width="50" height="50" class="rounded object-fit-cover">
                            <?php } ?>
                        </td>
                        <?php } ?>
                        <td><?php echo htmlspecialchars($c['telefono'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($c['correo'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($c['calle'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($c['ciudad'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($c['estado'] ?? ''); ?></td>
                        <td class="text-nowrap">
                            <div class="btn-group" role="group">
                                <a class="btn btn-sm btn-outline-primary" href="editar.php?txtID=<?php echo $c['cliente_id']; ?>">Editar</a>
                                <a class="btn btn-sm btn-outline-danger" href="index.php?txtID=<?php echo $c['cliente_id']; ?>" onclick="return confirm('¿Eliminar registro?');">Eliminar</a>
                            </div>
                        </td>
                    </tr>
                    <?php } ?>
                    <?php if (empty($lista_clientes)) { ?>
                        <tr><td colspan="9" class="text-center">Sin registros</td></tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include("../../templates/footer.php");?>

