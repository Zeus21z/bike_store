<?php
session_start();
include __DIR__ . '/../bd.php';

$token = $_GET['token'] ?? null;
if (!$token) {
    header('Location: ../index.php');
    exit;
}

// Detectar tabla de usuarios
$candidates = ['clientes','usuarios','users'];
$users_table = 'usuarios';
foreach ($candidates as $t) {
    $s = $conexion->prepare("SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = :t");
    $s->execute([':t'=>$t]);
    if ($s->fetch()) { $users_table = $t; break; }
}

// Intentar buscar usuario por token
$stmt = $conexion->prepare("SELECT * FROM {$users_table} WHERE verification_token = :token LIMIT 1");
$stmt->execute([':token'=>$token]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user) {
    $u = $conexion->prepare("UPDATE {$users_table} SET verified = 1, verification_token = NULL WHERE email = :email");
    $u->execute([':email'=>$user['email']]);
    $_SESSION['flash_success'] = 'Email verificado. Ya puedes iniciar sesión.';
} else {
    $_SESSION['flash_error'] = 'Token inválido o ya usado.';
}

header('Location: login_cliente.php');
exit;
