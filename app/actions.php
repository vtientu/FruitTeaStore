<?php
function handlePost(array $products, array $toppings, bool $cartChanged): never
{
    $action = inputString($_POST, 'action');
    $returnPage = match ($action) {
        'login' => 'login',
        'register' => 'register',
        'checkout' => 'checkout',
        'review' => 'product',
        default => 'cart',
    };
    $returnParams = $action === 'review' ? ['id' => (int) inputString($_POST, 'product_id')] : [];
    if (!hash_equals($_SESSION['csrf_token'], inputString($_POST, 'csrf_token'))) {
        flash('Phiên biểu mẫu đã hết hạn. Vui lòng thử lại.');
        redirect('home');
    }
    try {
        if (str_starts_with($action, 'admin_')) {
            handleAdmin($action);
        }
        if (in_array($action, ['login', 'register', 'logout'], true)) {
            handleAuth($action);
        }
        switch ($action) {
            case 'add':
                $_SESSION['cart'][bin2hex(random_bytes(8))] = configuredItem(
                    $_POST,
                    $products,
                    $toppings,
                );
                flash('Đã thêm món ngon vào giỏ!');
                break;
            case 'quantity':
                $key = inputString($_POST, 'key');
                if (!isset($_SESSION['cart'][$key])) {
                    throw new InvalidArgumentException('Món không còn trong giỏ.');
                }
                $_SESSION['cart'][$key]['quantity'] = integerField(
                    $_POST,
                    'quantity',
                    1,
                    MAX_QUANTITY,
                );
                break;
            case 'remove':
                unset($_SESSION['cart'][inputString($_POST, 'key')]);
                break;
            case 'voucher':
                $code = strtoupper(inputString($_POST, 'code'));
                if ($code !== '') {
                    voucherDiscount($code, cartSubtotal($_SESSION['cart']));
                }
                $_SESSION['voucher'] = $code;
                flash($code === '' ? 'Đã bỏ voucher.' : 'Đã áp dụng voucher.');
                break;
            case 'checkout':
                if ($cartChanged) {
                    flash('Giỏ hàng đã thay đổi. Vui lòng kiểm tra lại giá/món trước khi đặt.');
                    redirect('cart');
                }
                placeOrder();
            case 'cancel_order':
                $user = requireLogin();
                $affected = query(
                    "UPDATE orders SET status='cancelled' WHERE id=? AND user_id=? AND status='pending'",
                    [(int) inputString($_POST, 'order_id'), $user['id']],
                )->rowCount();
                flash($affected ? 'Đã hủy đơn hàng.' : 'Đơn đã được xử lý, không thể hủy.');
                redirect('orders');
            case 'review':
                saveReview();
            default:
                throw new InvalidArgumentException('Thao tác không hợp lệ.');
        }
        redirect('cart');
    } catch (InvalidArgumentException $error) {
        flash($error->getMessage());
    } catch (PDOException $error) {
        error_log($error->getMessage());
        flash('Không lưu được dữ liệu. Vui lòng thử lại; giỏ hàng của bạn vẫn được giữ.');
    }
    redirect($returnPage, $returnParams);
}
