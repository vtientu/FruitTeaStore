<?php
const FREE_DELIVERY_THRESHOLD = 150000;
const DELIVERY_FEE = 20000;
const LARGE_SIZE_SURCHARGE = 10000;
const MAX_QUANTITY = 20;
function deliveryFee(int $subtotal): int
{
    return $subtotal === 0 || $subtotal >= FREE_DELIVERY_THRESHOLD ? 0 : DELIVERY_FEE;
}
function cartSubtotal(array $cart): int
{
    return array_reduce($cart, fn($total, $item) => $total + $item['price'] * $item['quantity'], 0);
}
function cartCount(array $cart): int
{
    return array_sum(array_column($cart, 'quantity'));
}
function configuredItem(array $input, array $products, array $toppings): array
{
    $product = $products[(int) inputString($input, 'product_id')] ?? null;
    $size = inputString($input, 'size', 'M');
    $sweet = inputString($input, 'sweet', '50%');
    $ice = inputString($input, 'ice', 'Bình thường');
    $quantity = integerField($input + ['quantity' => '1'], 'quantity', 1, MAX_QUANTITY);
    if (
        !$product ||
        !in_array($size, ['M', 'L'], true) ||
        !in_array($sweet, ['0%', '30%', '50%', '100%'], true) ||
        !in_array($ice, ['Không đá', 'Ít đá', 'Bình thường'], true)
    ) {
        throw new InvalidArgumentException('Món hoặc tùy chỉnh không còn hợp lệ.');
    }
    $ids = $input['toppings'] ?? [];
    if (!is_array($ids)) {
        throw new InvalidArgumentException('Topping không hợp lệ.');
    }
    $selected = [];
    foreach ($ids as $id) {
        if (!is_string($id) || !ctype_digit($id) || !isset($toppings[(int) $id])) {
            throw new InvalidArgumentException('Topping không còn bán. Vui lòng chọn lại món.');
        }
        $selected[(int) $id] = $toppings[(int) $id];
    }
    $product['price'] =
        (int) $product['price'] +
        ($size === 'L' ? LARGE_SIZE_SURCHARGE : 0) +
        array_sum(array_column($selected, 'price'));
    return $product + [
        'size' => $size,
        'sweet' => $sweet,
        'ice' => $ice,
        'toppings' => array_column($selected, 'name'),
        'topping_ids' => array_keys($selected),
        'quantity' => $quantity,
    ];
}
function syncCart(array $products, array $toppings): bool
{
    $changed = false;
    foreach ($_SESSION['cart'] as $key => $item) {
        try {
            $fresh = configuredItem(
                [
                    'product_id' => (string) $item['id'],
                    'size' => $item['size'],
                    'sweet' => $item['sweet'],
                    'ice' => $item['ice'],
                    'toppings' => array_map('strval', $item['topping_ids']),
                    'quantity' => (string) $item['quantity'],
                ],
                $products,
                $toppings,
            );
            if ($fresh['price'] !== $item['price']) {
                $changed = true;
            }
            $_SESSION['cart'][$key] = $fresh;
        } catch (InvalidArgumentException $error) {
            unset($_SESSION['cart'][$key]);
            $changed = true;
        }
    }
    return $changed;
}
function voucherDiscount(string $code, int $subtotal, bool $lock = false): int
{
    if ($code === '') {
        return 0;
    }
    $voucher = query('SELECT * FROM vouchers WHERE code=?' . ($lock ? ' FOR UPDATE' : ''), [
        $code,
    ])->fetch();
    if (
        !$voucher ||
        !$voucher['active'] ||
        $voucher['expires_on'] < date('Y-m-d') ||
        $voucher['used_count'] >= $voucher['max_uses'] ||
        $subtotal < $voucher['min_total']
    ) {
        throw new InvalidArgumentException(
            'Voucher không hợp lệ, đã hết hạn/lượt dùng hoặc đơn chưa đạt giá trị tối thiểu.',
        );
    }
    return min(
        $subtotal,
        $voucher['kind'] === 'percent'
            ? (int) floor(($subtotal * $voucher['value']) / 100)
            : (int) $voucher['value'],
    );
}
