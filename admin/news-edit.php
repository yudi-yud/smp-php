<?php
/**
 * Edit News
 * SMPN 3 Satu Atap Cipari
 */

require_once 'auth.php';
requireAuth();
require_once '../config/database.php';

$page_title = 'Edit Berita';

$id = (int)($_GET['id'] ?? 0);

if (!$id) {
    header('Location: news.php');
    exit;
}

// Get news data
$pdo = getDB();
$stmt = $pdo->prepare("SELECT * FROM news WHERE id = ?");
$stmt->execute([$id]);
$news = $stmt->fetch();

if (!$news) {
    header('Location: news.php');
    exit;
}

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize($_POST['title'] ?? '');
    $slug = sanitize($_POST['slug'] ?? '');
    $excerpt = sanitize($_POST['excerpt'] ?? '');
    $content = $_POST['content'] ?? '';
    $category = sanitize($_POST['category'] ?? 'umum');
    $status = sanitize($_POST['status'] ?? 'draft');
    $csrf_token = $_POST['csrf_token'] ?? '';

    // Validate CSRF
    if (!verifyCSRFToken($csrf_token)) {
        $errors[] = 'Token CSRF tidak valid.';
    }

    // Validate input
    if (empty($title)) {
        $errors[] = 'Judul wajib diisi.';
    }

    if (empty($content)) {
        $errors[] = 'Konten wajib diisi.';
    }

    // Generate slug from title if empty
    if (empty($slug)) {
        $slug = strtolower(trim(preg_replace('/[^a-z0-9]+/', '-', $title), '-'));
    }

    // Check if slug already exists (excluding current)
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM news WHERE slug = ? AND id != ?");
    $stmt->execute([$slug, $id]);
    if ($stmt->fetchColumn() > 0) {
        $errors[] = 'Slug sudah digunakan. Silakan gunakan slug lain.';
    }

    if (empty($errors)) {
        try {
            $pdo = getDB();

            $sql = "UPDATE news SET title = ?, slug = ?, excerpt = ?, content = ?, category = ?, status = ?, updated_at = NOW()";
            $params = [$title, $slug, $excerpt, $content, $category, $status];

            // Update published_at if status changed to published
            if ($status === 'published' && $news['status'] !== 'published') {
                $sql .= ", published_at = NOW()";
            }

            $sql .= " WHERE id = ?";
            $params[] = $id;

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);

            $success = true;
            $message = 'Berita berhasil diperbarui.';

            // Refresh news data
            $stmt = $pdo->prepare("SELECT * FROM news WHERE id = ?");
            $stmt->execute([$id]);
            $news = $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Update News Error: " . $e->getMessage());
            $errors[] = 'Gagal memperbarui berita. Silakan coba lagi.';
        }
    }
}

$csrf_token = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> - Admin Panel</title>
    <link rel="stylesheet" href="assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .full-width { grid-column: 1 / -1; }
        @media (max-width: 768px) {
            .form-row { grid-template-columns: 1fr; }
        }
        textarea.form-control { min-height: 200px; font-family: inherit; }
    </style>
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>

    <main class="main-content">
        <header class="topbar">
            <div class="topbar-left">
                <button class="sidebar-toggle" onclick="toggleSidebar()">
                    <i class="fas fa-bars"></i>
                </button>
                <h1 class="page-title">Edit Berita</h1>
            </div>
            <div class="topbar-right">
                <a href="news.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i>
                    <span>Kembali</span>
                </a>
            </div>
        </header>

        <div class="content-wrapper">
            <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?= htmlspecialchars($message) ?>
            </div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i>
                <ul style="margin: 0; padding-left: 20px;">
                    <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-edit"></i> Edit Berita</h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Judul Berita *</label>
                                <input type="text" name="title" class="form-control form-control-lg"
                                       placeholder="Masukkan judul berita"
                                       value="<?= htmlspecialchars($news['title']) ?>"
                                       required>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Slug URL</label>
                                <input type="text" name="slug" class="form-control"
                                       placeholder="url-slug-berita"
                                       value="<?= htmlspecialchars($news['slug']) ?>">
                                <small class="text-muted">URL slug untuk berita ini</small>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Kategori</label>
                                <select name="category" class="form-control">
                                    <option value="umum" <?= $news['category'] === 'umum' ? 'selected' : '' ?>>Umum</option>
                                    <option value="pendidikan" <?= $news['category'] === 'pendidikan' ? 'selected' : '' ?>>Pendidikan</option>
                                    <option value="prestasi" <?= $news['category'] === 'prestasi' ? 'selected' : '' ?>>Prestasi</option>
                                    <option value="kegiatan" <?= $news['category'] === 'kegiatan' ? 'selected' : '' ?>>Kegiatan</option>
                                    <option value="pengumuman" <?= $news['category'] === 'pengumuman' ? 'selected' : '' ?>>Pengumuman</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-control">
                                    <option value="draft" <?= $news['status'] === 'draft' ? 'selected' : '' ?>>Draft</option>
                                    <option value="published" <?= $news['status'] === 'published' ? 'selected' : '' ?>>Terbit</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Ringkasan (Excerpt)</label>
                            <textarea name="excerpt" class="form-control" rows="3"
                                      placeholder="Ringkasan singkat berita (opsional)"><?= htmlspecialchars($news['excerpt'] ?? '') ?></textarea>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Konten Berita *</label>
                            <textarea name="content" class="form-control" rows="12"
                                      placeholder="Tulis konten berita di sini..."
                                      required><?= htmlspecialchars($news['content']) ?></textarea>
                        </div>

                        <div class="form-actions" style="display: flex; gap: 10px; margin-top: 30px;">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Simpan Perubahan
                            </button>
                            <a href="news.php" class="btn btn-secondary">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <script src="assets/js/admin.js"></script>
</body>
</html>
