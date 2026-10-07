<div
    class="flex min-h-8 items-center justify-center gap-3 bg-primary px-2 py-2 text-center text-[9px] text-white"
>
    Một chút tươi mát cho ngày của bạn
    <span class="text-amber-200">✦</span>
    Miễn phí giao hàng cho đơn từ 150k
</div>
<header
    class="mx-auto flex h-24 max-w-[1320px] items-center justify-between border-b px-6 md:px-12"
>
    <a
        href="<?= e(url()) ?>"
        aria-label="Mộc Trà — Trang chủ"
        class="font-display text-5xl font-bold leading-none tracking-tighter"
    >
        mộc
        <span class="mt-2 block font-sans text-[7px] font-normal tracking-[2px]">
            TRÀ TRÁI CÂY TƯƠI
        </span>
    </a>
    <nav aria-label="Điều hướng chính" class="hidden items-center gap-7 text-xs md:flex">
        <?php foreach (
            [
                'home' => 'Trang chủ',
                'menu' => 'Thực đơn',
                'story' => 'Chuyện nhà Mộc',
                'orders' => 'Đơn hàng',
            ]
            as $key => $label
        ): ?>
        <a
            href="<?= e(url($key)) ?>"
            <?= $page === $key ? 'aria-current="page"' : '' ?>
            class="py-3 hover:text-brand-orange <?= $page === $key ? 'text-brand-orange' : '' ?>"
        >
            <?= e($label) ?>
        </a>
        <?php endforeach; ?>
    </nav>
    <div class="flex items-center gap-3">
        <?php if (!empty($user)): ?>
        <?php if ($user['role'] === 'admin'): ?>
        <a class="text-xs underline" href="<?= e(url('admin')) ?>">Admin</a>
        <?php endif; ?>
        <form method="post" action="index.php">
            <?php csrfField(); ?>
            <input type="hidden" name="action" value="logout" />
            <button class="text-xs" title="<?= e($user['name']) ?>">Đăng xuất</button>
        </form>
        <?php else: ?>
        <a class="text-xs" href="<?= e(url('login')) ?>">Đăng nhập</a>
        <?php endif; ?>

        <a
            class="btn-outline rounded-full text-xs"
            href="<?= e(url('cart')) ?>"
            aria-label="Giỏ hàng (<?= e($count) ?> món)"
        >
            <span aria-hidden="true">♧</span>
            <span class="hidden sm:inline">Giỏ hàng</span>
            <span
                class="grid size-5 place-items-center rounded-full bg-primary text-[10px] text-white"
            >
                <?= e($count) ?>
            </span>
        </a>
        <details class="relative md:hidden">
            <summary class="cursor-pointer rounded-md p-2 text-xl" aria-label="Mở menu">☰</summary>
            <nav
                aria-label="Điều hướng mobile"
                class="absolute right-0 top-12 z-30 flex w-56 flex-col gap-2 rounded-xl border bg-background p-4 text-sm shadow-lg"
            >
                <?php foreach (
                    [
                        'home' => 'Trang chủ',
                        'menu' => 'Thực đơn',
                        'story' => 'Chuyện nhà Mộc',
                        'orders' => 'Đơn hàng',
                    ]
                    as $key => $label
                ): ?>
                <a class="rounded-md p-3 hover:bg-accent" href="<?= e(url($key)) ?>">
                    <?= e($label) ?>
                </a>
                <?php endforeach; ?>
            </nav>
        </details>
    </div>
</header>
