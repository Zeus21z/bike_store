<?php
/**
 * BIKE STORE - Login
 * VERSIÓN FUNCIONAL - Corregido error de $conexion
 */

// IMPORTANTE: Incluir bd.php ANTES de cualquier cosa
require_once(__DIR__ . '/bd.php');

// Verificar que la conexión existe
if (!isset($conexion) || $conexion === null) {
    die("ERROR CRÍTICO: No se pudo establecer conexión a la base de datos. Verifique bd.php");
}

$error = "";

// Si ya está logueado, redirigir según tipo de usuario
if (isset($_SESSION['usuario'])) {
    $tipo_usuario = $_SESSION['tipo_usuario'] ?? 'admin';
    if ($tipo_usuario === 'cliente') {
        header("Location: index_cliente.php");
    } else {
        header("Location: index_admin.php");
    }
    exit;
}

// Procesar login cuando se envía el formulario
if ($_POST) {
    $usuario = isset($_POST['usuario']) ? trim($_POST['usuario']) : '';
    $contrasenia = isset($_POST['contrasenia']) ? $_POST['contrasenia'] : '';
    $recordar = isset($_POST['recordar']);

    // ==========================================
    // LOGIN PARA ADMINISTRADORES - Usuario y contraseña
    // ==========================================
    try {
            $stmt = $conexion->prepare("
                SELECT * FROM usuarios 
                WHERE usuario = :u 
                LIMIT 1
            ");
            $stmt->bindParam(":u", $usuario, PDO::PARAM_STR);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                // Verificar contraseña
                $password_valida = false;
                
                if (isset($user['password']) && !empty($user['password'])) {
                    // Si la contraseña empieza con $2y$ es un hash bcrypt
                    if (strpos($user['password'], '$2y$') === 0) {
                        $password_valida = password_verify($contrasenia, $user['password']);
                    } else {
                        // Comparación directa para contraseñas sin hash
                        $password_valida = ($contrasenia === $user['password']);
                    }
                }
                
                if ($password_valida) {
                    // Login exitoso
                    $_SESSION['usuario'] = $user['usuario'];
                    $_SESSION['tipo_usuario'] = isset($user['rol']) ? $user['rol'] : 'admin';
                    $_SESSION['user_id'] = $user['user_id'];
                    
                    // Registrar último acceso si la columna existe
                    try {
                        $stmt_update = $conexion->prepare("
                            UPDATE usuarios 
                            SET ultimo_acceso = NOW() 
                            WHERE user_id = :id
                        ");
                        $stmt_update->execute([':id' => $user['user_id']]);
                    } catch (PDOException $e) {
                        // Columna ultimo_acceso no existe todavía, ignorar
                    }
                    
                    // Guardar cookie si solicitó recordar
                    if ($recordar) {
                        setcookie('usuario', $usuario, time() + 60*60*24*30, '/');
                    } else {
                        setcookie('usuario', '', time() - 3600, '/');
                    }
                    
                    // Log de acceso exitoso
                    log_message('INFO', 'Login admin exitoso', [
                        'user_id' => $user['user_id'],
                        'usuario' => $user['usuario']
                    ]);
                    
                    header("Location: index_admin.php");
                    exit;
                } else {
                    $error = "Contraseña incorrecta";
                    log_message('WARNING', 'Contraseña incorrecta para usuario: ' . $usuario);
                }
            } else {
                $error = "Usuario no encontrado";
                log_message('WARNING', 'Usuario no encontrado: ' . $usuario);
            }
        } catch (PDOException $e) {
            $error = "Error al procesar el login: " . $e->getMessage();
            log_message('ERROR', 'Error en login admin', [
                'error' => $e->getMessage()
            ]);
    }
}
?>
<?php include("templates/header.php"); ?>

<div class="d-flex align-items-center justify-content-center" style="min-height:80vh;">
    <div class="card shadow-sm" style="max-width:450px;width:100%;">
        <div class="card-body p-4">
            <div class="text-center mb-4">
                <h3 class="mb-1">🚴 Bike Store</h3>
                <h5 class="text-muted">Iniciar Sesión</h5>
            </div>
            
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill"></i> <?php echo htmlspecialchars($error); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <form method="post" id="loginForm">
                <!-- Selector de tipo de usuario -->
                <div class="mb-3">
                    <label class="form-label fw-bold">Tipo de acceso</label>
                    <div class="d-grid gap-2">
                        <label class="btn btn-primary active">
                            <i class="bi bi-shield-lock"></i> Administrador
                        </label>

                        <a href="auth/login_cliente.php" class="btn btn-outline-success">
                            <i class="bi bi-person"></i> Cliente
                        </a>
                    </div>
                </div>
                
                <!-- Campo de usuario/nombre -->
                <div class="mb-3">
                    <label for="usuario" class="form-label fw-bold" id="usuarioLabel">Usuario</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-person-circle"></i></span>
                        <input type="text" 
                               class="form-control" 
                               id="usuario" 
                               name="usuario" 
                               placeholder="Tu usuario" 
                               value="<?php echo isset($_COOKIE['usuario']) ? htmlspecialchars($_COOKIE['usuario']) : ''; ?>" 
                               required
                               autocomplete="username">
                    </div>
                    <small class="form-text text-muted" id="usuarioHint">Ingrese su nombre de usuario</small>
                </div>
                
                <!-- Campo de contraseña (solo admin) -->
                <div class="mb-3" id="passwordField">
                    <label for="contrasenia" class="form-label fw-bold">Contraseña</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                        <input type="password" 
                               class="form-control" 
                               id="contrasenia" 
                               name="contrasenia" 
                               placeholder="••••••••"
                               autocomplete="current-password">
                        <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                            <i class="bi bi-eye" id="toggleIcon"></i>
                        </button>
                    </div>
                </div>
                
                <!-- Recordar usuario -->
                <div class="form-check mb-3">
                    <input class="form-check-input" 
                           type="checkbox" 
                           value="1" 
                           id="recordar" 
                           name="recordar" 
                           <?php echo isset($_COOKIE['usuario']) ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="recordar">
                        Recordar mi usuario
                    </label>
                </div>
                
                <!-- Botón de login -->
                <div class="d-grid mb-3">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="bi bi-box-arrow-in-right"></i> Ingresar
                    </button>
                </div>
                
                <!-- Enlaces adicionales -->
                <div class="text-center">
                    <small class="text-muted">
                        ¿No tienes cuenta? <a href="registro.php">Regístrate aquí</a>
                    </small>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const passwordInput = document.getElementById('contrasenia');
    const togglePassword = document.getElementById('togglePassword');
    const toggleIcon = document.getElementById('toggleIcon');
    
    if (togglePassword) {
        togglePassword.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            toggleIcon.classList.toggle('bi-eye');
            toggleIcon.classList.toggle('bi-eye-slash');
        });
    }
    
    // Validación del formulario
    const loginForm = document.getElementById('loginForm');
    loginForm.addEventListener('submit', function(e) {
        const usuarioInput = document.getElementById('usuario');
        const usuario = usuarioInput.value.trim();
        
        if (!usuario) {
            e.preventDefault();
            alert('Por favor ingrese su usuario o nombre');
            usuarioInput.focus();
            return false;
        }
        
        const password = passwordInput.value;
        if (!password) {
            e.preventDefault();
            alert('Por favor ingrese su contraseña');
            passwordInput.focus();
            return false;
        }
        
        // Mostrar loading en botón
        const btn = this.querySelector('button[type="submit"]');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Ingresando...';
    });
});
</script>

<?php include("templates/footer.php"); ?>