<section class="page-section">
    <a class="text-xs text-muted-foreground" href="<?= e(url('menu')) ?>">← Quay lại thực đơn</a>
    <div class="mt-6 grid overflow-hidden rounded-2xl border md:grid-cols-2">
        <div
            class="flex h-72 items-center overflow-hidden md:h-auto"
            style="background: <?= e($product['color']) ?>"
        >
            <?php render('components/drink', [
                'product' => $product,
                'hero' => true,
            ]); ?>
        </div>
        <div class="p-6 md:p-9">
            <p class="eyebrow">MỘT LY TƯƠI MÁT</p>
            <h1 class="my-4 font-display text-3xl"><?= e($product['name']) ?></h1>
            <p class="text-xs leading-loose text-muted-foreground">
                <?= e($product['description']) ?>. Pha mới theo khẩu vị của bạn.
            </p>
            <p class="my-5 text-xl font-semibold text-brand-orange">
                <?= money($product['price']) ?>
                <span class="text-xs font-normal">/ size M</span>
            </p>
            <form
                method="post"
                action="index.php"
                class="space-y-5"
                data-product-form
                data-base-price="<?= e($product['price']) ?>"
                data-size-price="<?= LARGE_SIZE_SURCHARGE ?>"
            >
                <?php csrfField(); ?>
                <input type="hidden" name="action" value="add" />
                <input type="hidden" name="product_id" value="<?= e($product['id']) ?>" />
                <?php foreach (
                    [
                        'size' => [
                            'Chọn size',
                            ['M' => 'M · 500ml', 'L' => 'L · 700ml (+10k)'],
                            'M',
                        ],
                        'sweet' => [
                            'Độ ngọt',
                            ['0%' => '0%', '30%' => '30%', '50%' => '50%', '100%' => '100%'],
                            '50%',
                        ],
                        'ice' => [
                            'Lượng đá',
                            [
                                'Không đá' => 'Không đá',
                                'Ít đá' => 'Ít đá',
                                'Bình thường' => 'Bình thường',
                            ],
                            'Bình thường',
                        ],
                    ]
                    as $name => [$legend, $options, $default]
                ): ?>
                <fieldset>
                    <legend class="mb-3 text-xs font-semibold"><?= e($legend) ?></legend>
                    <div class="flex flex-wrap gap-2">
                        <?php foreach ($options as $value => $label): ?>
                        <label class="cursor-pointer">
                            <input
                                class="peer sr-only"
                                type="radio"
                                name="<?= e($name) ?>"
                                value="<?= e($value) ?>"
                                <?= $value === $default ? 'checked' : '' ?>
                            />
                            <span
                                class="inline-flex rounded-md border px-3 py-3 text-xs peer-checked:border-primary peer-checked:bg-accent peer-focus-visible:outline-2 peer-focus-visible:outline-ring"
                            >
                                <?= e($label) ?>
                            </span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </fieldset>
                <?php endforeach; ?>
                <fieldset>
                    <legend class="mb-3 text-xs font-semibold">
                        Thêm chút thú vị
                        <span class="ml-2 text-[10px] font-normal text-muted-foreground">
                            Chọn loại bạn thích
                        </span>
                    </legend>
                    <div class="flex flex-wrap gap-2">
                        <?php foreach ($toppings as $topping): ?>
                        <label
                            class="flex cursor-pointer items-center gap-2 rounded-md border p-3 text-xs has-checked:bg-accent"
                        >
                            <input
                                type="checkbox"
                                name="toppings[]"
                                value="<?= e($topping['id']) ?>"
                                data-price="<?= e($topping['price']) ?>"
                                class="size-4 accent-primary"
                            />
                            <?= e($topping['name']) ?> (+<?= money($topping['price']) ?>)
                        </label>
                        <?php endforeach; ?>
                    </div>
                </fieldset>
                <div class="flex flex-wrap items-end gap-3">
                    <label class="text-xs">
                        Số lượng
                        <input
                            class="field mt-2 w-20"
                            type="number"
                            name="quantity"
                            value="1"
                            min="1"
                            max="<?= MAX_QUANTITY ?>"
                            required
                        />
                    </label>
                    <button type="submit" class="btn min-h-11 flex-1">
                        Thêm vào giỏ
                        <span data-product-total><?= money($product['price']) ?></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>

<?php render('components/reviews', ['product' => $product, 'user' => $user]); ?>
