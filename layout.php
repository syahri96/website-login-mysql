<?php
function page_header(string $title): void {
    $flash = get_flash();
    ?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title) ?> · AkunKu</title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="page-bg"></div>
<main class="wrap">
  <a class="brand" href="index.php"><span class="brand-mark">A</span><span>AkunKu</span></a>
  <?php if ($flash): ?><div class="alert <?= e($flash['type']) ?>"><?= e($flash['message']) ?></div><?php endif; ?>
<?php
}
function page_footer(): void {
?>
</main>
<footer class="footer">© <?= date('Y') ?> AkunKu. Dibuat dengan aman.</footer>
</body>
</html>
<?php } ?>
