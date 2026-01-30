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
        $stmt = $pdo->prepare("DELETE FROM contacts WHERE id = ?");
        $stmt->execute([$id]);
        $message = 'Pesan berhasil dihapus.';
        $messageType = 'success';
    } catch (PDOException $e) {
        $message = 'Gagal menghapus pesan.';
        $messageType = 'error';
    }
}

if ($action === 'mark-read' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    try {
        $pdo = getDB();
        $stmt = $pdo->prepare("UPDATE contacts SET status = 'read' WHERE id = ?");
        $stmt->execute([$id]);
        $message = 'Pesan ditandai sebagai dibaca.';
        $messageType = 'success';
    } catch (PDOException $e) {
        $message = 'Gagal memperbarui status.';
        $messageType = 'error';
    }
}

// Get filter
$status_filter = $_GET['status'] ?? '';

// Get contacts list
$pdo = getDB();

if ($status_filter) {
    $stmt = $pdo->prepare("SELECT * FROM contacts WHERE status = ? ORDER BY created_at DESC");
    $stmt->execute([$status_filter]);
    $contacts = $stmt->fetchAll();
} else {
    $stmt = $pdo->query("SELECT * FROM contacts ORDER BY created_at DESC");
    $contacts = $stmt->fetchAll();
}

// Get message detail
$detail = null;
if (isset($_GET['view'])) {
    $stmt = $pdo->prepare("SELECT * FROM contacts WHERE id = ?");
    $stmt->execute([(int)$_GET['view']]);
    $detail = $stmt->fetch();

    // Mark as read if viewing new message
    if ($detail && ($detail['status'] ?? 'new') === 'new') {
        $stmt = $pdo->prepare("UPDATE contacts SET status = 'read' WHERE id = ?");
        $stmt->execute([(int)$_GET['view']]);
        $detail['status'] = 'read';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kotak Masuk - Admin Panel</title>
    <link rel="stylesheet" href="assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
    <?php include 'includes/sidebar.php'; ?>

    <main class="main-content">
        <header class="topbar">
            <div class="topbar-left">
                <button class="sidebar-toggle" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>
                <h1 class="page-title">Kotak Masuk</h1>
            </div>
        </header>

        <div class="content-wrapper">
            <?php if ($message): ?>
            <div class="alert alert-<?= $messageType === 'success' ? 'success' : 'danger' ?>">
                <i class="fas fa-<?= $messageType === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
                <?= htmlspecialchars($message) ?>
            </div>
            <?php endif; ?>

            <!-- Filter -->
            <div class="card" style="margin-bottom: 20px;">
                <div class="card-body" style="padding: 15px 20px;">
                    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                        <a href="contacts.php" class="badge badge-<?= $status_filter === '' ? 'primary' : 'secondary' ?>"
                           style="text-decoration: none; padding: 8px 16px;">Semua</a>
                        <a href="contacts.php?status=new" class="badge badge-<?= $status_filter === 'new' ? 'danger' : 'secondary' ?>"
                           style="text-decoration: none; padding: 8px 16px;">Baru</a>
                        <a href="contacts.php?status=read" class="badge badge-<?= $status_filter === 'read' ? 'primary' : 'secondary' ?>"
                           style="text-decoration: none; padding: 8px 16px;">Dibaca</a>
                        <a href="contacts.php?status=replied" class="badge badge-<?= $status_filter === 'replied' ? 'success' : 'secondary' ?>"
                           style="text-decoration: none; padding: 8px 16px;">Dibalas</a>
                    </div>
                </div>
            </div>

            <!-- Message Detail Modal -->
            <?php if ($detail): ?>
            <div class="card" style="margin-bottom: 20px;">
                <div class="card-header">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <h3><i class="fas fa-envelope-open"></i> Detail Pesan</h3>
                            <p style="margin: 5px 0 0 0; color: var(--text-secondary); font-size: 0.9rem;">
                                Dari: <strong><?= htmlspecialchars($detail['name']) ?></strong>
                                (<?= htmlspecialchars($detail['email']) ?>)
                            </p>
                        </div>
                        <a href="contacts.php" class="btn btn-secondary btn-sm">
                            <i class="fas fa-times"></i> Tutup
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div style="margin-bottom: 20px;">
                        <span class="badge badge-<?= ($detail['status'] ?? 'new') === 'new' ? 'danger' : (($detail['status'] ?? 'new') === 'read' ? 'primary' : 'success') ?>">
                            <?= ($detail['status'] ?? 'new') === 'new' ? 'Baru' : (($detail['status'] ?? 'new') === 'read' ? 'Dibaca' : 'Dibalas') ?>
                        </span>
                        <span style="color: var(--text-secondary); margin-left: 10px;">
                            <i class="far fa-clock"></i>
                            <?= date('d/m/Y H:i', strtotime($detail['created_at'])) ?>
                        </span>
                    </div>

                    <?php if ($detail['subject']): ?>
                    <h4 style="margin-bottom: 15px;">Subjek: <?= htmlspecialchars($detail['subject']) ?></h4>
                    <?php endif; ?>

                    <div style="background: var(--background); padding: 20px; border-radius: 8px; line-height: 1.6;">
                        <?= nl2br(htmlspecialchars($detail['message'])) ?>
                    </div>

                    <div style="margin-top: 20px; display: flex; gap: 10px;">
                        <a href="mailto:<?= htmlspecialchars($detail['email']) ?>"
                           class="btn btn-primary">
                            <i class="fas fa-reply"></i> Balas Email
                        </a>
                        <a href="?action=mark-read&id=<?= $detail['id'] ?>"
                           class="btn btn-secondary">
                            <i class="fas fa-check"></i> Tandai Dibaca
                        </a>
                        <a href="?action=delete&id=<?= $detail['id'] ?>"
                           class="btn btn-danger"
                           onclick="return confirmDelete()">
                            <i class="fas fa-trash"></i> Hapus
                        </a>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- List -->
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-inbox"></i> Daftar Pesan</h3>
                    <span class="badge badge-primary"><?= count($contacts) ?> pesan</span>
                </div>
                <div class="card-body">
                    <?php if (empty($contacts)): ?>
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <p>Tidak ada pesan.</p>
                    </div>
                    <?php else: ?>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th width="5%">No</th>
                                    <th width="25%">Nama</th>
                                    <th width="35%">Subjek</th>
                                    <th width="15%">Tanggal</th>
                                    <th width="10%">Status</th>
                                    <th width="10%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1; foreach ($contacts as $item): ?>
                                <tr class="<?= ($item['status'] ?? 'new') === 'new' ? 'table-primary' : '' ?>">
                                    <td><?= $no++ ?></td>
                                    <td>
                                        <strong <?= ($item['status'] ?? 'new') === 'new' ? 'style="color: var(--primary-color);"' : '' ?>>
                                            <?= htmlspecialchars($item['name']) ?>
                                        </strong>
                                        <br><small class="text-muted"><?= htmlspecialchars($item['email']) ?></small>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($item['subject'] ?: '(Tanpa subjek)') ?>
                                        <br><small class="text-muted"><?= htmlspecialchars(substr($item['message'], 0, 50)) ?>...</small>
                                    </td>
                                    <td>
                                        <small><?= date('d/m/Y', strtotime($item['created_at'])) ?></small>
                                        <br><small class="text-muted"><?= date('H:i', strtotime($item['created_at'])) ?></small>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?= ($item['status'] ?? 'new') === 'new' ? 'danger' : (($item['status'] ?? 'new') === 'read' ? 'primary' : 'success') ?>">
                                            <?= ($item['status'] ?? 'new') === 'new' ? 'Baru' : (($item['status'] ?? 'new') === 'read' ? 'Dibaca' : 'Dibalas') ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="?view=<?= $item['id'] ?>" class="btn-icon" title="Lihat Detail">
                                            <i class="fas fa-eye"></i>
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
