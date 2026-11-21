<?php
require_once dirname(__DIR__) . '/src/bootstrap.php';
require_once dirname(__DIR__) . '/src/layout.php';
if (current_user($pdo)) redirect('dashboard.php');
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $name = trim($_POST['name'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $phone = normalize_phone($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['password_confirmation'] ?? '';
    if (mb_strlen($name) < 2 || mb_strlen($name) > 120) $errors[] = 'Nama harus berisi 2–120 karakter.';
    if ($email === '' && $phone === '') $errors[] = 'Isi email atau nomor HP minimal salah satu.';
    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Format email tidak valid.';
    if ($phone !== '' && !preg_match('/^\+[1-9]\d{7,14}$/', $phone)) $errors[] = 'Nomor HP harus format internasional, contoh +6281234567890.';
    if (strlen($password) < 8) $errors[] = 'Password minimal 8 karakter.';
    if ($password !== $confirm) $errors[] = 'Konfirmasi password tidak sama.';
    if (!$errors) {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE (email IS NOT NULL AND email = ?) OR (phone IS NOT NULL AND phone = ?)');
        $stmt->execute([$email ?: null, $phone ?: null]);
        if ($stmt->fetch()) $errors[] = 'Email atau nomor HP sudah terdaftar.';
        else {
            $stmt = $pdo->prepare('INSERT INTO users (name,email,phone,password_hash) VALUES (?,?,?,?)');
            $stmt->execute([$name, $email ?: null, $phone ?: null, password_hash($password, PASSWORD_DEFAULT)]);
            login_user((int)$pdo->lastInsertId());
            flash('success', 'Akun berhasil dibuat. Selamat datang!');
            redirect('dashboard.php');
        }
    }
}
page_header('Buat akun');
?>
<section class="card">
<h1>Buat akun baru</h1><p class="subtitle">Daftar untuk mulai menggunakan akun Anda.</p>
<?php foreach ($errors as $error): ?><div class="alert error"><?= e($error) ?></div><?php endforeach; ?>
<form method="post" action="register.php">
<input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
<label for="name">Nama lengkap</label><input id="name" name="name" required maxlength="120" autocomplete="name" value="<?= e($_POST['name'] ?? '') ?>">
<label for="email">Email <span style="color:#8993a5;font-weight:400">(opsional)</span></label><input id="email" name="email" type="email" autocomplete="email" value="<?= e($_POST['email'] ?? '') ?>" placeholder="nama@email.com">
<label for="phone">Nomor HP <span style="color:#8993a5;font-weight:400">(opsional)</span></label><input id="phone" name="phone" type="tel" autocomplete="tel" value="<?= e($_POST['phone'] ?? '') ?>" placeholder="+6281234567890">
<p class="hint">Isi email, nomor HP, atau keduanya. Nomor HP gunakan format internasional.</p>
<label for="password">Password</label><input id="password" name="password" type="password" minlength="8" required autocomplete="new-password">
<label for="password_confirmation">Ulangi password</label><input id="password_confirmation" name="password_confirmation" type="password" minlength="8" required autocomplete="new-password">
<button class="btn" type="submit">Daftar sekarang</button>
</form>
<div class="row"><span>Sudah punya akun?</span><a href="login.php">Masuk</a></div>
</section>
<?php page_footer(); ?>
