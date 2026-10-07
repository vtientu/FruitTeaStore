<?php

declare(strict_types=1);

const FREE_DELIVERY_THRESHOLD = 150000;
const DELIVERY_FEE = 20000;
const LARGE_SIZE_SURCHARGE = 10000;
const TOPPING_PRICE = 7000;
const TOPPINGS = ['Trân châu trắng', 'Thạch trái cây'];
const MAX_QUANTITY = 20;

function deliveryFee(int $subtotal): int
{
    return $subtotal >= FREE_DELIVERY_THRESHOLD || $subtotal === 0 ? 0 : DELIVERY_FEE;
}

function cartSubtotal(array $cart): int
{
    return array_reduce(
        $cart,
        fn(int $total, array $item): int => $total + $item['price'] * $item['quantity'],
        0,
    );
}

function cartCount(array $cart): int
{
    return array_sum(array_column($cart, 'quantity'));
}

function configuredItem(array $input, array $products): array
{
    $product = $products[(int) inputString($input, 'product_id')] ?? null;
    $size = inputString($input, 'size', 'M');
    $sweet = inputString($input, 'sweet', '50%');
    $ice = inputString($input, 'ice', 'Bình thường');
    $quantity = filter_var(inputString($input, 'quantity', '1'), FILTER_VALIDATE_INT);
    $toppings = $input['toppings'] ?? [];
    if (
        !$product ||
        !in_array($size, ['M', 'L'], true) ||
        !in_array($sweet, ['0%', '30%', '50%', '100%'], true) ||
        !in_array($ice, ['Không đá', 'Ít đá', 'Bình thường'], true)
    ) {
        throw new InvalidArgumentException('Vui lòng chọn món và tùy chỉnh hợp lệ.');
    }
    if ($quantity === false || $quantity < 1 || $quantity > MAX_QUANTITY || !is_array($toppings)) {
        throw new InvalidArgumentException('Số lượng phải từ 1 đến ' . MAX_QUANTITY . '.');
    }
    foreach ($toppings as $topping) {
        if (!is_string($topping) || !in_array($topping, TOPPINGS, true)) {
            throw new InvalidArgumentException('Topping không hợp lệ.');
        }
    }
    $toppings = array_values(array_unique($toppings));
    return $product + [
        'size' => $size,
        'sweet' => $sweet,
        'ice' => $ice,
        'toppings' => $toppings,
        'quantity' => $quantity,
        'unit_price' =>
            $product['price'] +
            ($size === 'L' ? LARGE_SIZE_SURCHARGE : 0) +
            count($toppings) * TOPPING_PRICE,
    ];
}
