<?php
// notify.php - notifica administradores via arquivo de log e (opcional) e-mail/Teams

// Carrega configurações locais se existir
$configFile = __DIR__ . '/config.local.php';
if (file_exists($configFile)) {
    require_once $configFile;
}

// Valores padrão
if (!isset($NOTIFY_EMAILS)) $NOTIFY_EMAILS = [];
if (!isset($SMTP_ENABLED)) $SMTP_ENABLED = false;
if (!isset($SMTP_HOST)) $SMTP_HOST = '';
if (!isset($SMTP_PORT)) $SMTP_PORT = 587;
if (!isset($SMTP_USERNAME)) $SMTP_USERNAME = '';
if (!isset($SMTP_PASSWORD)) $SMTP_PASSWORD = '';
if (!isset($SMTP_SECURE)) $SMTP_SECURE = 'tls';
if (!isset($FROM_EMAIL)) $FROM_EMAIL = 'no-reply@localhost';
if (!isset($FROM_NAME)) $FROM_NAME = 'SimLover Notificações';
if (!isset($TEAMS_WEBHOOK_URL)) $TEAMS_WEBHOOK_URL = '';

/**
 * Envia e-mail de recuperação de senha
 */
function send_password_reset_email(string $to, string $subject, string $text, ?string $html = null): void {
    global $FROM_EMAIL, $FROM_NAME;
    $headers = [];
    $headers[] = 'From: ' . sprintf('%s <%s>', $FROM_NAME, $FROM_EMAIL);
    $headers[] = 'MIME-Version: 1.0';
    $headers[] = 'Content-type: text/html; charset=UTF-8';
    $body = $html ?: nl2br(htmlspecialchars($text));
    @mail($to, $subject, $body, implode("\r\n", $headers));
    
    // Log da tentativa
    $logLine = sprintf('[%s] Email recuperacao enviado para: %s%s', date('Y-m-d H:i:s'), $to, PHP_EOL);
    file_put_contents(__DIR__ . '/admin_notifications.log', $logLine, FILE_APPEND | LOCK_EX);
}

/**
 * Envia notificação para administradores
 * - Sempre escreve em admin_notifications.log
 * - Se TEAMS_WEBHOOK_URL estiver configurado, envia mensagem para Teams/Slack
 * - Se SMTP_ENABLED=true, tenta enviar e-mail (requer servidor SMTP acessível)
 */
function send_admin_notification(string $title, string $text, ?string $html = null): void {
    $logLine = sprintf('[%s] %s - %s%s', date('Y-m-d H:i:s'), $title, $text, PHP_EOL);
    file_put_contents(__DIR__ . '/admin_notifications.log', $logLine, FILE_APPEND | LOCK_EX);

    // Teams/Slack via webhook
    global $TEAMS_WEBHOOK_URL;
    if (!empty($TEAMS_WEBHOOK_URL)) {
        $payload = [
            'text' => "**$title**\n$text",
        ];
        $json = json_encode($payload);
        $opts = [
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/json\r\n",
                'content' => $json,
                'timeout' => 5,
            ]
        ];
        @file_get_contents($TEAMS_WEBHOOK_URL, false, stream_context_create($opts));
    }

    // E-mail simples (requer configuração de SMTP no servidor):
    global $SMTP_ENABLED, $NOTIFY_EMAILS, $FROM_EMAIL, $FROM_NAME;
    if ($SMTP_ENABLED && !empty($NOTIFY_EMAILS)) {
        $to = implode(',', $NOTIFY_EMAILS);
        $subject = $title;
        $headers = [];
        $headers[] = 'From: ' . sprintf('%s <%s>', $FROM_NAME, $FROM_EMAIL);
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Content-type: text/html; charset=UTF-8';
        $body = $html ?: nl2br(htmlspecialchars($text));
        // Observação: em Windows/XAMPP, a função mail() pode não enviar sem configuração do sendmail.
        // Para produção, é recomendado integrar PHPMailer com SMTP corporativo.
        @mail($to, $subject, $body, implode("\r\n", $headers));
    }
}
