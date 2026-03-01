<?php
require 'connection.php';
date_default_timezone_set('Asia/Jakarta');

// Fitur Toggle Selesai/Belum
if (isset($_GET['status_id'])) {
    $id = $_GET['status_id'];
    $current = $_GET['current'];
    $new = ($current == 0) ? 1 : 0;
    $stmt = $pdo->prepare("UPDATE tugas SET status = ? WHERE id = ?");
    $stmt->execute([$new, $id]);
    header("Location: index.php");
    exit;
}

// Mengambil Daftar Mata Kuliah Unik untuk Dropdown Filter
$stmt_matkul = $pdo->query("SELECT DISTINCT mata_kuliah FROM tugas ORDER BY mata_kuliah ASC");
$list_matkul = $stmt_matkul->fetchAll(PDO::FETCH_COLUMN);

// LOGIC FILTER & SORTING
$whereClauses = [];
$params = [];

$filter_status = $_GET['filter_status'] ?? 'semua';
if ($filter_status === 'selesai') {
    $whereClauses[] = "status = 1";
} elseif ($filter_status === 'berjalan') {
    $whereClauses[] = "status = 0 AND tenggat_waktu >= NOW()";
} elseif ($filter_status === 'terlambat') {
    $whereClauses[] = "status = 0 AND tenggat_waktu < NOW()";
}

$filter_matkul = $_GET['filter_matkul'] ?? 'semua';
if ($filter_matkul !== 'semua') {
    $whereClauses[] = "mata_kuliah = ?";
    $params[] = $filter_matkul;
}

$whereSql = "";
if (count($whereClauses) > 0) {
    $whereSql = " WHERE " . implode(" AND ", $whereClauses);
}

$sort = $_GET['sort'] ?? 'dekat';
$orderSql = " ORDER BY status ASC, tenggat_waktu ASC"; 
if ($sort === 'jauh') {
    $orderSql = " ORDER BY status ASC, tenggat_waktu DESC";
} elseif ($sort === 'az') {
    $orderSql = " ORDER BY status ASC, nama_tugas ASC";
} elseif ($sort === 'za') {
    $orderSql = " ORDER BY status ASC, nama_tugas DESC";
}

$sql = "SELECT * FROM tugas" . $whereSql . $orderSql;
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$tugas_kuliah = $stmt->fetchAll(PDO::FETCH_ASSOC);

// LOGIC SUMMARY & PROGRESS BAR
$stmt_all = $pdo->query("SELECT * FROM tugas");
$semua_tugas = $stmt_all->fetchAll(PDO::FETCH_ASSOC);

$total_tugas = count($semua_tugas);
$tugas_selesai = 0;
$tugas_pending = 0;
$tugas_terlambat = 0;
$sekarang = time();

foreach($semua_tugas as $t) {
    if($t['status'] == 1) {
        $tugas_selesai++;
    } else {
        $tugas_pending++;
        if(strtotime($t['tenggat_waktu']) < $sekarang) {
            $tugas_terlambat++;
        }
    }
}

$progress_percent = ($total_tugas > 0) ? round(($tugas_selesai / $total_tugas) * 100) : 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Tugas - Sopi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        :root {
            --primary: #2563eb; --primary-light: #eff6ff;
            --success: #10b981; --success-light: #d1fae5;
            --warning: #f59e0b; --warning-light: #fef3c7;
            --danger: #ef4444; --danger-light: #fee2e2;
            --orange: #f97316; --orange-light: #ffedd5;
            --gray: #64748b; --gray-light: #f1f5f9;
            --surface: #ffffff; --background: #f8fafc;
            --text-main: #0f172a; --text-muted: #64748b;
            --border: #e2e8f0;
        }

        body { background-color: var(--background); font-family: 'Inter', sans-serif; padding-bottom: 50px; color: var(--text-main); }
        
        .dashboard-header { max-width: 1000px; margin: 40px auto 45px; text-align: center; }
        .dashboard-title { font-weight: 800; font-size: 1.8rem; color: var(--text-main); margin: 0; letter-spacing: -0.5px; }
        
        
        /* Progress Bar Section */
        .progress-wrapper { max-width: 1000px; margin: 0 auto 30px; background: white; padding: 15px 20px; border-radius: 12px; border: 1px solid var(--border); box-shadow: 0 2px 4px rgba(0,0,0,0.02); }
        .progress-header { display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 0.85rem; font-weight: 600; color: var(--text-main); }
        .progress { height: 10px; border-radius: 50px; background-color: var(--border); }
        
        /* Animasi Progress Bar saat diload */
        .progress-bar { 
            background: linear-gradient(90deg, var(--primary) 0%, #60a5fa 100%); 
            border-radius: 50px; 
            transition: width 1s cubic-bezier(0.4, 0, 0.2, 1); 
        }

        /* Filter & Action Bar */
        .action-bar { max-width: 1000px; margin: 0 auto 15px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; }
        .filter-group { display: flex; gap: 10px; }
        .form-select-custom { padding: 8px 30px 8px 15px; border-radius: 8px; border: 1px solid var(--border); font-size: 0.85rem; font-weight: 500; color: var(--text-main); background-color: white; cursor: pointer; box-shadow: 0 1px 2px rgba(0,0,0,0.02); transition: 0.2s; }
        .form-select-custom:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px var(--primary-light); }

        .btn-add-task { background: var(--primary); color: white; border: none; border-radius: 8px; padding: 8px 20px; font-weight: 600; font-size: 0.85rem; transition: 0.2s; text-decoration: none; display: flex; align-items: center; gap: 6px; box-shadow: 0 2px 4px rgba(37,99,235,0.2); }
        .btn-add-task:hover { background: #1d4ed8; color: white; transform: translateY(-1px); }

        /* Content Card & Tabel */
        .content-card { max-width: 1000px; margin: 0 auto; background: var(--surface); border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); border: 1px solid var(--border); overflow: hidden; }

        .table { margin-bottom: 0; }
        .table thead th { background: #f8fafc; color: var(--text-muted); font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; padding: 14px 20px; border-bottom: 2px solid var(--border); font-weight: 700; }
        .table tbody td { padding: 14px 20px; border-bottom: 1px solid var(--border); vertical-align: middle; font-size: 0.85rem; }
        
        /* Micro Interaction: Hover Effect Ringan */
        .table tbody tr { transition: background-color 0.15s ease-in-out; }
        .table tbody tr:hover { background-color: #f1f5f9; cursor: default; }
        .table tbody tr:last-child td { border-bottom: none; }
        
        /* Checkbox Tone Down & Animasi */
        .task-done { text-decoration: line-through; color: #94a3b8 !important; }
        .btn-check-done { color: var(--success); font-size: 1.25rem; transition: transform 0.2s cubic-bezier(0.34, 1.56, 0.64, 1); text-decoration: none; display: inline-block; }
        .btn-check-pending { color: #cbd5e1; font-size: 1.25rem; transition: transform 0.2s cubic-bezier(0.34, 1.56, 0.64, 1); text-decoration: none; display: inline-block; }
        .btn-check-done:hover { color: #059669; transform: scale(1.15); }
        .btn-check-pending:hover { color: var(--primary); transform: scale(1.15); }
        
        /* Alignment Deadline (Gap 12px) */
        .deadline-text { font-weight: 600; display: flex; align-items: center; gap: 12px; justify-content: center; white-space: nowrap; }
        
        .badge-status { font-size: 0.65rem; padding: 4px 8px; border-radius: 6px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }
        .badge-green { background-color: var(--success-light); color: var(--success); border: 1px solid #a7f3d0; }
        .badge-yellow { background-color: var(--warning-light); color: #b45309; border: 1px solid #fde68a; }
        .badge-orange { background-color: var(--orange-light); color: var(--orange); border: 1px solid #fed7aa; }
        .badge-red { background-color: var(--danger-light); color: var(--danger); border: 1px solid #fecaca; }
        .badge-gray { background-color: var(--gray-light); color: var(--gray); border: 1px solid var(--border); }
        
        .action-btn { padding: 6px 12px; border-radius: 6px; font-size: 0.8rem; border: none; transition: 0.2s; display: inline-flex; }
        .action-edit { background: var(--warning-light); color: #d97706; }
        .action-edit:hover { background: #fde68a; }
        .action-delete { background: var(--danger-light); color: var(--danger); }
        .action-delete:hover { background: #fecaca; }

        .empty-state { padding: 60px 20px; text-align: center; }
        .empty-icon-bg { display: inline-flex; align-items: center; justify-content: center; width: 70px; height: 70px; background: var(--gray-light); color: var(--gray); border-radius: 50%; margin-bottom: 20px; }
        
        @media (max-width: 768px) {
            .summary-wrapper { grid-template-columns: 1fr; }
            .action-bar { flex-direction: column; align-items: stretch; }
            .filter-group { flex-direction: column; }
        }
    </style>
</head>
<body>

<div class="container px-4">
    <div class="dashboard-header">
        <h1 class="dashboard-title">🔥 StudySync 🔥</h1>
        <p class="dashboard-subtitle">Stay on track. Stay in sync.</p>
           </div>

   
    <div class="progress-wrapper">
        <div class="progress-header">
            <span>Progress Penyelesaian</span>
            <span class="text-primary"><?= $tugas_selesai; ?> dari <?= $total_tugas; ?> Selesai (<?= $progress_percent; ?>%)</span>
        </div>
        <div class="progress">
            <div class="progress-bar progress-bar-striped" id="progressBar" role="progressbar" style="width: 0%;" data-target="<?= $progress_percent; ?>%"></div>
        </div>
    </div>

    <form method="GET" action="index.php" id="filterForm">
        <div class="action-bar">
            <div class="filter-group">
                <select name="filter_status" class="form-select-custom" onchange="document.getElementById('filterForm').submit();">
                    <option value="semua" <?= $filter_status == 'semua' ? 'selected' : '' ?>>Semua Status</option>
                    <option value="berjalan" <?= $filter_status == 'berjalan' ? 'selected' : '' ?>>⏳ Sedang Berjalan</option>
                    <option value="selesai" <?= $filter_status == 'selesai' ? 'selected' : '' ?>>✅ Selesai</option>
                    <option value="terlambat" <?= $filter_status == 'terlambat' ? 'selected' : '' ?>>🚨 Terlambat</option>
                </select>

                <select name="filter_matkul" class="form-select-custom" onchange="document.getElementById('filterForm').submit();">
                    <option value="semua">Semua Mata Kuliah</option>
                    <?php foreach($list_matkul as $mk): ?>
                        <option value="<?= htmlspecialchars($mk); ?>" <?= $filter_matkul == $mk ? 'selected' : '' ?>>
                            <?= htmlspecialchars($mk); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="filter-group">
                <select name="sort" class="form-select-custom" onchange="document.getElementById('filterForm').submit();">
                    <option value="dekat" <?= $sort == 'dekat' ? 'selected' : '' ?>>Urutkan: Deadline Terdekat</option>
                    <option value="jauh" <?= $sort == 'jauh' ? 'selected' : '' ?>>Urutkan: Deadline Terjauh</option>
                    <option value="az" <?= $sort == 'az' ? 'selected' : '' ?>>Urutkan: Nama (A-Z)</option>
                    <option value="za" <?= $sort == 'za' ? 'selected' : '' ?>>Urutkan: Nama (Z-A)</option>
                </select>
                <a href="tambah.php" class="btn-add-task">
                    <i class="fas fa-plus"></i> Tugas Baru
                </a>
            </div>
        </div>
    </form>

    <div class="content-card">
        <div class="table-responsive">
            <table class="table text-center align-middle">
                <thead>
                    <tr>
                        <th width="8%">Cek</th>
                        <th width="24%">Mata Kuliah</th>
                        <th width="30%">Nama Tugas</th>
                        <th width="23%">Tenggat Waktu</th>
                        <th width="15%">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($tugas_kuliah as $row): 
                        $badge_class = '';
                        $badge_text = '';
                        
                        if ($row['status'] == 1) {
                            $badge_class = 'badge-green';
                            $badge_text = '<i class="fas fa-check"></i> Selesai';
                        } else {
                            $tenggat_date = date('Y-m-d', strtotime($row['tenggat_waktu']));
                            $today_date = date('Y-m-d');
                            $diff_days = (strtotime($tenggat_date) - strtotime($today_date)) / 86400;
                            $diff_seconds = strtotime($row['tenggat_waktu']) - time();

                            if ($diff_seconds < 0) {
                                $badge_class = 'badge-red';
                                $badge_text = 'Terlambat';
                            } elseif ($diff_days == 0) {
                                $badge_class = 'badge-orange';
                                $badge_text = 'Hari Ini';
                            } elseif ($diff_days > 0 && $diff_days <= 3) {
                                $badge_class = 'badge-yellow';
                                $badge_text = 'Segera';
                            } else {
                                $badge_class = 'badge-gray';
                                $badge_text = 'Normal';
                            }
                        }
                    ?>
                    <tr>
                        <td>
                            <a href="index.php?status_id=<?= $row['id']; ?>&current=<?= $row['status']; ?>&filter_status=<?= $filter_status ?>&filter_matkul=<?= urlencode($filter_matkul) ?>&sort=<?= $sort ?>" class="<?= $row['status'] ? 'btn-check-done' : 'btn-check-pending' ?>">
                                <i class="<?= $row['status'] ? 'fa-solid fa-square-check' : 'fa-regular fa-square' ?>"></i>
                            </a>
                        </td>
                        <td class="fw-bold <?= $row['status'] ? 'task-done' : 'text-slate-800' ?>">
                            <?= htmlspecialchars($row['mata_kuliah']); ?>
                        </td>
                        <td class="<?= $row['status'] ? 'task-done' : 'fw-medium' ?>">
                            <?= htmlspecialchars($row['nama_tugas']); ?>
                        </td>
                        <td>
                            <div class="deadline-text <?= $row['status'] ? 'task-done' : '' ?>">
                                <span><?= date('d M H:i', strtotime($row['tenggat_waktu'])); ?></span>
                                <span class="badge-status <?= $badge_class ?>"><?= $badge_text ?></span>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex justify-content-center gap-2">
                                <a href="edit.php?id=<?= $row['id']; ?>" class="action-btn action-edit" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit"><i class="fas fa-pen"></i></a>
                                <button onclick="konfirmasiHapus(<?= $row['id']; ?>)" class="action-btn action-delete" data-bs-toggle="tooltip" data-bs-placement="top" title="Hapus"><i class="fas fa-trash"></i></button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>

                    <?php if(empty($tugas_kuliah)): ?>
                    <tr>
                        <td colspan="5" class="py-5">
                            <div class="empty-icon-bg"><i class="fas fa-folder-open fa-2x"></i></div>
                            <h5 class="fw-bold text-dark mb-1">Tidak ada data ditemukan</h5>
                            <p class="text-muted">Coba ubah filter di atas atau tambahkan tugas baru.</p>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // 1. Inisialisasi Tooltip Bootstrap
    document.addEventListener("DOMContentLoaded", function(){
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // 2. Animasi Progress Bar saat diload
        setTimeout(function() {
            let pBar = document.getElementById('progressBar');
            if(pBar) {
                pBar.style.width = pBar.getAttribute('data-target');
            }
        }, 150); // Delay sedikit agar animasinya terlihat halus
    });

    // 3. Konfirmasi Hapus SweetAlert
    function konfirmasiHapus(id) {
        Swal.fire({
            title: 'Hapus Tugas?',
            text: "Data yang dihapus tidak dapat dipulihkan.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#94a3b8',
            confirmButtonText: 'Ya, Hapus!'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'hapus.php?id=' + id;
            }
        })
    }
</script>

</body>
</html>