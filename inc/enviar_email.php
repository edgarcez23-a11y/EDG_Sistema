<?php
// inc/enviar_email.php - wrapper simples para envio via PHPMailer
// Configure estas credenciais para usar Gmail com senha de app.
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../vendor/autoload.php';

$env = function_exists('getenv') ? getenv() : [];
$env = array_merge($_ENV ?? [], $env);

define('SMTP_HOST', $env['SMTP_HOST'] ?? 'smtp.gmail.com');
define('SMTP_USER', $env['SMTP_USER'] ?? '');
define('SMTP_PASS', $env['SMTP_PASS'] ?? '');
define('SMTP_PORT', (int)($env['SMTP_PORT'] ?? 465));
define('SMTP_SECURE', $env['SMTP_SECURE'] ?? 'ssl');

function enviar_email($to, $subject, $body) {
    if (empty(SMTP_USER) || empty(SMTP_PASS)) {
        error_log('Mail error: credenciais SMTP do Gmail não configuradas. Defina SMTP_USER e SMTP_PASS com seu e-mail e senha de app do Gmail.');
        return false;
    }

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USER;
        $mail->Password = SMTP_PASS;
        $mail->SMTPSecure = SMTP_SECURE;
        $mail->Port = SMTP_PORT;
        $mail->CharSet = 'UTF-8';

        $mail->setFrom(SMTP_USER, 'Oficina Inteligente');
        $mail->addAddress($to);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $body;
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log('Mail error: ' . $mail->ErrorInfo . ' | ' . $e->getMessage());
        return false;
    }
}
