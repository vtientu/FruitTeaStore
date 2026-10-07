<?php
require __DIR__ . '/../app/helpers.php';
require __DIR__ . '/../app/cart.php';
$products = require __DIR__ . '/../database/seed_products.php';
$toppings = [
    1 => ['id' => 1, 'name' => 'Trân châu trắng', 'price' => 7000],
    2 => ['id' => 2, 'name' => 'Thạch trái cây', 'price' => 5000],
];
$checks = 0;
function check(bool $condition, string $message): void
{
    global $checks;
    if (!$condition) {
        throw new RuntimeException($message);
    }
    $checks++;
}
check(deliveryFee(0) === 0, 'Empty cart');
check(deliveryFee(149999) === 20000, 'Shipping below threshold');
check(deliveryFee(150000) === 0, 'Free shipping at threshold');
$item = configuredItem(
    ['product_id' => '2', 'size' => 'L', 'toppings' => ['1'], 'quantity' => '2', 'price' => '1'],
    $products,
    $toppings,
);
check($item['price'] === 66000, 'Server price ignores client price');
check(cartSubtotal([$item]) === 132000, 'Cart subtotal');
check(cartCount([$item]) === 2, 'Cart quantity');
$item = configuredItem(['product_id' => '2', 'toppings' => ['1', '1', '2']], $products, $toppings);
check($item['price'] === 61000, 'Different topping prices and duplicate removal');
foreach (
    [
        ['product_id' => '999'],
        ['product_id' => '1', 'quantity' => '-1'],
        ['product_id' => '1', 'quantity' => '21'],
        ['product_id' => '1', 'size' => 'XL'],
        ['product_id' => '1', 'toppings' => ['99']],
        ['product_id' => '1', 'toppings' => [['nested']]],
    ]
    as $invalid
) {
    try {
        configuredItem($invalid, $products, $toppings);
        throw new RuntimeException('Invalid input accepted');
    } catch (InvalidArgumentException $error) {
        $checks++;
    }
}
check(e('<script>') === '&lt;script&gt;', 'HTML escaping');
check(nextStatuses('completed') === [], 'Completed order is final');
check(
    !in_array('completed', nextStatuses('pending'), true),
    'Cannot skip preparation and shipping',
);
check(nextStatuses('cancelled') === [], 'Cancelled order is final');
echo "PASS: {$checks} checks\n";
