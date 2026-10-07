<?php
function placeOrder(): never
{
    $user = requireLogin();
    $token = inputString($_POST, 'checkout_token');
    // Cùng một lần bấm thanh toán chỉ tạo một đơn, kể cả khi gửi lại request.
    $existing = query('SELECT id FROM orders WHERE checkout_token=? AND user_id=?', [
        $token,
        $user['id'],
    ])->fetch();
    if ($existing) {
        redirect('orders', ['id' => $existing['id']]);
    }
    if (!hash_equals($_SESSION['checkout_token'], $token)) {
        throw new InvalidArgumentException('Phiên thanh toán đã thay đổi. Vui lòng tải lại trang.');
    }
    $name = requiredText($_POST, 'name', 100);
    $address = requiredText($_POST, 'address', 500);
    $phone = inputString($_POST, 'phone');
    $_SESSION['checkout_old'] = ['name' => $name, 'phone' => $phone, 'address' => $address];
    if (!preg_match('/^\+?[0-9]{9,15}$/D', $phone)) {
        throw new InvalidArgumentException('Số điện thoại phải gồm 9–15 chữ số.');
    }
    db()->beginTransaction();
    try {
        // Khóa giá sản phẩm và voucher trong lúc ghi đơn.
        $products = availableProducts(true);
        $toppings = array_column(
            query('SELECT * FROM toppings WHERE active=1 ORDER BY id FOR UPDATE')->fetchAll(),
            null,
            'id',
        );
        if (syncCart($products, $toppings)) {
            throw new InvalidArgumentException(
                'Giá hoặc món đã thay đổi. Vui lòng kiểm tra lại giỏ hàng.',
            );
        }
        if (!$_SESSION['cart']) {
            throw new InvalidArgumentException('Giỏ hàng đang trống.');
        }
        $subtotal = cartSubtotal($_SESSION['cart']);
        $voucher = $_SESSION['voucher'] ?? '';
        $discount = voucherDiscount($voucher, $subtotal, true);
        $shipping = deliveryFee($subtotal);
        $code = 'MOC-' . date('ymd') . '-' . strtoupper(bin2hex(random_bytes(4)));
        query(
            'INSERT INTO orders(code,checkout_token,user_id,recipient_name,phone,address,subtotal,discount,shipping_fee,total,voucher_code) VALUES(?,?,?,?,?,?,?,?,?,?,?)',
            [
                $code,
                $token,
                $user['id'],
                $name,
                $phone,
                $address,
                $subtotal,
                $discount,
                $shipping,
                $subtotal - $discount + $shipping,
                $voucher ?: null,
            ],
        );
        $orderId = (int) db()->lastInsertId();
        foreach ($_SESSION['cart'] as $item) {
            $options = json_encode(
                [
                    'size' => $item['size'],
                    'sweet' => $item['sweet'],
                    'ice' => $item['ice'],
                    'toppings' => $item['toppings'],
                ],
                JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR,
            );
            query(
                'INSERT INTO order_items(order_id,product_id,product_name,options_json,unit_price,quantity) VALUES(?,?,?,?,?,?)',
                [$orderId, $item['id'], $item['name'], $options, $item['price'], $item['quantity']],
            );
        }
        if ($voucher !== '') {
            query('UPDATE vouchers SET used_count=used_count+1 WHERE code=?', [$voucher]);
        }
        db()->commit();
    } catch (Throwable $error) {
        if (db()->inTransaction()) {
            db()->rollBack();
        }
        throw $error;
    }
    $_SESSION['cart'] = [];
    $_SESSION['checkout_token'] = bin2hex(random_bytes(32));
    unset($_SESSION['voucher'], $_SESSION['checkout_old']);
    flash('Đặt hàng thành công! Bạn có thể theo dõi đơn tại đây.');
    redirect('orders', ['id' => $orderId]);
}
function saveReview(): never
{
    $user = requireLogin();
    $productId = integerField($_POST, 'product_id', 1, PHP_INT_MAX);
    $rating = integerField($_POST, 'rating', 1, 5);
    $comment = requiredText($_POST, 'comment', 1000);
    $purchased = query(
        "SELECT oi.id FROM order_items oi JOIN orders o ON o.id=oi.order_id WHERE o.user_id=? AND oi.product_id=? AND o.status='completed' LIMIT 1",
        [$user['id'], $productId],
    )->fetch();
    if (!$purchased) {
        throw new InvalidArgumentException('Bạn chỉ có thể đánh giá món trong đơn đã hoàn thành.');
    }
    query(
        'INSERT INTO reviews(user_id,product_id,rating,comment) VALUES(?,?,?,?) ON DUPLICATE KEY UPDATE rating=VALUES(rating),comment=VALUES(comment)',
        [$user['id'], $productId, $rating, $comment],
    );
    flash('Đã lưu đánh giá của bạn.');
    redirect('product', ['id' => $productId]);
}
