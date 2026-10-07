<?php
$rows = query('SELECT * FROM vouchers ORDER BY id DESC')->fetchAll();
$edit = inputString($_GET, 'edit');
$item =
    $edit && $edit !== 'new'
        ? query('SELECT * FROM vouchers WHERE id=?', [(int) $edit])->fetch()
        : [];
$item =
    ($_SESSION['admin_old']['action'] ?? '') === 'admin_voucher'
        ? $_SESSION['admin_old']
        : ($item ?:
        []);
unset($_SESSION['admin_old']);
?>
<a class="btn mb-5" href="<?= e(
    url('admin', ['section' => 'vouchers', 'edit' => 'new']),
) ?>">+ Thêm voucher</a>
<?php if ($edit !== ''): ?>
<form method="post" action="index.php" class="mb-7 grid gap-4 rounded-xl border p-6 md:grid-cols-2">
    <?php csrfField(); ?>
    <input type="hidden" name="action" value="admin_voucher" />
    <input type="hidden" name="id" value="<?= e($item['id'] ?? '') ?>" />
    <label>
        Mã voucher
        <input
            class="field mt-2 uppercase"
            name="code"
            required
            pattern="[A-Za-z0-9_\-]{3,24}"
            maxlength="24"
            value="<?= e($item['code'] ?? '') ?>"
        />
    </label>
    <label>
        Kiểu giảm
        <select class="field mt-2" name="kind">
            <option value="fixed" <?= ($item['kind'] ?? '') === 'fixed'
                ? 'selected'
                : '' ?>>Số tiền (đ)</option>
            <option value="percent" <?= ($item['kind'] ?? '') === 'percent'
                ? 'selected'
                : '' ?>>Phần trăm (%)</option>
        </select>
    </label>
    <label>
        Giá trị giảm
        <input
            class="field mt-2"
            type="number"
            name="value"
            required
            min="1"
            max="1000000"
            value="<?= e($item['value'] ?? 10000) ?>"
        />
    </label>
    <label>
        Đơn tối thiểu (đ)
        <input
            class="field mt-2"
            type="number"
            name="min_total"
            required
            min="0"
            max="10000000"
            value="<?= e($item['min_total'] ?? 0) ?>"
        />
    </label>
    <label>
        Hết hạn ngày
        <input
            class="field mt-2"
            type="date"
            name="expires_on"
            required
            value="<?= e($item['expires_on'] ?? date('Y-m-d', strtotime('+30 days'))) ?>"
        />
    </label>
    <label>
        Số lượt tối đa
        <input
            class="field mt-2"
            type="number"
            name="max_uses"
            required
            min="1"
            max="1000000"
            value="<?= e($item['max_uses'] ?? 100) ?>"
        />
    </label>
    <label class="flex items-center gap-2">
        <input type="checkbox" name="active" value="1" <?= $item['active'] ?? 1
            ? 'checked'
            : '' ?> />
        Cho phép sử dụng
    </label>
    <div class="md:col-span-2"><button class="btn">Lưu voucher</button></div>
</form>
<?php endif; ?>
<div class="table-wrap">
    <table class="admin-table">
        <thead>
            <tr>
                <th>Mã</th>
                <th>Giảm</th>
                <th>Đơn tối thiểu</th>
                <th>Hết hạn</th>
                <th>Lượt dùng</th>
                <th>Thao tác</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $row): ?>
            <tr>
                <td><?= e($row['code']) . (!$row['active'] ? ' (đã ẩn)' : '') ?></td>
                <td><?= $row['kind'] === 'percent'
                    ? (int) $row['value'] . '%'
                    : money($row['value']) ?></td>
                <td><?= money($row['min_total']) ?></td>
                <td><?= e($row['expires_on']) ?></td>
                <td><?= (int) $row['used_count'] ?>/<?= (int) $row['max_uses'] ?></td>
                <td>
                    <a class="underline" href="<?= e(
                        url('admin', ['section' => 'vouchers', 'edit' => $row['id']]),
                    ) ?>">Sửa</a>
                    <?php if ($row['active']):
                        render('admin/archive-form', ['entity' => 'vouchers', 'id' => $row['id']]);
                    endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<p class="mt-4 text-xs text-muted-foreground">
    Đơn đã hủy vẫn tính lượt dùng voucher để tránh đặt rồi hủy lặp lại.
</p>
