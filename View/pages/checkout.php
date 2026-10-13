<?php $old = $_SESSION['checkout_old'] ?? ['name' => $user['name']]; ?>
<section class="page-section">
    <p class="eyebrow">THÊM MỘT CHÚT THÔNG TIN</p>
    <h2>Gửi Mộc địa chỉ của bạn.</h2>
    <div class="mt-8 grid gap-8 md:grid-cols-[1.7fr_1fr]">
        <form method="post" action="index.php" class="space-y-5 rounded-xl border p-6">
            <?php csrfField(); ?>
            <input type="hidden" name="action" value="checkout" />
            <input type="hidden" name="checkout_token" value="<?= e(
                $_SESSION['checkout_token'],
            ) ?>" />
            <label class="block text-sm">
                Người nhận
                <input
                    class="field mt-2"
                    name="name"
                    required
                    maxlength="100"
                    value="<?= e($old['name'] ?? '') ?>"
                    placeholder="Họ và tên"
                    autocomplete="name"
                />
            </label>
            <label class="block text-sm">
                Số điện thoại
                <input
                    class="field mt-2"
                    type="tel"
                    name="phone"
                    required
                    pattern="\+?[0-9]{9,15}"
                    maxlength="16"
                    value="<?= e($old['phone'] ?? '') ?>"
                    placeholder="0901234567"
                    autocomplete="tel"
                />
            </label>
            <label class="block text-sm">
                Địa chỉ giao hàng
                <textarea
                    class="field mt-2 min-h-24"
                    name="address"
                    required
                    maxlength="500"
                    placeholder="Số nhà, đường, phường, thành phố"
                    autocomplete="street-address"
                >
<?= e($old['address'] ?? '') ?></textarea>
            </label>
            <p class="text-xs text-muted-foreground">Thanh toán khi nhận hàng (COD).</p>
            <button class="btn w-full" type="submit">Xác nhận đặt hàng ✓</button>
        </form>
        <?php render('components/order-summary', [
            'subtotal' => $subtotal,
            'discount' => $discount,
        ]); ?>
    </div>
</section>
