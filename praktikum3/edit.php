<?php
require 'connection.php';

$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM tugas WHERE id = ?");
$stmt->execute([$id]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $mata_kuliah = $_POST['mata_kuliah'];
    $nama_tugas = $_POST['nama_tugas'];
    $tenggat_waktu = $_POST['tenggat_waktu'];

    $sql = "UPDATE tugas SET mata_kuliah = ?, nama_tugas = ?, tenggat_waktu = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    
    if ($stmt->execute([$mata_kuliah, $nama_tugas, $tenggat_waktu, $id])) {
        header("Location: index.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Tugas Kuliah</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <style>
        body {
            /* Background */
            background-color: #f4f7f6;
            background-image: url('https://www.transparenttextures.com/patterns/dark-dot-2.png'), linear-gradient(135deg, #fdfbfb 0%, #ebedee 100%);
            min-height: 100vh;
            display: flex; 
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            padding: 20px;
        }

        .card-login-style {
            border: none;
            border-radius: 20px; 
            box-shadow: 0 15px 40px rgba(0,0,0,0.1); 
            background-color: white;
            padding: 40px; 
            width: 100%;
            max-width: 480px; 
        }

        /* Styling Judul dan Deskripsi */
        .card-login-style .head-title {
            color: #36465d;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 2px;
            font-size: 1.1rem;
            margin-bottom: 5px;
        }

        .card-login-style .sub-title {
            color: #f79c2a; 
            font-weight: 600;
            font-size: 1rem;
            margin-bottom: 10px;
        }

        .card-login-style .desc {
            color: #888;
            font-size: 0.9rem;
            margin-bottom: 30px;
        }

        .input-group-modern {
            position: relative;
            margin-bottom: 20px;
        }

        .input-group-modern .input-icon {
            position: absolute;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: #aaa; 
            z-index: 10;
        }

        .form-control-modern {
            border-radius: 50px; 
            border: 1px solid #ddd;
            padding: 12px 12px 12px 55px; 
            background-color: #fcfcfc;
            transition: all 0.3s;
            color: #555;
            font-size: 0.9rem;
        }

        .form-control-modern:focus {
            border-color: #f79c2a; 
            background-color: white;
            box-shadow: 0 4px 15px rgba(247, 156, 42, 0.1);
        }

        .btn-modern-submit {
            border-radius: 50px; 
            padding: 12px;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 2px;
            background-color: #f79c2a; 
            border: none;
            color: white;
            transition: all 0.3s;
            margin-top: 15px;
        }

        .btn-modern-submit:hover {
            background-color: #e58b1d;
            box-shadow: 0 5px 20px rgba(247, 156, 42, 0.4);
            color: white;
        }

        /* Tombol Batal */
        .btn-modern-outline {
            border-radius: 50px;
            padding: 8px;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 1px;
            color: #aaa;
            border: 2px solid #ddd;
            background: none;
            margin-top: 10px;
        }

        .btn-modern-outline:hover {
            border-color: #36465d;
            color: #36465d;
            background-color: #f9f9f9;
        }
    </style>
</head>
<body>

<div class="card card-login-style text-center">
    
    <div class="head-title">EDIT DATA TUGAS</div>
    <div class="sub-title">PERBARUI TUGAS ANDA</div>
    <p class="desc mb-4">Ubah data tugas kuliah di bawah ini</p>
    
    <form method="POST" action="">
        
        <div class="input-group-modern">
            <i class="fas fa-book input-icon"></i>
            <input type="text" class="form-control form-control-modern" name="mata_kuliah" placeholder="Mata Kuliah" value="<?= htmlspecialchars($data['mata_kuliah']); ?>" required>
        </div>
        
        <div class="input-group-modern">
            <i class="fas fa-pen input-icon"></i>
            <input type="text" class="form-control form-control-modern" name="nama_tugas" placeholder="Nama Tugas" value="<?= htmlspecialchars($data['nama_tugas']); ?>" required>
        </div>
        
        <div class="input-group-modern">
            <i class="fas fa-clock input-icon"></i>
            <input type="text" class="form-control form-control-modern" name="tenggat_waktu" placeholder="Tenggat Waktu" value="<?= htmlspecialchars($data['tenggat_waktu']); ?>" required>
        </div>
        
        <div class="d-grid gap-2 mb-3">
            <button type="submit" class="btn btn-modern-submit">Update Data</button>
        </div>
        
        <div class="d-grid gap-2">
            <a href="index.php" class="btn btn-modern-outline">
                <i class="fas fa-arrow-left me-1"></i> Batal / Kembali
            </a>
        </div>
        
    </form>
</div>

</body>
</html>