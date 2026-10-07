<?php
$stats = query(
    "SELECT COUNT(*) AS orders, COALESCE(SUM(status='pending'),0) AS pending, COALESCE(SUM(CASE WHEN status='completed' THEN subtotal-discount ELSE 0 END),0) AS revenue FROM orders",
)->fetch();
$customers = (int) query("SELECT COUNT(*) FROM users WHERE role='customer'")->fetchColumn();
$days = query(
    "SELECT DATE(created_at) AS day, SUM(subtotal-discount) AS revenue FROM orders WHERE status='completed' AND created_at>=DATE_SUB(CURDATE(),INTERVAL 6 DAY) GROUP BY DATE(created_at) ORDER BY day",
)->fetchAll();
$maxRevenue = max([1, ...array_column($days, 'revenue')]);
$best = query(
    "SELECT oi.product_name,SUM(oi.quantity) AS quantity FROM order_items oi JOIN orders o ON o.id=oi.order_id WHERE o.status='completed' GROUP BY oi.product_id,oi.product_name ORDER BY quantity DESC LIMIT 5",
)->fetchAll();
$recent = query('SELECT * FROM orders ORDER BY id DESC LIMIT 8')->fetchAll();
?>
<div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
    <?php foreach (
        [
            'Doanh thu' => money($stats['revenue']),
            'Tổng đơn' => (int) $stats['orders'],
            'Chờ xác nhận' => (int) $stats['pending'],
            'Khách hàng' => $customers,
        ]
        as $label => $value
    ): ?>
    <article class="rounded-xl border bg-white/50 p-5">
        <p class="text-xs text-muted-foreground"><?= e($label) ?></p>
        <strong class="mt-3 block text-2xl"><?= e($value) ?></strong>
    </article>
    <?php endforeach; ?>
</div>
<p class="my-4 text-xs text-muted-foreground">
    Doanh thu = tiền hàng sau giảm giá của đơn hoàn thành, không gồm phí giao hàng.
</p>
<div class="my-7 grid gap-5 md:grid-cols-2">
    <article class="rounded-xl border p-6">
        <h3 class="mb-5 font-semibold">Doanh thu 7 ngày</h3>
        <?php if (!$days): ?>
        <p class="text-sm text-muted-foreground">Chưa có đơn hoàn thành.</p>
        <?php endif; ?>
        <?php foreach ($days as $day): ?>
        <div class="mb-4">
            <p class="mb-2 flex justify-between text-xs">
                <span><?= e($day['day']) ?></span>
                <strong><?= money($day['revenue']) ?></strong>
            </p>
            <div class="h-2 rounded-full bg-secondary">
                <div class="h-2 rounded-full bg-primary" style="width: <?= (int) (($day['revenue'] /
                    $maxRevenue) *
                    100) ?>%"></div>
            </div>
        </div>
        <?php endforeach; ?>
    </article>
    <article class="rounded-xl border p-6">
        <h3 class="mb-5 font-semibold">Món bán chạy</h3>
        <?php foreach ($best as $item): ?>
        <p class="flex justify-between border-b py-3 text-sm">
            <span><?= e($item['product_name']) ?></span>
            <strong><?= (int) $item['quantity'] ?> ly</strong>
        </p>
        <?php endforeach; ?>
        <?php if (!$best): ?>
        <p class="text-sm text-muted-foreground">Chưa có dữ liệu.</p>
        <?php endif; ?>
    </article>
</div>
<h3 class="mb-4 font-semibold">Đơn hàng gần đây</h3>
<div class="table-wrap">
    <table class="admin-table">
        <thead>
            <tr>
                <th>Mã đơn</th>
                <th>Khách hàng</th>
                <th>Tổng tiền</th>
                <th>Trạng thái</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($recent as $order): ?>
            <tr>
                <td><a class="underline" href="<?= e(
                    url('admin', ['section' => 'orders', 'id' => $order['id']]),
                ) ?>"><?= e($order['code']) ?></a></td>
                <td><?= e($order['recipient_name']) ?></td>
                <td><?= money($order['total']) ?></td>
                <td><?= e(orderStatuses()[$order['status']]) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
