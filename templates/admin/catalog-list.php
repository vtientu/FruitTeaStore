<?php
// entity do hai template cố định truyền vào, không lấy trực tiếp từ URL.
$rows = query('SELECT * FROM ' . $entity . ' ORDER BY id DESC')->fetchAll();
$edit = inputString($_GET, 'edit');
$item =
    $edit && $edit !== 'new'
        ? query('SELECT * FROM ' . $entity . ' WHERE id=?', [(int) $edit])->fetch()
        : [];
$item =
    ($_SESSION['admin_old']['action'] ?? '') === $action ? $_SESSION['admin_old'] : ($item ?: []);
unset($_SESSION['admin_old']);
?>
<a class="btn mb-5" href="<?= e(
    url('admin', ['section' => $entity, 'edit' => 'new']),
) ?>">+ Thêm <?= e($label) ?></a>
<?php if ($edit !== ''): ?>
<form method="post" action="index.php" class="mb-7 space-y-4 rounded-xl border p-6">
    <?php csrfField(); ?>
    <input type="hidden" name="action" value="<?= e($action) ?>" />
    <input type="hidden" name="id" value="<?= e($item['id'] ?? '') ?>" />
    <label class="block">
        Tên <?= e($label) ?>
        <input class="field mt-2" name="name" required maxlength="100" value="<?= e(
            $item['name'] ?? '',
        ) ?>" />
    </label>
    <?php if ($entity === 'toppings'): ?>
    <label class="block">
        Giá (đ)
        <input
            class="field mt-2"
            type="number"
            name="price"
            min="0"
            max="1000000"
            required
            value="<?= e($item['price'] ?? 7000) ?>"
        />
    </label>
    <?php endif; ?>
    <label class="flex items-center gap-2">
        <input type="checkbox" name="active" value="1" <?= $item['active'] ?? 1
            ? 'checked'
            : '' ?> />
        Đang hiển thị
    </label>
    <button class="btn">Lưu <?= e($label) ?></button>
</form>
<?php endif; ?>
<div class="table-wrap">
    <table class="admin-table">
        <thead>
            <tr>
                <th>Tên</th>
                <?php if ($entity === 'toppings'): ?>
                <th>Giá</th>
                <?php endif; ?>
                <th>Trạng thái</th>
                <th>Thao tác</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $row): ?>
            <tr>
                <td><?= e($row['name']) ?></td>
                <?php if ($entity === 'toppings'): ?>
                <td><?= money($row['price']) ?></td>
                <?php endif; ?>
                <td><?= $row['active'] ? 'Hiển thị' : 'Đã ẩn' ?></td>
                <td>
                    <a class="underline" href="<?= e(
                        url('admin', ['section' => $entity, 'edit' => $row['id']]),
                    ) ?>">Sửa</a>
                    <?php if ($row['active']):
                        render('admin/archive-form', ['entity' => $entity, 'id' => $row['id']]);
                    endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
