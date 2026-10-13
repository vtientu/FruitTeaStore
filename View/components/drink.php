<div
    class="drink-art <?= e($product['type']) ?> <?= !empty($hero) ? 'hero-drink' : '' ?>"
    role="img"
    aria-label="Minh họa <?= e($product['name']) ?>"
>
    <span class="fruit fruit-one"><?= e($product['fruit']) ?></span>
    <span class="fruit fruit-two"><?= $product['type'] === 'berry' ? '🍓' : '🍋' ?></span>
    <span class="art-leaf">🌿</span>
    <div class="straw"></div>
    <div class="cup">
        <div class="cup-lid"></div>
        <div class="tea">
            <i></i>
            <i></i>
            <i></i>
            <i></i>
            <div class="slice"></div>
            <div class="cup-brand">
                mộc
                <span>TRÀ TƯƠI · NGÀY VUI</span>
                <span class="text-xl">♧</span>
            </div>
            <div class="pearls">● ● ● ● ●</div>
        </div>
    </div>
    <div class="cup-shadow"></div>
</div>
