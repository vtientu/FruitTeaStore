<?php
// XAMPP mặc định: MySQL port 3306, user root, mật khẩu trống.
$config = [
    'host' => '127.0.0.1',
    'port' => 3306,
    'database' => 'fruit_tea_store',
    'username' => 'root',
    'password' => '',
];
// File local không commit, dùng nếu cấu hình MySQL trên máy bạn khác mặc định.
if (is_file(__DIR__ . '/config.local.php')) {
    $config = array_merge($config, require __DIR__ . '/config.local.php');
}
return $config;
