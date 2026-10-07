<?php
function handleAdmin(string $action): never
{
    $admin = requireAdmin();
    $section = match ($action) {
        'admin_product' => 'products',
        'admin_category' => 'categories',
        'admin_topping' => 'toppings',
        'admin_voucher' => 'vouchers',
        'admin_order' => 'orders',
        'admin_user' => 'users',
        default => 'dashboard',
    };
    $id = (int) inputString($_POST, 'id');
    if (
        $action === 'admin_archive' &&
        in_array(
            inputString($_POST, 'entity'),
            ['products', 'categories', 'toppings', 'vouchers'],
            true,
        )
    ) {
        $section = inputString($_POST, 'entity');
    }
    try {
        switch ($action) {
            case 'admin_archive':
                if (
                    !in_array($section, ['products', 'categories', 'toppings', 'vouchers'], true) ||
                    $id < 1
                ) {
                    throw new InvalidArgumentException('Mục không hợp lệ.');
                }
                query('UPDATE ' . $section . ' SET active=0 WHERE id=?', [$id]);
                break;
            case 'admin_product':
                $name = requiredText($_POST, 'name', 120);
                $description = requiredText($_POST, 'description', 500);
                $price = integerField($_POST, 'price', 1000, 1000000);
                $category = integerField($_POST, 'category_id', 1, PHP_INT_MAX);
                if (!query('SELECT id FROM categories WHERE id=?', [$category])->fetch()) {
                    throw new InvalidArgumentException('Danh mục không tồn tại.');
                }
                $color = inputString($_POST, 'color');
                $type = inputString($_POST, 'type');
                if (
                    !preg_match('/^#[0-9a-fA-F]{6}$/D', $color) ||
                    !in_array($type, ['peach', 'berry', 'lychee', 'mango'], true)
                ) {
                    throw new InvalidArgumentException('Màu hoặc kiểu ly không hợp lệ.');
                }
                $params = [
                    $category,
                    $name,
                    $description,
                    $price,
                    $color,
                    requiredText($_POST, 'fruit', 20),
                    requiredText($_POST, 'tag', 40),
                    $type,
                    isset($_POST['active']) ? 1 : 0,
                ];
                if ($id) {
                    query(
                        'UPDATE products SET category_id=?,name=?,description=?,price=?,color=?,fruit=?,tag=?,type=?,active=? WHERE id=?',
                        [...$params, $id],
                    );
                } else {
                    query(
                        'INSERT INTO products(category_id,name,description,price,color,fruit,tag,type,active) VALUES(?,?,?,?,?,?,?,?,?)',
                        $params,
                    );
                }
                break;
            case 'admin_category':
                $params = [requiredText($_POST, 'name', 100), isset($_POST['active']) ? 1 : 0];
                if ($id) {
                    query('UPDATE categories SET name=?,active=? WHERE id=?', [...$params, $id]);
                } else {
                    query('INSERT INTO categories(name,active) VALUES(?,?)', $params);
                }
                break;
            case 'admin_topping':
                $params = [
                    requiredText($_POST, 'name', 100),
                    integerField($_POST, 'price', 0, 1000000),
                    isset($_POST['active']) ? 1 : 0,
                ];
                if ($id) {
                    query('UPDATE toppings SET name=?,price=?,active=? WHERE id=?', [
                        ...$params,
                        $id,
                    ]);
                } else {
                    query('INSERT INTO toppings(name,price,active) VALUES(?,?,?)', $params);
                }
                break;
            case 'admin_voucher':
                $code = strtoupper(inputString($_POST, 'code'));
                $kind = inputString($_POST, 'kind');
                $date = inputString($_POST, 'expires_on');
                $validDate = DateTime::createFromFormat('!Y-m-d', $date);
                if (
                    !preg_match('/^[A-Z0-9_-]{3,24}$/D', $code) ||
                    !in_array($kind, ['fixed', 'percent'], true) ||
                    !$validDate ||
                    $validDate->format('Y-m-d') !== $date
                ) {
                    throw new InvalidArgumentException(
                        'Kiểm tra mã, kiểu giảm giá và ngày hết hạn.',
                    );
                }
                $value = integerField($_POST, 'value', 1, $kind === 'percent' ? 100 : 1000000);
                $maxUses = integerField($_POST, 'max_uses', 1, 1000000);
                if ($id) {
                    $used = (int) query('SELECT used_count FROM vouchers WHERE id=?', [
                        $id,
                    ])->fetchColumn();
                    if ($maxUses < $used) {
                        throw new InvalidArgumentException(
                            'Lượt tối đa phải lớn hơn hoặc bằng lượt đã dùng.',
                        );
                    }
                }
                $params = [
                    $code,
                    $kind,
                    $value,
                    integerField($_POST, 'min_total', 0, 10000000),
                    $date,
                    $maxUses,
                    isset($_POST['active']) ? 1 : 0,
                ];
                if ($id) {
                    query(
                        'UPDATE vouchers SET code=?,kind=?,value=?,min_total=?,expires_on=?,max_uses=?,active=? WHERE id=?',
                        [...$params, $id],
                    );
                } else {
                    query(
                        'INSERT INTO vouchers(code,kind,value,min_total,expires_on,max_uses,active) VALUES(?,?,?,?,?,?,?)',
                        $params,
                    );
                }
                break;
            case 'admin_order':
                $status = inputString($_POST, 'status');
                $current = query('SELECT status FROM orders WHERE id=?', [$id])->fetchColumn();
                if (!$current || !in_array($status, nextStatuses($current), true)) {
                    throw new InvalidArgumentException(
                        'Không thể chuyển trạng thái này. Hãy tải lại đơn hàng.',
                    );
                }
                if (
                    !query('UPDATE orders SET status=? WHERE id=? AND status=?', [
                        $status,
                        $id,
                        $current,
                    ])->rowCount()
                ) {
                    throw new InvalidArgumentException('Đơn vừa được cập nhật. Vui lòng thử lại.');
                }
                break;
            case 'admin_user':
                if ($id === $admin['id']) {
                    throw new InvalidArgumentException('Không được khóa chính mình.');
                }
                $active = integerField($_POST, 'active', 0, 1);
                query("UPDATE users SET active=? WHERE id=? AND role='customer'", [$active, $id]);
                break;
            default:
                throw new InvalidArgumentException('Thao tác quản trị không hợp lệ.');
        }
        unset($_SESSION['admin_old']);
        flash('Đã lưu thay đổi.');
        redirect('admin', ['section' => $section]);
    } catch (InvalidArgumentException $error) {
        flash($error->getMessage());
    } catch (PDOException $error) {
        error_log($error->getMessage());
        flash('Không lưu được. Kiểm tra tên/mã trùng hoặc thông tin liên quan.');
    }
    $_SESSION['admin_old'] = $_POST + ['active' => 0];
    redirect('admin', ['section' => $section, 'edit' => $id ?: 'new']);
}
