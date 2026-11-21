<?php
require_once dirname(__DIR__) . '/src/bootstrap.php';
require_once dirname(__DIR__) . '/vendor/autoload.php';
use League\OAuth2\Client\Provider\Google;
use League\OAuth2\Client\Provider\Facebook;

$providerName = $_GET['provider'] ?? '';
$state = $_GET['state'] ?? '';
if (!in_array($providerName,['google','facebook'],true) ||
    $providerName !== ($_SESSION['oauth_provider'] ?? '') ||
    !hash_equals($_SESSION['oauth_state'] ?? '', (string)$state) || empty($_GET['code'])) {
    unset($_SESSION['oauth_state'], $_SESSION['oauth_provider']);
    flash('error','Autentikasi sosial dibatalkan atau tidak valid.');
    redirect('login.php');
}
unset($_SESSION['oauth_state'], $_SESSION['oauth_provider']);
$settings = $config['oauth'][$providerName];
$options = ['clientId'=>$settings['client_id'],'clientSecret'=>$settings['client_secret'],'redirectUri'=>$settings['redirect_uri']];
$provider = $providerName === 'google' ? new Google($options) : new Facebook($options);
try {
    $token = $provider->getAccessToken('authorization_code',['code'=>$_GET['code']]);
    $owner = $provider->getResourceOwner($token);
    if ($providerName === 'google') {
        $data = $owner->toArray();
        $oauthId = (string)($data['sub'] ?? $owner->getId());
        $email = strtolower((string)($data['email'] ?? ''));
        $name = (string)($data['name'] ?? 'Pengguna Google');
    } else {
        $oauthId = (string)$owner->getId();
        $email = strtolower((string)($owner->getEmail() ?? ''));
        $name = (string)($owner->getName() ?? 'Pengguna Facebook');
    }
    if ($email === '' || !filter_var($email,FILTER_VALIDATE_EMAIL)) throw new RuntimeException('Provider tidak memberikan email yang valid. Pastikan izin email aktif.');
    $stmt = $pdo->prepare('SELECT id FROM users WHERE oauth_provider=? AND oauth_id=?');
    $stmt->execute([$providerName,$oauthId]);
    $user = $stmt->fetch();
    if (!$user) {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email=?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if ($user) {
            $stmt = $pdo->prepare('UPDATE users SET oauth_provider=?,oauth_id=? WHERE id=?');
            $stmt->execute([$providerName,$oauthId,$user['id']]);
        } else {
            $stmt = $pdo->prepare('INSERT INTO users (name,email,oauth_provider,oauth_id,email_verified_at) VALUES (?,?,?,?,NOW())');
            $stmt->execute([$name,$email,$providerName,$oauthId]);
            $user = ['id'=>$pdo->lastInsertId()];
        }
    }
    login_user((int)$user['id']);
    flash('success','Berhasil masuk dengan ' . ucfirst($providerName) . '.');
    redirect('dashboard.php');
} catch (Throwable $e) {
    error_log('OAuth error: ' . $e->getMessage());
    flash('error','Login sosial gagal. Periksa konfigurasi provider dan coba lagi.');
    redirect('login.php');
}
