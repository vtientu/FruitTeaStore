<?php

declare(strict_types=1);

function handlePost(array $products): never
{
    if (!hash_equals($_SESSION['csrf_token'], inputString($_POST, 'csrf_token'))) {
        http_response_code(403);
        echo 'Phiên gửi biểu mẫu không hợp lệ. Vui lòng tải lại trang.';
        exit();
    }
    $action = inputString($_POST, 'action');
    try {
        switch ($action) {
            case 'add':
                $item = configuredItem($_POST, $products);
                $item['price'] = $item['unit_price'];
                unset($item['unit_price']);
                $_SESSION['cart'][bin2hex(random_bytes(8))] = $item;
                flash('Đã thêm món ngon vào giỏ!');
                redirect('cart');
            case 'quantity':
                $key = inputString($_POST, 'key');
                $quantity = filter_var(inputString($_POST, 'quantity'), FILTER_VALIDATE_INT);
                if (
                    !isset($_SESSION['cart'][$key]) ||
                    $quantity === false ||
                    $quantity < 1 ||
                    $quantity > MAX_QUANTITY
                ) {
                    throw new InvalidArgumentException('Số lượng không hợp lệ.');
                }
                $_SESSION['cart'][$key]['quantity'] = $quantity;
                redirect('cart');
            case 'remove':
                unset($_SESSION['cart'][inputString($_POST, 'key')]);
                flash('Đã xóa món khỏi giỏ.');
                redirect('cart');
            case 'checkout':
                if (empty($_SESSION['cart'])) {
                    throw new InvalidArgumentException('Giỏ hàng đang trống.');
                }
                $name = inputString($_POST, 'name');
                $phone = inputString($_POST, 'phone');
                $address = inputString($_POST, 'address');
                if (
                    $name === '' ||
                    strlen($name) > 300 ||
                    !preg_match('/^\+?[0-9]{9,15}$/D', $phone) ||
                    $address === '' ||
                    strlen($address) > 1500
                ) {
                    $_SESSION['checkout_old'] = [
                        'name' => $name,
                        'phone' => $phone,
                        'address' => $address,
                    ];
                    flash('Vui lòng nhập họ tên, địa chỉ và số điện thoại gồm 9–15 chữ số.');
                    redirect('checkout');
                }
                $subtotal = cartSubtotal($_SESSION['cart']);
                $_SESSION['order'] = [
                    'id' => 'MOC-' . strtoupper(bin2hex(random_bytes(3))),
                    'items' => $_SESSION['cart'],
                    'total' => $subtotal + deliveryFee($subtotal),
                    'recipient' => ['name' => $name, 'phone' => $phone, 'address' => $address],
                ];
                $_SESSION['cart'] = [];
                unset($_SESSION['checkout_old']);
                flash('Đã tạo đơn mẫu. Mộc chưa gửi đơn cho cửa hàng.');
                redirect('orders');
            default:
                throw new InvalidArgumentException('Thao tác không hợp lệ.');
        }
    } catch (InvalidArgumentException $error) {
        flash($error->getMessage());
        redirect('cart');
    }
}
