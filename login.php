<?php
session_start();
require '../koneksi.php';

// Setup database admin if not exists
$conn->query("CREATE TABLE IF NOT EXISTS admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
)");

// Insert default admin: admin / admin123
$result = $conn->query("SELECT * FROM admin WHERE username='admin'");
if ($result->num_rows == 0) {
    $pass = password_hash('admin123', PASSWORD_DEFAULT);
    $conn->query("INSERT INTO admin (username, password) VALUES ('admin', '$pass')");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user = $_POST['username'];
    $pass = $_POST['password'];
    
    $stmt = $conn->prepare("SELECT * FROM admin WHERE username = ?");
    $stmt->bind_param("s", $user);
    $stmt->execute();
    $res = $stmt->get_result();
    
    if ($res->num_rows > 0) {
        $row = $res->fetch_assoc();
        if (password_verify($pass, $row['password'])) {
            $_SESSION['admin'] = true;
            $_SESSION['username'] = $user;
            header("Location: index.php");
            exit;
        } else {
            $error = "Password salah!";
        }
    } else {
        $error = "Username tidak ditemukan!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login Admin - Peta Jember</title>
<style>
    body { font-family: 'Segoe UI', Arial, sans-serif; background: #f4f6f9; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
    .login-box { background: #fff; padding: 30px; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); width: 100%; max-width: 400px; text-align: center; }
    .login-box h2 { color: #1e3a5f; margin-bottom: 20px; }
    .input-group { margin-bottom: 15px; text-align: left; }
    .input-group label { display: block; margin-bottom: 5px; color: #555; }
    .input-group input { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 6px; box-sizing: border-box; }
    button { background: #1e3a5f; color: #fff; padding: 10px 15px; border: none; border-radius: 6px; width: 100%; cursor: pointer; font-size: 16px; font-weight: bold; }
    button:hover { background: #132742; }
    .error { color: #c0392b; background: #fdeaea; padding: 10px; border-radius: 6px; margin-bottom: 15px; border: 1px solid #f5c6cb; }
</style>
</head>
<body>
    <div class="login-box">
        <h2>Login Admin</h2>
        <?php if(isset($error)): ?>
            <div class="error"><?= $error ?></div>
        <?php endif; ?>
        <form method="POST" action="">
            <div class="input-group">
                <label>Username</label>
                <input type="text" name="username" required autofocus>
            </div>
            <div class="input-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit">Login</button>
            <p style="margin-top: 15px; font-size: 12px; color: #888;">Default: admin / admin123</p>
            <p style="margin-top: 5px; font-size: 12px;"><a href="../" style="color: #1e3a5f;">Kembali ke Peta</a></p>
        </form>
    </div>
</body>
</html>
