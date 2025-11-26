<?php
session_start();

// Buscar bd.php
$possible_bd = [
    __DIR__ . '/../bd.php',
    __DIR__ . '/../../bd.php',
    __DIR__ . '/bd.php'
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
// FORZAR TABLA CLIENTES (NO USUARIOS)
// ==========================================
$users_table = 'clientes';
$id_col = 'cliente_id';

// Detectar columnas disponibles
$has_password = table_has_columns($conexion, $users_table, ['password']);
$has_correo = table_has_columns($conexion, $users_table, ['correo']);
$has_nombre = table_has_columns($conexion, $users_table, ['nombre']);
$has_activo = table_has_columns($conexion, $users_table, ['activo']);

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($identifier === '' || $password === '') {
        $errors[] = 'Completa todos los campos.';
    }

    if (empty($errors)) {
        // Buscar cliente por correo o nombre
        $sql = "SELECT * FROM {$users_table} WHERE ";
        if ($has_correo) {
            $sql .= "correo = :ident";
        } else {
            $sql .= "nombre = :ident";
        }
        $sql .= " LIMIT 1";
        
        $stmt = $conexion->prepare($sql);
        $stmt->execute([':ident' => $identifier]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            $errors[] = 'Cliente no encontrado.';
        } else {
            $password_ok = false;
            
            if ($has_password && !empty($user['password'])) {
                $stored = $user['password'];
                
                // Verificar si es hash o texto plano
                if (strpos($stored, '$2y$') === 0 || strpos($stored, '$2a$') === 0) {
                    // Es hash bcrypt
                    if (password_verify($password, $stored)) {
                        $password_ok = true;
                    }
                } elseif ($stored === $password) {
                    // Es texto plano, convertir a hash
                    $newhash = password_hash($password, PASSWORD_DEFAULT);
                    try {
                        $up = $conexion->prepare("UPDATE {$users_table} SET password = :h WHERE {$id_col} = :id");
                        $up->execute([':h' => $newhash, ':id' => $user[$id_col]]);
                    } catch (Exception $e) { /* ignorar */ }
                    $password_ok = true;
                }
            } else {
                // No hay columna password, permitir login solo con correo/nombre
                $password_ok = true;
            }

            if (!$password_ok) {
                $errors[] = 'Contraseña incorrecta.';
            }

            // Verificar si está activo
            if ($has_activo && isset($user['activo']) && intval($user['activo']) === 0) {
                $errors[] = 'Cuenta inactiva. Contacta al administrador.';
            }

            if (empty($errors)) {
                // Establecer sesión de CLIENTE
                $_SESSION['usuario'] = $user['nombre'] . ' ' . ($user['apellido'] ?? '');
                $_SESSION['tipo_usuario'] = 'cliente';
                $_SESSION['cliente_id'] = $user[$id_col];
                $_SESSION['correo'] = $user['correo'] ?? '';

                // Actualizar ultimo_acceso si existe
                if (table_has_columns($conexion, $users_table, ['ultimo_acceso'])) {
                    try {
                        $u = $conexion->prepare("UPDATE {$users_table} SET ultimo_acceso = NOW() WHERE {$id_col} = :id");
                        $u->execute([':id' => $user[$id_col]]);
                    } catch (Exception $e) { /* ignorar */ }
                }

                // Redirigir a index_cliente.php
                header('Location: ../index_cliente.php');
                exit;
            }
        }
    }
}

// Incluir header
$paths = [__DIR__ . '/../templates/header_publico.php', __DIR__ . '/templates/header_publico.php'];
foreach ($paths as $p) {
    if (file_exists($p)) { 
        include $p; 
        break; 
    }
}
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h2 class="text-center mb-4">
                        <i class="bi bi-person-circle"></i> Login Cliente
                    </h2>
                    
                    <?php if (!empty($_SESSION['flash_success'])): ?>
                        <div class="alert alert-success">
                            <?php echo htmlspecialchars($_SESSION['flash_success']); 
                            unset($_SESSION['flash_success']); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <?php foreach($errors as $e) echo htmlspecialchars($e) . "<br>"; ?>
                        </div>
                    <?php endif; ?>

                    <form method="post">
                        <div class="mb-3">
                            <label class="form-label">
                                <?php echo $has_correo ? 'Correo Electrónico' : 'Nombre'; ?>
                            </label>
                            <input name="email" 
                                   type="text" 
                                   class="form-control" 
                                   placeholder="<?php echo $has_correo ? 'tu@email.com' : 'Tu nombre'; ?>"
                                   value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" 
                                   required>
                        </div>
                        
                        <?php if ($has_password): ?>
                        <div class="mb-3">
                            <label class="form-label">Contraseña</label>
                            <input name="password" 
                                   type="password" 
                                   class="form-control" 
                                   placeholder="••••••••"
                                   required>
                        </div>
                        <?php endif; ?>
                        
                        <div class="mb-3">
                            <a href="olvide_password.php">¿Olvidaste tu contraseña?</a>
                        </div>
                        
                        <div class="d-grid mb-3">
                            <button class="btn btn-primary btn-lg">
                                <i class="bi bi-box-arrow-in-right"></i> Entrar
                            </button>
                        </div>
                        
                        <div class="text-center">
                            <small class="text-muted">
                                ¿No tienes cuenta? 
                                <a href="registro.php">Regístrate aquí</a>
                            </small>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$paths = [__DIR__ . '/../templates/footer.php', __DIR__ . '/templates/footer.php'];
foreach ($paths as $p) {
    if (file_exists($p)) { 
        include $p; 
        break; 
    }
}
?>