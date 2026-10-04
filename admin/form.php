<?php
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

require '../koneksi.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$mode = $id > 0 ? 'Edit' : 'Tambah';

$data = [
    'id' => '',
    'nama' => '',
    'jumlah_penduduk' => '',
    'laju_pertumbuhan' => '',
    'latitude' => '',
    'longitude' => ''
];

$errors = [];


// ============================================================
// DAFTAR KECAMATAN TETAP
// Digunakan sebagai master pilihan saat menambah data.
// Tidak mengubah database.
// ============================================================

$kecamatanTetap = [

    'Kencong' => [
        'latitude' => -8.285510,
        'longitude' => 113.358112
    ],

    'Gumuk Mas' => [
        'latitude' => -8.333965,
        'longitude' => 113.413900
    ],

    'Puger' => [
        'latitude' => -8.328785,
        'longitude' => 113.477199
    ],

    'Wuluhan' => [
        'latitude' => -8.351245,
        'longitude' => 113.542359
    ],

    'Ambulu' => [
        'latitude' => -8.379440,
        'longitude' => 113.612737
    ],

    'Tempurejo' => [
        'latitude' => -8.419075,
        'longitude' => 113.752272
    ],

    'Silo' => [
        'latitude' => -8.258970,
        'longitude' => 113.867990
    ],

    'Mayang' => [
        'latitude' => -8.206520,
        'longitude' => 113.811729
    ],

    'Mumbulsari' => [
        'latitude' => -8.255755,
        'longitude' => 113.742095
    ],

    'Jenggawah' => [
        'latitude' => -8.288960,
        'longitude' => 113.631917
    ],

    'Ajung' => [
        'latitude' => -8.239380,
        'longitude' => 113.660642
    ],

    'Rambipuji' => [
        'latitude' => -8.230265,
        'longitude' => 113.598181
    ],

    'Balung' => [
        'latitude' => -8.273950,
        'longitude' => 113.519597
    ],

    'Umbulsari' => [
        'latitude' => -8.243335,
        'longitude' => 113.416520
    ],

    'Semboro' => [
        'latitude' => -8.179055,
        'longitude' => 113.432464
    ],

    'Jombang' => [
        'latitude' => -8.224315,
        'longitude' => 113.355439
    ],

    'Sumberbaru' => [
        'latitude' => -8.091140,
        'longitude' => 113.410436
    ],

    'Tanggul' => [
        'latitude' => -8.100540,
        'longitude' => 113.501462
    ],

    'Bangsalsari' => [
        'latitude' => -8.117990,
        'longitude' => 113.565351
    ],

    'Panti' => [
        'latitude' => -8.076495,
        'longitude' => 113.618488
    ],

    'Sukorambi' => [
        'latitude' => -8.129035,
        'longitude' => 113.662086
    ],

    'Arjasa' => [
        'latitude' => -8.101945,
        'longitude' => 113.734209
    ],

    'Pakusari' => [
        'latitude' => -8.154100,
        'longitude' => 113.774940
    ],

    'Kalisat' => [
        'latitude' => -8.124300,
        'longitude' => 113.807580
    ],

    'Ledokombo' => [
        'latitude' => -8.137980,
        'longitude' => 113.941442
    ],

    'Sumberjambe' => [
        'latitude' => -8.069785,
        'longitude' => 113.932088
    ],

    'Sukowono' => [
        'latitude' => -8.058165,
        'longitude' => 113.823701
    ],

    'Jelbuk' => [
        'latitude' => -8.046210,
        'longitude' => 113.707806
    ],

    'Kaliwates' => [
        'latitude' => -8.173680,
        'longitude' => 113.688720
    ],

    'Sumbersari' => [
        'latitude' => -8.173635,
        'longitude' => 113.728461
    ],

    'Patrang' => [
        'latitude' => -8.126835,
        'longitude' => 113.700827
    ]

];


// ============================================================
// EDIT
// ============================================================

if ($id > 0) {

    $stmt = $conn->prepare(
        "SELECT *
         FROM kecamatan
         WHERE id = ?"
    );

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


// ============================================================
// DAFTAR KECAMATAN YANG SUDAH PUNYA DATA
// ============================================================

$existingNames = [];

$resultExisting = $conn->query(
    "SELECT nama FROM kecamatan"
);

while ($rowExisting = $resultExisting->fetch_assoc()) {

    $existingNames[] = $rowExisting['nama'];

}


// ============================================================
// CREATE / UPDATE
// ============================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $jumlah = isset($_POST['jumlah'])
        ? (int)$_POST['jumlah']
        : 0;

    $laju = isset($_POST['laju'])
        ? (float)$_POST['laju']
        : 0;


    // ========================================================
    // MODE TAMBAH
    // ========================================================

    if ($id === 0) {

        $nama = isset($_POST['nama'])
            ? trim($_POST['nama'])
            : '';


        // Validasi kecamatan

        if ($nama === '') {

            $errors[] = "Kecamatan harus dipilih.";

        } elseif (!array_key_exists($nama, $kecamatanTetap)) {

            $errors[] = "Kecamatan tidak valid.";

        }


        // Cek duplikasi

        if ($nama !== '') {

            $stmtCheck = $conn->prepare(
                "SELECT id
                 FROM kecamatan
                 WHERE nama = ?
                 LIMIT 1"
            );

            $stmtCheck->bind_param("s", $nama);
            $stmtCheck->execute();

            $resCheck = $stmtCheck->get_result();

            if ($resCheck->num_rows > 0) {

                $errors[] =
                    "Data penduduk untuk Kecamatan " .
                    htmlspecialchars($nama) .
                    " sudah tersedia. Silakan gunakan menu Edit.";

            }

        }


        // Validasi jumlah

        if ($jumlah <= 0) {

            $errors[] =
                "Jumlah penduduk harus lebih dari 0.";

        }


        // Validasi laju

        if ($laju < -100 || $laju > 100) {

            $errors[] =
                "Laju pertumbuhan tidak valid.";

        }


        // Simpan

        if (empty($errors)) {

            $lat = $kecamatanTetap[$nama]['latitude'];
            $lng = $kecamatanTetap[$nama]['longitude'];


            $stmt = $conn->prepare(
                "INSERT INTO kecamatan
                (nama, jumlah_penduduk, laju_pertumbuhan, latitude, longitude)
                VALUES (?, ?, ?, ?, ?)"
            );

            $stmt->bind_param(
                "siddd",
                $nama,
                $jumlah,
                $laju,
                $lat,
                $lng
            );


            if ($stmt->execute()) {

                header("Location: index.php?msg=saved");
                exit;

            } else {

                $errors[] =
                    "Gagal menyimpan data penduduk: " .
                    $conn->error;

            }

        }


        // Isi kembali jika error

        $data['nama'] = $nama;
        $data['jumlah_penduduk'] = $jumlah;
        $data['laju_pertumbuhan'] = $laju;

    }


    // ========================================================
    // MODE EDIT
    // ========================================================

    else {

        // Nama tidak diambil dari POST.
        // Nama kecamatan tetap mengikuti record database.

        $nama = $data['nama'];


        if ($jumlah <= 0) {

            $errors[] =
                "Jumlah penduduk harus lebih dari 0.";

        }


        if ($laju < -100 || $laju > 100) {

            $errors[] =
                "Laju pertumbuhan tidak valid.";

        }


        if (empty($errors)) {

            $stmt = $conn->prepare(
                "UPDATE kecamatan
                 SET jumlah_penduduk = ?,
                     laju_pertumbuhan = ?
                 WHERE id = ?"
            );

            $stmt->bind_param(
                "idi",
                $jumlah,
                $laju,
                $id
            );


            if ($stmt->execute()) {

                header("Location: index.php?msg=saved");
                exit;

            } else {

                $errors[] =
                    "Gagal memperbarui data penduduk: " .
                    $conn->error;

            }

        }


        $data['jumlah_penduduk'] = $jumlah;
        $data['laju_pertumbuhan'] = $laju;

    }

}

?>

<!DOCTYPE html>
<html lang="id">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>
    <?= $mode ?> Data Penduduk – Admin
</title>


<style>

    * {
        box-sizing: border-box;
    }

    body {
        font-family: 'Segoe UI', Arial, sans-serif;
        background: #f4f6f9;
        margin: 0;
        color: #333;
    }

    header {
        background: #1e3a5f;
        color: #fff;
        padding: 15px 24px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    header h1 {
        margin: 0;
        font-size: 18px;
    }

    header a {
        color: #fff;
        background: #3498db;
        padding: 6px 12px;
        border-radius: 4px;
        text-decoration: none;
        font-size: 13px;
    }

    header a:hover {
        background: #2980b9;
    }

    .container {
        max-width: 560px;
        margin: 30px auto;
        padding: 0 16px;
    }

    .card {
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 6px rgba(0,0,0,.1);
        padding: 28px;
    }

    .card h2 {
        margin: 0 0 20px;
        color: #1e3a5f;
        font-size: 18px;
    }

    .form-group {
        margin-bottom: 16px;
    }

    .form-group label {
        display: block;
        font-weight: 600;
        margin-bottom: 5px;
        color: #444;
        font-size: 14px;
    }

    .form-group input,
    .form-group select {
        width: 100%;
        padding: 9px 12px;
        border: 1px solid #ccc;
        border-radius: 4px;
        font-size: 14px;
        background: #fff;
    }

    .form-group input:focus,
    .form-group select:focus {
        outline: none;
        border-color: #1e3a5f;
    }

    .form-group select:disabled {
        background: #f1f3f5;
        color: #555;
        cursor: not-allowed;
    }

    .form-group small {
        display: block;
        margin-top: 4px;
        color: #888;
        font-size: 12px;
    }

    .btn-save {
        background: #27ae60;
        color: #fff;
        padding: 11px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 15px;
        font-weight: bold;
        width: 100%;
        margin-top: 4px;
    }

    .btn-save:hover {
        background: #219653;
    }

    .btn-save:disabled {
        background: #95a5a6;
        cursor: not-allowed;
    }

    .btn-back {
        display: block;
        text-align: center;
        margin-top: 12px;
        color: #1e3a5f;
        text-decoration: none;
        font-size: 14px;
    }

    .btn-back:hover {
        text-decoration: underline;
    }

    .alert-danger {
        background: #fdeaea;
        color: #721c24;
        border: 1px solid #f5c6cb;
        padding: 10px 14px;
        border-radius: 4px;
        margin-bottom: 16px;
        font-size: 14px;
    }

    .alert-danger ul {
        margin: 6px 0 0 18px;
        padding: 0;
    }

    .info-box {
        background: #e8f4fd;
        border: 1px solid #c8dff5;
        border-radius: 4px;
        padding: 10px 12px;
        margin-bottom: 18px;
        font-size: 12px;
        color: #1e3a5f;
        line-height: 1.5;
    }

    .empty-box {
        background: #fff3cd;
        border: 1px solid #ffeeba;
        color: #856404;
        padding: 12px;
        border-radius: 4px;
        font-size: 13px;
        line-height: 1.5;
        margin-bottom: 16px;
    }

    @media (max-width: 600px) {
        header {
            gap: 10px;
            flex-wrap: wrap;
        }
        .container {
            margin-top: 20px;
        }
    }
</style
</head>
<body>
<header>
    <h1>
        <?= $mode === 'Edit'
            ? '✏️ Edit Data Penduduk'
            : '➕ Tambah Data Penduduk'
        ?>
    </h1>
    <a href="index.php">
        ← Kembali ke Dashboard
    </a>
</header>
<div class="container">
    <div class="card">
        <h2>
            <?= $mode === 'Edit'
                ? 'Edit Data Penduduk Kecamatan'
                : 'Tambah Data Penduduk Kecamatan'
            ?>
        </h2>
        <?php if (!empty($errors)): ?>
            <div class="alert-danger">
                <strong>
                    ⚠️ Perbaiki kesalahan berikut:
                </strong>
                <ul>
                    <?php foreach ($errors as $e): ?>
                        <li>
                            <?= htmlspecialchars(strip_tags($e)) ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        <?php if ($mode === 'Edit'): ?>
            <div class="info-box">
                🔒 Kecamatan, latitude, dan longitude
                merupakan data wilayah tetap.
                Pada mode edit, yang dapat diubah hanya
                jumlah penduduk dan laju pertumbuhan.
            </div>
        <?php else: ?>
            <?php
            $jumlahTersedia = 0;
            foreach ($kecamatanTetap as $namaKec => $koordinat) {
                if (!in_array($namaKec, $existingNames)) {
                    $jumlahTersedia++;
                }
            }
            ?>
            <?php if ($jumlahTersedia === 0): ?>
                <div class="empty-box">
                    ℹ️ Saat ini seluruh kecamatan sudah memiliki
                    data penduduk.
                    <br><br>
                    Gunakan menu <strong>Edit</strong> untuk
                    memperbarui jumlah penduduk atau laju
                    pertumbuhan.
                </div>
            <?php else: ?>
                <div class="info-box">
                    Pilih kecamatan yang sudah tersedia pada
                    wilayah peta. Nama kecamatan tidak dapat
                    dibuat secara bebas.
                </div>
            <?php endif; ?>
        <?php endif; ?>
        <form method="POST" action="">
            <?php if ($mode === 'Tambah'): ?>
                <div class="form-group">
                    <label>
                        Kecamatan
                    </label>
                    <select
                        name="nama"
                        required
                        <?= $jumlahTersedia === 0 ? 'disabled' : '' ?>
                    >
                        <option value="">
                            -- Pilih Kecamatan --
                        </option>
                        <?php foreach ($kecamatanTetap as $namaKec => $koordinat): ?>
                            <?php if (!in_array($namaKec, $existingNames) || $namaKec === $data['nama']): ?>
                                <option
                                    value="<?= htmlspecialchars($namaKec) ?>"
                                    <?= $data['nama'] === $namaKec ? 'selected' : '' ?>
                                >
                                    <?= htmlspecialchars($namaKec) ?>
                                </option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                    <small>
                        Hanya kecamatan yang belum memiliki
                        data yang dapat dipilih.
                    </small>
                </div>
            <?php else: ?>
                <div class="form-group">
                    <label>
                        Kecamatan
                    </label>
                    <input
                        type="text"
                        value="<?= htmlspecialchars($data['nama']) ?>"
                        disabled
                    >
                    <small>
                        Nama kecamatan tidak dapat diubah.
                    </small>
                </div>
            <?php endif; ?>
            <div class="form-group">
                <label>
                    Jumlah Penduduk (jiwa)
                </label>
                <input
                    type="number"
                    name="jumlah"
                    required
                    min="1"
                    value="<?= htmlspecialchars($data['jumlah_penduduk']) ?>"
                    placeholder="contoh: 121482"
                >
                <small>
                    Masukkan jumlah penduduk dalam satuan jiwa.
                </small>
            </div>
            <div class="form-group">
                <label>
                    Laju Pertumbuhan (% per tahun)
                </label>
                <input
                    type="number"
                    step="0.01"
                    name="laju"
                    required
                    value="<?= htmlspecialchars($data['laju_pertumbuhan']) ?>"
                    placeholder="contoh: 0.64"
                >
                <small>
                    Nilai negatif berarti pertumbuhan penduduk menurun.
                    Contoh: -0.42
                </small>
            </div>
            <button
                type="submit"
                class="btn-save"
                <?= ($mode === 'Tambah' && $jumlahTersedia === 0) ? 'disabled' : '' ?>
            >
                <?= $mode === 'Edit'
                    ? '💾 Simpan Perubahan'
                    : '➕ Tambah Data Penduduk'
                ?>
            </button>
            <a
                href="index.php"
                class="btn-back"
            >
                Batal & kembali ke dashboard
            </a>
        </form>
    </div>
</div>
</body>
</html>
