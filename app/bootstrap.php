<?php
// Mỗi request chỉ đi qua file này rồi render một trang trong danh sách cho phép.
date_default_timezone_set('Asia/Ho_Chi_Minh');
session_name('webphp_session_v2');
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
require __DIR__ . '/database.php';
require __DIR__ . '/cart.php';
require __DIR__ . '/install.php';
require __DIR__ . '/auth_actions.php';
require __DIR__ . '/order_actions.php';
require __DIR__ . '/admin_actions.php';
require __DIR__ . '/actions.php';
$_SESSION['csrf_token'] ??= bin2hex(random_bytes(32));
$_SESSION['checkout_token'] ??= bin2hex(random_bytes(32));
$_SESSION['cart'] ??= [];
$page = inputString($_GET, 'page', 'home');
$pages = [
    'home' => 'Trang chủ',
    'menu' => 'Thực đơn',
    'product' => 'Chi tiết món',
    'story' => 'Chuyện nhà Mộc',
    'cart' => 'Giỏ hàng',
    'checkout' => 'Đặt hàng',
    'orders' => 'Đơn hàng',
    'login' => 'Đăng nhập',
    'register' => 'Đăng ký',
    'admin' => 'Quản trị',
    'setup' => 'Cài đặt',
];
$ready = false;
$databaseError = '';
try {
    $ready = (bool) query("SELECT COUNT(*) FROM users WHERE role='admin'")->fetchColumn();
} catch (Throwable $error) {
    $databaseError =
        'Chưa kết nối được dữ liệu. Hãy bật MySQL trong XAMPP, kiểm tra app/config.php rồi cài đặt.';
}
if (!$ready) {
    $page = 'setup';
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!hash_equals($_SESSION['csrf_token'], inputString($_POST, 'csrf_token'))) {
            flash('Phiên biểu mẫu đã hết hạn. Vui lòng thử lại.');
            redirect('setup');
        }
        // Trình cài đặt chỉ dùng trên máy đang chạy XAMPP, không mở đăng ký admin công khai.
        if (!in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'], true)) {
            http_response_code(403);
            exit('Cài đặt chỉ được thực hiện từ localhost.');
        }
        try {
            installDatabase($_POST);
            flash('Cài đặt thành công. Hãy đăng nhập bằng tài khoản admin vừa tạo.');
            redirect('login');
        } catch (InvalidArgumentException $error) {
            $databaseError = $error->getMessage();
        } catch (Throwable $error) {
            error_log($error->getMessage());
            $databaseError =
                'Không cài đặt được. Kiểm tra PHP có pdo_mysql, MySQL đang chạy và tài khoản có quyền tạo database.';
        }
    }
}
$user = $ready ? currentUser() : null;
$products = $ready ? availableProducts() : [];
$toppings = $ready ? availableToppings() : [];
$cartChanged = $ready ? syncCart($products, $toppings) : false;
if ($cartChanged) {
    flash('Giỏ hàng đã cập nhật theo giá/món đang bán. Vui lòng kiểm tra lại.');
}
if ($ready && $_SERVER['REQUEST_METHOD'] === 'POST') {
    handlePost($products, $toppings, $cartChanged);
}
if ($ready && $page === 'setup') {
    redirect('home');
}
if (!isset($pages[$page])) {
    http_response_code(404);
    $page = 'not-found';
}
if (in_array($page, ['checkout', 'orders'], true) && !$user) {
    $_SESSION['after_login'] = $page;
    flash('Đăng nhập để đặt hàng và xem lịch sử mua hàng.');
    redirect('login');
}
if ($page === 'admin') {
    requireAdmin();
}
if (
    $page === 'orders' &&
    inputString($_GET, 'id') !== '' &&
    !query('SELECT id FROM orders WHERE id=? AND user_id=?', [
        (int) inputString($_GET, 'id'),
        $user['id'],
    ])->fetch()
) {
    http_response_code(404);
    $page = 'not-found';
}
$product = $products[(int) inputString($_GET, 'id')] ?? null;
if ($page === 'product' && !$product) {
    http_response_code(404);
    $page = 'not-found';
}
$cart = $_SESSION['cart'];
$subtotal = cartSubtotal($cart);
$discount = 0;
if ($ready && !empty($_SESSION['voucher'])) {
    try {
        $discount = voucherDiscount($_SESSION['voucher'], $subtotal);
    } catch (InvalidArgumentException $error) {
        unset($_SESSION['voucher']);
        flash($error->getMessage());
    }
}
if ($page === 'checkout' && !$cart) {
    redirect('cart');
}
$flash = $_SESSION['flash'] ?? '';
unset($_SESSION['flash']);
$title = $pages[$page] ?? 'Không tìm thấy trang';
