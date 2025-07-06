<?php
require_once '../config.php'; // Sesuaikan path jika diperlukan

// Memulai sesi jika belum ada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Pastikan hanya mahasiswa yang bisa mengakses halaman ini
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'mahasiswa') {
    header("Location: ../login.php");
    exit();
}

$mahasiswa_id = $_SESSION['user_id']; // Mengambil ID mahasiswa dari sesi

// --- LOGIKA UNTUK STATISTIK DASHBOARD ---

// 1. Menghitung jumlah praktikum yang diikuti oleh mahasiswa
$stmt_prak = $conn->prepare("SELECT COUNT(id) AS total FROM pendaftaran_praktikum WHERE mahasiswa_id = ?");
$stmt_prak->bind_param("i", $mahasiswa_id);
$stmt_prak->execute();
$prak_diikuti = $stmt_prak->get_result()->fetch_assoc()['total'];
$stmt_prak->close();

// 2. Menghitung jumlah tugas (laporan) yang sudah dinilai (status 'Dinilai')
$stmt_selesai = $conn->prepare("SELECT COUNT(id) AS total FROM laporan WHERE mahasiswa_id = ? AND status = 'Dinilai'");
$stmt_selesai->bind_param("i", $mahasiswa_id);
$stmt_selesai->execute();
$tugas_selesai = $stmt_selesai->get_result()->fetch_assoc()['total'];
$stmt_selesai->close();

// 3. Menghitung jumlah tugas yang masih menunggu penilaian atau belum dikumpulkan
//    a. Menghitung total modul yang terdaftar untuk praktikum yang diikuti mahasiswa
$stmt_total_modul = $conn->prepare("SELECT COUNT(m.id) AS total FROM modul m JOIN pendaftaran_praktikum pp ON m.praktikum_id = pp.praktikum_id WHERE pp.mahasiswa_id = ?");
$stmt_total_modul->bind_param("i", $mahasiswa_id);
$stmt_total_modul->execute();
$total_modul = $stmt_total_modul->get_result()->fetch_assoc()['total'];
$stmt_total_modul->close();

//    b. Menghitung total laporan yang sudah terkumpul (apapun statusnya)
$stmt_terkumpul = $conn->prepare("SELECT COUNT(id) AS total FROM laporan WHERE mahasiswa_id = ?");
$stmt_terkumpul->bind_param("i", $mahasiswa_id);
$stmt_terkumpul->execute();
$laporan_terkumpul = $stmt_terkumpul->get_result()->fetch_assoc()['total'];
$stmt_terkumpul->close();

//    c. Tugas menunggu = Total modul - Laporan yang sudah terkumpul
$tugas_menunggu = $total_modul - $laporan_terkumpul;


// --- LOGIKA UNTUK NOTIFIKASI TERBARU ---
$notifikasi = [];

// Notifikasi 1: Mendapatkan informasi nilai terakhir yang diberikan
$sql_nilai = "SELECT m.nama_modul, mp.nama_praktikum, l.tanggal_kumpul 
              FROM laporan l
              JOIN modul m ON l.modul_id = m.id
              JOIN mata_praktikum mp ON m.praktikum_id = mp.id
              WHERE l.mahasiswa_id = ? AND l.status = 'Dinilai'
              ORDER BY l.tanggal_kumpul DESC LIMIT 1";
$stmt_nilai = $conn->prepare($sql_nilai);
$stmt_nilai->bind_param("i", $mahasiswa_id);
$stmt_nilai->execute();
$res_nilai = $stmt_nilai->get_result();
if ($res_nilai->num_rows > 0) {
    $notifikasi[] = $res_nilai->fetch_assoc();
}
$stmt_nilai->close();

// Notifikasi 2: Mendapatkan informasi pendaftaran praktikum terakhir
$sql_daftar = "SELECT mp.nama_praktikum, pp.tanggal_daftar
               FROM pendaftaran_praktikum pp
               JOIN mata_praktikum mp ON pp.praktikum_id = mp.id
               WHERE pp.mahasiswa_id = ?
               ORDER BY pp.tanggal_daftar DESC LIMIT 1";
$stmt_daftar = $conn->prepare($sql_daftar);
$stmt_daftar->bind_param("i", $mahasiswa_id);
$stmt_daftar->execute();
$res_daftar = $stmt_daftar->get_result();
if ($res_daftar->num_rows > 0) {
    $notifikasi[] = $res_daftar->fetch_assoc();
}
$stmt_daftar->close();

// Notifikasi 3: Mendapatkan informasi laporan terakhir yang dikumpulkan (status 'Terkumpul')
$sql_kumpul = "SELECT m.nama_modul, mp.nama_praktikum, l.tanggal_kumpul
               FROM laporan l
               JOIN modul m ON l.modul_id = m.id
               JOIN mata_praktikum mp ON m.praktikum_id = mp.id
               WHERE l.mahasiswa_id = ? AND l.status = 'Terkumpul'
               ORDER BY l.tanggal_kumpul DESC LIMIT 1";
$stmt_kumpul = $conn->prepare($sql_kumpul);
$stmt_kumpul->bind_param("i", $mahasiswa_id);
$stmt_kumpul->execute();
$res_kumpul = $stmt_kumpul->get_result();
if ($res_kumpul->num_rows > 0) {
    $notifikasi[] = $res_kumpul->fetch_assoc();
}
$stmt_kumpul->close();


// --- PENGATURAN Tampilan ---
$pageTitle = 'Dashboard'; // Judul halaman
$activePage = 'dashboard'; // Untuk penanda menu aktif di sidebar (jika ada)

// Memuat bagian header halaman (biasanya berisi HTML <head>, navbar, atau sidebar)
require_once 'templates/header_mahasiswa.php'; 
?>

<div class="bg-gradient-to-r from-purple-700 to-indigo-500 text-white p-8 rounded-xl shadow-lg mb-8">
    <h1 class="text-3xl font-bold">Selamat Datang Kembali, <?php echo htmlspecialchars($_SESSION['nama']); ?>!</h1>
    <p class="mt-2 opacity-90">Jangan Lupa Kerjain Tugas!!.</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
    
    <div class="bg-white p-6 rounded-xl shadow-md flex flex-col items-center justify-center">
        <div class="text-5xl font-extrabold text-purple-700"><?php echo $prak_diikuti; ?></div>
        <div class="mt-2 text-lg text-gray-600">Praktikum Diikuti</div>
    </div>
    
    <div class="bg-white p-6 rounded-xl shadow-md flex flex-col items-center justify-center">
        <div class="text-5xl font-extrabold text-indigo-600"><?php echo $tugas_selesai; ?></div>
        <div class="mt-2 text-lg text-gray-600">Tugas Selesai</div>
    </div>
    
    <div class="bg-white p-6 rounded-xl shadow-md flex flex-col items-center justify-center">
        <div class="text-5xl font-extrabold text-purple-500"><?php echo $tugas_menunggu; ?></div>
        <div class="mt-2 text-lg text-gray-600">Tugas Menunggu</div>
    </div>
    
</div>

<div class="bg-white p-6 rounded-xl shadow-md">
    <h3 class="text-2xl font-bold text-gray-800 mb-4">Notifikasi Terbaru</h3>
    <ul class="space-y-4">
        <?php if (empty($notifikasi)): // Jika tidak ada notifikasi ?>
            <li class="text-gray-500">Belum ada aktivitas terbaru.</li>
        <?php else: // Menampilkan notifikasi jika ada ?>
            <?php foreach ($notifikasi as $notif): ?>
                <?php if (isset($notif['tanggal_daftar'])): // Notifikasi Pendaftaran Praktikum ?>
                    <li class="flex items-start p-3 border-b border-gray-100 last:border-b-0">
                        <span class="text-xl mr-4">✅</span>
                        <div>
                            Anda berhasil mendaftar pada mata praktikum <strong class="text-purple-600"><?php echo htmlspecialchars($notif['nama_praktikum']); ?></strong>.
                            <span class="block text-xs text-gray-500 mt-1"><?php echo date('d M Y, H:i', strtotime($notif['tanggal_daftar'])); ?></span>
                        </div>
                    </li>
                <?php elseif (isset($notif['nama_modul']) && isset($notif['tanggal_kumpul']) && !isset($notif['tanggal_daftar'])): // Notifikasi Nilai Diberikan atau Laporan Dikumpulkan
                     // Logika ini sedikit diperbaiki untuk membedakan notifikasi nilai dan kumpul jika diperlukan data 'status'
                     // Untuk kode ini, notifikasi nilai datang dari query nilai, dan kumpul dari query kumpul
                ?>
                    <?php 
                        // Jika notifikasi berasal dari query nilai (ada 'status' dinilai di query aslinya)
                        // Perlu diingat, query nilai hanya mengambil yang 'Dinilai'. Jadi jika ada nama_modul, itu pasti nilai.
                        // Jika Anda ingin lebih spesifik, bisa tambahkan penanda di array $notifikasi saat di-fetch
                    ?>
                    <?php if (strpos($sql_nilai, $notif['nama_modul']) !== false && strpos($sql_nilai, $notif['nama_praktikum']) !== false): ?>
                        <li class="flex items-start p-3 border-b border-gray-100 last:border-b-0">
                            <span class="text-xl mr-4">🔔</span>
                            <div>
                                Nilai untuk <strong class="text-purple-600"><?php echo htmlspecialchars($notif['nama_modul']); ?> (<?php echo htmlspecialchars($notif['nama_praktikum']); ?>)</strong> telah diberikan.
                                <span class="block text-xs text-gray-500 mt-1"><?php echo date('d M Y, H:i', strtotime($notif['tanggal_kumpul'])); ?></span>
                            </div>
                        </li>
                    <?php else: // Ini berarti notifikasi laporan dikumpulkan ?>
                        <li class="flex items-start p-3 border-b border-gray-100 last:border-b-0">
                            <span class="text-xl mr-4">📤</span>
                            <div>
                                Anda telah mengumpulkan laporan untuk <strong class="text-purple-600"><?php echo htmlspecialchars($notif['nama_modul']); ?> (<?php echo htmlspecialchars($notif['nama_praktikum']); ?>)</strong>.
                                <span class="block text-xs text-gray-500 mt-1"><?php echo date('d M Y, H:i', strtotime($notif['tanggal_kumpul'])); ?></span>
                            </div>
                        </li>
                    <?php endif; ?>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php endif; ?>
    </ul>
</div>

<?php
// Memuat bagian footer halaman
require_once 'templates/footer_mahasiswa.php';
?>