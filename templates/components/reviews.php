<?php
$reviews = query(
    'SELECT r.*,u.name FROM reviews r JOIN users u ON u.id=r.user_id WHERE r.product_id=? ORDER BY r.created_at DESC LIMIT 20',
    [$product['id']],
)->fetchAll();
$summary = query(
    'SELECT COUNT(*) AS count,AVG(rating) AS average FROM reviews WHERE product_id=?',
    [$product['id']],
)->fetch();
$canReview =
    $user &&
    query(
        "SELECT oi.id FROM order_items oi JOIN orders o ON o.id=oi.order_id WHERE o.user_id=? AND oi.product_id=? AND o.status='completed' LIMIT 1",
        [$user['id'], $product['id']],
    )->fetch();
$myReview = $user
    ? query('SELECT * FROM reviews WHERE user_id=? AND product_id=?', [
        $user['id'],
        $product['id'],
    ])->fetch()
    : null;
?>
<section class="px-6 pb-12 md:px-12">
    <h2>Khách nhà Mộc nói gì?</h2>
    <p class="mb-6 text-sm text-muted-foreground"><?= (int) $summary[
        'count'
    ] ?> đánh giá<?= $summary['count']
     ? ' · ' . number_format((float) $summary['average'], 1) . '/5 ★'
     : '' ?></p>
    <div class="grid gap-6 md:grid-cols-2">
        <div class="space-y-4">
            <?php foreach ($reviews as $review): ?>
            <article class="rounded-xl border p-5">
                <strong class="text-sm"><?= e($review['name']) ?></strong>
                <span class="ml-3 text-amber-600"><?= str_repeat(
                    '★',
                    (int) $review['rating'],
                ) ?></span>
                <p class="mt-3 break-words text-sm leading-relaxed"><?= e($review['comment']) ?></p>
            </article>
            <?php endforeach; ?>
            <?php if (!$reviews): ?>
            <p class="text-sm text-muted-foreground">
                Chưa có đánh giá. Hãy thử món và chia sẻ cảm nhận nhé.
            </p>
            <?php endif; ?>
        </div>
        <?php if ($canReview): ?>
        <form method="post" action="index.php" class="h-fit space-y-4 rounded-xl bg-secondary p-6">
            <?php csrfField(); ?>
            <input type="hidden" name="action" value="review" />
            <input type="hidden" name="product_id" value="<?= e($product['id']) ?>" />
            <h3 class="font-semibold">Đánh giá của bạn</h3>
            <label class="block text-sm">
                Số sao
                <select class="field mt-2" name="rating">
                    <?php for ($rating = 5; $rating >= 1; $rating--): ?>
                    <option value="<?= $rating ?>" <?= (int) ($myReview['rating'] ?? 5) === $rating
    ? 'selected'
    : '' ?>>
                        <?= $rating ?> sao
                    </option>
                    <?php endfor; ?>
                </select>
            </label>
            <label class="block text-sm">
                Cảm nhận
                <textarea class="field mt-2" name="comment" required maxlength="1000">
<?= e($myReview['comment'] ?? '') ?></textarea>
            </label>
            <button class="btn">Lưu đánh giá</button>
        </form>
        <?php else: ?>
        <p class="text-sm leading-loose text-muted-foreground">
            Bạn có thể đánh giá sau khi đăng nhập và đơn có món này đã hoàn thành.
        </p>
        <?php endif; ?>
    </div>
</section>
