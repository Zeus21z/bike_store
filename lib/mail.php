<?php
if (!file_exists(__DIR__ . '/../vendor/autoload.php')) {
    die('Error: falta vendor/autoload.php. Ejecuta composer require phpmailer/phpmailer');
}
require __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

function send_mail_smtp(string $toEmail, string $toName, string $subject, string $htmlBody, string $altBody = ''): bool {
    $mail = new PHPMailer(true);

    try {
        // === DEBUG ACTIVADO (para que veamos el error real) ===
        $mail->SMTPDebug = SMTP::DEBUG_SERVER;      // ← Quita esta línea o ponla en 0 cuando ya funcione
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'villarroelgarciazeus@gmail.com';
        $mail->Password   = 'tkzgljqylukvgsbj';                    // ← tu App Password actual
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;        // tls obligatorio
        $mail->Port       = 587;

        $mail->setFrom('villarroelgarciazeus@gmail.com', 'Bike Store');
        $mail->addAddress($toEmail, $toName);

        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->Subject = $subject;
        $mail->Body    = $htmlBody;
        $mail->AltBody = $altBody ?: strip_tags($htmlBody);

        $mail->send();
        return true;

    } catch (Exception $e) {
        // Aquí verás el error EXACTO de Gmail
        echo "<pre style='background:#f8d7da;padding:15px;border-radius:5px;color:#721c24'>";
        echo "ERROR DE SMTP:\n";
        echo htmlspecialchars($mail->ErrorInfo);
        echo "</pre>";
        return false;
    }
}