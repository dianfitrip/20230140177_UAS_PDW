<?php
require_once 'config.php';

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama = trim($_POST['nama']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $role = trim($_POST['role']);

    // Validasi sederhana
    if (empty($nama) || empty($email) || empty($password) || empty($role)) {
        $message = "Semua field harus diisi!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Format email tidak valid!";
    } elseif (!in_array($role, ['mahasiswa', 'asisten'])) {
        $message = "Peran tidak valid!";
    } else {
        // Cek apakah email sudah terdaftar
        $sql = "SELECT id FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $message = "Email sudah terdaftar. Silakan gunakan email lain.";
        } else {
            // Hash password untuk keamanan
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);

            // Simpan ke database
            $sql_insert = "INSERT INTO users (nama, email, password, role) VALUES (?, ?, ?, ?)";
            $stmt_insert = $conn->prepare($sql_insert);
            $stmt_insert->bind_param("ssss", $nama, $email, $hashed_password, $role);

            if ($stmt_insert->execute()) {
                header("Location: login.php?status=registered");
                exit();
            } else {
                $message = "Terjadi kesalahan. Silakan coba lagi.";
            }
            $stmt_insert->close();
        }
        $stmt->close();
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi Pengguna</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f0ff 0%, #e9e4f0 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            background-color: #fff;
            padding: 30px 40px;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(106, 74, 168, 0.15);
            width: 350px;
            border-top: 5px solid #6a4aa8;
        }
        h2 {
            text-align: center;
            color: #4a2d7a;
            margin-bottom: 25px;
            font-weight: 600;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #5e5e5e;
            font-weight: 500;
        }
        .form-group input, 
        .form-group select {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0d6f5;
            border-radius: 6px;
            box-sizing: border-box;
            font-size: 15px;
            transition: all 0.3s ease;
        }
        .form-group input:focus,
        .form-group select:focus {
            border-color: #8a6dcc;
            outline: none;
            box-shadow: 0 0 0 3px rgba(138, 109, 204, 0.2);
        }
        .btn {
            background-color: #6a4aa8;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s ease;
            margin-top: 10px;
        }
        .btn:hover {
            background-color: #5a3a98;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(106, 74, 168, 0.3);
        }
        .message {
            color: #d32f2f;
            text-align: center;
            margin-bottom: 20px;
            padding: 10px;
            border-radius: 4px;
            background-color: #ffebee;
            font-size: 14px;
        }
        .login-link {
            text-align: center;
            margin-top: 20px;
            color: #666;
            font-size: 14px;
        }
        .login-link a {
            color: #8a6dcc;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.2s ease;
        }
        .login-link a:hover {
            color: #6a4aa8;
            text-decoration: underline;
        }
        .logo {
            text-align: center;
            margin-bottom: 20px;
        }
        .logo img {
            height: 50px;
        }
        .role-select {
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%236a4aa8'%3e%3cpath d='M7 10l5 5 5-5z'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 10px center;
            background-size: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            <!-- Anda bisa menambahkan logo di sini -->
            <!-- <img src="path/to/logo.png" alt="Logo"> -->
        </div>
        <h2>Buat Akun Baru</h2>
        <?php if (!empty($message)): ?>
            <p class="message"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>
        <form action="register.php" method="post">
            <div class="form-group">
                <label for="nama">Nama Lengkap</label>
                <input type="text" id="nama" name="nama" required placeholder="Masukkan nama lengkap">
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required placeholder="Masukkan alamat email">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required placeholder="Buat password">
            </div>
            <div class="form-group">
                <label for="role">Daftar Sebagai</label>
                <select id="role" name="role" required class="role-select">
                    <option value="" disabled selected>Pilih peran</option>
                    <option value="mahasiswa">Mahasiswa</option>
                    <option value="asisten">Asisten</option>
                </select>
            </div>
            <button type="submit" class="btn">Daftar Sekarang</button>
        </form>
        <div class="login-link">
            <p>Sudah punya akun? <a href="login.php">Masuk di sini</a></p>
        </div>
    </div>
</body>
</html>