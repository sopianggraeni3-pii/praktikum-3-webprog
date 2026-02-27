<?php
require 'connection.php';

$id = $_GET['id'];
$stmt = $pdo->prepare("DELETE FROM tugas WHERE id = ?");

if ($stmt->execute([$id])) {
    // Kirim parameter pesan=hapus ke URL
    header("Location: index.php?pesan=hapus");
    exit;
}
?>