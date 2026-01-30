<?php
require_once 'auth.php';
requireAuth();
require_once '../config/database.php';

// Handle actions
$action = $_GET['action'] ?? '';
$message = '';
$messageType = '';

if ($action === 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    try {
        $pdo = getDB();
        $stmt = $pdo->prepare("DELETE FROM gallery WHERE id = ?");
        $stmt->execute([$id]);
        $message = 'Galeri berhasil dihapus.';
        $messageType = 'success';
    } catch (PDOException $e) {
        $message = 'Gagal menghapus galeri.';
        $messageType = 'error';
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
    $title = sanitize($_POST['title'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    $image = sanitize($_POST['image'] ?? '');
    $category = sanitize($_POST['category'] ?? 'kegiatan');
    $status = sanitize($_POST['status'] ?? 'draft');
    $sort_order = (int)($_POST['sort_order'] ?? 0);
    $edit_id = (int)($_POST['edit_id'] ?? 0);

    if (empty($title) || empty($image)) {
        $message = 'Judul dan nama gambar wajib diisi.';
        $messageType = 'error';
    } else {
        try {
            $pdo = getDB();

            if ($edit_id) {
                $stmt = $pdo->prepare("UPDATE gallery SET title = ?, description = ?, image = ?, category = ?, status = ?, sort_order = ? WHERE id = ?");
                $stmt->execute([$title, $description, $image, $category, $status, $sort_order, $edit_id]);
                $message = 'Galeri berhasil diperbarui.';
            } else {
                $stmt = $pdo->prepare("INSERT INTO gallery (title, description, image, category, status, sort_order, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
                $stmt->execute([$title, $description, $image, $category, $status, $sort_order]);
                $message = 'Galeri berhasil ditambahkan.';
            }
            $messageType = 'success';
        } catch (PDOException $e) {
            $message = 'Gagal menyimpan galeri.';
            $messageType = 'error';
        }
    }
}

// Get gallery list
$pdo = getDB();
$stmt = $pdo->query("SELECT * FROM gallery ORDER BY sort_order ASC, created_at DESC");
$gallery = $stmt->fetchAll();

// Get edit data
$edit_data = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM gallery WHERE id = ?");
    $stmt->execute([(int)$_GET['edit']]);
    $edit_data = $stmt->fetch();
}

$csrf_token = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Galeri - Admin Panel</title>
    <link rel="stylesheet" href="assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body>
    <?php include 'includes/sidebar.php'; ?>

    <main class="main-content">
        <header class="topbar">
            <div class="topbar-left">
                <button class="sidebar-toggle" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>
                <h1 class="page-title">Kelola Galeri</h1>
            </div>
        </header>

        <div class="content-wrapper">
            <?php if ($message): ?>
                <div class="alert alert-<?= $messageType === 'success' ? 'success' : 'danger' ?>">
                    <i class="fas fa-<?= $messageType === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <!-- Form -->
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-<?= $edit_data ? 'edit' : 'plus-circle' ?>"></i>
                        <?= $edit_data ? 'Edit' : 'Tambah' ?> Galeri</h3>
                    <?php if ($edit_data): ?>
                        <a href="gallery.php" class="btn btn-secondary btn-sm">Batal Edit</a>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                        <input type="hidden" name="edit_id" value="<?= $edit_data['id'] ?? '' ?>">

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Judul *</label>
                                <input type="text" name="title" class="form-control" required
                                    value="<?= htmlspecialchars($edit_data['title'] ?? $_POST['title'] ?? '') ?>">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Kategori</label>
                                <select name="category" class="form-control">
                                    <option value="kegiatan" <?= ($_POST['category'] ?? $edit_data['category'] ?? '') === 'kegiatan' ? 'selected' : '' ?>>Kegiatan</option>
                                    <option value="fasilitas" <?= ($_POST['category'] ?? $edit_data['category'] ?? '') === 'fasilitas' ? 'selected' : '' ?>>Fasilitas</option>
                                    <option value="prestasi" <?= ($_POST['category'] ?? $edit_data['category'] ?? '') === 'prestasi' ? 'selected' : '' ?>>Prestasi</option>
                                    <option value="lainnya" <?= ($_POST['category'] ?? $edit_data['category'] ?? '') === 'lainnya' ? 'selected' : '' ?>>Lainnya</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Nama File Gambar *</label>
                            <input type="text" name="image" class="form-control" required
                                value="<?= htmlspecialchars($edit_data['image'] ?? $_POST['image'] ?? '') ?>"
                                placeholder="img/gallery/nama-file.jpg">
                            <small class="text-muted">Masukkan path relatif gambar dari folder root</small>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Urutan</label>
                                <input type="number" name="sort_order" class="form-control" min="0"
                                    value="<?= htmlspecialchars($edit_data['sort_order'] ?? $_POST['sort_order'] ?? 0) ?>">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-control">
                                    <option value="draft" <?= ($_POST['status'] ?? $edit_data['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Draft</option>
                                    <option value="published" <?= ($_POST['status'] ?? $edit_data['status'] ?? '') === 'published' ? 'selected' : '' ?>>Terbit</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Deskripsi</label>
                            <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($edit_data['description'] ?? $_POST['description'] ?? '') ?></textarea>
                        </div>

                        <div style="display: flex; gap: 10px;">
                            <button type="submit" name="save" value="1" class="btn btn-primary">
                                <i class="fas fa-save"></i> Simpan
                            </button>
                            <?php if ($edit_data): ?>
                                <a href="gallery.php" class="btn btn-secondary">Batal</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>

            <!-- List -->
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-list"></i> Daftar Galeri</h3>
                    <span class="badge badge-primary"><?= count($gallery) ?> foto</span>
                </div>
                <div class="card-body">
                    <?php if (empty($gallery)): ?>
                        <div class="empty-state">
                            <i class="fas fa-images"></i>
                            <p>Belum ada galeri.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-container">
                            <table>
                                <thead>
                                    <tr>
                                        <th width="5%">No</th>
                                        <th width="15%">Gambar</th>
                                        <th width="25%">Judul</th>
                                        <th width="15%">Kategori</th>
                                        <th width="10%">Status</th>
                                        <th width="10%">Urutan</th>
                                        <th width="10%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $no = 1;
                                    foreach ($gallery as $item): ?>
                                        <tr>
                                            <td><?= $no++ ?></td>
                                            <td>
                                                <?php if (file_exists('../' . $item['image'])): ?>
                                                    <img src="../<?= htmlspecialchars($item['image']) ?>"
                                                        alt="<?= htmlspecialchars($item['title']) ?>"
                                                        style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px;">
                                                <?php else: ?>
                                                    <div style="width: 60px; height: 60px; background: #f1f5f9; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                                        <i class="fas fa-image text-muted"></i>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <strong><?= htmlspecialchars($item['title']) ?></strong>
                                                <?php if ($item['description']): ?>
                                                    <br><small class="text-muted"><?= htmlspecialchars(substr($item['description'], 0, 50)) ?>...</small>
                                                <?php endif; ?>
                                            </td>
                                            <td><span class="badge badge-secondary"><?= htmlspecialchars($item['category']) ?></span></td>
                                            <td>
                                                <span class="badge badge-<?= $item['status'] === 'published' ? 'success' : 'warning' ?>">
                                                    <?= $item['status'] === 'published' ? 'Terbit' : 'Draft' ?>
                                                </span>
                                            </td>
                                            <td><?= $item['sort_order'] ?></td>
                                            <td>
                                                <a href="?edit=<?= $item['id'] ?>" class="btn-icon" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="?action=delete&id=<?= $item['id'] ?>"
                                                    class="btn-icon text-danger"
                                                    onclick="return confirmDelete()">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <script src="assets/js/admin.js"></script>
</body>

</html>