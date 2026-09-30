<?php
require 'koneksi.php';

// Ringkasan untuk kartu statistik
$ringkas = $conn->query("SELECT COUNT(*) AS jml_kec, SUM(jumlah_penduduk) AS total,
                                MAX(jumlah_penduduk) AS maks, MIN(jumlah_penduduk) AS min
                         FROM kecamatan")->fetch_assoc();
$terbanyak = $conn->query("SELECT nama FROM kecamatan ORDER BY jumlah_penduduk DESC LIMIT 1")->fetch_assoc()['nama'];
$tercepat  = $conn->query("SELECT nama, laju_pertumbuhan FROM kecamatan ORDER BY laju_pertumbuhan DESC LIMIT 1")->fetch_assoc();
$turun     = $conn->query("SELECT COUNT(*) AS n FROM kecamatan WHERE laju_pertumbuhan < 0")->fetch_assoc()['n'];

// Data tabel
$tabel = $conn->query("SELECT * FROM kecamatan ORDER BY nama");
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Peta Penduduk Kabupaten Jember 2024</title>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<style>
    * { box-sizing: border-box; }
    body { margin: 0; font-family: 'Segoe UI', Arial, sans-serif; background: #f4f6f9; color: #333; }
    header { background: #1e3a5f; color: #fff; padding: 18px 24px; }
    header h1 { margin: 0; font-size: 22px; }
    header p { margin: 4px 0 0; font-size: 13px; opacity: .8; }
    .container { max-width: 1200px; margin: auto; padding: 20px; }
    .cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 14px; margin-bottom: 20px; }
    .card { background: #fff; border-radius: 10px; padding: 16px; box-shadow: 0 2px 6px rgba(0,0,0,.08); }
    .card .label { font-size: 12px; color: #777; text-transform: uppercase; }
    .card .value { font-size: 22px; font-weight: bold; color: #1e3a5f; margin-top: 4px; }
    .card .sub { font-size: 12px; color: #999; }
    .panel { background: #fff; border-radius: 10px; padding: 16px; box-shadow: 0 2px 6px rgba(0,0,0,.08); margin-bottom: 20px; }
    .panel h2 { margin: 0 0 12px; font-size: 17px; color: #1e3a5f; }
    #map { height: 560px; border-radius: 8px; }
    .toolbar { display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 10px; justify-content: space-between; }
    .toolbar .btn-group { display: flex; gap: 6px; }
    .toolbar .search-group { position: relative; display: flex; gap: 6px; }
    .toolbar input { padding: 6px 12px; border: 1px solid #ccc; border-radius: 6px; width: 220px; font-family: inherit; }
    .toolbar input:focus { outline: none; border-color: #1e3a5f; }
    .toolbar button { border: 1px solid #1e3a5f; background: #fff; color: #1e3a5f; padding: 6px 12px;
                      border-radius: 6px; cursor: pointer; }
    .toolbar button:hover { background: #f0f6ff; }
    .toolbar button.active { background: #1e3a5f; color: #fff; }
    .toolbar button.btn-cari { background: #1e3a5f; color: #fff; font-weight: 600; }
    .toolbar button.btn-cari:hover { background: #132742; }
    .hasil-pencarian { position: absolute; top: 100%; left: 0; width: 220px; background: #fff; border: 1px solid #ccc; 
                       border-radius: 6px; box-shadow: 0 4px 6px rgba(0,0,0,.1); z-index: 9999; display: none; 
                       max-height: 200px; overflow-y: auto; margin-top: 4px; }
    .hasil-item { padding: 8px 12px; cursor: pointer; font-size: 13px; border-bottom: 1px solid #eee; }
    .hasil-item:hover { background: #f0f6ff; }
    .hasil-item:last-child { border-bottom: none; }
    .legend { background: #fff; padding: 10px 12px; border-radius: 6px; line-height: 20px; font-size: 12px;
              box-shadow: 0 1px 4px rgba(0,0,0,.3); }
    .legend i { width: 14px; height: 14px; float: left; margin-right: 6px; margin-top: 3px; border-radius: 3px; opacity: .85; }
    .info { background: rgba(255,255,255,.95); padding: 8px 12px; border-radius: 6px; font-size: 13px;
            box-shadow: 0 1px 4px rgba(0,0,0,.3); min-width: 190px; }
    .info h4 { margin: 0 0 4px; font-size: 14px; color: #1e3a5f; }
    .label-kec { background: transparent; border: none; box-shadow: none; font-size: 10px; font-weight: 600;
                 color: #222; text-shadow: 0 0 3px #fff, 0 0 3px #fff, 0 0 3px #fff; }
    .label-kec::before { display: none; }
    .leaflet-control-layers-expanded { font-size: 13px; }
    .grid2 { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
    @media (max-width: 800px) { .grid2 { grid-template-columns: 1fr; } }
    table { width: 100%; border-collapse: collapse; font-size: 14px; }
    th, td { padding: 8px 10px; border-bottom: 1px solid #eee; text-align: left; }
    th { background: #f0c419; color: #333; }
    td.num { text-align: right; }
    .neg { color: #c0392b; font-weight: bold; }
    .pos { color: #27ae60; }
    footer { text-align: center; font-size: 12px; color: #888; padding: 16px; }
    .leaflet-control-kompas { 
        background: white; 
        border-radius: 50%; 
        box-shadow: 0 1px 5px rgba(0,0,0,0.4); 
        line-height: 0;
        pointer-events: none;
    }
</style>
</head>
<body>

<header>
    <h1>Visualisasi Penduduk Kabupaten Jember 2024</h1>
    <p>Jumlah penduduk dan laju pertumbuhan per kecamatan (2020–2024) — Sumber: BPS Kabupaten Jember</p>
</header>

<div class="container">

    <!-- Kartu ringkasan -->
    <div class="cards">
        <div class="card">
            <div class="label">Total Penduduk</div>
            <div class="value"><?= number_format($ringkas['total'], 0, ',', '.') ?></div>
            <div class="sub"><?= $ringkas['jml_kec'] ?> kecamatan</div>
        </div>
        <div class="card">
            <div class="label">Kecamatan Terpadat</div>
            <div class="value"><?= htmlspecialchars($terbanyak) ?></div>
            <div class="sub"><?= number_format($ringkas['maks'], 0, ',', '.') ?> jiwa</div>
        </div>
        <div class="card">
            <div class="label">Pertumbuhan Tercepat</div>
            <div class="value"><?= htmlspecialchars($tercepat['nama']) ?></div>
            <div class="sub"><?= number_format($tercepat['laju_pertumbuhan'], 2, ',', '.') ?>% per tahun</div>
        </div>
        <div class="card">
            <div class="label">Kecamatan Penduduk Menurun</div>
            <div class="value"><?= $turun ?></div>
            <div class="sub">laju pertumbuhan negatif</div>
        </div>
    </div>

    <!-- Peta -->
    <div class="panel">
        <h2>Peta Sebaran Penduduk per Kecamatan</h2>
        <div class="toolbar">
            <div class="btn-group">
                <button id="btnJumlah" class="active" onclick="gantiMode('jumlah')">Jumlah Penduduk</button>
                <button id="btnLaju" onclick="gantiMode('laju')">Laju Pertumbuhan</button>
            </div>
            <div class="search-group">
                <input type="text" id="inputCari" placeholder="Cari kecamatan..." onkeypress="handleEnter(event)">
                <button class="btn-cari" onclick="cariKecamatan()">Cari</button>
                <div id="hasilPencarian" class="hasil-pencarian"></div>
            </div>
        </div>
        <div id="map"></div>
    </div>

    <!-- Grafik -->
    <div class="grid2">
        <div class="panel">
            <h2>Jumlah Penduduk per Kecamatan</h2>
            <canvas id="chartJumlah" height="420"></canvas>
        </div>
        <div class="panel">
            <h2>Laju Pertumbuhan per Tahun (%)</h2>
            <canvas id="chartLaju" height="420"></canvas>
        </div>
    </div>

    <!-- Tabel -->
    <div class="panel">
        <h2>Tabel Data</h2>
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th style="cursor:pointer" onclick="sortTable(1, false)">Kecamatan ↕</th>
                    <th style="cursor:pointer; text-align:right" onclick="sortTable(2, true)">Jumlah Penduduk (jiwa) ↕</th>
                    <th style="cursor:pointer; text-align:right" onclick="sortTable(3, true)">Laju Pertumbuhan (%) ↕</th>
                </tr>
            </thead>
            <tbody>
            <?php $no = 1; while ($r = $tabel->fetch_assoc()): ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><?= htmlspecialchars($r['nama']) ?></td>
                    <td class="num"><?= number_format($r['jumlah_penduduk'], 0, ',', '.') ?></td>
                    <td class="num <?= $r['laju_pertumbuhan'] < 0 ? 'neg' : 'pos' ?>">
                        <?= number_format($r['laju_pertumbuhan'], 2, ',', '.') ?>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<footer>
    Data penduduk: BPS Kabupaten Jember, Tabel 3.1.1. Batas kecamatan: batas-administrasi-indonesia (Alf-Anas, update Juni 2023), disederhanakan untuk web.
    &nbsp;|&nbsp; <a href="admin/login.php" style="color:#aaa; text-decoration:none;">Admin Panel</a>
</footer>

<script>
const fmt = n => n.toLocaleString('id-ID');
let dataKec = [], dataByNama = {}, mode = 'jumlah', legend;

// ---------- Peta dasar (base layer, pilih salah satu) ----------
const map = L.map('map');
const osm = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 18, attribution: '&copy; OpenStreetMap contributors'
});
const satelit = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
    maxZoom: 18, attribution: 'Tiles &copy; Esri'
});
osm.addTo(map);

// ---------- Layer tematik (overlay, bisa di-enable/disable) ----------
let layerBatas;                          // poligon choropleth
const layerTitik = L.layerGroup();       // lingkaran proporsional
const layerLabel = L.layerGroup();       // label nama kecamatan

// Warna berdasarkan jumlah penduduk
function warnaJumlah(n) {
    return n > 120000 ? '#800026' :
           n > 100000 ? '#BD0026' :
           n >  80000 ? '#E31A1C' :
           n >  60000 ? '#FC4E2A' :
           n >  40000 ? '#FD8D3C' : '#FEB24C';
}
// Warna berdasarkan laju pertumbuhan
function warnaLaju(p) {
    return p <  0    ? '#d73027' :
           p <  0.3  ? '#fee08b' :
           p <  0.6  ? '#91cf60' : '#1a9850';
}
function warna(d) {
    if (!d) return '#cccccc';
    return mode === 'jumlah' ? warnaJumlah(d.jumlah) : warnaLaju(d.laju);
}

function isiPopup(d) {
    return `<b>Kec. ${d.nama}</b><br>
            Jumlah penduduk: <b>${fmt(d.jumlah)}</b> jiwa<br>
            Laju pertumbuhan: <b>${d.laju.toLocaleString('id-ID')}%</b> / tahun`;
}

// Gaya poligon
function gayaBatas(feature) {
    return {
        fillColor: warna(dataByNama[feature.properties.nama]),
        weight: 1.2, color: '#ffffff', opacity: 1, fillOpacity: 0.75
    };
}

// ---------- Panel info (muncul saat kursor di atas wilayah) ----------
const info = L.control({ position: 'topleft' });
info.onAdd = function () { this._div = L.DomUtil.create('div', 'info'); this.update(); return this._div; };
info.update = function (d) {
    this._div.innerHTML = d
        ? `<h4>Kec. ${d.nama}</h4>
           Penduduk: <b>${fmt(d.jumlah)}</b> jiwa<br>
           Laju: <b>${d.laju.toLocaleString('id-ID')}%</b> / tahun`
        : '<h4>Kabupaten Jember</h4>Arahkan kursor ke sebuah kecamatan';
};

function sorot(e) {
    const l = e.target;
    l.setStyle({ weight: 3, color: '#1e3a5f', fillOpacity: 0.9 });
    l.bringToFront();
    info.update(dataByNama[l.feature.properties.nama]);
}
function lepas(e) {
    layerBatas.resetStyle(e.target);
    info.update();
}

function buatLayerBatas(geojson) {
    layerBatas = L.geoJSON(geojson, {
        style: gayaBatas,
        onEachFeature: (feature, layer) => {
            const d = dataByNama[feature.properties.nama];
            if (d) {
                layer.bindPopup(isiPopup(d));
                d.layer = layer; // Simpan referensi layer untuk fitur pencarian
            }
            layer.on({ mouseover: sorot, mouseout: lepas });

            // Label nama di titik yang dijamin berada di dalam poligon
            L.tooltip({ permanent: true, direction: 'center', className: 'label-kec' })
                .setLatLng(feature.properties.label)
                .setContent(feature.properties.nama)
                .addTo(layerLabel);
        }
    });
}

function buatLayerTitik() {
    layerTitik.clearLayers();
    dataKec.forEach(d => {
        L.circleMarker([d.lat, d.lng], {
            radius: Math.sqrt(d.jumlah) / 18,   // luas lingkaran sebanding jumlah penduduk
            fillColor: warna(d), color: '#333', weight: 1, fillOpacity: 0.85
        }).bindPopup(isiPopup(d)).addTo(layerTitik);
    });
}

// ---------- Legenda ----------
function buatLegenda() {
    if (legend) map.removeControl(legend);
    legend = L.control({ position: 'bottomright' });
    legend.onAdd = function () {
        const div = L.DomUtil.create('div', 'legend');
        if (mode === 'jumlah') {
            div.innerHTML = '<b>Jumlah Penduduk (jiwa)</b><br>';
            const batas = [0, 40000, 60000, 80000, 100000, 120000];
            batas.forEach((b, i) => {
                div.innerHTML += `<i style="background:${warnaJumlah(b + 1)}"></i>` +
                    (batas[i + 1] ? `${fmt(b)} – ${fmt(batas[i + 1])}` : `> ${fmt(b)}`) + '<br>';
            });
        } else {
            div.innerHTML = '<b>Laju Pertumbuhan</b><br>' +
                `<i style="background:${warnaLaju(-1)}"></i> Negatif (&lt; 0%)<br>` +
                `<i style="background:${warnaLaju(0.1)}"></i> 0 – 0,3%<br>` +
                `<i style="background:${warnaLaju(0.4)}"></i> 0,3 – 0,6%<br>` +
                `<i style="background:${warnaLaju(0.8)}"></i> ≥ 0,6%`;
        }
        return div;
    };
    legend.addTo(map);
}

function gantiMode(m) {
    mode = m;
    document.getElementById('btnJumlah').classList.toggle('active', m === 'jumlah');
    document.getElementById('btnLaju').classList.toggle('active', m === 'laju');
    if (layerBatas) layerBatas.setStyle(gayaBatas);
    buatLayerTitik();
    buatLegenda();
}

// ---------- Fitur Pencarian ----------
function handleEnter(e) {
    if (e.key === 'Enter') cariKecamatan();
}

function cariKecamatan() {
    const input = document.getElementById('inputCari').value.trim().toLowerCase();
    const wadahHasil = document.getElementById('hasilPencarian');
    
    if (input === '') {
        alert('Masukkan nama kecamatan yang ingin dicari.');
        return;
    }
    
    const hasil = dataKec.filter(d => d.nama.toLowerCase().includes(input));
    
    if (hasil.length === 0) {
        alert('Kecamatan tidak ditemukan.');
        wadahHasil.style.display = 'none';
        return;
    }
    
    if (hasil.length === 1) {
        wadahHasil.style.display = 'none';
        fokusKecamatan(hasil[0].nama);
    } else {
        wadahHasil.innerHTML = '';
        hasil.forEach(d => {
            const div = document.createElement('div');
            div.className = 'hasil-item';
            div.textContent = d.nama;
            div.onclick = () => {
                wadahHasil.style.display = 'none';
                document.getElementById('inputCari').value = d.nama;
                fokusKecamatan(d.nama);
            };
            wadahHasil.appendChild(div);
        });
        wadahHasil.style.display = 'block';
    }
}

function fokusKecamatan(nama) {
    const d = dataByNama[nama];
    if (d && d.layer) {
        const layer = d.layer;
        
        // Pastikan layerBatas tertampil
        if (!map.hasLayer(layerBatas)) {
            map.addLayer(layerBatas);
        }
        
        // Pindah fokus
        map.fitBounds(layer.getBounds(), { maxZoom: 13, padding: [20, 20] });
        
        // Buka popup
        layer.openPopup();
        
        // Sorot gaya poligon
        sorot({ target: layer });
        
        // Sembunyikan hasil dropdown pencarian bila ada
        document.getElementById('hasilPencarian').style.display = 'none';
    }
}

// Menyembunyikan dropdown hasil pencarian jika mengklik tempat lain
document.addEventListener('click', function(e) {
    if (!e.target.closest('.search-group')) {
        const wadahHasil = document.getElementById('hasilPencarian');
        if (wadahHasil) wadahHasil.style.display = 'none';
    }
});

// ---------- Grafik ----------
function buatGrafik() {
    new Chart(document.getElementById('chartJumlah'), {
        type: 'bar',
        data: {
            labels: dataKec.map(d => d.nama),
            datasets: [{ label: 'Jiwa', data: dataKec.map(d => d.jumlah),
                         backgroundColor: dataKec.map(d => warnaJumlah(d.jumlah)) }]
        },
        options: { indexAxis: 'y', plugins: { legend: { display: false } },
                   scales: { y: { ticks: { autoSkip: false, font: { size: 10 } } } } }
    });

    const urutLaju = [...dataKec].sort((a, b) => b.laju - a.laju);
    new Chart(document.getElementById('chartLaju'), {
        type: 'bar',
        data: {
            labels: urutLaju.map(d => d.nama),
            datasets: [{ label: '% per tahun', data: urutLaju.map(d => d.laju),
                         backgroundColor: urutLaju.map(d => warnaLaju(d.laju)) }]
        },
        options: { indexAxis: 'y', plugins: { legend: { display: false } },
                   scales: { y: { ticks: { autoSkip: false, font: { size: 10 } } } } }
    });
}

// ---------- Sprint 2: Fitur Sort Tabel ----------
let sortDirection = 1;
function sortTable(colIndex, isNumber) {
    const tbody = document.querySelector('table tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));
    
    rows.sort((a, b) => {
        let valA = a.children[colIndex].innerText.replace(/\./g, '').replace(/,/g, '.');
        let valB = b.children[colIndex].innerText.replace(/\./g, '').replace(/,/g, '.');
        
        if (isNumber) {
            return (parseFloat(valA) - parseFloat(valB)) * sortDirection;
        }
        return valA.localeCompare(valB) * sortDirection;
    });
    
    sortDirection *= -1;
    
    // Update nomor urut
    rows.forEach((row, index) => {
        row.children[0].innerText = index + 1;
    });
    
    tbody.append(...rows);
}

// ---------- Ambil data MySQL (api.php) + batas wilayah (GeoJSON) ----------
Promise.all([
    fetch('api.php').then(r => r.json()),
    fetch('jember_kecamatan.geojson').then(r => r.json())
]).then(([data, geojson]) => {
    dataKec = data;
    dataKec.forEach(d => dataByNama[d.nama] = d);

    buatLayerBatas(geojson);
    buatLayerTitik();

    // Layer yang aktif saat halaman dibuka
    layerBatas.addTo(map);
    layerLabel.addTo(map);
    map.fitBounds(layerBatas.getBounds(), { padding: [10, 10] });

    // Kontrol layer: base map (radio) + overlay (checkbox enable/disable)
    L.control.layers(
        { 'OpenStreetMap': osm, 'Citra Satelit': satelit },
        {
            'Batas Kecamatan (choropleth)': layerBatas,
            'Label Nama Kecamatan': layerLabel,
            'Lingkaran Jumlah Penduduk': layerTitik
        },
        { collapsed: false, position: 'topright' }
    ).addTo(map);

    info.addTo(map);
    L.control.scale({ imperial: false }).addTo(map);
    
    // ---------- Sprint 3: Kompas Arah Mata Angin ----------
    const kompas = L.control({ position: 'bottomleft' });
    kompas.onAdd = function () {
        const div = L.DomUtil.create('div', 'leaflet-control-kompas');
        div.title = 'Arah Utara';
        div.innerHTML = `
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 80 80" width="72" height="72">
            <circle cx="40" cy="40" r="38" fill="white" stroke="#ccc" stroke-width="1.5"/>
            <text x="40" y="11"  text-anchor="middle" font-size="10" font-weight="bold" fill="#c0392b">U</text>
            <text x="40" y="74"  text-anchor="middle" font-size="10" font-weight="bold" fill="#555">S</text>
            <text x="72" y="44"  text-anchor="middle" font-size="10" font-weight="bold" fill="#555">T</text>
            <text x="8"  y="44"  text-anchor="middle" font-size="10" font-weight="bold" fill="#555">B</text>
            <line x1="40" y1="16" x2="40" y2="64" stroke="#ddd" stroke-width="1"/>
            <line x1="16" y1="40" x2="64" y2="40" stroke="#ddd" stroke-width="1"/>
            <polygon points="40,15 35.5,40 44.5,40" fill="#c0392b"/>
            <polygon points="40,65 35.5,40 44.5,40" fill="#333"/>
            <circle cx="40" cy="40" r="3.5" fill="white" stroke="#888" stroke-width="1.2"/>
        </svg>`;
        return div;
    };
    kompas.addTo(map);

    buatLegenda();
    buatGrafik();
}).catch(err => alert('Gagal memuat data: ' + err));
</script>
</body>
</html>
<?php $conn->close(); ?>
