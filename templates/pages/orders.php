<?php
$orders = query('SELECT * FROM orders WHERE user_id=? ORDER BY id DESC', [$user['id']])->fetchAll();
$selectedId = (int) inputString($_GET, 'id');
$selected = null;
foreach ($orders as $row) {
    if ((int) $row['id'] === $selectedId) {
        $selected = $row;
    }
}
if (!$selected && !$selectedId && $orders) {
    $selected = $orders[0];
}
?>
<section class="page-section">
    <p class="eyebrow">MỘC ĐANG CHUẨN BỊ NIỀM VUI</p>
    <h2>Đơn hàng của bạn</h2>
    <?php if (!$orders): ?>
    <div class="py-16 text-center">
        <h3 class="mb-5 text-lg">Ly trà đầu tiên đang chờ bạn.</h3>
        <a class="btn" href="<?= e(url('menu')) ?>">Chọn món ngay →</a>
    </div>
    <?php else: ?>
    <div class="mt-8 grid items-start gap-6 lg:grid-cols-[1fr_2fr]">
        <div class="space-y-3">
            <?php foreach ($orders as $order): ?>
            <a
                class="block rounded-xl border p-5 hover:bg-accent <?= $selected &&
                $selected['id'] === $order['id']
                    ? 'bg-secondary'
                    : '' ?>"
                href="<?= e(url('orders', ['id' => $order['id']])) ?>"
            >
                <strong class="text-sm"><?= e($order['code']) ?></strong>
                <p class="mt-2 text-xs text-muted-foreground">
                    <?= e(orderStatuses()[$order['status']]) ?> · <?= money($order['total']) ?>
                </p>
                <small class="mt-2 block text-muted-foreground"><?= e(
                    $order['created_at'],
                ) ?></small>
            </a>
            <?php endforeach; ?>
        </div>
        <?php if ($selected):
            render('components/order-detail', ['order' => $selected, 'admin' => false]);
        else:
             ?>
        <p>Không tìm thấy đơn hàng của bạn.</p>
        <?php
        endif; ?>
    </div>
    <?php endif; ?>
</section>
