# Website Login & Register — PHP + MySQL

Template autentikasi modern berbahasa Indonesia. Fitur:
- Registrasi dengan nama, email dan/atau nomor HP, serta password.
- Login menggunakan email **atau** nomor HP + password.
- Login Google dan Facebook melalui OAuth 2.0.
- Login nomor HP menggunakan OTP SMS melalui Twilio Verify (opsional).
- Dashboard profil dan logout.
- Password di-hash dengan `password_hash()`, query menggunakan PDO prepared statements, CSRF token, session regeneration, dan cookie HttpOnly/SameSite.

## Persyaratan
- PHP 8.1+ dengan ekstensi PDO MySQL dan cURL.
- MySQL 5.7+ / MariaDB yang kompatibel.
- Composer.
- HTTPS untuk deployment publik (wajib untuk produksi, terutama OAuth/OTP).

## Instalasi lokal
1. Buat database dan tabel dengan menjalankan `database.sql` di phpMyAdmin atau MySQL.
2. Salin `config.example.php` menjadi `config.php`, lalu isi pengaturan database.
3. Jalankan `composer install` dari folder proyek.
4. Jalankan server lokal dari folder proyek:
   ```bash
   php -S localhost:8000 -t public
   ```
5. Buka `http://localhost:8000`.

> Jangan arahkan document root hosting ke folder proyek utama. Arahkan ke folder `public/` agar `src/`, `config.php`, dan file sensitif tidak dapat diakses dari web.

## Mengaktifkan Google Login
1. Buka Google Cloud Console dan buat/pilih project.
2. Konfigurasikan OAuth consent screen.
3. Buat OAuth Client ID tipe Web Application.
4. Tambahkan Authorized redirect URI yang sama persis dengan `oauth.google.redirect_uri` di `config.php`, misalnya `https://domainanda.com/oauth-callback.php?provider=google`.
5. Masukkan Client ID dan Client Secret ke bagian `oauth.google`.
6. Tambahkan domain ke daftar Authorized domains Google.

## Mengaktifkan Facebook Login
1. Buat aplikasi di Meta for Developers.
2. Tambahkan produk Facebook Login dan atur Valid OAuth Redirect URI.
3. Redirect URI harus sama persis dengan `oauth.facebook.redirect_uri` di `config.php`.
4. Isi App ID dan App Secret pada `oauth.facebook`.
5. Aktifkan mode Live dan lengkapi persyaratan Meta sebelum digunakan publik. Meta dapat meminta verifikasi bisnis/izin tertentu.

## Mengaktifkan OTP nomor HP (Twilio Verify)
1. Buat akun Twilio dan layanan Verify.
2. Isi `account_sid`, `auth_token`, dan `verify_service_sid` di bagian `twilio` pada `config.php`.
3. Endpoint `public/phone-otp.php` menerima POST:
   - `action=send`, `phone=+628...`, `_csrf=...`
   - `action=check`, `phone=+628...`, `code=123456`, `_csrf=...`
4. Endpoint mengembalikan JSON. Frontend perlu memanggil endpoint tersebut dan mengarahkan ke `dashboard.php` setelah respons sukses.
5. Uji nomor tujuan sesuai batasan akun Twilio dan regulasi SMS negara tujuan.

## Catatan penting untuk produksi
- Tambahkan verifikasi email, reset password, rate limiting/login throttling, audit log, dan proteksi bot.
- Pertimbangkan mengharuskan verifikasi email/nomor sebelum akun dipakai.
- Simpan rahasia di environment variables atau secret manager; jangan commit `config.php`.
- Batasi percobaan OTP dan login, gunakan HTTPS, backup database, dan perbarui dependency.
- Social OAuth menghubungkan akun berdasarkan email yang diberikan provider. Tinjau kebijakan account-linking sebelum dipakai pada layanan sensitif.
- Form registrasi saat ini dapat membuat akun dengan email atau nomor HP; password minimal 8 karakter.
- Login dengan nomor HP + password tersedia langsung. OTP SMS disediakan sebagai endpoint Twilio opsional yang bisa dihubungkan ke antarmuka.

## Struktur
```text
website-login-mysql/
├── config.example.php
├── composer.json
├── database.sql
├── README.md
├── src/
│   ├── bootstrap.php
│   ├── helpers.php
│   └── layout.php
└── public/
    ├── index.php
    ├── login.php
    ├── register.php
    ├── dashboard.php
    ├── logout.php
    ├── oauth-start.php
    ├── oauth-callback.php
    ├── phone-otp.php
    └── assets/style.css
```
