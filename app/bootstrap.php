<?php

declare(strict_types=1);

session_name('webphp_session');
session_set_cookie_params([
    'httponly' => true,
    'samesite' => 'Lax',
    'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
]);
session_start();
header('Content-Type: text/html; charset=UTF-8');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: no-store');
require __DIR__ . '/helpers.php';
require __DIR__ . '/cart.php';
require __DIR__ . '/actions.php';
$products = require __DIR__ . '/products.php';
$_SESSION['csrf_token'] ??= bin2hex(random_bytes(32));
$_SESSION['cart'] ??= [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    handlePost($products);
}
$page = inputString($_GET, 'page', 'home');
$pages = [
    'home' => 'Trang chủ',
    'menu' => 'Thực đơn',
    'product' => 'Chi tiết món',
    'story' => 'Chuyện nhà Mộc',
    'cart' => 'Giỏ hàng',
    'checkout' => 'Đặt hàng',
    'orders' => 'Đơn hàng',
];
if (!isset($pages[$page])) {
    http_response_code(404);
    $page = 'not-found';
}
$product = $products[(int) inputString($_GET, 'id')] ?? null;
if ($page === 'product' && !$product) {
    http_response_code(404);
    $page = 'not-found';
}
if ($page === 'checkout' && !$_SESSION['cart']) {
    redirect('cart');
}
$cart = $_SESSION['cart'];
$subtotal = cartSubtotal($cart);
$flash = $_SESSION['flash'] ?? '';
unset($_SESSION['flash']);
$title = $pages[$page] ?? 'Không tìm thấy trang';
