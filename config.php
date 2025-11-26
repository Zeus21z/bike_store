<?php
/**
 * BIKE STORE - Archivo de Configuración Central
 * Versión: 2.0
 * Fecha: 2025-11-25
 * 
 * IMPORTANTE: Este archivo contiene información sensible.
 * NO subir a repositorios públicos.
 */

// ============================================
// CONFIGURACIÓN DE BASE DE DATOS
// ============================================
define('DB_HOST', 'localhost');
define('DB_NAME', 'zeta');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// ============================================
// CONFIGURACIÓN DE LA APLICACIÓN
// ============================================
define('APP_NAME', 'Bike Store');
define('APP_VERSION', '2.0');
define('APP_URL', 'http://localhost/bike_store/');
define('APP_ENV', 'development'); // development | production

// ============================================
// CONFIGURACIÓN DE SESIONES
// ============================================
define('SESSION_LIFETIME', 3600); // 1 hora en segundos
define('SESSION_NAME', 'BIKE_STORE_SESSION');
define('SESSION_SECURE', false); // true en producción con HTTPS
define('SESSION_HTTPONLY', true);
define('SESSION_SAMESITE', 'Lax'); // Lax | Strict | None

// ============================================
// CONFIGURACIÓN DE SEGURIDAD
// ============================================
define('PASSWORD_MIN_LENGTH', 8);
define('PASSWORD_HASH_ALGO', PASSWORD_BCRYPT);
define('PASSWORD_HASH_COST', 12);
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOCKOUT_TIME', 900); // 15 minutos en segundos

// Token CSRF
define('CSRF_TOKEN_NAME', 'csrf_token');
define('CSRF_TOKEN_EXPIRE', 3600); // 1 hora

// ============================================
// CONFIGURACIÓN DE EMAIL (SMTP)
// ============================================
define('MAIL_HOST', 'smtp.gmail.com');
define('MAIL_PORT', 587);
define('MAIL_USERNAME', 'tu_correo@gmail.com'); // CAMBIAR
define('MAIL_PASSWORD', ''); // CAMBIAR - Usar App Password de Gmail
define('MAIL_FROM_EMAIL', 'noreply@bikestore.com');
define('MAIL_FROM_NAME', 'Bike Store');
define('MAIL_ENCRYPTION', 'tls'); // tls | ssl

// ============================================
// CONFIGURACIÓN DE ARCHIVOS
// ============================================
define('UPLOAD_MAX_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
define('UPLOAD_DIR', __DIR__ . '/secciones/Productos/img/');
define('CLIENTE_IMG_DIR', __DIR__ . '/secciones/clientes/img/');

// ============================================
// CONFIGURACIÓN DE PRODUCTOS
// ============================================
define('PRODUCTOS_POR_PAGINA', 12);
define('DESTACADOS_MAX', 8);
define('MAS_VENDIDOS_MAX', 8);
define('STOCK_MINIMO_ALERTA', 5);

// ============================================
// CONFIGURACIÓN DE CARRITO
// ============================================
define('CARRITO_EXPIRACION', 86400); // 24 horas en segundos
define('ENVIO_GRATIS_MINIMO', 500); // Monto mínimo para envío gratis

// ============================================
// CONFIGURACIÓN DE FACTURACIÓN
// ============================================
define('IVA_PORCENTAJE', 13);
define('MONEDA', 'BOB');
define('MONEDA_SIMBOLO', 'Bs.');
define('FACTURA_PREFIJO', 'FAC-');

// ============================================
// RUTAS DE LIBRERÍAS
// ============================================
define('TCPDF_PATH', __DIR__ . '/libs/tcpdf/tcpdf.php');
define('PHPQRCODE_PATH', __DIR__ . '/libs/phpqrcode/qrlib.php');

// ============================================
// CONFIGURACIÓN DE LOGS
// ============================================
define('ENABLE_LOGS', true);
define('LOG_FILE', __DIR__ . '/logs/app.log');
define('LOG_LEVEL', 'DEBUG'); // DEBUG | INFO | WARNING | ERROR

// ============================================
// ZONAS HORARIAS
// ============================================
date_default_timezone_set('America/La_Paz');

// ============================================
// CONFIGURACIÓN DE ERRORES
// ============================================
if (APP_ENV === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/logs/php_errors.log');
}

// ============================================
// FUNCIÓN: Obtener URL Base
// ============================================
function get_base_url() {
    return APP_URL;
}

// ============================================
// FUNCIÓN: Formatear Moneda
// ============================================
function format_currency($amount) {
    return MONEDA_SIMBOLO . ' ' . number_format($amount, 2, '.', ',');
}

// ============================================
// FUNCIÓN: Registrar Log
// ============================================
function log_message($level, $message, $context = []) {
    if (!ENABLE_LOGS) return;
    
    $timestamp = date('Y-m-d H:i:s');
    $context_str = !empty($context) ? json_encode($context) : '';
    $log_entry = "[$timestamp] [$level] $message $context_str" . PHP_EOL;
    
    // Crear directorio de logs si no existe
    $log_dir = dirname(LOG_FILE);
    if (!is_dir($log_dir)) {
        mkdir($log_dir, 0755, true);
    }
    
    file_put_contents(LOG_FILE, $log_entry, FILE_APPEND);
}

// ============================================
// FUNCIÓN: Sanitizar Entrada
// ============================================
function sanitize_input($data) {
    if (is_array($data)) {
        return array_map('sanitize_input', $data);
    }
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

// ============================================
// FUNCIÓN: Validar Email
// ============================================
function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// ============================================
// FUNCIÓN: Generar Token CSRF
// ============================================
function generate_csrf_token() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    $token = bin2hex(random_bytes(32));
    $_SESSION[CSRF_TOKEN_NAME] = $token;
    $_SESSION[CSRF_TOKEN_NAME . '_time'] = time();
    
    return $token;
}

// ============================================
// FUNCIÓN: Verificar Token CSRF
// ============================================
function verify_csrf_token($token) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isset($_SESSION[CSRF_TOKEN_NAME]) || !isset($_SESSION[CSRF_TOKEN_NAME . '_time'])) {
        return false;
    }
    
    // Verificar expiración
    if (time() - $_SESSION[CSRF_TOKEN_NAME . '_time'] > CSRF_TOKEN_EXPIRE) {
        unset($_SESSION[CSRF_TOKEN_NAME]);
        unset($_SESSION[CSRF_TOKEN_NAME . '_time']);
        return false;
    }
    
    return hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
}

// ============================================
// FUNCIÓN: Redireccionar
// ============================================
function redirect($path, $permanent = false) {
    $status_code = $permanent ? 301 : 302;
    
    if (strpos($path, 'http') === 0) {
        $url = $path;
    } else {
        $url = APP_URL . ltrim($path, '/');
    }
    
    header("Location: $url", true, $status_code);
    exit;
}

// ============================================
// FUNCIÓN: Verificar Autenticación
// ============================================
function require_auth($tipo = null) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isset($_SESSION['usuario'])) {
        redirect('login.php');
    }
    
    if ($tipo !== null && ($_SESSION['tipo_usuario'] ?? '') !== $tipo) {
        redirect('login.php');
    }
    
    return true;
}

// ============================================
// FUNCIÓN: Obtener Usuario Actual
// ============================================
function get_current_user() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    return [
        'usuario' => $_SESSION['usuario'] ?? null,
        'tipo_usuario' => $_SESSION['tipo_usuario'] ?? null,
        'cliente_id' => $_SESSION['cliente_id'] ?? null,
        'correo' => $_SESSION['correo'] ?? null,
    ];
}

// ============================================
// FUNCIÓN: Es Admin
// ============================================
function is_admin() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    return ($_SESSION['tipo_usuario'] ?? '') === 'admin';
}

// ============================================
// FUNCIÓN: Es Cliente
// ============================================
function is_cliente() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    return ($_SESSION['tipo_usuario'] ?? '') === 'cliente';
}

// ============================================
// CREAR DIRECTORIOS NECESARIOS
// ============================================
$required_dirs = [
    dirname(LOG_FILE),
    UPLOAD_DIR,
    CLIENTE_IMG_DIR,
    __DIR__ . '/backups',
    __DIR__ . '/temp',
];

foreach ($required_dirs as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

// ============================================
// CONFIGURAR SESIONES SEGURAS
// ============================================
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_lifetime', SESSION_LIFETIME);
    ini_set('session.cookie_httponly', SESSION_HTTPONLY);
    ini_set('session.cookie_secure', SESSION_SECURE);
    ini_set('session.cookie_samesite', SESSION_SAMESITE);
    ini_set('session.use_strict_mode', 1);
    ini_set('session.use_only_cookies', 1);
    
    session_name(SESSION_NAME);
}

// ============================================
// MENSAJE DE INICIALIZACIÓN (Solo en desarrollo)
// ============================================
if (APP_ENV === 'development') {
    log_message('INFO', 'Configuración cargada correctamente', [
        'app_name' => APP_NAME,
        'version' => APP_VERSION,
        'environment' => APP_ENV
    ]);
}