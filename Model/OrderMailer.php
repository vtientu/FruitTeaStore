<?php
require_once __DIR__ . '/SmtpMailer.php';

function tea_confirmation_body(array $order, array $items): string
{
    $money = static fn($value) => number_format((float) $value, 0, ',', '.') . ' đ';
    $lines = [
        'Mộc Trà đã nhận đơn ' . $order['code'] . '. Cảm ơn bạn đã chọn Mộc!',
        'Trạng thái: Chờ xử lý | Thanh toán khi nhận hàng (COD)',
        'Người nhận: ' . $order['recipient_name'],
        'Email: ' . $order['email'],
        'Số điện thoại: ' . $order['phone'],
        'Địa chỉ: ' . $order['address'], '', 'Món của bạn:',
    ];
    foreach ($items as $item) {
        $options = json_decode($item['options_json'], true, 512, JSON_THROW_ON_ERROR);
        $lines[] = $item['product_name'] . ' × ' . $item['quantity'] . ' — ' . $money($item['unit_price'] * $item['quantity']);
        $lines[] = '  Size: ' . ($options['size'] ?? '') . ' | Đường: ' . ($options['sweet'] ?? '') . ' | Đá: ' . ($options['ice'] ?? '');
        $toppings = array_map(static fn($t) => is_array($t) ? ($t['name'] ?? '') : (string) $t, $options['toppings'] ?? []);
        if ($toppings) {
            $lines[] = '  Topping: ' . implode(', ', $toppings);
        }
    }
    $lines[] = 'Tiền món: ' . $money($order['subtotal']);
    $lines[] = 'Giảm giá: ' . $money($order['discount']);
    $lines[] = 'Phí giao hàng: ' . $money($order['shipping_fee']);
    $lines[] = 'Tổng thanh toán: ' . $money($order['total']);
    return implode("\n", $lines);
}

function send_tea_confirmation(data_shop $data, int $orderId): void
{
    try {
        $config = require __DIR__ . '/../app/mail.php';
        if (!$config['enabled']) {
            error_log('[MocTra mail] disabled; order id=' . $orderId);
            return;
        }
        $order = $data->select_order_id($orderId)->fetch();
        $items = $data->select_order_items($orderId)->fetchAll();
        send_order_smtp($config, $order['email'], 'Mộc Trà · Xác nhận đơn ' . $order['code'], tea_confirmation_body($order, $items));
    } catch (Throwable $error) {
        error_log('[MocTra mail] confirmation failed; order id=' . $orderId);
    }
}
