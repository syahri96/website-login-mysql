<?php
require_once dirname(__DIR__) . '/src/bootstrap.php';
require_once dirname(__DIR__) . '/src/layout.php';
if (current_user($pdo)) redirect('dashboard.php');
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $identifier = trim($_POST['identifier'] ?? '');
    $password = $_POST['password'] ?? '';
    if ($identifier === '' || $password === '') $error = 'Email/nomor HP dan password wajib diisi.';
    else {
        $normalized = str_contains($identifier, '@') ? strtolower($identifier) : normalize_phone($identifier);
        $stmt = $pdo->prepare('SELECT id,password_hash FROM users WHERE email = ? OR phone = ? LIMIT 1');
        $stmt->execute([$normalized, $normalized]);
        $user = $stmt->fetch();
        if ($user && $user['password_hash'] && password_verify($password, $user['password_hash'])) {
            if (password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)) {
                $update = $pdo->prepare('UPDATE users SET password_hash=? WHERE id=?');
                $update->execute([password_hash($password, PASSWORD_DEFAULT), $user['id']]);
            }
            login_user((int)$user['id']);
            flash('success', 'Anda berhasil masuk.');
            redirect('dashboard.php');
        }
        $error = 'Kredensial tidak cocok. Periksa kembali email/nomor HP dan password.';
    }
}
page_header('Masuk');
?>
<section class="card">
<h1>Selamat datang kembali</h1><p class="subtitle">Masuk menggunakan email atau nomor HP yang terdaftar.</p>
<?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>
<form method="post" action="login.php">
<input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
<label for="identifier">Email atau nomor HP</label><input id="identifier" name="identifier" required autocomplete="username" placeholder="nama@email.com / +6281234567890" value="<?= e($_POST['identifier'] ?? '') ?>">
<label for="password">Password</label><input id="password" name="password" type="password" required autocomplete="current-password">
<button class="btn" type="submit">Masuk</button>
</form>
<div class="divider">atau masuk dengan</div>
<a class="btn btn-social" href="oauth-start.php?provider=google"><img src="https://www.gstatic.com/firebasejs/ui/2.0.0/images/auth/google.svg" alt=""> Google</a>
<a class="btn btn-social" href="oauth-start.php?provider=facebook"><img src="https://upload.wikimedia.org/wikipedia/commons/0/05/Facebook_Logo_%282019%29.png" alt=""> Facebook</a>
<div class="row"><span>Belum punya akun?</span><a href="register.php">Daftar gratis</a></div>
<p class="hint">Login sosial memerlukan Client ID dan Client Secret dari Google Cloud dan Meta for Developers.</p>
</section>
<?php page_footer(); ?>
