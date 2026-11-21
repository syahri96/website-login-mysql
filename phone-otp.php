<?php
// Endpoint OTP via Twilio Verify. Aktifkan setelah mengisi konfigurasi Twilio.
require_once dirname(__DIR__) . '/src/bootstrap.php';
header('Content-Type: application/json; charset=utf-8');
function respond(int $status, array $data): never { http_response_code($status); echo json_encode($data); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') respond(405,['error'=>'Method tidak diizinkan.']);
verify_csrf();
$phone = normalize_phone($_POST['phone'] ?? '');
$action = $_POST['action'] ?? '';
$t = $config['twilio'];
if (!$t['account_sid'] || !$t['auth_token'] || !$t['verify_service_sid']) respond(503,['error'=>'Layanan OTP belum dikonfigurasi.']);
if (!preg_match('/^\+[1-9]\d{7,14}$/',$phone)) respond(422,['error'=>'Gunakan format nomor internasional, contoh +6281234567890.']);
$base = 'https://verify.twilio.com/v2/Services/' . rawurlencode($t['verify_service_sid']) . '/Verifications';
if ($action === 'send') {
    $url = $base;
    $fields = ['To'=>$phone,'Channel'=>'sms'];
} elseif ($action === 'check') {
    $url = $base . '/' . rawurlencode($phone);
    $fields = ['Code'=>trim($_POST['code'] ?? '')];
    if ($fields['Code'] === '') respond(422,['error'=>'Masukkan kode OTP.']);
} else respond(422,['error'=>'Aksi tidak dikenal.']);
$ch = curl_init($url);
curl_setopt_array($ch,[CURLOPT_POST=>true,CURLOPT_POSTFIELDS=>http_build_query($fields),CURLOPT_RETURNTRANSFER=>true,CURLOPT_USERPWD=>$t['account_sid'].':'.$t['auth_token'],CURLOPT_HTTPAUTH=>CURLAUTH_BASIC,CURLOPT_TIMEOUT=>20]);
$body = curl_exec($ch); $status = curl_getinfo($ch,CURLINFO_HTTP_CODE); curl_close($ch);
$data = json_decode((string)$body,true) ?: [];
if ($status < 200 || $status >= 300) respond(400,['error'=>$data['message'] ?? 'Permintaan OTP gagal.']);
if ($action === 'send') respond(200,['success'=>true,'message'=>'Kode OTP dikirim.']);
if (($data['status'] ?? '') !== 'approved') respond(400,['error'=>'Kode OTP salah atau kedaluwarsa.']);
$stmt = $pdo->prepare('SELECT id FROM users WHERE phone=?');
$stmt->execute([$phone]); $user = $stmt->fetch();
if (!$user) respond(404,['error'=>'Nomor belum terdaftar. Daftar dahulu menggunakan nomor ini.']);
$pdo->prepare('UPDATE users SET phone_verified_at=NOW() WHERE id=?')->execute([$user['id']]);
login_user((int)$user['id']);
respond(200,['success'=>true,'redirect'=>'dashboard.php']);
