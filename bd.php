<?php
// ============================================
// BIKE STORE - Conexión Ultra Simple
// ============================================

$servidor = "localhost";
$basededatos = "mambo";
$usuario = "root";
$contrasenia = "";

$conexion = null;

try {
    $conexion = new PDO(
        "mysql:host=$servidor;dbname=$basededatos;charset=utf8mb4",
        $usuario,
        $contrasenia,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    
    // Conexión exitosa
    // Descomentar para ver mensaje: echo "Conexión OK";
    
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage() . "<br>Verificar: XAMPP > MySQL iniciado");
}

// Iniciar sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Función simple de log
function log_message($level, $message, $context = []) {
    $log_dir = __DIR__ . '/logs';
    if (!is_dir($log_dir)) {
        @mkdir($log_dir, 0755, true);
    }
    $log_file = $log_dir . '/app.log';
    $timestamp = date('Y-m-d H:i:s');
    @file_put_contents($log_file, "[$timestamp] [$level] $message\n", FILE_APPEND);
}

// URL base
$url_base = "http://localhost/bike_store/";

// Timezone
date_default_timezone_set('America/La_Paz');