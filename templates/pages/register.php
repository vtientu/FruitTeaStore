<?php $old = $_SESSION['form_old'] ?? []; ?>
<section class="page-section mx-auto max-w-lg">
    <p class="eyebrow">GIA NHẬP NHÀ MỘC</p>
    <h2>Đăng ký tài khoản</h2>
    <form method="post" action="index.php" class="space-y-5 rounded-xl border p-6">
        <?php csrfField(); ?>
        <input type="hidden" name="action" value="register" />
        <label class="block text-sm">
            Họ và tên
            <input
                class="field mt-2"
                name="name"
                required
                maxlength="100"
                value="<?= e($old['name'] ?? '') ?>"
                autocomplete="name"
            />
        </label>
        <label class="block text-sm">
            Email
            <input
                class="field mt-2"
                type="email"
                name="email"
                required
                maxlength="190"
                value="<?= e($old['email'] ?? '') ?>"
                autocomplete="email"
            />
        </label>
        <label class="block text-sm">
            Mật khẩu (ít nhất 8 ký tự)
            <input
                class="field mt-2"
                type="password"
                name="password"
                required
                minlength="8"
                maxlength="72"
                autocomplete="new-password"
            />
        </label>
        <label class="block text-sm">
            Xác nhận mật khẩu
            <input
                class="field mt-2"
                type="password"
                name="password_confirm"
                required
                minlength="8"
                maxlength="72"
                autocomplete="new-password"
            />
        </label>
        <button class="btn w-full">Tạo tài khoản</button>
        <a class="block text-xs underline" href="<?= e(
            url('login'),
        ) ?>">Đã có tài khoản? Đăng nhập</a>
    </form>
</section>
