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
        $stmt = $pdo->prepare("DELETE FROM testimonials WHERE id = ?");
        $stmt->execute([$id]);
        $message = 'Testimoni berhasil dihapus.';
        $messageType = 'success';
    } catch (PDOException $e) {
        $message = 'Gagal menghapus testimoni.';
        $messageType = 'error';
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
    $name = sanitize($_POST['name'] ?? '');
    $role = sanitize($_POST['role'] ?? '');
    $content = sanitize($_POST['content'] ?? '');
    $image = sanitize($_POST['image'] ?? '');
    $rating = (int)($_POST['rating'] ?? 5);
    $status = sanitize($_POST['status'] ?? 'draft');
    $sort_order = (int)($_POST['sort_order'] ?? 0);
    $edit_id = (int)($_POST['edit_id'] ?? 0);

    if (empty($name) || empty($content)) {
        $message = 'Nama dan konten testimoni wajib diisi.';
        $messageType = 'error';
    } else {
        $rating = max(1, min(5, $rating)); // Clamp between 1-5
        try {
            $pdo = getDB();

            if ($edit_id) {
                $stmt = $pdo->prepare("UPDATE testimonials SET name = ?, role = ?, content = ?, image = ?, rating = ?, status = ?, sort_order = ? WHERE id = ?");
                $stmt->execute([$name, $role, $content, $image, $rating, $status, $sort_order, $edit_id]);
                $message = 'Testimoni berhasil diperbarui.';
            } else {
                $stmt = $pdo->prepare("INSERT INTO testimonials (name, role, content, image, rating, status, sort_order, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
                $stmt->execute([$name, $role, $content, $image, $rating, $status, $sort_order]);
                $message = 'Testimoni berhasil ditambahkan.';
            }
            $messageType = 'success';
        } catch (PDOException $e) {
            $message = 'Gagal menyimpan testimoni.';
            $messageType = 'error';
        }
    }
}

// Get testimonials list
$pdo = getDB();
$stmt = $pdo->query("SELECT * FROM testimonials ORDER BY sort_order ASC, created_at DESC");
$testimonials = $stmt->fetchAll();

// Get edit data
$edit_data = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM testimonials WHERE id = ?");
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
    <title>Kelola Testimoni - Admin Panel</title>
    <link rel="stylesheet" href="assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body>
    <?php include 'includes/sidebar.php'; ?>

    <main class="main-content">
        <header class="topbar">
            <div class="topbar-left">
                <button class="sidebar-toggle" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>
                <h1 class="page-title">Kelola Testimoni</h1>
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
                        <?= $edit_data ? 'Edit' : 'Tambah' ?> Testimoni</h3>
                    <?php if ($edit_data): ?>
                        <a href="testimonials.php" class="btn btn-secondary btn-sm">Batal Edit</a>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                        <input type="hidden" name="edit_id" value="<?= $edit_data['id'] ?? '' ?>">

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Nama *</label>
                                <input type="text" name="name" class="form-control" required
                                    value="<?= htmlspecialchars($edit_data['name'] ?? $_POST['name'] ?? '') ?>">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Role/Jabatan</label>
                                <input type="text" name="role" class="form-control"
                                    value="<?= htmlspecialchars($edit_data['role'] ?? $_POST['role'] ?? '') ?>"
                                    placeholder="Contoh: Wali Murid, Alumni, dll">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Konten Testimoni *</label>
                            <textarea name="content" class="form-control" rows="4" required><?= htmlspecialchars($edit_data['content'] ?? $_POST['content'] ?? '') ?></textarea>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Nama File Foto</label>
                                <input type="text" name="image" class="form-control"
                                    value="<?= htmlspecialchars($edit_data['image'] ?? $_POST['image'] ?? '') ?>"
                                    placeholder="img/testimonials/nama-file.jpg">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Rating (1-5)</label>
                                <input type="number" name="rating" class="form-control" min="1" max="5"
                                    value="<?= htmlspecialchars($edit_data['rating'] ?? $_POST['rating'] ?? 5) ?>">
                            </div>
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

                        <div style="display: flex; gap: 10px;">
                            <button type="submit" name="save" value="1" class="btn btn-primary">
                                <i class="fas fa-save"></i> Simpan
                            </button>
                            <?php if ($edit_data): ?>
                                <a href="testimonials.php" class="btn btn-secondary">Batal</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>

            <!-- List -->
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-list"></i> Daftar Testimoni</h3>
                    <span class="badge badge-primary"><?= count($testimonials) ?> testimoni</span>
                </div>
                <div class="card-body">
                    <?php if (empty($testimonials)): ?>
                        <div class="empty-state">
                            <i class="fas fa-quote-left"></i>
                            <p>Belum ada testimoni.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-container">
                            <table>
                                <thead>
                                    <tr>
                                        <th width="5%">No</th>
                                        <th width="20%">Nama</th>
                                        <th width="40%">Testimoni</th>
                                        <th width="10%">Rating</th>
                                        <th width="10%">Status</th>
                                        <th width="15%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $no = 1;
                                    foreach ($testimonials as $item): ?>
                                        <tr>
                                            <td><?= $no++ ?></td>
                                            <td>
                                                <strong><?= htmlspecialchars($item['name']) ?></strong>
                                                <?php if ($item['role']): ?>
                                                    <br><small class="text-muted"><?= htmlspecialchars($item['role']) ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= htmlspecialchars(substr($item['content'], 0, 100)) ?><?= strlen($item['content']) > 100 ? '...' : '' ?></td>
                                            <td>
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <i class="fas fa-star" style="color: <?= $i <= $item['rating'] ? '#f59e0b' : '#e2e8f0' ?>; font-size: 0.8rem;"></i>
                                                <?php endfor; ?>
                                            </td>
                                            <td>
                                                <span class="badge badge-<?= $item['status'] === 'published' ? 'success' : 'warning' ?>">
                                                    <?= $item['status'] === 'published' ? 'Terbit' : 'Draft' ?>
                                                </span>
                                            </td>
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