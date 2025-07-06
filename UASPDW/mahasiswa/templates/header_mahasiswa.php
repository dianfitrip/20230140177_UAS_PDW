<?php
// Pastikan session sudah dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek jika pengguna belum login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Panel Mahasiswa - <?php echo $pageTitle ?? 'Dashboard'; ?></title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Opsional: Jika ada masalah dengan ketinggian konten, bisa tambahkan ini */
        html, body {
            height: 100%;
        }
    </style>
</head>
<body class="bg-gray-100 font-sans min-h-screen flex flex-col">

    <nav class="bg-purple-800 shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <span class="text-white text-2xl font-bold">KUTU(Kumpul Tugas)</span>
                    </div>
                    
                    <div class="hidden md:block">
                        <div class="ml-10 flex items-baseline space-x-4">
                            <?php 
                                // Kelas CSS untuk link navigasi aktif dan non-aktif
                                $activeClass = 'bg-purple-700 text-white';
                                $inactiveClass = 'text-gray-200 hover:bg-purple-700 hover:text-white';
                            ?>
                            <a href="dashboard.php" class="<?php echo ($activePage == 'dashboard') ? $activeClass : $inactiveClass; ?> px-3 py-2 rounded-md text-sm font-medium">Dashboard</a>
                            <a href="my_courses.php" class="<?php echo ($activePage == 'my_courses') ? $activeClass : $inactiveClass; ?> px-3 py-2 rounded-md text-sm font-medium">Praktikum Saya</a>
                            <a href="courses.php" class="<?php echo ($activePage == 'courses') ? $activeClass : $inactiveClass; ?> px-3 py-2 rounded-md text-sm font-medium">Cari Praktikum</a>
                        </div>
                    </div>
                </div>

                <div class="hidden md:block">
                    <div class="ml-4 flex items-center md:ml-6">
                        <a href="../logout.php" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-md transition-colors duration-300">
                            Logout
                        </a>
                    </div>
                </div>

                </div>
        </div>
    </nav>

    <div class="container mx-auto p-6 lg:p-8 flex-grow">