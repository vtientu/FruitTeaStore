<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require __DIR__ . '/../Model/OrderMailer.php';
$order = ['id' => 1, 'code' => 'TEST-123', 'recipient_name' => 'Nguyễn An', 'email' => 'customer@example.test',
    'phone' => '0901234567', 'address' => '12 Đường Hoa, TP.HCM', 'subtotal' => 100000,
    'shipping_fee' => 30000, 'discount' => 10000, 'total' => 120000];
$items = [['product_name' => 'Sản phẩm thử', 'unit_price' => 50000, 'quantity' => 2, 'options_json' => json_encode(['size' => 'L', 'sweet' => '30%', 'ice' => 'Ít đá', 'toppings' => ['Thạch trái cây']])]];
$body = tea_confirmation_body($order, $items);
foreach (['Mộc Trà', 'TEST-123', 'Nguyễn An', 'customer@example.test', '0901234567', '12 Đường Hoa', '100.000 đ', '30.000 đ', '120.000 đ', 'Sản phẩm thử × 2'] as $value) {
    if (!str_contains($body, $value)) { throw new RuntimeException('Missing: ' . $value); }
}
foreach (['Size: L', '30%', 'Ít đá', 'Thạch trái cây', 'Giảm giá: 10.000 đ'] as $value) { if (!str_contains($body, $value)) { throw new RuntimeException('Missing option: ' . $value); } }
foreach (["bad
Bcc: attacker@example.test", 'invalid-email'] as $recipient) {
    try {
        send_order_smtp(['from_address' => 'sender@example.test'], $recipient, 'Test', 'Test');
        throw new LogicException('Invalid recipient accepted');
    } catch (RuntimeException $expected) {
        if ($expected->getMessage() !== 'Invalid mail address') { throw $expected; }
    }
}
echo "PASS: confirmation content and SMTP recipient validation\n";
