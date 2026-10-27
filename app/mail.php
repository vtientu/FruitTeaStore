<?php
$config = [
    'enabled' => filter_var(getenv('MOCTRA_MAIL_ENABLED') ?: 'false', FILTER_VALIDATE_BOOLEAN),
    'host' => getenv('MOCTRA_SMTP_HOST') ?: 'smtp.gmail.com',
    'port' => (int) (getenv('MOCTRA_SMTP_PORT') ?: 587),
    'encryption' => getenv('MOCTRA_SMTP_ENCRYPTION') ?: 'tls',
    'username' => getenv('MOCTRA_SMTP_USERNAME') ?: '',
    'password' => getenv('MOCTRA_SMTP_PASSWORD') ?: '',
    'from_address' => getenv('MOCTRA_MAIL_FROM') ?: '',
    'from_name' => 'Mộc Trà',
];
if (is_file(__DIR__ . '/mail.local.php')) {
    $config = array_replace($config, require __DIR__ . '/mail.local.php');
}
return $config;
