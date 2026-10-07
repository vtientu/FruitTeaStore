<?php
$status = inputString($_GET, 'status');
$search = inputString($_GET, 'q');
$params = [];
$conditions = [];
if (isset(orderStatuses()[$status])) {
    $conditions[] = 'o.status=?';
    $params[] = $status;
}
if ($search !== '') {
    $conditions[] = '(o.code LIKE ? OR o.recipient_name LIKE ?)';
    $params[] = '%' . $search . '%';
    $params[] = '%' . $search . '%';
}
$rows = query(
    'SELECT o.* FROM orders o' .
        ($conditions ? ' WHERE ' . implode(' AND ', $conditions) : '') .
        ' ORDER BY o.id DESC',
    $params,
)->fetchAll();
$detail = query('SELECT * FROM orders WHERE id=?', [(int) inputString($_GET, 'id')])->fetch();
?>
<form method="get" class="mb-6 flex flex-wrap gap-3">
    <input type="hidden" name="page" value="admin" />
    <input type="hidden" name="section" value="orders" />
    <input
        class="field min-w-40 flex-1"
        name="q"
        placeholder="Mã đơn / người nhận"
        value="<?= e($search) ?>"
        aria-label="Tìm đơn"
    />
    <select class="field w-auto" name="status" aria-label="Lọc trạng thái">
        <option value="">Tất cả trạng thái</option>
        <?php foreach (orderStatuses() as $key => $name): ?>
        <option value="<?= e($key) ?>" <?= $status === $key ? 'selected' : '' ?>><?= e(
    $name,
) ?></option>
        <?php endforeach; ?>
    </select>
    <button class="btn">Lọc đơn</button>
</form>
<?php if ($detail): ?>
<div class="mb-7"><?php render('components/order-detail', [
    'order' => $detail,
    'admin' => true,
]); ?></div>
<?php endif; ?>
<div class="table-wrap">
    <table class="admin-table">
        <thead>
            <tr>
                <th>Mã đơn</th>
                <th>Người nhận</th>
                <th>Tổng tiền</th>
                <th>Trạng thái</th>
                <th>Ngày đặt</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $row): ?>
            <tr>
                <td><a class="underline" href="<?= e(
                    url('admin', ['section' => 'orders', 'id' => $row['id']]),
                ) ?>"><?= e($row['code']) ?></a></td>
                <td><?= e($row['recipient_name']) ?></td>
                <td><?= money($row['total']) ?></td>
                <td><?= e(orderStatuses()[$row['status']]) ?></td>
                <td><?= e($row['created_at']) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php if (!$rows): ?>
<p class="py-8 text-center text-sm text-muted-foreground">Chưa có đơn phù hợp.</p>
<?php endif; ?>
