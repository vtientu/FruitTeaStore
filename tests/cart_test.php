<?php

declare(strict_types=1);

require __DIR__ . '/../app/helpers.php';
require __DIR__ . '/../app/cart.php';
$products = require __DIR__ . '/../app/products.php';
$checks = 0;
function check(bool $condition, string $message): void
{
    global $checks;
    if (!$condition) {
        throw new RuntimeException($message);
    }
    $checks++;
}
check(deliveryFee(0) === 0, 'Empty cart has no shipping.');
check(deliveryFee(149999) === 20000, 'Shipping applies below threshold.');
check(deliveryFee(150000) === 0, 'Shipping is free at threshold.');
$item = configuredItem(
    [
        'product_id' => '2',
        'size' => 'L',
        'toppings' => ['Trân châu trắng'],
        'quantity' => '2',
        'price' => '1',
    ],
    $products,
);
check($item['unit_price'] === 66000, 'Server computes canonical price, ignoring submitted price.');
check($item['quantity'] === 2, 'Quantity is parsed.');
$duplicate = configuredItem(
    ['product_id' => '2', 'toppings' => ['Trân châu trắng', 'Trân châu trắng']],
    $products,
);
check($duplicate['unit_price'] === 56000, 'Duplicate toppings count only once.');
foreach (
    [
        ['product_id' => '999'],
        ['product_id' => '1', 'quantity' => '-1'],
        ['product_id' => '1', 'quantity' => '21'],
        ['product_id' => '1', 'size' => 'XL'],
        ['product_id' => '1', 'toppings' => ['unknown']],
        ['product_id' => '1', 'toppings' => [['nested']]],
    ]
    as $invalid
) {
    try {
        configuredItem($invalid, $products);
        throw new RuntimeException('Invalid input was accepted.');
    } catch (InvalidArgumentException $error) {
        $checks++;
    }
}
check(e('<script>') === '&lt;script&gt;', 'HTML output is escaped.');
echo "PASS: {$checks} cart/validation checks\n";
