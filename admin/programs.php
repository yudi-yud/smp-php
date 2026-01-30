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
        $stmt = $pdo->prepare("DELETE FROM programs WHERE id = ?");
        $stmt->execute([$id]);
        $message = 'Program berhasil dihapus.';
        $messageType = 'success';
    } catch (PDOException $e) {
        $message = 'Gagal menghapus program.';
        $messageType = 'error';
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
    $title = sanitize($_POST['title'] ?? '');
    $slug = sanitize($_POST['slug'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    $icon = sanitize($_POST['icon'] ?? 'fas fa-graduation-cap');
    $status = sanitize($_POST['status'] ?? 'draft');
    $sort_order = (int)($_POST['sort_order'] ?? 0);
    $edit_id = (int)($_POST['edit_id'] ?? 0);

    if (empty($title)) {
        $message = 'Judul wajib diisi.';
        $messageType = 'error';
    } else {
        if (empty($slug)) {
            $slug = strtolower(trim(preg_replace('/[^a-z0-9]+/', '-', $title), '-'));
        }

        try {
            $pdo = getDB();

            if ($edit_id) {
                $stmt = $pdo->prepare("UPDATE programs SET title = ?, slug = ?, description = ?, icon = ?, status = ?, sort_order = ? WHERE id = ?");
                $stmt->execute([$title, $slug, $description, $icon, $status, $sort_order, $edit_id]);
                $message = 'Program berhasil diperbarui.';
            } else {
                $stmt = $pdo->prepare("INSERT INTO programs (title, slug, description, icon, status, sort_order, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
                $stmt->execute([$title, $slug, $description, $icon, $status, $sort_order]);
                $message = 'Program berhasil ditambahkan.';
            }
            $messageType = 'success';
        } catch (PDOException $e) {
            $message = 'Gagal menyimpan program.';
            $messageType = 'error';
        }
    }
}

// Get programs list
$pdo = getDB();
$stmt = $pdo->query("SELECT * FROM programs ORDER BY sort_order ASC, created_at DESC");
$programs = $stmt->fetchAll();

// Get edit data
$edit_data = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM programs WHERE id = ?");
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
    <title>Kelola Program - Admin Panel</title>
    <link rel="stylesheet" href="assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body>
    <?php include 'includes/sidebar.php'; ?>

    <main class="main-content">
        <header class="topbar">
            <div class="topbar-left">
                <button class="sidebar-toggle" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>
                <h1 class="page-title">Kelola Program</h1>
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
                        <?= $edit_data ? 'Edit' : 'Tambah' ?> Program</h3>
                    <?php if ($edit_data): ?>
                        <a href="programs.php" class="btn btn-secondary btn-sm">Batal Edit</a>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                        <input type="hidden" name="edit_id" value="<?= $edit_data['id'] ?? '' ?>">

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Judul Program *</label>
                                <input type="text" name="title" class="form-control" required
                                    value="<?= htmlspecialchars($edit_data['title'] ?? $_POST['title'] ?? '') ?>">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Slug URL</label>
                                <input type="text" name="slug" class="form-control"
                                    value="<?= htmlspecialchars($edit_data['slug'] ?? $_POST['slug'] ?? '') ?>">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Icon (Font Awesome)</label>
                                <input type="text" name="icon" class="form-control"
                                    value="<?= htmlspecialchars($edit_data['icon'] ?? $_POST['icon'] ?? 'fas fa-graduation-cap') ?>"
                                    placeholder="fas fa-graduation-cap">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Urutan</label>
                                <input type="number" name="sort_order" class="form-control" min="0"
                                    value="<?= htmlspecialchars($edit_data['sort_order'] ?? $_POST['sort_order'] ?? 0) ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Deskripsi</label>
                            <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($edit_data['description'] ?? $_POST['description'] ?? '') ?></textarea>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-control">
                                <option value="draft" <?= ($_POST['status'] ?? $edit_data['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Draft</option>
                                <option value="published" <?= ($_POST['status'] ?? $edit_data['status'] ?? '') === 'published' ? 'selected' : '' ?>>Terbit</option>
                            </select>
                        </div>

                        <div style="display: flex; gap: 10px;">
                            <button type="submit" name="save" value="1" class="btn btn-primary">
                                <i class="fas fa-save"></i> Simpan
                            </button>
                            <?php if ($edit_data): ?>
                                <a href="programs.php" class="btn btn-secondary">Batal</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>

            <!-- List -->
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-list"></i> Daftar Program</h3>
                    <span class="badge badge-primary"><?= count($programs) ?> program</span>
                </div>
                <div class="card-body">
                    <?php if (empty($programs)): ?>
                        <div class="empty-state">
                            <i class="fas fa-graduation-cap"></i>
                            <p>Belum ada program.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-container">
                            <table>
                                <thead>
                                    <tr>
                                        <th width="5%">No</th>
                                        <th width="10%">Icon</th>
                                        <th width="30%">Judul</th>
                                        <th width="35%">Deskripsi</th>
                                        <th width="10%">Status</th>
                                        <th width="10%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $no = 1;
                                    foreach ($programs as $item): ?>
                                        <tr>
                                            <td><?= $no++ ?></td>
                                            <td><i class="<?= htmlspecialchars($item['icon']) ?> fa-lg"></i></td>
                                            <td>
                                                <strong><?= htmlspecialchars($item['title']) ?></strong>
                                                <br><small class="text-muted"><?= htmlspecialchars($item['slug']) ?></small>
                                            </td>
                                            <td><?= htmlspecialchars(substr($item['description'], 0, 80)) ?><?= strlen($item['description']) > 80 ? '...' : '' ?></td>
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