<section
    class="relative grid gap-8 overflow-hidden px-6 pt-10 pb-16 md:min-h-[580px] md:grid-cols-[1.08fr_1fr] md:px-12 md:pt-16"
>
    <div>
        <p class="eyebrow">● TƯƠI TỪ TRÁI CÂY. THƠM TỪ LÁ TRÀ.</p>
        <h1 class="my-6 font-display text-[50px] leading-tight tracking-tight md:text-[62px]">
            Một ngụm
            <em>tươi,</em>
            <br />
            một ngày
            <em>vui.</em>
            <span class="text-4xl text-amber-600">✳</span>
        </h1>
        <p class="text-xs leading-loose text-muted-foreground">
            Trà thơm ủ chậm, trái cây tươi mỗi ngày.
            <br />
            Mộc pha một chút tự nhiên, gửi bạn thật nhiều dễ chịu.
        </p>
        <div class="mt-7 flex flex-wrap items-center gap-5">
            <a class="btn" href="<?= e(url('menu')) ?>">Khám phá thực đơn ↗</a>
            <a class="text-xs" href="<?= e(url('story')) ?>">Chuyện nhà Mộc →</a>
        </div>
        <div class="mt-8 flex items-center gap-3">
            <span class="text-2xl">👩🏻 👨🏻 👩🏽</span>
            <div>
                <p class="text-[10px] tracking-widest text-amber-600">★★★★★</p>
                <small class="text-[9px] text-muted-foreground">Hơn 1.200 ngày vui cùng Mộc</small>
            </div>
        </div>
    </div>
    <div class="relative mx-auto h-[400px] w-full max-w-[440px]">
        <div
            class="absolute left-1/2 top-4 size-80 -translate-x-1/2 rounded-full bg-[#f3e9d3] md:size-[380px]"
        ></div>
        <div
            class="absolute right-0 top-0 z-10 flex size-20 rotate-12 flex-col items-center justify-center rounded-full bg-accent font-display text-2xl outline-1 -outline-offset-6 outline-dashed outline-primary/50"
        >
            100%
            <small class="mt-1 font-sans text-[7px]">TRÁI CÂY TƯƠI</small>
        </div>
        <div class="relative scale-85 md:scale-100"><?php render('components/drink', [
            'product' => $products[1],
            'hero' => true,
        ]); ?></div>
        <a
            href="<?= e(url('product', ['id' => 1])) ?>"
            class="absolute bottom-0 left-1/2 z-10 flex w-64 -translate-x-1/2 items-center gap-3 rounded-lg border bg-background p-4 text-xs shadow-sm"
        >
            <span class="text-2xl">🍑</span>
            <span>
                Trà đào cam sả
                <small class="mt-1 block text-[8px] text-muted-foreground">
                    Một chút nắng trong ly trà
                </small>
            </span>
            <span class="ml-auto">↗</span>
        </a>
    </div>
</section>
<section
    class="grid grid-cols-2 gap-5 border-y bg-secondary/70 px-6 py-6 text-[10px] md:flex md:justify-between md:px-12"
>
    <span>♧ Trái cây tươi mỗi ngày</span>
    <span>♧ Pha mới khi bạn đặt</span>
    <span>♡ Ngọt theo cách bạn thích</span>
    <span>↗ Giao nhanh, vẫn tươi mát</span>
</section>
<section class="px-6 py-12 md:px-12">
    <div class="flex items-center justify-between gap-4">
        <div>
            <p class="eyebrow">MENU NHÀ MỘC</p>
            <h2>
                Hôm nay, bạn uống
                <em>gì?</em>
            </h2>
            <p class="text-xs text-muted-foreground">
                Một chiếc menu nhỏ, thật nhiều hương vị để thương.
            </p>
        </div>
        <a class="shrink-0 text-xs" href="<?= e(url('menu')) ?>">Xem tất cả ↗</a>
    </div>
    <div class="mt-7 grid grid-cols-2 gap-4 md:grid-cols-4 md:gap-6"><?php foreach (
        array_slice($products, 0, 4)
        as $item
    ):
        render('components/product-card', ['product' => $item]);
    endforeach; ?></div>
</section>
<section
    class="mx-6 grid overflow-hidden rounded-xl bg-accent/70 p-7 md:mx-12 md:grid-cols-2 md:p-10"
>
    <div>
        <p class="eyebrow">MỘC MỘT CHÚT, VUI NHIỀU CHÚT</p>
        <h2>
            Vị ngon bắt đầu từ
            <br />
            những điều
            <em>tự nhiên.</em>
        </h2>
        <p class="my-5 text-xs leading-loose text-muted-foreground">
            Không cầu kỳ, chỉ là lá trà ngon, trái cây tươi
            <br />
            và sự chăm chút trong từng lần pha.
        </p>
        <a class="btn" href="<?= e(url('story')) ?>">Ghé nhà Mộc ↗</a>
    </div>
    <div aria-hidden="true" class="relative flex min-h-48 items-center justify-center text-[110px]">
        🌿
        <span class="absolute font-display text-4xl italic">
            good tea.
            <br />
            good mood.
        </span>
        <span class="absolute right-0 bottom-0 text-7xl">🍊</span>
    </div>
</section>
