-- SIGAP Lingkungan - Skema Basis Data (MySQL / MariaDB, InnoDB, utf8mb4)
-- Cara pakai: phpMyAdmin > pilih database > tab Import > pilih berkas ini.
-- Catatan: akun contoh (admin, pengurus, warga) dibuat oleh install.php karena
-- nama/HP dienkripsi dengan APP_KEY dan sandi di-hash. Jika memakai berkas ini,
-- tetap jalankan install.php sekali untuk membuat akun contoh.
SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS areas (
  id    TINYINT PRIMARY KEY,
  nama  VARCHAR(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS users (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  role       ENUM('warga','pengurus','admin') NOT NULL,
  nama_enc   TEXT,                       -- nama terenkripsi (AES-256-CBC)
  hp_enc     TEXT,                       -- nomor HP terenkripsi
  hp_hash    CHAR(64) NOT NULL UNIQUE,   -- HMAC-SHA256 nomor HP (untuk login/cek duplikat)
  pass_hash  VARCHAR(255) NOT NULL,      -- password_hash()
  area_id    TINYINT NULL,               -- NULL = akses semua area (pengurus/admin)
  status     VARCHAR(12) NOT NULL DEFAULT 'aktif',  -- menunggu | aktif | nonaktif | diblokir
  created    DATETIME,
  CONSTRAINT fk_users_area FOREIGN KEY (area_id) REFERENCES areas(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS reports (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  user_id    INT NOT NULL,
  area_id    TINYINT NOT NULL,
  label      VARCHAR(20),                -- label anonim, contoh "Warga #47"
  tiket      VARCHAR(24) UNIQUE,         -- SGP-YYYYMMDD-000N
  deskripsi  TEXT NOT NULL,
  foto       VARCHAR(60),                -- nama berkas di folder storage/
  lat        DECIMAL(10,6),
  lng        DECIMAL(10,6),
  status     VARCHAR(10) NOT NULL DEFAULT 'Baru',   -- Baru | Diproses | Selesai
  created    DATETIME,
  KEY idx_reports_user (user_id),
  KEY idx_reports_area_status (area_id, status),
  KEY idx_reports_created (created),
  CONSTRAINT fk_reports_user FOREIGN KEY (user_id) REFERENCES users(id),
  CONSTRAINT fk_reports_area FOREIGN KEY (area_id) REFERENCES areas(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS history (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  report_id  INT NOT NULL,
  status     VARCHAR(10) NOT NULL,
  waktu      DATETIME,
  KEY idx_history_report (report_id),
  CONSTRAINT fk_history_report FOREIGN KEY (report_id) REFERENCES reports(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS notifications (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  user_id    INT NOT NULL,
  report_id  INT NOT NULL,
  pesan      VARCHAR(200),
  waktu      DATETIME,
  KEY idx_notif_user (user_id),
  CONSTRAINT fk_notif_user   FOREIGN KEY (user_id)   REFERENCES users(id),
  CONSTRAINT fk_notif_report FOREIGN KEY (report_id) REFERENCES reports(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS audit (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  admin_id   INT NOT NULL,
  report_id  INT NOT NULL,
  alasan     VARCHAR(255) NOT NULL,
  waktu      DATETIME,
  CONSTRAINT fk_audit_admin  FOREIGN KEY (admin_id)  REFERENCES users(id),
  CONSTRAINT fk_audit_report FOREIGN KEY (report_id) REFERENCES reports(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS otp (
  hp_hash  CHAR(64) PRIMARY KEY,
  kode     CHAR(6) NOT NULL,
  exp      DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

REPLACE INTO areas (id, nama) VALUES
  (1,'RT 01/RW 05'), (2,'RT 02/RW 05'), (3,'RT 03/RW 05');
