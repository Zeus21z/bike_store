<?php
// Script de prueba para enviar un correo usando lib/mail.php (PHPMailer)
// Uso: abre en el navegador: http://localhost/bike_store/scripts/test_mail.php

// Comprobar dependencias
if (!file_exists(__DIR__ . '/../lib/mail.php')) {
    echo "<p>Error: falta lib/mail.php. Crea el archivo y configura tus credenciales SMTP.</p>";
    exit;
}
require_once __DIR__ . '/../lib/mail.php';

$resultMessage = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $to = trim($_POST['to'] ?? '');
    if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
        $resultMessage = 'Email no válido.';
    } else {
        $subject = 'Prueba de correo - Bike Store';
        $html = "<p>Este es un correo de prueba enviado desde el script <strong>test_mail.php</strong>.</p>";
        $sent = send_mail_smtp($to, $to, $subject, $html);
        $resultMessage = $sent ? 'Correo enviado correctamente. Revisa tu bandeja.' : 'Error al enviar el correo. Revisa logs y configuración SMTP.';
    }
}
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Test SMTP - Bike Store</title>
    <link href="/bike_store/templates/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
    <div class="container">
        <h3>Prueba de envío SMTP (PHPMailer)</h3>
        <?php if ($resultMessage): ?>
            <div class="alert alert-info"><?php echo htmlspecialchars($resultMessage); ?></div>
        <?php endif; ?>
        <form method="post" style="max-width:480px">
            <div class="mb-3">
                <label class="form-label">Enviar a (email)</label>
                <input name="to" type="email" class="form-control" required value="<?php echo htmlspecialchars($_POST['to'] ?? ''); ?>">
            </div>
            <button class="btn btn-primary">Enviar correo de prueba</button>
        </form>

        <hr>
        <h5>Notas</h5>
        <ul>
            <li>Asegúrate de editar <code>lib/mail.php</code> con tu SMTP (smtp.gmail.com, user, app password).</li>
            <li>Si usas Gmail y 2FA, usa App Password; revisa registros de PHP si falla.</li>
        </ul>
    </div>
</body>
</html>
