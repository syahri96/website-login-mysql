<?php
declare(strict_types=1);

function e(?string $value): string {
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
function csrf_token(): string {
    if (empty($_SESSION['_csrf'])) $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['_csrf'];
}
function verify_csrf(): void {
    $token = $_POST['_csrf'] ?? '';
    if (!is_string($token) || !hash_equals($_SESSION['_csrf'] ?? '', $token)) {
        http_response_code(419);
        exit('Sesi formulir tidak valid. Muat ulang halaman dan coba lagi.');
    }
}
function flash(string $type, string $message): void {
    $_SESSION['_flash'] = ['type' => $type, 'message' => $message];
}
function get_flash(): ?array {
    $item = $_SESSION['_flash'] ?? null;
    unset($_SESSION['_flash']);
    return $item;
}
function redirect(string $path): never {
    header('Location: ' . $path);
    exit;
}
function current_user(PDO $pdo): ?array {
    if (empty($_SESSION['user_id'])) return null;
    $stmt = $pdo->prepare('SELECT id, name, email, phone, created_at FROM users WHERE id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    if (!$user) { unset($_SESSION['user_id']); return null; }
    return $user;
}
function require_login(PDO $pdo): array {
    $user = current_user($pdo);
    if (!$user) { flash('error', 'Silakan masuk terlebih dahulu.'); redirect('login.php'); }
    return $user;
}
function login_user(int $id): void {
    session_regenerate_id(true);
    $_SESSION['user_id'] = $id;
}
function normalize_phone(string $phone): string {
    return preg_replace('/[\s().-]/', '', trim($phone));
}
function app_config(): array {
    global $config;
    return $config;
}
