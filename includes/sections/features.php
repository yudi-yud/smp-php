<?php
/**
 * Features Section - Keunggulan sekolah
 */

// Fetch features from database
$features = getFeatures(6);

// Fallback to default data if database is empty
if (empty($features)) {
    $features = [
        [
            'icon' => '📚',
            'title' => 'Kurikulum Berkualitas',
            'description' => 'Menerapkan kurikulum terkini yang mengintegrasikan pengetahuan akademis dengan pengembangan karakter dan keterampilan abad 21.'
        ],
        [
            'icon' => '👨‍🏫',
            'title' => 'Guru Berpengalaman',
            'description' => 'Tenaga pendidik profesional dan berdedikasi tinggi yang siap membimbing siswa mencapai prestasi terbaik mereka.'
        ],
        [
            'icon' => '🏆',
            'title' => 'Prestasi Gemilang',
            'description' => 'Siswa kami rutin meraih prestasi di berbagai kompetisi akademik dan non-akademik tingkat kabupaten hingga nasional.'
        ],
        [
            'icon' => '💻',
            'title' => 'Fasilitas Modern',
            'description' => 'Dilengkapi laboratorium komputer, perpustakaan, dan ruang multimedia untuk mendukung pembelajaran interaktif.'
        ],
        [
            'icon' => '🎨',
            'title' => 'Ekstrakurikuler Beragam',
            'description' => 'Berbagai pilihan kegiatan ekstrakurikuler untuk mengembangkan bakat, minat, dan kepribadian siswa secara holistik.'
        ],
        [
            'icon' => '🤝',
            'title' => 'Lingkungan Positif',
            'description' => 'Menciptakan suasana sekolah yang aman, nyaman, dan mendukung perkembangan sosial-emosional siswa.'
        ]
    ];
}
?>

<section class="features" id="tentang">
    <div class="section-header">
        <div class="section-label">Keunggulan Kami</div>
        <h2 class="section-title">Mengapa Memilih SMPN 3?</h2>
        <p class="section-description">Kami menyediakan lingkungan belajar yang kondusif dengan fasilitas modern dan tenaga pendidik profesional untuk mengembangkan potensi setiap siswa.</p>
    </div>
    <div class="features-grid">
        <?php foreach ($features as $feature): ?>
            <div class="feature-card">
                <div class="feature-icon"><?= htmlspecialchars($feature['icon'] ?? '') ?></div>
                <h3 class="feature-title"><?= htmlspecialchars($feature['title'] ?? '') ?></h3>
                <p class="feature-description"><?= htmlspecialchars($feature['description'] ?? '') ?></p>
            </div>
        <?php endforeach; ?>
    </div>
</section>
