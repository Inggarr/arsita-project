<?php

require_once __DIR__.'/../config/database.php';

$pdo->exec("
    CREATE TABLE IF NOT EXISTS arsip (
        id_arsip INT AUTO_INCREMENT PRIMARY KEY,
        tipe ENUM('masuk', 'keluar') NOT NULL,
        referensi_id INT NOT NULL,
        tanggal_arsip DATE NOT NULL,
        catatan VARCHAR(255) NULL
    ) ENGINE=InnoDB;
");

echo 'Tabel arsip verified!';
