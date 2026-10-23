<?php

require_once __DIR__.'/../config/database.php';

$jumlah = $pdo->query('SELECT COUNT(*) FROM roles')->fetchColumn();
echo 'Koneksi berhasil! Jumlah role di database: '.$jumlah;
