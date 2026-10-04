<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}
require '../koneksi.php';

$id   = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$mode = $id > 0 ? 'Edit' : 'Tambah';
$data = ['nama' => '', 'jumlah_penduduk' => '', 'laju_pertumbuhan' => '', 'latitude' => '', 'longitude' => ''];
$errors = [];

// ── Read: ambil data jika mode Edit ─────────────────────────
if ($id > 0) {
    $stmt = $conn->prepare("SELECT * FROM kecamatan WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows > 0) {
        $data = $res->fetch_assoc();
    } else {
        header("Location: index.php");
        exit;
    }
}

// ── Create / Update: proses form POST ───────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama   = trim($_POST['nama']);
    $jumlah = (int)$_POST['jumlah'];
    $laju   = (float)$_POST['laju'];
    $lat    = (float)$_POST['lat'];
    $lng    = (float)$_POST['lng'];

    // Validasi server-side
    if ($nama === '')          $errors[] = "Nama kecamatan tidak boleh kosong.";
    if ($jumlah <= 0)          $errors[] = "Jumlah penduduk harus lebih dari 0.";
    if ($lat < -90 || $lat > 90)   $errors[] = "Latitude tidak valid (harus antara -90 dan 90).";
    if ($lng < -180 || $lng > 180) $errors[] = "Longitude tidak valid (harus antara -180 dan 180).";

    if (empty($errors)) {
        if ($id > 0) {
            // UPDATE
            $stmt = $conn->prepare("UPDATE kecamatan SET nama=?, jumlah_penduduk=?, laju_pertumbuhan=?, latitude=?, longitude=? WHERE id=?");
            $stmt->bind_param("sidddi", $nama, $jumlah, $laju, $lat, $lng, $id);
        } else {
            // INSERT
            $stmt = $conn->prepare("INSERT INTO kecamatan (nama, jumlah_penduduk, laju_pertumbuhan, latitude, longitude) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("siddd", $nama, $jumlah, $laju, $lat, $lng);
        }

        if ($stmt->execute()) {
            header("Location: index.php?msg=saved");
            exit;
        } else {
            $errors[] = "Gagal menyimpan data ke database: " . $conn->error;
        }
    }

    // Jika ada error, isi ulang $data dari POST agar form tidak kosong
    $data['nama']             = $nama;
    $data['jumlah_penduduk']  = $jumlah;
    $data['laju_pertumbuhan'] = $laju;
    $data['latitude']         = $lat;
    $data['longitude']        = $lng;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $mode ?> Kecamatan – Admin</title>
<style>
    * { box-sizing: border-box; }
    body { font-family: 'Segoe UI', Arial, sans-serif; background: #f4f6f9; margin: 0; color: #333; }
    header { background: #1e3a5f; color: #fff; padding: 15px 24px; display: flex; justify-content: space-between; align-items: center; }
    header h1 { margin: 0; font-size: 18px; }
    header a { color: #fff; background: #3498db; padding: 6px 12px; border-radius: 4px; text-decoration: none; font-size: 13px; }
    header a:hover { background: #2980b9; }
    .container { max-width: 560px; margin: 30px auto; padding: 0 16px; }
    .card { background: #fff; border-radius: 8px; box-shadow: 0 2px 6px rgba(0,0,0,.1); padding: 28px; }
    .card h2 { margin: 0 0 20px; color: #1e3a5f; font-size: 18px; }
    .form-group { margin-bottom: 16px; }
    .form-group label { display: block; font-weight: 600; margin-bottom: 5px; color: #444; font-size: 14px; }
    .form-group input { width: 100%; padding: 9px 12px; border: 1px solid #ccc; border-radius: 4px; font-size: 14px; }
    .form-group input:focus { outline: none; border-color: #1e3a5f; }
    .form-group small { display: block; margin-top: 4px; color: #888; font-size: 12px; }
    .btn-save { background: #27ae60; color: #fff; padding: 11px; border: none; border-radius: 4px; cursor: pointer; font-size: 15px; font-weight: bold; width: 100%; margin-top: 4px; }
    .btn-save:hover { background: #219653; }
    .btn-back { display: block; text-align: center; margin-top: 12px; color: #1e3a5f; text-decoration: none; font-size: 14px; }
    .btn-back:hover { text-decoration: underline; }
    .alert-danger { background: #fdeaea; color: #721c24; border: 1px solid #f5c6cb; padding: 10px 14px; border-radius: 4px; margin-bottom: 16px; font-size: 14px; }
    .alert-danger ul { margin: 6px 0 0 18px; padding: 0; }
    .divider { border: none; border-top: 1px solid #eee; margin: 20px 0; }
    .coord-hint { background: #f0f6ff; border: 1px solid #c8dff5; border-radius: 4px; padding: 10px 12px; margin-bottom: 16px; font-size: 12px; color: #1e3a5f; }
</style>
</head>
<body>

<header>
    <h1><?= $mode === 'Edit' ? '✏️ Edit' : '➕ Tambah' ?> Data Kecamatan</h1>
    <a href="index.php">← Kembali ke Dashboard</a>
</header>

<div class="container">
    <div class="card">
        <h2><?= $mode ?> Kecamatan <?= $id > 0 ? '— ' . htmlspecialchars($data['nama']) : '' ?></h2>

        <?php if (!empty($errors)): ?>
            <div class="alert-danger">
                <strong>⚠️ Perbaiki kesalahan berikut:</strong>
                <ul>
                    <?php foreach ($errors as $e): ?>
                        <li><?= htmlspecialchars($e) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label>Nama Kecamatan</label>
                <input type="text" name="nama" required maxlength="50"
                       value="<?= htmlspecialchars($data['nama']) ?>"
                       placeholder="contoh: Ambulu">
            </div>

            <div class="form-group">
                <label>Jumlah Penduduk (jiwa)</label>
                <input type="number" name="jumlah" required min="1"
                       value="<?= $data['jumlah_penduduk'] ?>"
                       placeholder="contoh: 121482">
            </div>

            <div class="form-group">
                <label>Laju Pertumbuhan (% per tahun)</label>
                <input type="number" step="0.01" name="laju" required
                       value="<?= $data['laju_pertumbuhan'] ?>"
                       placeholder="contoh: 0.64  (negatif jika menurun)">
                <small>Nilai negatif berarti penduduk menurun. Contoh: -0.42</small>
            </div>

            <hr class="divider">

            <div class="coord-hint">
                📍 <strong>Koordinat</strong> digunakan untuk menentukan posisi titik/lingkaran kecamatan di peta.
                Isi dengan titik di dalam wilayah kecamatan (bukan tepi batas).
            </div>

            <div class="form-group">
                <label>Latitude</label>
                <input type="number" step="0.000001" name="lat" required
                       value="<?= $data['latitude'] ?>"
                       placeholder="contoh: -8.379440">
                <small>Untuk Jember: sekitar -8.0 hingga -8.5</small>
            </div>

            <div class="form-group">
                <label>Longitude</label>
                <input type="number" step="0.000001" name="lng" required
                       value="<?= $data['longitude'] ?>"
                       placeholder="contoh: 113.612737">
                <small>Untuk Jember: sekitar 113.3 hingga 114.0</small>
            </div>

            <button type="submit" class="btn-save">
                <?= $id > 0 ? '💾 Simpan Perubahan' : '➕ Tambah Kecamatan' ?>
            </button>
            <a href="index.php" class="btn-back">Batal & kembali ke dashboard</a>
        </form>
    </div>
</div>

</body>
</html>
