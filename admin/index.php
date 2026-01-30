<?php
/**
 * Admin Dashboard
 * SMPN 3 Satu Atap Cipari
 */

require_once 'auth.php';
requireAuth();

require_once '../config/database.php';
$page_title = 'Dashboard';

// Get statistics
$stats = [
    'news' => 0,
    'achievements' => 0,
    'programs' => 0,
    'gallery' => 0,
    'testimonials' => 0,
    'contacts' => 0
];

try {
    $pdo = getDB();

    // Count published items
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM news WHERE status = 'published'");
    $stmt->execute();
    $stats['news'] = $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM achievements WHERE status = 'published'");
    $stmt->execute();
    $stats['achievements'] = $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM programs WHERE status = 'published'");
    $stmt->execute();
    $stats['programs'] = $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM gallery WHERE status = 'published'");
    $stmt->execute();
    $stats['gallery'] = $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM testimonials WHERE status = 'published'");
    $stmt->execute();
    $stats['testimonials'] = $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM contacts WHERE status = 'new'");
    $stmt->execute();
    $stats['contacts'] = $stmt->fetchColumn();

} catch (PDOException $e) {
    error_log("Dashboard Error: " . $e->getMessage());
}

// Get recent activity
$recent_news = [];
$recent_contacts = [];

try {
    $pdo = getDB();

    // Get recent news
    $stmt = $pdo->query("SELECT id, title, created_at FROM news ORDER BY created_at DESC LIMIT 5");
    $recent_news = $stmt->fetchAll();

    // Get recent contacts
    $stmt = $pdo->query("SELECT id, name, subject, created_at, status FROM contacts ORDER BY created_at DESC LIMIT 5");
    $recent_contacts = $stmt->fetchAll();

} catch (PDOException $e) {
    error_log("Recent Activity Error: " . $e->getMessage());
}

// Get system info
$system_info = [
    'php_version' => phpversion(),
    'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
    'storage_type' => 'MySQL Database'
];
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
                <h1 class="page-title">Dashboard</h1>
            </div>
            <div class="topbar-right">
                <a href="../" target="_blank" class="btn-view-site">
                    <i class="fas fa-external-link-alt"></i>
                    <span>Lihat Website</span>
                </a>
                <div class="user-menu">
                    <button class="user-dropdown" onclick="toggleUserMenu()">
                        <img src="../img/logo.jpeg" alt="Admin" class="user-avatar">
                        <span class="user-name"><?= htmlspecialchars($_SESSION['admin_name']) ?></span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="user-dropdown-menu" id="userDropdown">
                        <div class="user-info">
                            <p class="user-name-full"><?= htmlspecialchars($_SESSION['admin_name']) ?></p>
                            <p class="user-role"><?= htmlspecialchars(ucfirst($_SESSION['admin_role'])) ?></p>
                        </div>
                        <hr>
                        <a href="profile.php" class="dropdown-item">
                            <i class="fas fa-user"></i> Profil
                        </a>
                        <a href="settings.php" class="dropdown-item">
                            <i class="fas fa-cog"></i> Pengaturan
                        </a>
                        <hr>
                        <a href="logout.php" class="dropdown-item text-danger">
                            <i class="fas fa-sign-out-alt"></i> Keluar
                        </a>
                    </div>
                </div>
            </div>
        </header>

        <div class="content-wrapper">
            <!-- Welcome Section -->
            <div class="welcome-banner">
                <div class="welcome-content">
                    <h2>Selamat Datang, <?= htmlspecialchars(explode(' ', $_SESSION['admin_name'])[0]) ?>! 👋</h2>
                    <p>Berikut adalah ringkasan aktivitas website SMPN 3 Satu Atap Cipari.</p>
                </div>
                <div class="welcome-time">
                    <i class="fas fa-calendar-alt"></i>
                    <span><?= date('d F Y') ?></span>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon" style="background: #dbeafe; color: #2563eb;">
                        <i class="fas fa-newspaper"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?= number_format($stats['news']) ?></h3>
                        <p>Berita Terbit</p>
                    </div>
                    <a href="news.php" class="stat-link">
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>

                <div class="stat-card">
                    <div class="stat-icon" style="background: #fef3c7; color: #d97706;">
                        <i class="fas fa-trophy"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?= number_format($stats['achievements']) ?></h3>
                        <p>Prestasi</p>
                    </div>
                    <a href="achievements.php" class="stat-link">
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>

                <div class="stat-card">
                    <div class="stat-icon" style="background: #dcfce7; color: #22c55e;">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?= number_format($stats['programs']) ?></h3>
                        <p>Program</p>
                    </div>
                    <a href="programs.php" class="stat-link">
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>

                <div class="stat-card">
                    <div class="stat-icon" style="background: #fce7f3; color: #ec4899;">
                        <i class="fas fa-images"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?= number_format($stats['gallery']) ?></h3>
                        <p>Galeri</p>
                    </div>
                    <a href="gallery.php" class="stat-link">
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>

                <div class="stat-card">
                    <div class="stat-icon" style="background: #e0e7ff; color: #6366f1;">
                        <i class="fas fa-quote-left"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?= number_format($stats['testimonials']) ?></h3>
                        <p>Testimoni</p>
                    </div>
                    <a href="testimonials.php" class="stat-link">
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>

                <div class="stat-card">
                    <div class="stat-icon" style="background: #fee2e2; color: #ef4444;">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?= number_format($stats['contacts']) ?></h3>
                        <p>Pesan Baru</p>
                    </div>
                    <a href="contacts.php" class="stat-link">
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="dashboard-row">
                <div class="dashboard-col-2">
                    <div class="card">
                        <div class="card-header">
                            <h3><i class="fas fa-newspaper"></i> Berita Terbaru</h3>
                            <a href="news.php" class="btn-view-all">Lihat Semua</a>
                        </div>
                        <div class="card-body">
                            <?php if (empty($recent_news)): ?>
                            <div class="empty-state">
                                <i class="fas fa-inbox"></i>
                                <p>Belum ada berita</p>
                            </div>
                            <?php else: ?>
                            <div class="list-group">
                                <?php foreach ($recent_news as $item): ?>
                                <div class="list-item">
                                    <div class="list-item-icon">
                                        <i class="fas fa-file-alt"></i>
                                    </div>
                                    <div class="list-item-content">
                                        <h4><?= htmlspecialchars($item['title'] ?? 'Tanpa judul') ?></h4>
                                        <p><i class="far fa-clock"></i> <?= date('d/m/Y H:i', strtotime($item['created_at'] ?? 'now')) ?></p>
                                    </div>
                                    <a href="news-edit.php?id=<?= $item['id'] ?? '' ?>" class="btn-icon" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="dashboard-col-1">
                    <div class="card">
                        <div class="card-header">
                            <h3><i class="fas fa-envelope"></i> Pesan Terbaru</h3>
                            <a href="contacts.php" class="btn-view-all">Lihat Semua</a>
                        </div>
                        <div class="card-body">
                            <?php if (empty($recent_contacts)): ?>
                            <div class="empty-state">
                                <i class="fas fa-inbox"></i>
                                <p>Belum ada pesan</p>
                            </div>
                            <?php else: ?>
                            <div class="list-group compact">
                                <?php foreach ($recent_contacts as $item): ?>
                                <div class="list-item">
                                    <div class="list-item-icon">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <div class="list-item-content">
                                        <h4><?= htmlspecialchars($item['name'] ?? 'Tanpa nama') ?></h4>
                                        <p><?= htmlspecialchars($item['subject'] ?? 'Tanpa subjek') ?></p>
                                    </div>
                                    <span class="badge badge-<?= ($item['status'] ?? 'new') === 'new' ? 'danger' : 'secondary' ?>">
                                        <?= ($item['status'] ?? 'new') === 'new' ? 'Baru' : ucfirst($item['status'] ?? '') ?>
                                    </span>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h3><i class="fas fa-server"></i> Sistem</h3>
                        </div>
                        <div class="card-body">
                            <div class="system-info">
                                <div class="info-row">
                                    <span class="info-label">PHP Version</span>
                                    <span class="info-value"><?= $system_info['php_version'] ?></span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Penyimpanan</span>
                                    <span class="info-value"><?= $system_info['storage_type'] ?></span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Server</span>
                                    <span class="info-value"><?= $system_info['server_software'] ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="assets/js/admin.js"></script>
</body>
</html>
