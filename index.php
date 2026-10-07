<?php
declare(strict_types=1);
require __DIR__ . '/app/bootstrap.php';
?>
<!DOCTYPE html>
<html lang="vi">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <meta name="theme-color" content="#fbf8f1" />
        <meta name="description" content="Mộc Trà — trà trái cây tươi, pha theo cách bạn thích." />
        <title><?= e($title) ?> · Mộc Trà</title>
        <link rel="stylesheet" href="assets/css/app.css" />
        <script src="assets/js/app.js" defer></script>
    </head>
    <body class="bg-background font-sans text-foreground antialiased">
<?php render('layout/header', [
    'page' => $page,
    'title' => $title,
    'count' => cartCount($cart),
    'user' => $user,
]); ?>
<main class="mx-auto max-w-[1320px]">
    <?php if ($flash): ?>
        <div role="status" class="mx-6 mt-5 rounded-xl border bg-accent px-5 py-4 text-sm"><?= e(
            $flash,
        ) ?></div>
    <?php endif; ?>
    <?php render('pages/' . $page, [
        'products' => $products,
        'product' => $product,
        'cart' => $cart,
        'subtotal' => $subtotal,
        'discount' => $discount,
        'user' => $user,
        'databaseError' => $databaseError,
        'toppings' => $toppings,
    ]); ?>
</main>
<?php render('layout/footer'); ?>
</body>
</html>
