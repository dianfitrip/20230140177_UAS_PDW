<?php
// Pastikan koneksi database ditutup jika menggunakan koneksi persisten
// atau jika koneksi dibuka secara global dan perlu ditutup di akhir setiap halaman
if (isset($conn)) {
    $conn->close();
}
?>

        </main> </div> <footer class="bg-purple-800 text-white py-4 shadow-lg mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row justify-between items-center text-sm">
            <div class="text-center md:text-left mb-2 md:mb-0 text-purple-100">
                &copy; <?php echo date('Y'); ?> KUTU "Kumpul Tugas". Hak Cipta Dilindungi.
            </div>
            <div class="text-center md:text-right text-purple-100">
                Dikembangkan dengan ❤️ oleh DIANNNN
            </div>
        </div>
    </footer>

</body>
</html>