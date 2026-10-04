<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}
require '../koneksi.php';

// Hapus data penduduk
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];

    if ($id > 0) {
        $stmt = $conn->prepare("DELETE FROM kecamatan WHERE id = ?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            header("Location: index.php?msg=deleted");
            exit;
        } else {
            $errHapus = "Gagal menghapus data penduduk.";
        }
    }
}

// Read / Search data
$search = trim(isset($_GET['search']) ? $_GET['search'] : '');

if ($search !== '') {
    $stmt = $conn->prepare(
        "SELECT id, nama, jumlah_penduduk, laju_pertumbuhan
         FROM kecamatan
         WHERE nama LIKE ?
         ORDER BY nama"
    );
    $keyword = '%' . $search . '%';
    $stmt->bind_param("s", $keyword);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query(
        "SELECT id, nama, jumlah_penduduk, laju_pertumbuhan
         FROM kecamatan
         ORDER BY nama"
    );
}

$rows = [];
while ($row = $result->fetch_assoc()) {
    $rows[] = $row;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard – Data Penduduk Jember</title>
<style>
* { box-sizing: border-box; }
body { font-family:'Segoe UI',Arial,sans-serif; background:#f4f6f9; margin:0; color:#333; }
header { background:#1e3a5f; color:#fff; padding:15px 24px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px; }
header h1 { margin:0; font-size:18px; }
.header-links { display:flex; gap:8px; }
.header-links a { color:#fff; text-decoration:none; padding:6px 12px; border-radius:4px; font-size:13px; }
.btn-blue { background:#3498db; }
.btn-red { background:#e74c3c; }
.btn-blue:hover { background:#2980b9; }
.btn-red:hover { background:#c0392b; }
.container { max-width:1200px; margin:20px auto; padding:0 16px; }
.card { background:#fff; border-radius:8px; box-shadow:0 2px 6px rgba(0,0,0,.1); padding:20px; margin-bottom:20px; }
.toolbar { display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px; margin-bottom:16px; }
.btn-add { background:#27ae60; color:#fff; padding:8px 16px; text-decoration:none; border-radius:4px; font-weight:bold; font-size:14px; }
.btn-add:hover { background:#219653; }
.search-form { display:flex; gap:6px; }
.search-form input { padding:7px 10px; border:1px solid #ccc; border-radius:4px; width:220px; font-size:14px; }
.search-form button { padding:7px 14px; background:#1e3a5f; color:#fff; border:none; border-radius:4px; cursor:pointer; font-size:14px; }
.search-form a { padding:7px 10px; color:#666; text-decoration:none; font-size:13px; }
table { width:100%; border-collapse:collapse; font-size:14px; }
th,td { padding:10px 12px; border-bottom:1px solid #eee; text-align:left; }
th { background:#f0c419; color:#333; white-space:nowrap; }
td.num { text-align:right; font-variant-numeric:tabular-nums; }
.laju-neg { color:#c0392b; font-weight:bold; }
.laju-pos { color:#27ae60; }
.aksi { display:flex; gap:6px; flex-wrap:wrap; }
.btn-edit { background:#f39c12; color:#fff; padding:4px 10px; text-decoration:none; border-radius:4px; font-size:12px; }
.btn-hapus { background:#e74c3c; color:#fff; padding:4px 10px; text-decoration:none; border-radius:4px; font-size:12px; }
.btn-edit:hover { background:#d68910; }
.btn-hapus:hover { background:#c0392b; }
.alert { padding:10px 14px; border-radius:4px; margin-bottom:16px; font-size:14px; }
.alert-success { background:#d4edda; color:#155724; border:1px solid #c3e6cb; }
.alert-danger { background:#fdeaea; color:#721c24; border:1px solid #f5c6cb; }
.empty { text-align:center; padding:30px; color:#999; }
.info-row { font-size:13px; color:#666; }
.note { margin-top:14px; padding:10px 12px; background:#f8f9fa; border-left:4px solid #3498db; color:#555; font-size:13px; }
@media (max-width:700px) {
    .toolbar { align-items:stretch; }
    .btn-add { text-align:center; }
    .search-form { width:100%; }
    .search-form input { width:100%; }
    table { display:block; overflow-x:auto; white-space:nowrap; }
}
</style>
</head>
<body>

<header>
    <h1>🗂️ Admin Dashboard – Data Penduduk Kecamatan</h1>
    <div class="header-links">
        <a href="../" target="_blank" class="btn-blue">🗺️ Lihat Peta</a>
        <a href="logout.php" class="btn-red">🚪 Logout (<?= htmlspecialchars($_SESSION['username']) ?>)</a>
    </div>
</header>

<div class="container">
    <?php if (isset($_GET['msg']) && $_GET['msg'] === 'deleted'): ?>
        <div class="alert alert-success">✅ Data penduduk berhasil dihapus.</div>
    <?php endif; ?>

    <?php if (isset($_GET['msg']) && $_GET['msg'] === 'saved'): ?>
        <div class="alert alert-success">✅ Data penduduk berhasil disimpan.</div>
    <?php endif; ?>

    <?php if (isset($errHapus)): ?>
        <div class="alert alert-danger">❌ <?= htmlspecialchars($errHapus) ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="toolbar">
            <a href="form.php" class="btn-add">+ Tambah Data Penduduk</a>

            <form class="search-form" method="GET" action="">
                <input type="text" name="search" placeholder="Cari nama kecamatan..." value="<?= htmlspecialchars($search) ?>">
                <button type="submit">Cari</button>

                <?php if ($search !== ''): ?>
                    <a href="index.php">✕ Reset</a>
                <?php endif; ?>
            </form>
        </div>

        <p class="info-row">
            Menampilkan <strong><?= count($rows) ?></strong> data penduduk kecamatan
            <?= $search !== '' ? ' dengan kata kunci "<em>' . htmlspecialchars($search) . '</em>"' : '' ?>
        </p>

        <div class="note">
            ℹ️ Data kecamatan, koordinat, dan wilayah peta bersifat tetap.
            Admin hanya mengelola jumlah penduduk dan laju pertumbuhan.
        </div>

        <br>

        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Kecamatan</th>
                    <th style="text-align:right">Jumlah Penduduk (jiwa)</th>
                    <th style="text-align:right">Laju Pertumbuhan (%)</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
                <?php if (count($rows) === 0): ?>
                    <tr>
                        <td colspan="5" class="empty">Tidak ada data ditemukan.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($rows as $i => $row): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td><?= htmlspecialchars($row['nama']) ?></td>
                            <td class="num"><?= number_format($row['jumlah_penduduk'], 0, ',', '.') ?></td>
                            <td class="num <?= $row['laju_pertumbuhan'] < 0 ? 'laju-neg' : 'laju-pos' ?>">
                                <?= number_format($row['laju_pertumbuhan'], 2, ',', '.') ?>
                            </td>
                            <td>
                                <div class="aksi">
                                    <a href="form.php?id=<?= $row['id'] ?>" class="btn-edit">✏️ Edit</a>

                                    <a href="?hapus=<?= $row['id'] ?><?= $search ? '&search=' . urlencode($search) : '' ?>"
                                       class="btn-hapus"
                                       onclick="return confirm('Yakin ingin menghapus DATA PENDUDUK Kecamatan <?= htmlspecialchars(addslashes($row['nama'])) ?>?\n\nWilayah dan polygon kecamatan pada peta tidak ikut dihapus.')">
                                        🗑️ Hapus
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
