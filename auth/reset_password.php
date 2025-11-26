<?php
session_start();
include __DIR__ . '/../bd.php';

$token = $_GET['token'] ?? $_POST['token'] ?? null;
if (!$token) {
    header('Location: login_cliente.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? '';
    if ($password === '' || $password !== $password2) {
        $error = 'Las contraseñas no coinciden o están vacías.';
    } else {
        // Buscar token
        $stmt = $conexion->prepare("SELECT * FROM password_resets WHERE token = :token LIMIT 1");
        $stmt->execute([':token'=>$token]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            $error = 'Token inválido.';
        } elseif (strtotime($row['expires_at']) < time()) {
            $error = 'Token expirado.';
        } else {
            // Detectar tabla de usuarios y actualizar password por email
            $email = $row['email'];
            $candidates = ['clientes','usuarios','users'];
            $users_table = 'usuarios';
            foreach ($candidates as $t) {
                $s = $conexion->prepare("SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = :t");
                $s->execute([':t'=>$t]);
                if ($s->fetch()) { $users_table = $t; break; }
            }
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $up = $conexion->prepare("UPDATE {$users_table} SET password = :p WHERE email = :e");
            $up->execute([':p'=>$hash, ':e'=>$email]);

            // Eliminar token
            $del = $conexion->prepare("DELETE FROM password_resets WHERE token = :token");
            $del->execute([':token'=>$token]);

            $_SESSION['flash_success'] = 'Contraseña cambiada. Puedes iniciar sesión.';
            header('Location: login_cliente.php');
            exit;
        }
    }
}

include __DIR__ . '/../templates/header_publico.php';
?>
<div class="container py-4">
    <h2>Cambiar contraseña</h2>
    <?php if (!empty($error)): ?><div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
    <form method="post" class="w-50">
        <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
        <div class="mb-3">
            <label class="form-label">Nueva contraseña</label>
            <input name="password" type="password" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Repetir nueva contraseña</label>
            <input name="password2" type="password" class="form-control" required>
        </div>
        <button class="btn btn-primary">Cambiar contraseña</button>
    </form>
</div>
<?php include __DIR__ . '/../templates/footer.php'; ?>
