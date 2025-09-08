<?php
require_once 'config/database.php';

// Get published berita
$stmt = $pdo->prepare("SELECT b.*, w.nama_lengkap, w.perusahaan FROM berita b 
                       JOIN wartawan_data w ON b.wartawan_id = w.id 
                       WHERE b.status = 'published' 
                       ORDER BY b.published_at DESC LIMIT 10");
$stmt->execute();
$published_berita = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Portal Berita</title>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial; margin: 0; background: #f4f4f4; }
        .header { background: #007bff; color: white; padding: 20px; text-align: center; }
        .nav { background: #0056b3; padding: 10px; text-align: center; }
        .nav a { color: white; text-decoration: none; margin: 0 15px; padding: 10px 20px; border-radius: 5px; }
        .nav a:hover { background: rgba(255,255,255,0.2); }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        .berita-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 20px; }
        .berita-card { 
            background: white; border-radius: 10px; overflow: hidden; 
            box-shadow: 0 2px 10px rgba(0,0,0,0.1); transition: transform 0.3s; 
        }
        .berita-card:hover { transform: translateY(-5px); }
        .berita-image { width: 100%; height: 200px; object-fit: cover; }
        .berita-content { padding: 20px; }
        .berita-title { font-size: 18px; font-weight: bold; margin-bottom: 10px; color: #333; }
        .berita-meta { color: #666; font-size: 12px; margin-bottom: 10px; }
        .berita-excerpt { color: #555; line-height: 1.5; }
        .no-berita { text-align: center; padding: 50px; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <h1>🗞️ Portal Berita Online</h1>
        <p>Platform berita terpercaya dengan wartawan terverifikasi</p>
    </div>
    
    <div class="nav">
        <a href="index.php">🏠 Beranda</a>
        <a href="login.php">🔑 Login</a>
        <a href="register.php">📝 Daftar Wartawan</a>
    </div>
    
    <div class="container">
        <h2>📰 Berita Terbaru</h2>
        
        <?php if (count($published_berita) > 0): ?>
            <div class="berita-grid">
                <?php foreach ($published_berita as $berita): ?>
                <div class="berita-card">
                    <?php if ($berita['gambar']): ?>
                        <img src="uploads/berita/<?= $berita['gambar'] ?>" alt="<?= $berita['judul'] ?>" class="berita-image">
                    <?php endif; ?>
                    
                    <div class="berita-content">
                        <div class="berita-title"><?= $berita['judul'] ?></div>
                        <div class="berita-meta">
                            👤 <?= $berita['nama_lengkap'] ?> | 
                            🏢 <?= $berita['perusahaan'] ?> | 
                            📅 <?= date('d M Y, H:i', strtotime($berita['published_at'])) ?>
                        </div>
                        <div class="berita-excerpt">
                            <?= substr(strip_tags($berita['konten']), 0, 150) ?>...
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="no-berita">
                <h3>📭 Belum Ada Berita</h3>
                <p>Belum ada berita yang dipublikasikan. Silakan cek kembali nanti.</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>