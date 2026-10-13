<?php
// PDO và prepared statements giúp tách dữ liệu người dùng khỏi câu SQL.
function connectDatabase(bool $withDatabase = true): PDO
{
    $config = require __DIR__ . '/../app/config.php';
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
