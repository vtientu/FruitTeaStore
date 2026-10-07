<section class="page-section">
    <p class="eyebrow">MỘT CHÚT VUI SẮP ĐẾN</p>
    <h2>
        Giỏ hàng của bạn
        <em>(<?= cartCount($cart) ?>)</em>
    </h2>
    <?php if (!$cart): ?>
    <div class="py-16 text-center">
        <p class="text-4xl">♧</p>
        <h3 class="my-5 text-lg">Giỏ còn trống, ngày còn nhiều vị ngon.</h3>
        <a class="btn" href="<?= e(url('menu')) ?>">Khám phá thực đơn →</a>
    </div>
    <?php else: ?>
    <div class="mt-8 grid gap-8 md:grid-cols-[1.7fr_1fr]">
        <div>
            <?php foreach ($cart as $key => $item): ?>
            <article class="flex gap-3 border-b py-5">
                <div
                    class="grid h-24 w-16 shrink-0 place-items-center rounded-lg text-4xl"
                    style="background: <?= e($item['color']) ?>"
                >
                    <?= e($item['fruit']) ?>
                </div>
                <div class="min-w-0 flex-1 space-y-2">
                    <h3 class="text-sm font-semibold"><?= e($item['name']) ?></h3>
                    <p class="text-[10px] text-muted-foreground">
                        Size <?= e($item['size']) ?> · <?= e($item['sweet']) ?> đường · Đá: <?= e(
     $item['ice'],
 ) ?>
                    </p>
                    <p class="text-[10px] text-muted-foreground"><?= e(
                        implode(', ', $item['toppings']) ?: 'Không thêm topping',
                    ) ?></p>
                    <form method="post" action="index.php" class="flex flex-wrap gap-2">
                        <?php csrfField(); ?>
                        <input type="hidden" name="action" value="quantity" />
                        <input type="hidden" name="key" value="<?= e($key) ?>" />
                        <input
                            class="field w-17 p-2 text-xs"
                            type="number"
                            name="quantity"
                            min="1"
                            max="<?= MAX_QUANTITY ?>"
                            value="<?= e($item['quantity']) ?>"
                            aria-label="Số lượng <?= e($item['name']) ?>"
                            required
                        />
                        <button class="btn-outline px-2 text-[10px]" type="submit">Cập nhật</button>
                    </form>
                </div>
                <div class="flex flex-col items-end justify-between">
                    <strong class="text-xs"><?= money(
                        $item['price'] * $item['quantity'],
                    ) ?></strong>
                    <form method="post" action="index.php">
                        <?php csrfField(); ?>
                        <input type="hidden" name="action" value="remove" />
                        <input type="hidden" name="key" value="<?= e($key) ?>" />
                        <button class="text-xs text-muted-foreground underline" type="submit">
                            Xóa
                        </button>
                    </form>
                </div>
            </article>
            <?php endforeach; ?>
            <a class="mt-5 inline-block text-xs" href="<?= e(url('menu')) ?>">
                + Thêm một chút tươi mát
            </a>
        </div>
        <?php render('components/order-summary', [
            'subtotal' => $subtotal,
            'showCheckout' => true,
            'discount' => $discount,
        ]); ?>
    </div>
    <?php endif; ?>
</section>
