<?php $old = $_SESSION['form_old'] ?? []; ?>
<section class="page-section mx-auto max-w-lg">
    <p class="eyebrow">CHÀO MỪNG BẠN TRỞ LẠI</p>
    <h2>Đăng nhập</h2>
    <form method="post" action="index.php" class="space-y-5 rounded-xl border p-6">
        <?php csrfField(); ?>
        <input type="hidden" name="action" value="login" />
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
            Mật khẩu
            <input
                class="field mt-2"
                type="password"
                name="password"
                required
                maxlength="72"
                autocomplete="current-password"
            />
        </label>
        <button class="btn w-full">Đăng nhập</button>
        <p class="text-xs">
            Chưa có tài khoản?
            <a class="underline" href="<?= e(url('register')) ?>">Đăng ký</a>
        </p>
    </form>
</section>
