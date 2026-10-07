<section class="page-section">
    <p class="eyebrow">CHUYỆN NHÀ MỘC</p>
    <h1 class="my-6 font-display text-5xl leading-tight md:text-6xl">
        Giữ lại những điều
        <br />
        <em>tươi lành.</em>
    </h1>
    <p class="max-w-xl text-sm leading-loose text-muted-foreground">
        Mộc bắt đầu từ một ý nghĩ đơn giản: một ly trà ngon có thể khiến ngày bình thường trở nên dễ
        chịu hơn.
    </p>
    <div class="my-10 grid gap-5 md:grid-cols-3">
        <?php foreach (
            [
                ['01', 'Lá trà thật', 'Ủ chậm để giữ hương thơm dịu và vị trà thanh.'],
                ['02', 'Trái cây tươi', 'Chọn từng quả, chuẩn bị mỗi ngày, pha mới khi bạn đặt.'],
                [
                    '03',
                    'Theo cách bạn thích',
                    'Ít ngọt, thêm đá hay thêm topping — niềm vui là của bạn.',
                ],
            ]
            as [$number, $heading, $description]
        ): ?>
        <article class="rounded-xl bg-secondary p-7">
            <span class="text-xs text-brand-orange"><?= e($number) ?></span>
            <h2 class="text-2xl"><?= e($heading) ?></h2>
            <p class="text-xs leading-loose text-muted-foreground"><?= e($description) ?></p>
        </article>
        <?php endforeach; ?>
    </div>
    <a class="btn" href="<?= e(url('menu')) ?>">Tìm hương vị của bạn →</a>
</section>
