<?php
require 'connection.php';

// Atur zona waktu sesuai lokasimu agar jamnya presisi
date_default_timezone_set('Asia/Jakarta');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $mata_kuliah = $_POST['mata_kuliah'];
    $nama_tugas = $_POST['nama_tugas'];
    $tenggat_waktu = $_POST['tenggat_waktu'];

    // Validasi Back-End: Tolak jika waktu yang diinput lebih kecil dari waktu sekarang
    if (strtotime($tenggat_waktu) < time()) {
        header("Location: tambah.php?error=past_date");
        exit;
    }

    $sql = "INSERT INTO tugas (mata_kuliah, nama_tugas, tenggat_waktu) VALUES (?, ?, ?)";
    $stmt = $pdo->prepare($sql);

    if ($stmt->execute([$mata_kuliah, $nama_tugas, $tenggat_waktu])) {
        header("Location: index.php?pesan=tambah");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Tugas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        body { 
            background-color: #f3f4f6; 
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; 
            min-height: 100vh; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
        }
        .form-card { 
            background: #ffffff; 
            padding: 40px; 
            border-radius: 12px; 
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); 
            border: 1px solid #e5e7eb;
            width: 100%; 
            max-width: 420px; 
        }
        /* UPDATE: Ditambahkan text-align: center di sini */
        .form-title { font-weight: 800; font-size: 1.5rem; color: #111827; margin-bottom: 8px; text-align: center; }
        .form-subtitle { color: #6b7280; font-size: 0.9rem; margin-bottom: 25px; text-align: center; }
        
        .form-label { font-weight: 600; font-size: 0.85rem; color: #374151; margin-bottom: 6px; }
        .form-control-custom { 
            width: 100%; 
            padding: 10px 14px; 
            border-radius: 8px; 
            border: 1px solid #d1d5db; 
            background-color: #ffffff; 
            font-size: 0.95rem; 
            box-shadow: inset 0 1px 2px rgba(0,0,0,0.05);
            transition: all 0.2s; 
        }
        .form-control-custom:focus { outline: none; border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2); }
        
        .btn-submit { 
            background: linear-gradient(180deg, #3b82f6 0%, #2563eb 100%); 
            color: white; 
            border: 1px solid #1d4ed8; 
            width: 100%; 
            padding: 12px; 
            border-radius: 8px; 
            font-weight: 600; 
            margin-top: 15px; 
            box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.2);
            transition: 0.2s; 
        }
        .btn-submit:hover { background: linear-gradient(180deg, #2563eb 0%, #1d4ed8 100%); transform: translateY(-1px); }
        .btn-cancel { 
            display: block; 
            text-align: center; 
            margin-top: 15px; 
            color: #6b7280; 
            text-decoration: none; 
            font-weight: 500; 
            font-size: 0.9rem; 
        }
        .btn-cancel:hover { color: #111827; }
    </style>
</head>
<body>
<div class="form-card">
    <h2 class="form-title">Tambah Tugas</h2>
    <p class="form-subtitle">Lengkapi detail tugas di bawah ini.</p>
    
    <form method="POST">
        <div class="mb-3">
            <label class="form-label">Mata Kuliah</label>
            <select name="mata_kuliah" class="form-control-custom" required>
                <option value="" disabled selected>Pilih Mata Kuliah...</option>
                <option value="Analisis Numerik">Analisis Numerik</option>
                <option value="Grafika Komputer">Grafika Komputer</option>
                <option value="Jaringan Komputer">Jaringan Komputer</option>
                <option value="Manajemen Proyek Perangkat Lunak">Manajemen Proyek Perangkat Lunak</option>
                <option value="Pengembangan Web Berbasis Platform">Pengembangan Web Berbasis Platform</option>
                <option value="Pemodelan dan Simulasi">Pemodelan dan Simulasi</option>
                <option value="Pemrograman Web">Pemrograman Web</option>
                <option value="Sains Data">Sains Data</option>
                <option value="Sistem Informasi">Sistem Informasi</option>
                <option value="Strategi Algoritma">Strategi Algoritma</option>
            </select>
        </div>
        
        <div class="mb-3">
            <label class="form-label">Nama Tugas</label>
            <input type="text" name="nama_tugas" class="form-control-custom" placeholder="Contoh: Laporan Praktikum 3" required>
        </div>
        
        <div class="mb-4">
            <label class="form-label">Tenggat Waktu</label>
            <input type="datetime-local" name="tenggat_waktu" class="form-control-custom" min="<?= date('Y-m-d\TH:i'); ?>" required>
        </div>
        
        <button type="submit" class="btn-submit">Simpan Data</button>
        <a href="index.php" class="btn-cancel">Batal</a>
    </form>
</div>

<script>
    <?php if(isset($_GET['error']) && $_GET['error'] == 'past_date'): ?>
        Swal.fire({
            title: 'Waktu Tidak Valid!',
            text: 'Tenggat waktu tidak boleh di masa lalu. Silakan pilih waktu ke depan.',
            icon: 'error',
            confirmButtonColor: '#dc2626',
            borderRadius: '12px'
        });
        
        // Membersihkan URL agar pesan error tidak berulang saat halaman direfresh
        window.history.replaceState(null, null, window.location.pathname);
    <?php endif; ?>
</script>

</body>
</html>