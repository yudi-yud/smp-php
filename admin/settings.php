<?php

/**
 * Settings Page
 * SMPN 3 Satu Atap Cipari
 */

require_once 'auth.php';
requireAuth();
require_once '../config/database.php';

$page_title = 'Pengaturan';

$message = '';
$messageType = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
    $settings = [
        // General Settings
        'site_name' => sanitize($_POST['site_name'] ?? ''),
        'site_title' => sanitize($_POST['site_title'] ?? ''),
        'school_address' => sanitize($_POST['school_address'] ?? ''),
        'school_phone' => sanitize($_POST['school_phone'] ?? ''),
        'school_email' => sanitize($_POST['school_email'] ?? ''),
        'principal_name' => sanitize($_POST['principal_name'] ?? ''),
        'accreditation' => sanitize($_POST['accreditation'] ?? ''),
        // Social Media
        'facebook_url' => sanitize($_POST['facebook_url'] ?? ''),
        'instagram_url' => sanitize($_POST['instagram_url'] ?? ''),
        'youtube_url' => sanitize($_POST['youtube_url'] ?? ''),
    ];

    try {
        $pdo = getDB();

        // Check if settings record exists
        $stmt = $pdo->query("SELECT COUNT(*) FROM site_settings");
        $exists = $stmt->fetchColumn() > 0;

        if ($exists) {
            $sql = "UPDATE site_settings SET site_name = ?, site_title = ?, school_address = ?, school_phone = ?, school_email = ?, principal_name = ?, accreditation = ?, facebook_url = ?, instagram_url = ?, youtube_url = ?";
            $params = [$settings['site_name'], $settings['site_title'], $settings['school_address'], $settings['school_phone'], $settings['school_email'], $settings['principal_name'], $settings['accreditation'], $settings['facebook_url'], $settings['instagram_url'], $settings['youtube_url']];
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
        } else {
            $sql = "INSERT INTO site_settings (site_name, site_title, school_address, school_phone, school_email, principal_name, accreditation, facebook_url, instagram_url, youtube_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $params = [$settings['site_name'], $settings['site_title'], $settings['school_address'], $settings['school_phone'], $settings['school_email'], $settings['principal_name'], $settings['accreditation'], $settings['facebook_url'], $settings['instagram_url'], $settings['youtube_url']];
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
        }

        $message = 'Pengaturan berhasil disimpan.';
        $messageType = 'success';
    } catch (PDOException $e) {
        error_log("Settings Save Error: " . $e->getMessage());
        $message = 'Gagal menyimpan pengaturan.';
        $messageType = 'error';
    }
}

// Get current settings
$pdo = getDB();
$stmt = $pdo->query("SELECT * FROM site_settings LIMIT 1");
$result = $stmt->fetch();
$current_settings = $result ? $result : [];

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
</head>

<body>
    <?php include 'includes/sidebar.php'; ?>

    <main class="main-content">
        <header class="topbar">
            <div class="topbar-left">
                <button class="sidebar-toggle" onclick="toggleSidebar()">
                    <i class="fas fa-bars"></i>
                </button>
                <h1 class="page-title">Pengaturan Website</h1>
            </div>
        </header>

        <div class="content-wrapper">
            <?php if ($message): ?>
                <div class="alert alert-<?= $messageType === 'success' ? 'success' : 'danger' ?>">
                    <i class="fas fa-<?= $messageType === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">

                <!-- General Settings -->
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-cog"></i> Pengaturan Umum</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Nama Website</label>
                                <input type="text" name="site_name" class="form-control"
                                    value="<?= htmlspecialchars($current_settings['site_name'] ?? 'SMPN 3 Satu Atap Cipari') ?>">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Judul Website</label>
                                <input type="text" name="site_title" class="form-control"
                                    value="<?= htmlspecialchars($current_settings['site_title'] ?? 'SMP Negeri 3 Satu Atap Cipari') ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Alamat Sekolah</label>
                            <textarea name="school_address" class="form-control" rows="3"><?= htmlspecialchars($current_settings['school_address'] ?? 'Jl. Raya Cipari, Cilacap, Jawa Tengah') ?></textarea>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Nomor Telepon</label>
                                <input type="text" name="school_phone" class="form-control"
                                    value="<?= htmlspecialchars($current_settings['school_phone'] ?? '(0280) 123-4567') ?>">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Email</label>
                                <input type="email" name="school_email" class="form-control"
                                    value="<?= htmlspecialchars($current_settings['school_email'] ?? 'info@smpn3cipari.sch.id') ?>">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Nama Kepala Sekolah</label>
                                <input type="text" name="principal_name" class="form-control"
                                    value="<?= htmlspecialchars($current_settings['principal_name'] ?? 'Drs. H. Ahmad Fauzi, M.Pd') ?>">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Akreditasi</label>
                                <select name="accreditation" class="form-control">
                                    <option value="A" <?= ($current_settings['accreditation'] ?? 'A') === 'A' ? 'selected' : '' ?>>A</option>
                                    <option value="B" <?= ($current_settings['accreditation'] ?? '') === 'B' ? 'selected' : '' ?>>B</option>
                                    <option value="C" <?= ($current_settings['accreditation'] ?? '') === 'C' ? 'selected' : '' ?>>C</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Social Media Settings -->
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-share-alt"></i> Media Sosial</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label class="form-label"><i class="fab fa-facebook" style="color: #1877f2;"></i> URL Facebook</label>
                            <input type="url" name="facebook_url" class="form-control"
                                placeholder="https://facebook.com/smpn3cipari"
                                value="<?= htmlspecialchars($current_settings['facebook_url'] ?? '') ?>">
                        </div>

                        <div class="form-group">
                            <label class="form-label"><i class="fab fa-instagram" style="color: #e4405f;"></i> URL Instagram</label>
                            <input type="url" name="instagram_url" class="form-control"
                                placeholder="https://instagram.com/smpn3cipari"
                                value="<?= htmlspecialchars($current_settings['instagram_url'] ?? '') ?>">
                        </div>

                        <div class="form-group">
                            <label class="form-label"><i class="fab fa-youtube" style="color: #ff0000;"></i> URL YouTube</label>
                            <input type="url" name="youtube_url" class="form-control"
                                placeholder="https://youtube.com/@smpn3cipari"
                                value="<?= htmlspecialchars($current_settings['youtube_url'] ?? '') ?>">
                        </div>
                    </div>
                </div>

                <div style="display: flex; gap: 10px; margin-top: 20px;">
                    <button type="submit" name="save" value="1" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan Pengaturan
                    </button>
                </div>
            </form>
        </div>
    </main>

    <script src="assets/js/admin.js"></script>
</body>

</html>