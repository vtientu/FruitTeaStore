<?php
// Copy to mail.local.php and enter credentials locally. Never commit the real password.
return [
    'enabled' => true,
    'host' => 'smtp.gmail.com',
    'port' => 587,
    'encryption' => 'tls',
    'username' => 'email@gmail.com',
    'password' => '', // App Password
    'from_address' => 'email@gmail.com',
    'from_name' => 'Mộc Trà',
];
