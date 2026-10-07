<?php
$search = inputString($_GET, 'q');
$rows = query(
    'SELECT id,name,email,role,active,created_at FROM users WHERE name LIKE ? OR email LIKE ? ORDER BY id DESC',
    ['%' . $search . '%', '%' . $search . '%'],
)->fetchAll();
?>
<form method="get" class="mb-6 flex gap-3">
    <input type="hidden" name="page" value="admin" />
    <input type="hidden" name="section" value="users" />
    <input
        class="field"
        name="q"
        value="<?= e($search) ?>"
        placeholder="Tìm tên hoặc email"
        aria-label="Tìm người dùng"
    />
    <button class="btn">Tìm</button>
</form>
<div class="table-wrap">
    <table class="admin-table">
        <thead>
            <tr>
                <th>Họ tên</th>
                <th>Email</th>
                <th>Vai trò</th>
                <th>Trạng thái</th>
                <th>Thao tác</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $row): ?>
            <tr>
                <td><?= e($row['name']) ?></td>
                <td><?= e($row['email']) ?></td>
                <td><?= e($row['role']) ?></td>
                <td><?= $row['active'] ? 'Hoạt động' : 'Đã khóa' ?></td>
                <td>
                    <?php if ($row['role'] === 'customer'): ?>
                    <form
                        method="post"
                        action="index.php"
                        data-confirm="Thay đổi trạng thái tài khoản này?"
                    >
                        <?php csrfField(); ?>
                        <input type="hidden" name="action" value="admin_user" />
                        <input type="hidden" name="id" value="<?= e($row['id']) ?>" />
                        <input type="hidden" name="active" value="<?= $row['active'] ? 0 : 1 ?>" />
                        <button class="underline"><?= $row['active']
                            ? 'Khóa'
                            : 'Mở khóa' ?></button>
                    </form>
                    <?php else: ?>
                    —
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
