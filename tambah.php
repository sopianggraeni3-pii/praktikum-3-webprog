<?php
require 'connection.php';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nim = $_POST['npm'];
    $nama = $_POST['nama'];
    $jurusan = $_POST['jurusan'];
    // Gunakan Prepared Statement (?) untuk keamanan
    $sql = "INSERT INTO mahasiswa (npm, nama, jurusan) VALUES (?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    if ($stmt->execute([$nim, $nama, $jurusan])) {
        header("Location: index.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html>

<body>
    <h2>Tambah Mahasiswa</h2>
    <form method="POST" action="">
        <label>NIM:</label><br>
        <input type="text" name="npm" required><br>
        <label>Nama:</label><br>
        <input type="text" name="nama" required><br>
        <label>Jurusan:</label><br>
        <input type="text" name="jurusan" required><br><br>
        <button type="submit">Simpan Data</button>
        <a href="index.php">Batal</a>
    </form>
</body>

</html>