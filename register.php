<?php
session_start();
require_once 'config/database.php';

$message = '';

if ($_POST) {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = md5($_POST['password']);
    $nama_lengkap = $_POST['nama_lengkap'];
    $perusahaan = $_POST['perusahaan'];
    
    // Upload files
    $surat_tugas = uploadFile($_FILES['surat_tugas'], 'uploads/surat_tugas/');
    $surat_kompetensi = uploadFile($_FILES['surat_kompetensi'], 'uploads/surat_kompetensi/');
    
    if ($surat_tugas && $surat_kompetensi) {
        try {
            $pdo->beginTransaction();
            
            // Insert user
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'wartawan')");
            $stmt->execute([$username, $email, $password]);
            $user_id = $pdo->lastInsertId();
            
            // Insert wartawan data
            $stmt = $pdo->prepare("INSERT INTO wartawan_data (user_id, nama_lengkap, perusahaan, surat_tugas, surat_kompetensi) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$user_id, $nama_lengkap, $perusahaan, $surat_tugas, $surat_kompetensi]);
            
            $pdo->commit();
            $message = 'Pendaftaran berhasil! Data Anda sedang menunggu verifikasi admin.';
            
        } catch(Exception $e) {
            $pdo->rollback();
            $message = 'Error: ' . $e->getMessage();
        }
    } else {
        $message = 'Gagal upload file!';
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Daftar Wartawan - Sistem Berita</title>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial; margin: 20px; background: #f4f4f4; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; }
        input[type="text"], input[type="email"], input[type="password"], input[type="file"] { 
            width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ddd; 
        }
        .btn { background: #28a745; color: white; padding: 10px 20px; border: none; cursor: pointer; width: 100%; margin: 10px 0; }
        .btn:hover { background: #218838; }
        .message { color: green; margin: 10px 0; }
        .form-group { margin: 15px 0; }
        label { font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Pendaftaran Wartawan</h2>
        <?php if($message): ?>
            <div class="message"><?= $message ?></div>
        <?php endif; ?>
        
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label>Username:</label>
                <input type="text" name="username" required>
            </div>
            
            <div class="form-group">
                <label>Email:</label>
                <input type="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label>Password:</label>
                <input type="password" name="password" required>
            </div>
            
            <div class="form-group">
                <label>Nama Lengkap:</label>
                <input type="text" name="nama_lengkap" required>
            </div>
            
            <div class="form-group">
                <label>Perusahaan Media:</label>
                <input type="text" name="perusahaan" required>
            </div>
            
            <div class="form-group">
                <label>Surat Tugas:</label>
                <input type="file" name="surat_tugas" accept=".pdf,.jpg,.jpeg,.png" required>
            </div>
            
            <div class="form-group">
                <label>Surat Lulus Uji Kompetensi:</label>
                <input type="file" name="surat_kompetensi" accept=".pdf,.jpg,.jpeg,.png" required>
            </div>
            
            <button type="submit" class="btn">Daftar</button>
            <a href="login.php" class="btn" style="background: #6c757d; text-decoration: none; text-align: center; display: block;">Kembali ke Login</a>
        </form>
    </div>
</body>
</html>