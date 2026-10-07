<section class="page-section mx-auto max-w-2xl">
    <p class="eyebrow">BẮT ĐẦU VỚI MỘC TRÀ</p>
    <h2>Cài đặt website</h2>
    <p class="mb-5 text-sm leading-loose text-muted-foreground">
        Bật Apache và MySQL trong XAMPP. Thông tin database nằm ở
        <code>app/config.php</code>
        . Tạo tài khoản quản trị đầu tiên bên dưới; sản phẩm mẫu sẽ được thêm tự động.
    </p>
    <?php if ($databaseError): ?>
    <p class="mb-5 rounded-lg border bg-secondary p-4 text-sm" role="alert"><?= e(
        $databaseError,
    ) ?></p>
    <?php endif; ?>
    <form method="post" class="space-y-5 rounded-xl border p-6">
        <?php csrfField(); ?>
        <input type="hidden" name="action" value="install" />
        <label class="block text-sm">
            Tên admin
            <input class="field mt-2" name="name" required maxlength="100" autocomplete="name" />
        </label>
        <label class="block text-sm">
            Email admin
            <input
                class="field mt-2"
                name="email"
                type="email"
                required
                maxlength="190"
                autocomplete="email"
            />
        </label>
        <label class="block text-sm">
            Mật khẩu admin
            <input
                class="field mt-2"
                name="password"
                type="password"
                required
                minlength="8"
                maxlength="72"
                autocomplete="new-password"
            />
        </label>
        <button class="btn w-full">Tạo database và tài khoản admin</button>
    </form>
</section>
