<?php
require_once dirname(__DIR__) . '/src/bootstrap.php';
require_once dirname(__DIR__) . '/src/layout.php';
$user = require_login($pdo);
page_header('Dashboard');
?>
<section class="card dashboard">
<div class="avatar"><?= e(mb_strtoupper(mb_substr($user['name'],0,1))) ?></div>
<h1>Login berhasil!</h1><p class="subtitle">Selamat datang di dashboard akun Anda.</p>
<div class="profile-line"><span>Nama</span><strong><?= e($user['name']) ?></strong></div>
<div class="profile-line"><span>Email</span><strong><?= e($user['email'] ?: 'Belum ditambahkan') ?></strong></div>
<div class="profile-line"><span>Nomor HP</span><strong><?= e($user['phone'] ?: 'Belum ditambahkan') ?></strong></div>
<div class="profile-line"><span>Bergabung sejak</span><strong><?= e(date('d M Y', strtotime($user['created_at']))) ?></strong></div>
<form method="post" action="logout.php"><input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>"><button class="btn" type="submit">Logout</button></form>
</section>
<?php page_footer(); ?>
