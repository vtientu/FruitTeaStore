<?php
$sections = [
    'dashboard' => 'Tổng quan',
    'products' => 'Sản phẩm',
    'categories' => 'Danh mục',
    'toppings' => 'Topping',
    'orders' => 'Đơn hàng',
    'vouchers' => 'Voucher',
    'users' => 'Người dùng',
];
$section = inputString($_GET, 'section', 'dashboard');
if (!isset($sections[$section])) {
    $section = 'dashboard';
}
?>
<section class="px-6 py-10 md:px-12">
    <div class="mb-8 flex flex-wrap items-center justify-between gap-4">
        <div>
            <p class="eyebrow">MỘC TRÀ · QUẢN TRỊ</p>
            <h2><?= e($sections[$section]) ?></h2>
            <p class="text-xs text-muted-foreground">
                Xin chào, <?= e($user['name']) ?>. Quản lý cửa hàng tại đây.
            </p>
        </div>
        <a class="btn-outline" href="<?= e(url()) ?>">Xem cửa hàng ↗</a>
    </div>
    <div class="grid items-start gap-7 lg:grid-cols-[180px_minmax(0,1fr)]">
        <nav
            aria-label="Quản trị"
            class="flex flex-wrap gap-2 rounded-xl bg-secondary p-3 lg:flex-col"
        >
            <?php foreach ($sections as $key => $label): ?>
            <a
                class="rounded-lg px-4 py-3 text-sm <?= $section === $key
                    ? 'bg-primary text-white'
                    : 'hover:bg-accent' ?>"
                href="<?= e(url('admin', ['section' => $key])) ?>"
                <?= $section === $key ? 'aria-current="page"' : '' ?>
            >
                <?= e($label) ?>
            </a>
            <?php endforeach; ?>
        </nav>
        <div class="min-w-0"><?php render('admin/' . $section); ?></div>
    </div>
</section>
