CREATE DATABASE IF NOT EXISTS auth_website
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE auth_website;

CREATE TABLE IF NOT EXISTS users (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(190) NULL UNIQUE,
  phone VARCHAR(25) NULL UNIQUE,
  password_hash VARCHAR(255) NULL,
  oauth_provider VARCHAR(20) NULL,
  oauth_id VARCHAR(190) NULL,
  email_verified_at DATETIME NULL,
  phone_verified_at DATETIME NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_oauth_account (oauth_provider, oauth_id)
) ENGINE=InnoDB;

-- Email atau nomor HP minimal salah satu harus diisi.
-- Akun OAuth dapat memiliki password_hash NULL.
