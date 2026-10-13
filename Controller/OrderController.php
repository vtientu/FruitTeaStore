<?php
// =========================
// ĐẶT HÀNG VÀ ĐÁNH GIÁ
// =========================
function placeOrder(): never
{
    global $data;
    $user = requireLogin();
    $token = inputString($_POST, 'checkout_token');
    // Cùng một lần bấm thanh toán chỉ tạo một đơn, kể cả khi gửi lại request.
    $existing = $data->select_checkout_order($token, $user['id'])->fetch();
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
        $toppings = array_column($data->select_toppings_for_update()->fetchAll(), null, 'id');
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
        $data->insert_order(
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
            $data->insert_order_item(
                $orderId,
                $item['id'],
                $item['name'],
                $options,
                $item['price'],
                $item['quantity'],
            );
        }
        if ($voucher !== '') {
            $data->use_voucher($voucher);
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
    global $data;
    $user = requireLogin();
    $productId = integerField($_POST, 'product_id', 1, PHP_INT_MAX);
    $rating = integerField($_POST, 'rating', 1, 5);
    $comment = requiredText($_POST, 'comment', 1000);
    $purchased = $data->select_completed_purchase($user['id'], $productId)->fetch();
    if (!$purchased) {
        throw new InvalidArgumentException('Bạn chỉ có thể đánh giá món trong đơn đã hoàn thành.');
    }
    $data->save_review($user['id'], $productId, $rating, $comment);
    flash('Đã lưu đánh giá của bạn.');
    redirect('product', ['id' => $productId]);
}
