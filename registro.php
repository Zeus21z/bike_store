<?php
session_start();

// Buscar bd.php
$possible_bd = [
    __DIR__ . '/bd.php',
    __DIR__ . '/../bd.php'
];
$bd_included = false;
foreach ($possible_bd as $p) {
    if (file_exists($p)) { 
        include $p; 
        $bd_included = true; 
        break;
    }
}
if (!$bd_included) {
    die('Error: bd.php no encontrado.');
}

// Función para incluir templates
function include_template($name) {
    $paths = [
        __DIR__ . "/templates/{$name}",
        __DIR__ . "/../templates/{$name}"
    ];
    foreach ($paths as $p) {
        if (file_exists($p)) { 
            include $p; 
            return;
        }
    }
}

// Función para verificar columnas
function table_has_columns($pdo, $table, array $cols) {
    if (!$pdo) return false;
    $placeholders = str_repeat('?,', count($cols) - 1) . '?';
    $sql = "SELECT COLUMN_NAME FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = ? AND COLUMN_NAME IN ($placeholders)";
    $params = array_merge([$table], $cols);
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $found = $stmt->fetchAll(PDO::FETCH_COLUMN);
    return count($found) === count($cols);
}

// ==========================================
// REGISTRAR EN TABLA CLIENTES (NO USUARIOS)
// ==========================================
$users_table = 'clientes';

// Verificar que la tabla tiene las columnas básicas
$required_cols = ['nombre', 'apellido', 'correo', 'telefono'];
if (!table_has_columns($conexion, $users_table, $required_cols)) {
    die("Error: La tabla 'clientes' no contiene las columnas requeridas: " . implode(', ', $required_cols));
}

$has_password = table_has_columns($conexion, $users_table, ['password']);
$has_activo = table_has_columns($conexion, $users_table, ['activo']);
$has_calle = table_has_columns($conexion, $users_table, ['calle']);
$has_ciudad = table_has_columns($conexion, $users_table, ['ciudad']);
$has_estado = table_has_columns($conexion, $users_table, ['estado']);

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $apellido = trim($_POST['apellido'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $calle = trim($_POST['calle'] ?? '');
    $ciudad = trim($_POST['ciudad'] ?? '');
    $estado = trim($_POST['estado'] ?? '');
    $password = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? '';

    // Validaciones
    if ($nombre === '' || $apellido === '' || $email === '' || $telefono === '') {
        $errors[] = 'Completa todos los campos obligatorios.';
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email no válido.';
    }
    
    if ($has_password && $password !== '') {
        if ($password !== $password2) {
            $errors[] = 'Las contraseñas no coinciden.';
        }
        if (strlen($password) < 6) {
            $errors[] = 'La contraseña debe tener al menos 6 caracteres.';
        }
    }

    if (empty($errors)) {
        // Comprobar si el correo ya existe
        $stmt = $conexion->prepare("SELECT cliente_id FROM {$users_table} WHERE correo = :email LIMIT 1");
        $stmt->execute([':email' => $email]);
        
        if ($stmt->fetch()) {
            $errors[] = 'Ya existe una cuenta con ese correo electrónico.';
        } else {
            try {
                // Preparar datos para inserción
                $campos = ['nombre', 'apellido', 'correo', 'telefono'];
                $valores = [':nombre', ':apellido', ':correo', ':telefono'];
                $params = [
                    ':nombre' => $nombre,
                    ':apellido' => $apellido,
                    ':correo' => $email,
                    ':telefono' => $telefono
                ];
                
                // Agregar campos opcionales si existen en la tabla
                if ($has_calle && $calle !== '') {
                    $campos[] = 'calle';
                    $valores[] = ':calle';
                    $params[':calle'] = $calle;
                }
                
                if ($has_ciudad && $ciudad !== '') {
                    $campos[] = 'ciudad';
                    $valores[] = ':ciudad';
                    $params[':ciudad'] = $ciudad;
                }
                
                if ($has_estado && $estado !== '') {
                    $campos[] = 'estado';
                    $valores[] = ':estado';
                    $params[':estado'] = $estado;
                }
                
                if ($has_password && $password !== '') {
                    $campos[] = 'password';
                    $valores[] = ':password';
                    $params[':password'] = password_hash($password, PASSWORD_DEFAULT);
                }
                
                if ($has_activo) {
                    $campos[] = 'activo';
                    $valores[] = '1';
                }
                
                // Construir y ejecutar INSERT
                $sql = "INSERT INTO {$users_table} (" . implode(', ', $campos) . ") 
                        VALUES (" . implode(', ', $valores) . ")";
                
                $stmt = $conexion->prepare($sql);
                $stmt->execute($params);
                
                $cliente_id = $conexion->lastInsertId();
                
                // Auto-login después del registro
                $_SESSION['usuario'] = $nombre . ' ' . $apellido;
                $_SESSION['tipo_usuario'] = 'cliente';
                $_SESSION['cliente_id'] = $cliente_id;
                $_SESSION['correo'] = $email;
                
                $_SESSION['flash_success'] = 'Registro exitoso. ¡Bienvenido!';
                
                // Redirigir a index_cliente.php
                header('Location: ../index_cliente.php');
                exit;
                
            } catch (Exception $e) {
                $errors[] = 'Error al crear la cuenta: ' . htmlspecialchars($e->getMessage());
            }
        }
    }
}

include_template('header_publico.php');
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h2 class="text-center mb-4">
                        <i class="bi bi-person-plus-fill"></i> Registro de Cliente
                    </h2>
                    
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <?php foreach($errors as $err) echo htmlspecialchars($err) . "<br>"; ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="post">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    Nombre <span class="text-danger">*</span>
                                </label>
                                <input name="nombre" 
                                       class="form-control" 
                                       value="<?php echo htmlspecialchars($_POST['nombre'] ?? ''); ?>" 
                                       required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    Apellido <span class="text-danger">*</span>
                                </label>
                                <input name="apellido" 
                                       class="form-control" 
                                       value="<?php echo htmlspecialchars($_POST['apellido'] ?? ''); ?>" 
                                       required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    Correo Electrónico <span class="text-danger">*</span>
                                </label>
                                <input name="email" 
                                       type="email" 
                                       class="form-control" 
                                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" 
                                       required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    Teléfono <span class="text-danger">*</span>
                                </label>
                                <input name="telefono" 
                                       type="tel" 
                                       class="form-control" 
                                       value="<?php echo htmlspecialchars($_POST['telefono'] ?? ''); ?>" 
                                       required>
                            </div>
                        </div>
                        
                        <?php if ($has_calle): ?>
                        <div class="mb-3">
                            <label class="form-label">Dirección</label>
                            <input name="calle" 
                                   class="form-control" 
                                   value="<?php echo htmlspecialchars($_POST['calle'] ?? ''); ?>" 
                                   placeholder="Calle y número">
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($has_ciudad && $has_estado): ?>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Ciudad</label>
                                <input name="ciudad" 
                                       class="form-control" 
                                       value="<?php echo htmlspecialchars($_POST['ciudad'] ?? ''); ?>">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Departamento</label>
                                <select name="estado" class="form-select">
                                    <option value="">Seleccione...</option>
                                    <option value="Scz">Santa Cruz</option>
                                    <option value="Lpz">La Paz</option>
                                    <option value="Cbba">Cochabamba</option>
                                    <option value="Tarija">Tarija</option>
                                    <option value="Oruro">Oruro</option>
                                    <option value="Potosi">Potosí</option>
                                    <option value="Chuquisaca">Chuquisaca</option>
                                    <option value="Beni">Beni</option>
                                    <option value="Pando">Pando</option>
                                </select>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($has_password): ?>
                        <hr class="my-4">
                        <h5 class="mb-3">Contraseña (Opcional)</h5>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Contraseña</label>
                                <input name="password" 
                                       type="password" 
                                       class="form-control" 
                                       placeholder="Mínimo 6 caracteres">
                                <small class="form-text text-muted">
                                    Deja en blanco si prefieres acceder solo con tu nombre
                                </small>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Repetir Contraseña</label>
                                <input name="password2" 
                                       type="password" 
                                       class="form-control" 
                                       placeholder="Repite la contraseña">
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <div class="d-grid mb-3">
                            <button class="btn btn-primary btn-lg">
                                <i class="bi bi-person-plus"></i> Crear Cuenta
                            </button>
                        </div>
                        
                        <div class="text-center">
                            <small class="text-muted">
                                ¿Ya tienes cuenta? 
                                <a href="login_cliente.php">Iniciar sesión aquí</a>
                            </small>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_template('footer.php'); ?>