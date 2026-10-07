<?php $order = $_SESSION['order'] ?? null; ?>
<section class="page-section">
    <p class="eyebrow">MỘC ĐANG CHUẨN BỊ NIỀM VUI</p>
    <h2>Đơn hàng của bạn</h2>
    <?php if (!$order): ?>
    <div class="py-16 text-center">
        <h3 class="mb-5 text-lg">Ly trà đầu tiên đang chờ bạn.</h3>
        <a class="btn" href="<?= e(url('menu')) ?>">Chọn món ngay →</a>
    </div>
    <?php else: ?>
    <div class="mt-8 rounded-xl border p-5 md:p-8">
        <div class="flex items-center justify-between">
            <div>
                <p class="eyebrow">ĐƠN HÀNG MẪU</p>
                <h3 class="my-2 font-semibold">#<?= e($order['id']) ?></h3>
            </div>
            <span class="rounded-full bg-accent px-3 py-2 text-xs">✓ Đã tiếp nhận</span>
        </div>
        <ol
            aria-label="Tiến trình đơn hàng"
            class="relative my-10 flex justify-between before:absolute before:inset-x-[7%] before:top-4 before:h-px before:bg-border"
        >
            <?php foreach (
                ['Đã tiếp nhận', 'Đang pha chế', 'Đang giao hàng', 'Hoàn thành']
                as $index => $step
            ): ?>
            <li class="relative text-center" <?= $index === 0 ? 'aria-current="step"' : '' ?>>
                <span
                    class="mx-auto grid size-8 place-items-center rounded-full text-xs <?= $index ===
                    0
                        ? 'bg-primary text-white'
                        : 'bg-secondary text-muted-foreground' ?>"
                >
                    <?= $index === 0 ? '✓' : $index + 1 ?>
                </span>
                <p class="mt-3 text-[9px] md:text-xs"><?= e($step) ?></p>
            </li>
            <?php endforeach; ?>
        </ol>
        <?php foreach ($order['items'] as $item): ?>
        <div class="flex justify-between gap-3 border-t py-5 text-xs">
            <span>
                <?= e($item['quantity']) ?> × <?= e($item['name']) ?>
                <small class="text-muted-foreground">Size <?= e($item['size']) ?></small>
            </span>
            <strong class="shrink-0"><?= money($item['price'] * $item['quantity']) ?></strong>
        </div>
        <?php endforeach; ?>
        <div class="flex justify-between gap-3 border-t pt-5 text-xs">
            <span>Tổng thanh toán (gồm phí giao hàng)</span>
            <strong class="shrink-0 text-lg"><?= money($order['total']) ?></strong>
        </div>
        <p class="mt-6 text-xs leading-loose text-muted-foreground">
            Người nhận: <?= e($order['recipient']['name']) ?>
            <br />
            Địa chỉ: <?= e($order['recipient']['address']) ?>
        </p>
        <p class="mt-4 text-[10px] text-muted-foreground">
            Đơn hàng được lưu trong phiên PHP, chưa có hệ thống giao hàng thực tế.
        </p>
    </div>
    <?php endif; ?>
</section>
