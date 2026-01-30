<?php
/**
 * Gallery Section - Dokumentasi kegiatan
 */

// Fetch gallery items from database
$gallery_items = getGallery(6);

// Fallback to default data if database is empty
if (empty($gallery_items)) {
    $gallery_items = [
        ['image' => 'img/poto1.jpg', 'title' => 'Kegiatan Belajar Mengajar', 'class' => 'large', 'placeholder' => '📚'],
        ['image' => 'img/poto2.jpg', 'title' => 'Kegiatan Siswa', 'class' => '', 'placeholder' => '👨‍🎓'],
        ['image' => 'img/poto3.jpg', 'title' => 'Dokumentasi Sekolah', 'class' => '', 'placeholder' => '🏫'],
        ['image' => 'img/foto-sekolah.jpeg', 'title' => 'Suasana Sekolah', 'class' => 'wide', 'placeholder' => '🏫'],
        ['image' => 'img/poto1.jpg', 'title' => 'Ekstrakurikuler', 'class' => '', 'placeholder' => '🎨'],
        ['image' => 'img/poto2.jpg', 'title' => 'Kegiatan Lainnya', 'class' => '', 'placeholder' => '📸']
    ];
}
?>

<section class="gallery">
    <div class="section-header">
        <div class="section-label">Galeri</div>
        <h2 class="section-title">Dokumentasi Kegiatan</h2>
        <p class="section-description">Intip berbagai kegiatan dan fasilitas di SMPN 3 Satu Atap Cipari.</p>
    </div>
    <div class="gallery-grid">
        <?php foreach ($gallery_items as $item): ?>
            <?php
            // Set default class if not in database
            $itemClass = isset($item['class']) ? htmlspecialchars($item['class']) : '';
            $itemPlaceholder = isset($item['placeholder']) ? htmlspecialchars($item['placeholder']) : '📷';
            ?>
            <div class="gallery-item <?= $itemClass ?>">
                <img src="<?= htmlspecialchars($item['image'] ?? '') ?>" alt="<?= htmlspecialchars($item['title'] ?? '') ?>" onerror="this.parentElement.innerHTML='<div class=\'gallery-placeholder\'><?= $itemPlaceholder ?></div>'">
                <div class="gallery-overlay">
                    <h4><?= htmlspecialchars($item['title'] ?? '') ?></h4>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>
