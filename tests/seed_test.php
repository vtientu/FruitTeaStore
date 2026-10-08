<?php
// Tạo database tạm riêng, không thay đổi database của website.
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit();
}
require __DIR__ . '/../app/database.php';
$connection = connectDatabase(false);
$name = 'webphp_test_seed_' . bin2hex(random_bytes(6));
$connection->exec("CREATE DATABASE `$name` CHARACTER SET utf8mb4");
try {
    $connection->exec("USE `$name`");
    $run = function (string $file) use ($connection): void {
        foreach (explode(';', file_get_contents($file)) as $sql) {
            if (trim($sql) !== '') {
                $connection->exec($sql);
            }
        }
    };
    $assert = function (bool $ok, string $message): void {
        if (!$ok) {
            throw new RuntimeException($message);
        }
    };
    $run(__DIR__ . '/../database/schema.sql');
    $run(__DIR__ . '/../database/seed_products.sql');
    $assert(
        (int) $connection->query('SELECT COUNT(*) FROM products')->fetchColumn() === 24,
        'Fresh seed',
    );
    $connection->exec('DELETE FROM products WHERE id > 6');
    $connection->exec('UPDATE products SET price=51000,active=0 WHERE id=1');
    $connection->exec('UPDATE categories SET active=0 WHERE id=1');
    $run(__DIR__ . '/../database/seed_products.sql');
    $run(__DIR__ . '/../database/seed_products.sql');
    $assert(
        (int) $connection->query('SELECT COUNT(*) FROM products')->fetchColumn() === 24,
        'Upgrade and repeat import',
    );
    $assert(
        (int) $connection->query('SELECT COUNT(DISTINCT name) FROM products')->fetchColumn() === 24,
        'Unique names',
    );
    $assert(
        (int) $connection
            ->query('SELECT COUNT(*) FROM products WHERE id=1 AND price=51000 AND active=0')
            ->fetchColumn() === 1,
        'Preserve edited product',
    );
    $assert(
        (int) $connection->query('SELECT active FROM categories WHERE id=1')->fetchColumn() === 0,
        'Preserve hidden category',
    );
    $assert(
        (int) $connection->query('SELECT COUNT(*) FROM users')->fetchColumn() === 0,
        'Do not create accounts',
    );
    echo "PASS: seed mới, nâng cấp 6 lên 24 món, import lặp và giữ dữ liệu đã chỉnh.\n";
} finally {
    if ($connection->inTransaction()) {
        $connection->rollBack();
    }
    $connection->exec("DROP DATABASE `$name`");
}
