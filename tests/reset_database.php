<?php
// Chỉ xóa database thử nghiệm, không thể xóa fruit_tea_store qua script này.
if (PHP_SAPI !== 'cli' || !in_array('--confirm', $argv, true)) {
    exit("Dùng: php tests/reset_database.php --confirm\n");
}
$config = require __DIR__ . '/../app/config.php';
if (!preg_match('/^webphp_test_[a-zA-Z0-9_]+$/D', $config['database'])) {
    exit("Từ chối: tên database phải bắt đầu bằng webphp_test_.\n");
}
require __DIR__ . '/../app/database.php';
connectDatabase(false)->exec('DROP DATABASE IF EXISTS `' . $config['database'] . '`');
echo "Đã xóa database thử nghiệm.\n";
