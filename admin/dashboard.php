<?php
session_start();
require_once '../config/database.php';
checkLogin();
checkAdmin();

// Get pending verifications
$stmt = $pdo->prepare("SELECT w.*, u.username, u.email FROM wartawan_data w 
                       JOIN users u ON w.user_id = u.id 
                       WHERE w.status = 'pending' ORDER BY w.created_at DESC");
$stmt->execute();
$pending_wartawan = $stmt->fetchAll();

// Get berita pending price
$stmt = $pdo->prepare("SELECT b.*, w.nama_lengkap, w.perusahaan FROM berita b 
                       JOIN wartawan_data w ON b.wartawan_id = w.id 
                       WHERE b.status = 'pending_price' ORDER BY b.created_at DESC");
$stmt->execute();
$pending_berita = $stmt->fetchAll();

// Process verification
if ($_POST && isset($_POST['verify_wartawan'])) {
    $wartawan_id = $_POST['wartawan_id'];
    $action = $_POST['action'];
    
    $status = ($action == 'verify') ? 'verified' : 'rejected';
    $stmt = $pdo->prepare("UPDATE wartawan_data SET status = ?, verified_at = NOW() WHERE id = ?");
    $stmt->execute([$status, $wartawan_id]);
    
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial; margin: 0; background: #f4f4f4; }
        .header { background: #dc3545; color: white; padding: 15px; display: flex; justify-content: space-between; }
        .container { padding: 20px; }
        .card { background: white; padding: 20px; margin: 20px 0; border-radius: 10px; }
        .table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        .table th, .table td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        .table th { background: #f8f9fa; }
        .btn { padding: 8px 15px; border: none; cursor: pointer; text-decoration: none; border-radius: 5px; margin: 2px; }
        .btn-success { background: #28a745; color: white; }
        .btn-danger { background: #dc3545; color: white; }
        .btn-primary { background: #007bff; color: white; }
        .form-inline { display: flex; align-items: center; gap: 10px; }
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); }
        .modal-content { background: white; margin: 10% auto; padding: 20px; width: 50%; border-radius: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Admin Dashboard</h2>
        <a href="../logout.php" class="btn btn-danger">Logout</a>
    </div>
    
    <div class="container">
        <!-- Pending Wartawan Verification -->
        <div class="card">
            <h3>🔍 Verifikasi Wartawan (<?= count($pending_wartawan) ?>)</h3>
            <?php if (count($pending_wartawan) > 0): ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Perusahaan</th>
                            <th>Tanggal Daftar</th>
                            <th>Dokumen</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pending_wartawan as $w): ?>
                        <tr>
                            <td><?= $w['nama_lengkap'] ?></td>
                            <td><?= $w['email'] ?></td>
                            <td><?= $w['perusahaan'] ?></td>
                            <td><?= date('d/m/Y', strtotime($w['created_at'])) ?></td>
                            <td>
                                <a href="../uploads/surat_tugas/<?= $w['surat_tugas'] ?>" target="_blank" class="btn btn-primary">Surat Tugas</a>
                                <a href="../uploads/surat_kompetensi/<?= $w['surat_kompetensi'] ?>" target="_blank" class="btn btn-primary">Surat Kompetensi</a>
                            </td>
                            <td>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="wartawan_id" value="<?= $w['id'] ?>">
                                    <input type="hidden" name="verify_wartawan" value="1">
                                    <button type="submit" name="action" value="verify" class="btn btn-success" onclick="return confirm('Verifikasi wartawan ini?')">✅ Verifikasi</button>
                                    <button type="submit" name="action" value="reject" class="btn btn-danger" onclick="return confirm('Tolak wartawan ini?')">❌ Tolak</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>Tidak ada wartawan yang menunggu verifikasi.</p>
            <?php endif; ?>
        </div>
        
        <!-- Pending Price Setting -->
        <div class="card">
            <h3>💰 Tentukan Harga Berita (<?= count($pending_berita) ?>)</h3>
            <?php if (count($pending_berita) > 0): ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Judul</th>
                            <th>Penulis</th>
                            <th>Perusahaan</th>
                            <th>Tanggal Upload</th>
                            <th>Preview</th>
                            <th>Set Harga</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pending_berita as $b): ?>
                        <tr>
                            <td><?= substr($b['judul'], 0, 40) ?>...</td>
                            <td><?= $b['nama_lengkap'] ?></td>
                            <td><?= $b['perusahaan'] ?></td>
                            <td><?= date('d/m/Y', strtotime($b['created_at'])) ?></td>
                            <td>
                                <button onclick="showPreview('<?= $b['id'] ?>', '<?= addslashes($b['judul']) ?>', '<?= addslashes(substr($b['konten'], 0, 300)) ?>')" class="btn btn-primary">👁️ Preview</button>
                            </td>
                            <td>
                                <form method="POST" class="form-inline">
                                    <input type="hidden" name="berita_id" value="<?= $b['id'] ?>">
                                    <input type="hidden" name="set_price" value="1">
                                    <input type="number" name="harga" placeholder="Harga (Rp)" required style="width: 120px; padding: 5px;">
                                    <button type="submit" class="btn btn-success">Set Harga</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>Tidak ada berita yang menunggu penetapan harga.</p>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Modal Preview -->
    <div id="previewModal" class="modal">
        <div class="modal-content">
            <span onclick="closePreview()" style="float: right; font-size: 24px; cursor: pointer;">&times;</span>
            <h3 id="previewTitle"></h3>
            <p id="previewContent"></p>
        </div>
    </div>
    
    <script>
        function showPreview(id, title, content) {
            document.getElementById('previewTitle').innerHTML = title;
            document.getElementById('previewContent').innerHTML = content + '...';
            document.getElementById('previewModal').style.display = 'block';
        }
        
        function closePreview() {
            document.getElementById('previewModal').style.display = 'none';
        }
        
        window.onclick = function(event) {
            if (event.target == document.getElementById('previewModal')) {
                closePreview();
            }
        }
    </script>
</body>
</html>d.php');
    exit;
}

// Process price setting
if ($_POST && isset($_POST['set_price'])) {
    $berita_id = $_POST['berita_id'];
    $harga = $_POST['harga'];
    
    $stmt = $pdo->prepare("UPDATE berita SET harga = ?, status = 'pending_payment' WHERE id = ?");
    $stmt->execute([$harga, $berita_id]);
    
    header('Location: dashboar