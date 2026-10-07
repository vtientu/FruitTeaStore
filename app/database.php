<?php
// PDO và prepared statements giúp tách dữ liệu người dùng khỏi câu SQL.
function connectDatabase(bool $withDatabase = true): PDO
{
    $config = require __DIR__ . '/config.php';
    $dsn = "mysql:host={$config['host']};port={$config['port']};charset=utf8mb4";
    if ($withDatabase) {
        $dsn .= ';dbname=' . $config['database'];
    }
    $connection = new PDO($dsn, $config['username'], $config['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    $connection->exec("SET time_zone='+07:00'");
    return $connection;
}
function db(): PDO
{
    static $connection;
    return $connection ??= connectDatabase();
}
function query(string $sql, array $params = []): PDOStatement
{
    $statement = db()->prepare($sql);
    $statement->execute($params);
    return $statement;
}
function availableProducts(bool $lock = false): array
{
    $rows = query(
        'SELECT p.*, c.name AS category FROM products p JOIN categories c ON c.id=p.category_id WHERE p.active=1 AND c.active=1 ORDER BY p.id' .
            ($lock ? ' FOR UPDATE' : ''),
    )->fetchAll();
    return array_column($rows, null, 'id');
}
function availableToppings(): array
{
    return array_column(
        query('SELECT * FROM toppings WHERE active=1 ORDER BY id')->fetchAll(),
        null,
        'id',
    );
}
function currentUser(): ?array
{
    if (empty($_SESSION['user_id'])) {
        return null;
    }
    $user = query('SELECT id,name,email,role FROM users WHERE id=? AND active=1', [
        $_SESSION['user_id'],
    ])->fetch();
    if (!$user) {
        unset($_SESSION['user_id']);
    }
    return $user ?: null;
}
function requireLogin(): array
{
    $user = currentUser();
    if (!$user) {
        flash('Vui lòng đăng nhập để tiếp tục.');
        redirect('login');
    }
    return $user;
}
function requireAdmin(): array
{
    $user = requireLogin();
    if ($user['role'] !== 'admin') {
        http_response_code(403);
        exit('Bạn không có quyền quản trị.');
    }
    return $user;
}
