<?php
/**
 * News Section - Berita & pengumuman
 */

// Fetch news items from database
$news_items = getNews(3);

// Fallback to default data if database is empty
if (empty($news_items)) {
    $news_items = [
        [
            'icon' => '📢',
            'date' => '25 Jan 2026',
            'title' => 'Penerimaan Peserta Didik Baru (PPDB) 2026/2027',
            'description' => 'SMPN 3 Satu Atap Cipari membuka pendaftaran siswa baru untuk tahun ajaran 2026/2027. Segera daftarkan putra-putri Anda.',
            'link' => '#'
        ],
        [
            'icon' => '🏆',
            'date' => '20 Jan 2026',
            'title' => 'Siswa Kami Raih Juara 1 Olimpiade Sains Kabupaten',
            'description' => 'Selamat kepada Ahmad Fauzi yang berhasil meraih juara 1 dalam Olimpiade Sains tingkat kabupaten.',
            'link' => '#'
        ],
        [
            'icon' => '🏃',
            'date' => '15 Jan 2026',
            'title' => 'Pekan Olahraga Sekolah Berlangsung Meriah',
            'description' => 'Kegiatan tahunan Pekan Olahraga Sekolah berlangsung dengan antusias dan diikuti oleh seluruh siswa.',
            'link' => '#'
        ]
    ];
}
?>

<section class="news">
    <div class="news-container">
        <div class="section-header">
            <div class="section-label">Berita</div>
            <h2 class="section-title">Berita & Pengumuman</h2>
            <p class="section-description">Informasi terbaru seputar kegiatan dan prestasi sekolah.</p>
        </div>
        <div class="news-grid">
            <?php foreach ($news_items as $news): ?>
                <?php
                // Format date if it's a timestamp, otherwise use as-is
                $newsDate = isset($news['published_at']) ? date('d M Y', strtotime($news['published_at'])) : ($news['date'] ?? '');
                $newsLink = isset($news['slug']) ? 'berita/' . htmlspecialchars($news['slug']) : (isset($news['link']) ? htmlspecialchars($news['link']) : '#');
                ?>
                <div class="news-card">
                    <div class="news-image">
                        <?= htmlspecialchars($news['icon'] ?? '') ?>
                        <span class="news-date"><?= htmlspecialchars($newsDate) ?></span>
                    </div>
                    <div class="news-content">
                        <h3><?= htmlspecialchars($news['title'] ?? '') ?></h3>
                        <p><?= htmlspecialchars($news['description'] ?? '') ?></p>
                        <a href="<?= $newsLink ?>" class="news-link">Baca Selengkapnya →</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
