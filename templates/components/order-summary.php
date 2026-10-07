<aside class="h-fit rounded-xl bg-secondary p-6">
    <h3 class="mb-6 text-lg font-semibold">Tóm tắt đơn hàng</h3>
    <div class="space-y-5 text-xs">
        <div class="flex justify-between">
            <span>Tạm tính</span>
            <strong><?= money($subtotal) ?></strong>
        </div>
        <div class="flex justify-between">
            <span>Phí giao hàng</span>
            <strong><?= deliveryFee($subtotal) === 0
                ? 'Miễn phí'
                : money(deliveryFee($subtotal)) ?></strong>
        </div>
        <?php if ($subtotal < FREE_DELIVERY_THRESHOLD): ?>
        <p class="leading-relaxed text-muted-foreground">
            Thêm <?= money(FREE_DELIVERY_THRESHOLD - $subtotal) ?> để được miễn phí giao hàng.
        </p>
        <?php endif; ?>
        <div class="flex justify-between">
            <span>Giảm giá</span>
            <strong>-<?= money($discount ?? 0) ?></strong>
        </div>
        <div aria-label="Tổng cộng" class="flex justify-between border-t pt-5">
            <span>Tổng cộng</span>
            <strong class="text-lg"><?= money(
                $subtotal - ($discount ?? 0) + deliveryFee($subtotal),
            ) ?></strong>
        </div>
        <p class="text-[10px] leading-relaxed text-muted-foreground">
            Thanh toán COD. Đơn hàng được lưu vào tài khoản của bạn.
        </p>
        <form method="post" action="index.php" class="space-y-2">
            <?php csrfField(); ?>
            <input type="hidden" name="action" value="voucher" />
            <label class="block">
                Mã voucher
                <input
                    class="field mt-2 uppercase"
                    name="code"
                    maxlength="24"
                    value="<?= e($_SESSION['voucher'] ?? '') ?>"
                    placeholder="Nhập mã ưu đãi"
                />
            </label>
            <button class="btn-outline w-full">Áp dụng / bỏ mã</button>
            <p class="text-[10px] text-muted-foreground">Để trống và bấm nút để bỏ voucher.</p>
        </form>
        <?php if (!empty($showCheckout)): ?>
        <a class="btn w-full" href="<?= e(url('checkout')) ?>">Tiến hành đặt hàng →</a>
        <?php endif; ?>
    </div>
</aside>
