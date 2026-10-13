<?php
// =========================
// LẤY DỮ LIỆU CHUNG VÀ KIỂM TRA QUYỀN
// =========================
function availableProducts(bool $lock = false): array
{
    global $data;
    $rows = $data->select_available_products($lock)->fetchAll();
    return array_column($rows, null, 'id');
}
function availableToppings(): array
{
    global $data;
    return array_column($data->select_available_toppings()->fetchAll(), null, 'id');
}
function currentUser(): ?array
{
    global $account;
    if (empty($_SESSION['user_id'])) {
        return null;
    }
    $user = $account->select_active_user($_SESSION['user_id'])->fetch();
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
