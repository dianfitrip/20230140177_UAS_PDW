<?php
session_start();
require_once 'config.php';

// Jika sudah login, redirect ke halaman yang sesuai
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] == 'asisten') {
        header("Location: asisten/dashboard.php");
    } elseif ($_SESSION['role'] == 'mahasiswa') {
        header("Location: mahasiswa/dashboard.php");
    }
    exit();
}

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Verifikasi CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $message = "Invalid CSRF token";
    } else {
        $email = trim($_POST['email']);
        $password = trim($_POST['password']);

        if (empty($email) || empty($password)) {
            $message = "Email dan password harus diisi!";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = "Format email tidak valid";
        } else {
            $sql = "SELECT id, nama, email, password, role FROM users WHERE email = ?";
            $stmt = $conn->prepare($sql);
            
            if ($stmt === false) {
                $message = "Terjadi kesalahan sistem";
            } else {
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows === 1) {
                    $user = $result->fetch_assoc();

                    if (password_verify($password, $user['password'])) {
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['nama'] = $user['nama'];
                        $_SESSION['role'] = $user['role'];
                        $_SESSION['logged_in'] = true;

                        // Regenerate session ID untuk mencegah session fixation
                        session_regenerate_id(true);

                        if ($user['role'] == 'asisten') {
                            header("Location: asisten/dashboard.php");
                        } elseif ($user['role'] == 'mahasiswa') {
                            header("Location: mahasiswa/dashboard.php");
                        }
                        exit();
                    } else {
                        $message = "Email atau password salah";
                    }
                } else {
                    $message = "Email atau password salah";
                }
                $stmt->close();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
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
        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0d6f5;
            border-radius: 6px;
            box-sizing: border-box;
            font-size: 15px;
            transition: all 0.3s ease;
        }
        .form-group input:focus {
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
        .message.success {
            color: #388e3c;
            background-color: #e8f5e9;
        }
        .register-link {
            text-align: center;
            margin-top: 20px;
            color: #666;
            font-size: 14px;
        }
        .register-link a {
            color: #8a6dcc;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.2s ease;
        }
        .register-link a:hover {
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
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            <!-- Anda bisa menambahkan logo di sini -->
            <!-- <img src="path/to/logo.png" alt="Logo"> -->
        </div>
        <h2>Login ke Akun Anda</h2>
        <?php 
            if (isset($_GET['status']) && $_GET['status'] == 'registered') {
                echo '<p class="message success">Registrasi berhasil! Silakan login.</p>';
            }
            if (!empty($message)) {
                echo '<p class="message">' . htmlspecialchars($message) . '</p>';
            }
        ?>
        <form action="login.php" method="post">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required placeholder="Masukkan email Anda">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required placeholder="Masukkan password Anda">
            </div>
            <button type="submit" class="btn">Masuk</button>
        </form>
        <div class="register-link">
            <p>Belum punya akun? <a href="register.php">Daftar sekarang</a></p>
        </div>
    </div>
</body>
</html>