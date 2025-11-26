<?php
session_start();
include __DIR__ . '/../bd.php';

// Crear tabla password_resets si no existe
$conexion->exec("CREATE TABLE IF NOT EXISTS password_resets (
    email VARCHAR(255) NOT NULL,
    token VARCHAR(255) NOT NULL,
    expires_at DATETIME NOT NULL,
    PRIMARY KEY (token)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Introduce un email válido.';
    } else {
        $token = bin2hex(random_bytes(20));
        $expires = date('Y-m-d H:i:s', time() + 3600); // 1 hora
        $stmt = $conexion->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (:email, :token, :exp)");
        $stmt->execute([':email'=>$email, ':token'=>$token, ':exp'=>$expires]);

        // Intentar enviar email usando PHPMailer (wrapper)
        require_once __DIR__ . '/../lib/mail.php';

        // Construir enlace de reset (ajusta base si es necesario)
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];
        $base = rtrim(dirname($_SERVER['REQUEST_URI']), '/\\'); // por ejemplo /bike_store/auth
        $link = $protocol . '://' . $host . $base . '/reset_password.php?token=' . $token;

        $subject = 'Recuperar contraseña - Bike Store';
        $html = "<p>Recibimos una solicitud para restablecer tu contraseña.</p>
                 <p>Haz clic en el siguiente enlace para cambiarla (válido 1 hora):</p>
                 <p><a href=\"{$link}\">Cambiar contraseña</a></p>
                 <p>Si no solicitaste esto, ignora este correo.</p>";

        $sent = send_mail_smtp($email, $email, $subject, $html);

        if ($sent) {
            $_SESSION['flash_success'] = 'Si el email existe, recibirás instrucciones para recuperar la contraseña.';
        } else {
            $_SESSION['flash_success'] = 'Si el email existe, recibirás instrucciones. (No se pudo enviar el correo desde el servidor; revisa la configuración SMTP.)';
        }

        header('Location: ../auth/login_cliente.php');
        exit;
    }
}

include __DIR__ . '/../templates/header_publico.php';
?>
<div class="container py-4">
    <h2>Recuperar contraseña</h2>
    <?php if (!empty($error)): ?><div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
    <form method="post" class="w-50">
        <div class="mb-3">
            <label class="form-label">Email</label>
            <input name="email" type="email" class="form-control" required>
        </div>
        <button class="btn btn-primary">Enviar instrucciones</button>
    </form>
</div>
<?php include __DIR__ . '/../templates/footer.php'; ?>
