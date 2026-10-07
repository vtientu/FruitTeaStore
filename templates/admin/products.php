<?php
$rows = query(
    'SELECT p.*,c.name AS category FROM products p JOIN categories c ON c.id=p.category_id ORDER BY p.id DESC',
)->fetchAll();
$categories = query('SELECT * FROM categories ORDER BY id')->fetchAll();
$edit = inputString($_GET, 'edit');
$item =
    $edit && $edit !== 'new'
        ? query('SELECT * FROM products WHERE id=?', [(int) $edit])->fetch()
        : [];
$item =
    ($_SESSION['admin_old']['action'] ?? '') === 'admin_product'
        ? $_SESSION['admin_old']
        : ($item ?:
        []);
unset($_SESSION['admin_old']);
?>
<div class="mb-5 flex justify-between gap-3">
    <p class="text-sm text-muted-foreground">
        <?= count($rows) ?> sản phẩm · Ẩn thay vì xóa để giữ lịch sử đơn.
    </p>
    <a class="btn" href="<?= e(
        url('admin', ['section' => 'products', 'edit' => 'new']),
    ) ?>">+ Thêm sản phẩm</a>
</div>
<?php if ($edit !== ''): ?>
<form
    method="post"
    action="index.php"
    class="mb-8 grid gap-4 rounded-xl border bg-secondary/40 p-6 md:grid-cols-2"
>
    <?php csrfField(); ?>
    <input type="hidden" name="action" value="admin_product" />
    <input type="hidden" name="id" value="<?= e($item['id'] ?? '') ?>" />
    <label>
        Tên sản phẩm
        <input class="field mt-2" name="name" maxlength="120" required value="<?= e(
            $item['name'] ?? '',
        ) ?>" />
    </label>
    <label>
        Danh mục
        <select class="field mt-2" name="category_id" required>
            <?php foreach ($categories as $category): ?>
            <option value="<?= e($category['id']) ?>" <?= ($item['category_id'] ?? '') ==
$category['id']
    ? 'selected'
    : '' ?>>
                <?= e($category['name']) . (!$category['active'] ? ' (đang ẩn)' : '') ?>
            </option>
            <?php endforeach; ?>
        </select>
    </label>
    <label>
        Giá size M (đ)
        <input
            class="field mt-2"
            type="number"
            name="price"
            min="1000"
            max="1000000"
            required
            value="<?= e($item['price'] ?? 45000) ?>"
        />
    </label>
    <label>
        Nhãn sản phẩm
        <input class="field mt-2" name="tag" maxlength="40" required value="<?= e(
            $item['tag'] ?? 'MÓN MỚI',
        ) ?>" />
    </label>
    <label class="md:col-span-2">
        Mô tả
        <textarea class="field mt-2" name="description" maxlength="500" required>
<?= e($item['description'] ?? '') ?></textarea>
    </label>
    <label>
        Biểu tượng trái cây
        <input class="field mt-2" name="fruit" maxlength="20" required value="<?= e(
            $item['fruit'] ?? '🍑',
        ) ?>" />
    </label>
    <label>
        Màu nền
        <input class="field mt-2 h-12" type="color" name="color" value="<?= e(
            $item['color'] ?? '#f8d6a0',
        ) ?>" />
    </label>
    <label>
        Kiểu ly
        <select class="field mt-2" name="type">
            <?php foreach (
                ['peach' => 'Đào/cam', 'berry' => 'Dâu', 'lychee' => 'Vải/chanh', 'mango' => 'Xoài']
                as $key => $name
            ): ?>
            <option value="<?= e($key) ?>" <?= ($item['type'] ?? 'peach') === $key
    ? 'selected'
    : '' ?>><?= e($name) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <label class="flex items-center gap-2">
        <input type="checkbox" name="active" value="1" <?= $item['active'] ?? 1
            ? 'checked'
            : '' ?> />
        Đang bán
    </label>
    <div class="flex gap-3 md:col-span-2">
        <button class="btn">Lưu sản phẩm</button>
        <a class="btn-outline" href="<?= e(url('admin', ['section' => 'products'])) ?>">Hủy</a>
    </div>
</form>
<?php endif; ?>
<div class="table-wrap">
    <table class="admin-table">
        <thead>
            <tr>
                <th>Sản phẩm</th>
                <th>Danh mục</th>
                <th>Giá</th>
                <th>Trạng thái</th>
                <th>Thao tác</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $row): ?>
            <tr>
                <td><?= e($row['fruit'] . ' ' . $row['name']) ?></td>
                <td><?= e($row['category']) ?></td>
                <td><?= money($row['price']) ?></td>
                <td><?= $row['active'] ? 'Đang bán' : 'Đã ẩn' ?></td>
                <td>
                    <a class="underline" href="<?= e(
                        url('admin', ['section' => 'products', 'edit' => $row['id']]),
                    ) ?>">Sửa</a>
                    <?php if ($row['active']):
                        render('admin/archive-form', ['entity' => 'products', 'id' => $row['id']]);
                    endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
