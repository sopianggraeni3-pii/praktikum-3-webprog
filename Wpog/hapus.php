<?php
require 'connection.php';
$id = $_GET['id'];
$sql = "DELETE FROM mahasiswa WHERE id = ?";
$stmt = $pdo->prepare($sql);
if ($stmt->execute([$id])) {
    header("Location: index.php");
    exit;
}
