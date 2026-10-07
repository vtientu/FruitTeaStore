<?php
function installDatabase(array $input): void
{
    $name = requiredText($input, 'name', 100);
    $email = strtolower(requiredText($input, 'email', 190));
    $password = inputString($input, 'password');
    if (
        !filter_var($email, FILTER_VALIDATE_EMAIL) ||
        strlen($password) < 8 ||
        strlen($password) > 72
    ) {
        throw new InvalidArgumentException('Email hợp lệ và mật khẩu từ 8–72 ký tự.');
    }
    $config = require __DIR__ . '/config.php';
    if (!preg_match('/^[a-zA-Z0-9_]+$/D', $config['database'])) {
        throw new InvalidArgumentException('Tên database chỉ dùng chữ, số và dấu gạch dưới.');
    }
    $server = connectDatabase(false);
    $server->exec(
        'CREATE DATABASE IF NOT EXISTS `' .
            $config['database'] .
            '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci',
    );
    $connection = db();
    // Khóa cài đặt để hai yêu cầu đồng thời không tạo hai admin đầu tiên.
    $lock = query('SELECT GET_LOCK(?, 10)', [
        'webphp_install_' . $config['database'],
    ])->fetchColumn();
    if (!$lock) {
        throw new InvalidArgumentException('Đang có tiến trình cài đặt. Vui lòng thử lại.');
    }
    try {
        foreach (explode(';', file_get_contents(__DIR__ . '/../database/schema.sql')) as $sql) {
            if (trim($sql) !== '') {
                $connection->exec($sql);
            }
        }
        if (query("SELECT COUNT(*) FROM users WHERE role='admin'")->fetchColumn()) {
            throw new InvalidArgumentException('Website đã được cài đặt.');
        }
        $connection->beginTransaction();
        query('INSERT INTO users(name,email,password_hash,role) VALUES(?,?,?,?)', [
            $name,
            $email,
            password_hash($password, PASSWORD_DEFAULT),
            'admin',
        ]);
        if (!query('SELECT COUNT(*) FROM products')->fetchColumn()) {
            foreach (['Trà trái cây', 'Trà hoa', 'Trà nguyên bản'] as $category) {
                query('INSERT IGNORE INTO categories(name) VALUES(?)', [$category]);
            }
            foreach (require __DIR__ . '/../database/seed_products.php' as $product) {
                $categoryId = query('SELECT id FROM categories WHERE name=?', [
                    $product['category'],
                ])->fetchColumn();
                query(
                    'INSERT INTO products(category_id,name,description,price,color,fruit,tag,type) VALUES(?,?,?,?,?,?,?,?)',
                    [
                        $categoryId,
                        $product['name'],
                        $product['description'],
                        $product['price'],
                        $product['color'],
                        $product['fruit'],
                        $product['tag'],
                        $product['type'],
                    ],
                );
            }
            foreach (['Trân châu trắng', 'Thạch trái cây'] as $name) {
                query('INSERT IGNORE INTO toppings(name,price) VALUES(?,7000)', [$name]);
            }
        }
        $connection->commit();
    } catch (Throwable $error) {
        if ($connection->inTransaction()) {
            $connection->rollBack();
        }
        throw $error;
    } finally {
        query('SELECT RELEASE_LOCK(?)', ['webphp_install_' . $config['database']]);
    }
}
