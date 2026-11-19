<?php
require __DIR__.'/config.php';require __DIR__.'/lib.php';
if(($_GET['token']??'')!==INSTALL_TOKEN||INSTALL_TOKEN==='GANTI_TOKEN_INSTALL')die('Token salah. Isi INSTALL_TOKEN di config.php lalu buka install.php?token=ISI_TOKEN');
$p=db();
foreach(["CREATE TABLE IF NOT EXISTS areas(id TINYINT PRIMARY KEY,nama VARCHAR(30))",
"CREATE TABLE IF NOT EXISTS users(id INT AUTO_INCREMENT PRIMARY KEY,role ENUM('warga','pengurus','admin') NOT NULL,nama_enc TEXT,hp_enc TEXT,hp_hash CHAR(64) UNIQUE,pass_hash VARCHAR(255),area_id TINYINT NULL,status VARCHAR(12) DEFAULT 'aktif',created DATETIME)",
"CREATE TABLE IF NOT EXISTS reports(id INT AUTO_INCREMENT PRIMARY KEY,user_id INT,area_id TINYINT,label VARCHAR(20),tiket VARCHAR(24) UNIQUE,deskripsi TEXT,foto VARCHAR(60),lat DECIMAL(10,6),lng DECIMAL(10,6),status VARCHAR(10) DEFAULT 'Baru',created DATETIME,KEY(user_id))",
"CREATE TABLE IF NOT EXISTS history(id INT AUTO_INCREMENT PRIMARY KEY,report_id INT,status VARCHAR(10),waktu DATETIME,KEY(report_id))",
"CREATE TABLE IF NOT EXISTS notifications(id INT AUTO_INCREMENT PRIMARY KEY,user_id INT,report_id INT,pesan VARCHAR(200),waktu DATETIME,KEY(user_id))",
"CREATE TABLE IF NOT EXISTS audit(id INT AUTO_INCREMENT PRIMARY KEY,admin_id INT,report_id INT,alasan VARCHAR(255),waktu DATETIME)",
"CREATE TABLE IF NOT EXISTS otp(hp_hash CHAR(64) PRIMARY KEY,kode CHAR(6),exp DATETIME)"] as $q)$p->exec($q);
foreach(['RT 01/RW 05','RT 02/RW 05','RT 03/RW 05'] as $i=>$n)$p->prepare('REPLACE INTO areas VALUES(?,?)')->execute([$i+1,$n]);
$seed=[['admin','Admin Sistem','0800000001','Admin#2026',null],['pengurus','Ketua RT 03','0800000002','Pengurus#2026',null],['warga','Siti Rahma','0800000003','Warga#2026',3]];
foreach($seed as $s)$p->prepare("INSERT IGNORE INTO users(role,nama_enc,hp_enc,hp_hash,pass_hash,area_id,status,created) VALUES(?,?,?,?,?,?,'aktif',?)")->execute([$s[0],enc($s[1]),enc($s[2]),hh($s[2]),password_hash($s[3],PASSWORD_DEFAULT),$s[4],date('Y-m-d H:i:s')]);
echo '<h3>Instalasi selesai.</h3><p>HAPUS install.php dari hosting sekarang.</p><pre>Admin    : 0800000001 / Admin#2026
Pengurus : 0800000002 / Pengurus#2026
Warga    : 0800000003 / Warga#2026
(segera ganti/hapus akun contoh)</pre>';
