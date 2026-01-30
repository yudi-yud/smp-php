<?php
/**
 * News Management
 * SMPN 3 Satu Atap Cipari
 */

require_once 'auth.php';
requireAuth();

require_once '../config/database.php';
$page_title = 'Kelola Berita';

// Handle actions
$action = $_GET['action'] ?? '';
$message = '';
$messageType = '';

if ($action === 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    try {
        $pdo = getDB();
        $stmt = $pdo->prepare("DELETE FROM news WHERE id = ?");
        $stmt->execute([$id]);
        $message = 'Berita berhasil dihapus.';
        $messageType = 'success';
    } catch (PDOException $e) {
        $message = 'Gagal menghapus berita.';
        $messageType = 'error';
    }
}

// Get news list
$news = [];
try {
    $pdo = getDB();

    // Get news with author names
    $stmt = $pdo->query("
        SELECT n.*, u.full_name as author_name
        FROM news n
        LEFT JOIN admin_users u ON n.author_id = u.id
        ORDER BY n.created_at DESC
    ");
    $news = $stmt->fetchAll();

    $total = count($news);

} catch (PDOException $e) {
    error_log("News Query Error: " . $e->getMessage());
}

// Pagination
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage = 10;
$totalPages = ceil($total / $perPage);
$offset = ($page - 1) * $perPage;

// Apply pagination
$news = array_slice($news, $offset, $perPage);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> - Admin Panel</title>
    <link rel="stylesheet" href="assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>

    <main class="main-content">
        <header class="topbar">
            <div class="topbar-left">
                <button class="sidebar-toggle" onclick="toggleSidebar()">
                    <i class="fas fa-bars"></i>
                </button>
                <h1 class="page-title">Kelola Berita</h1>
            </div>
            <div class="topbar-right">
                <a href="news-add.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    <span>Tambah Berita</span>
                </a>
            </div>
        </header>

        <div class="content-wrapper">
            <?php if ($message): ?>
            <div class="alert alert-<?= $messageType === 'success' ? 'success' : 'danger' ?>">
                <i class="fas fa-<?= $messageType === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
                <?= htmlspecialchars($message) ?>
            </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-list"></i> Daftar Berita</h3>
                    <div class="card-actions">
                        <span class="badge badge-primary"><?= $total ?> berita</span>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (empty($news)): ?>
                    <div class="empty-state">
                        <i class="fas fa-newspaper"></i>
                        <p>Belum ada berita. Silakan tambah berita baru.</p>
                        <a href="news-add.php" class="btn btn-primary" style="margin-top: 15px;">
                            <i class="fas fa-plus"></i> Tambah Berita
                        </a>
                    </div>
                    <?php else: ?>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th width="5%">No</th>
                                    <th width="40%">Judul</th>
                                    <th width="15%">Kategori</th>
                                    <th width="15%">Penulis</th>
                                    <th width="10%">Status</th>
                                    <th width="15%" class="text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = $offset + 1; foreach ($news as $item): ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td>
                                        <strong><?= htmlspecialchars($item['title'] ?? 'Tanpa judul') ?></strong>
                                        <br>
                                        <small class="text-muted">
                                            <i class="far fa-calendar"></i>
                                            <?= date('d/m/Y H:i', strtotime($item['created_at'] ?? 'now')) ?>
                                        </small>
                                    </td>
                                    <td>
                                        <span class="badge badge-secondary"><?= htmlspecialchars($item['category'] ?? '-') ?></span>
                                    </td>
                                    <td><?= htmlspecialchars($item['author_name'] ?? '-') ?></td>
                                    <td>
                                        <span class="badge badge-<?= ($item['status'] ?? 'draft') === 'published' ? 'success' : 'warning' ?>">
                                            <?= ($item['status'] ?? 'draft') === 'published' ? 'Terbit' : 'Draft' ?>
                                        </span>
                                    </td>
                                    <td class="text-right">
                                        <a href="news-edit.php?id=<?= $item['id'] ?? '' ?>" class="btn-icon" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="news.php?action=delete&id=<?= $item['id'] ?? '' ?>"
                                           class="btn-icon text-danger"
                                           title="Hapus"
                                           onclick="return confirmDelete('Apakah Anda yakin ingin menghapus berita ini?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <?php if ($totalPages > 1): ?>
                    <div class="pagination" style="margin-top: 20px; display: flex; justify-content: center; gap: 10px;">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a href="?page=<?= $i ?>"
                           class="badge <?= $i === $page ? 'badge-primary' : 'badge-secondary' ?>"
                           style="text-decoration: none; padding: 8px 12px;"><?= $i ?></a>
                        <?php endfor; ?>
                    </div>
                    <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <script src="assets/js/admin.js"></script>
</body>
</html>
