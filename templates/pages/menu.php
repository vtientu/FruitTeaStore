<?php
$categories = ['Tất cả', 'Trà trái cây', 'Trà hoa', 'Trà nguyên bản'];
$category = inputString($_GET, 'category', 'Tất cả');
if (!in_array($category, $categories, true)) {
    $category = 'Tất cả';
}
$query = inputString($_GET, 'q');
$visible = array_filter(
    $products,
    fn(array $item): bool => ($category === 'Tất cả' || $item['category'] === $category) &&
        ($query === '' || preg_match('/' . preg_quote($query, '/') . '/iu', $item['name']) === 1),
);
?>
<section class="page-section">
    <p class="eyebrow">MENU NHÀ MỘC</p>
    <h2>Chọn một ly, vui cả ngày.</h2>
    <p class="text-xs text-muted-foreground">Một chiếc menu nhỏ, thật nhiều hương vị để thương.</p>
    <div class="my-7 flex flex-wrap items-center justify-between gap-5">
        <nav aria-label="Danh mục trà" class="flex flex-wrap gap-2">
            <?php foreach ($categories as $name): ?>
            <a
                class="<?= $category === $name
                    ? 'btn'
                    : 'btn-outline' ?> rounded-full px-3 py-2 text-[10px]"
                <?= $category === $name ? 'aria-current="true"' : '' ?>
                href="<?= e(url('menu', ['category' => $name, 'q' => $query])) ?>"
            >
                <?= e($name) ?>
            </a>
            <?php endforeach; ?>
        </nav>
        <form method="get" class="flex w-full gap-2 md:w-auto">
            <input type="hidden" name="page" value="menu" />
            <input type="hidden" name="category" value="<?= e($category) ?>" />
            <input
                class="field"
                type="search"
                name="q"
                value="<?= e($query) ?>"
                aria-label="Tìm món"
                placeholder="Tìm hương vị bạn thích…"
            />
            <button class="btn-outline shrink-0" type="submit">Tìm</button>
        </form>
    </div>
    <div class="grid grid-cols-2 gap-4 md:grid-cols-4 md:gap-6"><?php foreach ($visible as $item):
        render('components/product-card', ['product' => $item]);
    endforeach; ?></div>
    <?php if (!$visible): ?>
    <div class="py-16 text-center">
        <h3 class="text-lg">Chưa tìm thấy hương vị này</h3>
        <p class="my-5 text-xs text-muted-foreground">
            Thử từ khóa khác hoặc chọn một nhóm trà khác nhé.
        </p>
        <a class="btn" href="<?= e(url('menu')) ?>">Xem toàn bộ menu</a>
    </div>
    <?php endif; ?>
</section>
