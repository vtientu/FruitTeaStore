<?php

declare(strict_types=1);

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function money(int $amount): string
{
    return number_format($amount, 0, ',', '.') . 'đ';
}

function url(string $page = 'home', array $params = []): string
{
    return 'index.php?' . http_build_query(['page' => $page] + $params);
}

function render(string $template, array $data = []): void
{
    extract($data, EXTR_SKIP);
    require __DIR__ . '/../templates/' . $template . '.php';
}

function redirect(string $page, array $params = []): never
{
    header('Location: ' . url($page, $params), true, 303);
    exit();
}

function csrfField(): void
{
    echo '<input type="hidden" name="csrf_token" value="' . e($_SESSION['csrf_token']) . '">';
}

function inputString(array $input, string $key, string $default = ''): string
{
    return isset($input[$key]) && is_string($input[$key]) ? trim($input[$key]) : $default;
}

function flash(string $message): void
{
    $_SESSION['flash'] = $message;
}
