<?php
// Prevent direct access
if (!defined('ADMIN_LOADED')) {
    define('ADMIN_LOADED', true);
}

$current_page = basename($_SERVER['PHP_SELF']);

$nav_items = [
    ['url' => 'index.php', 'icon' => 'fas fa-home', 'label' => 'Dashboard'],
    ['url' => 'news.php', 'icon' => 'fas fa-newspaper', 'label' => 'Berita'],
    ['url' => 'achievements.php', 'icon' => 'fas fa-trophy', 'label' => 'Prestasi'],
    ['url' => 'programs.php', 'icon' => 'fas fa-graduation-cap', 'label' => 'Program'],
    ['url' => 'gallery.php', 'icon' => 'fas fa-images', 'label' => 'Galeri'],
    ['url' => 'testimonials.php', 'icon' => 'fas fa-quote-left', 'label' => 'Testimoni'],
    ['url' => 'features.php', 'icon' => 'fas fa-star', 'label' => 'Keunggulan'],
    ['url' => 'contacts.php', 'icon' => 'fas fa-envelope', 'label' => 'Kotak Masuk'],
];
?>
<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <img src="../img/logo.jpeg" alt="Logo" class="sidebar-logo">
        <div class="sidebar-title">
            <h2>SMPN 3 Cipari</h2>
            <p>Admin Panel</p>
        </div>
    </div>

    <nav class="sidebar-nav">
        <ul>
            <?php foreach ($nav_items as $item): ?>
            <li>
                <a href="<?= $item['url'] ?>"
                   class="nav-link <?= $current_page === $item['url'] ? 'active' : '' ?>">
                    <i class="<?= $item['icon'] ?>"></i>
                    <span><?= $item['label'] ?></span>
                </a>
            </li>
            <?php endforeach; ?>
        </ul>
    </nav>

    <div class="sidebar-footer">
        <a href="../" target="_blank" class="sidebar-link">
            <i class="fas fa-external-link-alt"></i>
            <span>Lihat Website</span>
        </a>
        <a href="settings.php" class="sidebar-link">
            <i class="fas fa-cog"></i>
            <span>Pengaturan</span>
        </a>
        <a href="logout.php" class="sidebar-link text-danger">
            <i class="fas fa-sign-out-alt"></i>
            <span>Keluar</span>
        </a>
    </div>
</aside>

<div class="sidebar-overlay" onclick="toggleSidebar()"></div>
