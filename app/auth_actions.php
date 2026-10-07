<?php
function handleAuth(string $action): never
{
    if ($action === 'logout') {
        $_SESSION = [];
        session_regenerate_id(true);
        flash('Đã đăng xuất.');
        redirect('home');
    }
    $email = strtolower(inputString($_POST, 'email'));
    $password = inputString($_POST, 'password');
    $_SESSION['form_old'] = ['email' => $email, 'name' => inputString($_POST, 'name')];
    if ($action === 'register') {
        $name = requiredText($_POST, 'name', 100);
        if (
            !filter_var($email, FILTER_VALIDATE_EMAIL) ||
            strlen($email) > 190 ||
            strlen($password) < 8 ||
            strlen($password) > 72 ||
            $password !== inputString($_POST, 'password_confirm')
        ) {
            throw new InvalidArgumentException(
                'Kiểm tra email, mật khẩu 8–72 ký tự và mật khẩu xác nhận.',
            );
        }
        if (query('SELECT id FROM users WHERE email=?', [$email])->fetch()) {
            throw new InvalidArgumentException('Email đã được đăng ký.');
        }
        query('INSERT INTO users(name,email,password_hash) VALUES(?,?,?)', [
            $name,
            $email,
            password_hash($password, PASSWORD_DEFAULT),
        ]);
        $userId = (int) db()->lastInsertId();
    } else {
        $user = query('SELECT * FROM users WHERE email=? AND active=1', [$email])->fetch();
        if (!$user || !password_verify($password, $user['password_hash'])) {
            throw new InvalidArgumentException(
                'Email hoặc mật khẩu không đúng, hoặc tài khoản đã bị khóa.',
            );
        }
        $userId = (int) $user['id'];
    }
    session_regenerate_id(true);
    $_SESSION['user_id'] = $userId;
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    unset($_SESSION['form_old']);
    flash('Đăng nhập thành công.');
    $destination = $_SESSION['after_login'] ?? 'home';
    unset($_SESSION['after_login']);
    redirect(in_array($destination, ['checkout', 'orders'], true) ? $destination : 'home');
}
