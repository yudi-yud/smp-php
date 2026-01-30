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
        $stmt = $pdo->prepare("DELETE FROM achievements WHERE id = ?");
        $stmt->execute([$id]);
        $message = 'Prestasi berhasil dihapus.';
        $messageType = 'success';
    } catch (PDOException $e) {
        $message = 'Gagal menghapus prestasi.';
        $messageType = 'error';
    }
}

// Handle form submission for add/edit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
    $title = sanitize($_POST['title'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    $category = sanitize($_POST['category'] ?? 'akademik');
    $level = sanitize($_POST['level'] ?? 'kabupaten');
    $year = (int)($_POST['year'] ?? date('Y'));
    $recipient = sanitize($_POST['recipient'] ?? '');
    $status = sanitize($_POST['status'] ?? 'draft');
    $edit_id = (int)($_POST['edit_id'] ?? 0);

    if (empty($title)) {
        $message = 'Judul wajib diisi.';
        $messageType = 'error';
    } else {
        try {
            $pdo = getDB();

            if ($edit_id) {
                $stmt = $pdo->prepare("UPDATE achievements SET title = ?, description = ?, category = ?, level = ?, year = ?, recipient = ?, status = ? WHERE id = ?");
                $stmt->execute([$title, $description, $category, $level, $year, $recipient, $status, $edit_id]);
                $message = 'Prestasi berhasil diperbarui.';
            } else {
                $stmt = $pdo->prepare("INSERT INTO achievements (title, description, category, level, year, recipient, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
                $stmt->execute([$title, $description, $category, $level, $year, $recipient, $status]);
                $message = 'Prestasi berhasil ditambahkan.';
            }
            $messageType = 'success';
        } catch (PDOException $e) {
            $message = 'Gagal menyimpan prestasi.';
            $messageType = 'error';
        }
    }
}

// Get achievements list
$pdo = getDB();
$stmt = $pdo->query("SELECT * FROM achievements ORDER BY year DESC, created_at DESC");
$achievements = $stmt->fetchAll();

// Get edit data
$edit_data = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM achievements WHERE id = ?");
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
    <title>Kelola Prestasi - Admin Panel</title>
    <link rel="stylesheet" href="assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>

    <main class="main-content">
        <header class="topbar">
            <div class="topbar-left">
                <button class="sidebar-toggle" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>
                <h1 class="page-title">Kelola Prestasi</h1>
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
                        <?= $edit_data ? 'Edit' : 'Tambah' ?> Prestasi</h3>
                    <?php if ($edit_data): ?>
                    <a href="achievements.php" class="btn btn-secondary btn-sm">Batal Edit</a>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                        <input type="hidden" name="edit_id" value="<?= $edit_data['id'] ?? '' ?>">

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Judul Prestasi *</label>
                                <input type="text" name="title" class="form-control" required
                                       value="<?= htmlspecialchars($edit_data['title'] ?? $_POST['title'] ?? '') ?>">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Penerima</label>
                                <input type="text" name="recipient" class="form-control"
                                       value="<?= htmlspecialchars($edit_data['recipient'] ?? $_POST['recipient'] ?? '') ?>"
                                       placeholder="Nama siswa/tim">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Kategori</label>
                                <select name="category" class="form-control">
                                    <option value="akademik" <?= (($_POST['category'] ?? $edit_data['category'] ?? '') === 'akademik' ? 'selected' : '') ?>>Akademik</option>
                                    <option value="non_akademik" <?= (($_POST['category'] ?? $edit_data['category'] ?? '') === 'non_akademik' ? 'selected' : '') ?>>Non-Akademik</option>
                                    <option value="ekstrakurikuler" <?= (($_POST['category'] ?? $edit_data['category'] ?? '') === 'ekstrakurikuler' ? 'selected' : '') ?>>Ekstrakurikuler</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Tingkat</label>
                                <select name="level" class="form-control">
                                    <option value="sekolah" <?= (($_POST['level'] ?? $edit_data['level'] ?? '') === 'sekolah' ? 'selected' : '') ?>>Sekolah</option>
                                    <option value="kecamatan" <?= (($_POST['level'] ?? $edit_data['level'] ?? '') === 'kecamatan' ? 'selected' : '') ?>>Kecamatan</option>
                                    <option value="kabupaten" <?= (($_POST['level'] ?? $edit_data['level'] ?? '') === 'kabupaten' ? 'selected' : '') ?>>Kabupaten</option>
                                    <option value="provinsi" <?= (($_POST['level'] ?? $edit_data['level'] ?? '') === 'provinsi' ? 'selected' : '') ?>>Provinsi</option>
                                    <option value="nasional" <?= (($_POST['level'] ?? $edit_data['level'] ?? '') === 'nasional' ? 'selected' : '') ?>>Nasional</option>
                                    <option value="internasional" <?= (($_POST['level'] ?? $edit_data['level'] ?? '') === 'internasional' ? 'selected' : '') ?>>Internasional</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Tahun</label>
                                <input type="number" name="year" class="form-control" min="2000" max="2099"
                                       value="<?= htmlspecialchars($edit_data['year'] ?? $_POST['year'] ?? date('Y')) ?>">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-control">
                                    <option value="draft" <?= (($_POST['status'] ?? $edit_data['status'] ?? '') === 'draft' ? 'selected' : '') ?>>Draft</option>
                                    <option value="published" <?= (($_POST['status'] ?? $edit_data['status'] ?? '') === 'published' ? 'selected' : '') ?>>Terbit</option>
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
                            <a href="achievements.php" class="btn btn-secondary">Batal</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>

            <!-- List -->
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-list"></i> Daftar Prestasi</h3>
                    <span class="badge badge-primary"><?= count($achievements) ?> prestasi</span>
                </div>
                <div class="card-body">
                    <?php if (empty($achievements)): ?>
                    <div class="empty-state">
                        <i class="fas fa-trophy"></i>
                        <p>Belum ada prestasi.</p>
                    </div>
                    <?php else: ?>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th width="5%">No</th>
                                    <th width="30%">Judul</th>
                                    <th width="15%">Kategori</th>
                                    <th width="15%">Tingkat</th>
                                    <th width="10%">Tahun</th>
                                    <th width="10%">Status</th>
                                    <th width="15%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1; foreach ($achievements as $item): ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td>
                                        <strong><?= htmlspecialchars($item['title']) ?></strong>
                                        <?php if ($item['recipient']): ?>
                                        <br><small><?= htmlspecialchars($item['recipient']) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td><span class="badge badge-secondary"><?= htmlspecialchars($item['category']) ?></span></td>
                                    <td><span class="badge badge-info"><?= htmlspecialchars($item['level']) ?></span></td>
                                    <td><?= $item['year'] ?></td>
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
