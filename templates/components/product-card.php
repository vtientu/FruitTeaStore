<article data-product-card class="min-w-0">
    <a
        href="<?= e(url('product', ['id' => $product['id']])) ?>"
        aria-label="Xem <?= e($product['name']) ?>"
        class="group relative block overflow-hidden rounded-xl pt-5 pb-3"
        style="background: <?= e($product['color']) ?>"
    >
        <span
            class="absolute left-3 top-3 z-10 rounded bg-background/80 px-2 py-1 text-[7px] tracking-wide"
        >
            <?= e($product['tag']) ?>
        </span>
        <div class="transition-transform group-hover:-rotate-3"><?php render('components/drink', [
            'product' => $product,
        ]); ?></div>
        <span class="block text-center text-[5px] tracking-widest opacity-60">
            FRESHLY BREWED, JUST FOR YOU
        </span>
    </a>
    <div class="space-y-2 py-4">
        <a class="text-sm font-semibold" href="<?= e(
            url('product', ['id' => $product['id']]),
        ) ?>"><?= e($product['name']) ?></a>
        <p class="min-h-8 text-[10px] leading-relaxed text-muted-foreground"><?= e(
            $product['description'],
        ) ?></p>
        <div class="flex items-center justify-between">
            <strong class="text-xs font-medium"><?= money($product['price']) ?></strong>
            <a
                href="<?= e(url('product', ['id' => $product['id']])) ?>"
                class="btn-outline size-9 rounded-full p-0 text-lg"
                aria-label="Chọn <?= e($product['name']) ?>"
            >
                +
            </a>
        </div>
    </div>
</article>
