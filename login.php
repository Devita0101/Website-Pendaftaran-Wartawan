<?php
session_start();
require_once 'config/database.php';

$error = '';

if ($_POST) {
    $username = $_POST['username'];
    $password = md5($_POST['password']);
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND password = ?");
    $stmt->execute([$username, $password]);
    $user = $stmt->fetch();
    
    if ($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        
        if ($user['role'] == 'admin') {
            header('Location: admin/dashboard.php');
        } else {
            header('Location: wartawan/dashboard.php');
        }
        exit;
    } else {
        $error = 'Username atau password salah!';
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login - Sistem Berita</title>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial; margin: 50px; background: #f4f4f4; }
        .container { max-width: 400px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; }
        input[type="text"], input[type="password"] { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ddd; }
        .btn { background: #007bff; color: white; padding: 10px 20px; border: none; cursor: pointer; width: 100%; }
        .btn:hover { background: #0056b3; }
        .error { color: red; margin: 10px 0; }
        .register-link { text-align: center; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Login Sistem Berita</h2>
        <?php if($error): ?>
            <div class="error"><?= $error ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" class="btn">Login</button>
        </form>
        
        <div class="register-link">
            <p>Belum punya akun? <a href="register.php">Daftar sebagai Wartawan</a></p>
            <p><small>Admin default: username: admin, password: admin123</small></p>
        </div>
    </div>
</body>
</html>