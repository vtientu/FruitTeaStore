<?php $reviewableProducts = !$admin && $order['status'] === 'completed' ? availableProducts() : [];
$items = query('SELECT * FROM order_items WHERE order_id=? ORDER BY id', [
    $order['id'],
])->fetchAll();
?>
<article class="rounded-xl border p-5 md:p-7">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <p class="eyebrow">ĐƠN HÀNG</p>
            <h3 class="my-2 font-semibold"><?= e($order['code']) ?></h3>
        </div>
        <span class="rounded-full bg-accent px-3 py-2 text-xs"><?= e(
            orderStatuses()[$order['status']],
        ) ?></span>
    </div>
    <p class="mt-4 text-xs leading-loose text-muted-foreground">
        Người nhận: <?= e($order['recipient_name']) ?> · <?= e($order['phone']) ?>
        <br />
        Địa chỉ: <?= e($order['address']) ?>
        <br />
        Thời gian: <?= e($order['created_at']) ?>
    </p>
    <?php foreach ($items as $item):
        $options = json_decode($item['options_json'], true); ?>
    <div class="flex justify-between gap-3 border-b py-5 text-xs">
        <div>
            <strong><?= (int) $item['quantity'] ?> × <?= e($item['product_name']) ?></strong>
            <p class="mt-2 text-muted-foreground">
                Size <?= e($options['size']) ?> · <?= e($options['sweet']) ?> đường · <?= e(
     $options['ice'],
 ) ?> đá
                <br />
                <?= e(implode(', ', $options['toppings'])) ?>
            </p>
            <?php if (
                !$admin &&
                $order['status'] === 'completed' &&
                isset($reviewableProducts[$item['product_id']])
            ): ?>
            <a class="mt-2 inline-block underline" href="<?= e(
                url('product', ['id' => $item['product_id']]),
            ) ?>">Đánh giá món</a>
            <?php endif; ?>
        </div>
        <strong class="shrink-0"><?= money($item['unit_price'] * $item['quantity']) ?></strong>
    </div>
    <?php
    endforeach; ?>
    <div class="space-y-3 py-5 text-xs">
        <p class="flex justify-between">
            <span>Tạm tính</span>
            <strong><?= money($order['subtotal']) ?></strong>
        </p>
        <p class="flex justify-between">
            <span>Giảm giá <?= e($order['voucher_code'] ?? '') ?></span>
            <strong>-<?= money($order['discount']) ?></strong>
        </p>
        <p class="flex justify-between">
            <span>Phí giao hàng</span>
            <strong><?= money($order['shipping_fee']) ?></strong>
        </p>
        <p class="flex justify-between border-t pt-4">
            <span>Tổng thanh toán (COD)</span>
            <strong class="text-lg"><?= money($order['total']) ?></strong>
        </p>
    </div>
    <?php if (!$admin && $order['status'] === 'pending'): ?>
    <form method="post" action="index.php" data-confirm="Bạn muốn hủy đơn hàng này?">
        <?php csrfField(); ?>
        <input type="hidden" name="action" value="cancel_order" />
        <input type="hidden" name="order_id" value="<?= e($order['id']) ?>" />
        <button class="btn-outline">Hủy đơn hàng</button>
    </form>
    <?php endif; ?>
    <?php if ($admin && nextStatuses($order['status'])): ?>
    <form method="post" action="index.php" class="flex flex-wrap gap-3">
        <?php csrfField(); ?>
        <input type="hidden" name="action" value="admin_order" />
        <input type="hidden" name="id" value="<?= e($order['id']) ?>" />
        <select class="field flex-1" name="status" aria-label="Trạng thái mới">
            <?php foreach (nextStatuses($order['status']) as $status): ?>
            <option value="<?= e($status) ?>"><?= e(orderStatuses()[$status]) ?></option>
            <?php endforeach; ?>
        </select>
        <button class="btn">Cập nhật trạng thái</button>
    </form>
    <?php endif; ?>
</article>
