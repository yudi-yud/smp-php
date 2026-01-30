<?php
/**
 * Programs Section - Program unggulan sekolah
 */

// Fetch programs from database
$programs = getPrograms(4);

// Fallback to default data if database is empty
if (empty($programs)) {
    $programs = [
        [
            'image' => 'img/literasi.png',
            'title' => 'Program Literasi Digital',
            'description' => 'Meningkatkan kemampuan siswa dalam memanfaatkan teknologi informasi secara bijak dan bertanggung jawab.',
            'features' => ['Pembelajaran berbasis teknologi', 'Pelatihan coding dasar', 'Media pembelajaran interaktif', 'Internet sehat dan aman']
        ],
        [
            'image' => 'img/pengembangan-karakter.jpg',
            'title' => 'Pengembangan Karakter',
            'description' => 'Membentuk kepribadian siswa yang berakhlak mulia, jujur, disiplin, dan bertanggung jawab.',
            'features' => ['Kegiatan keagamaan rutin', 'Pendidikan budi pekerti', 'Leadership training', 'Kepedulian sosial']
        ],
        [
            'image' => 'img/sains.jpg',
            'title' => 'Olimpiade Sains',
            'description' => 'Mempersiapkan siswa untuk berkompetisi dalam olimpiade sains tingkat kabupaten, provinsi, dan nasional.',
            'features' => ['Pembinaan matematika', 'Pembinaan IPA', 'Bimbingan intensif', 'Try out berkala']
        ],
        [
            'image' => 'img/olahraga.jpeg',
            'title' => 'Olahraga & Seni',
            'description' => 'Mengembangkan bakat dan minat siswa di bidang olahraga dan seni budaya.',
            'features' => ['Sepak bola & bola voli', 'Seni musik & tari', 'Pramuka', 'Pencak silat']
        ]
    ];
}
?>

<section class="programs" id="program">
    <div class="programs-container">
        <div class="section-header">
            <div class="section-label">Program Unggulan</div>
            <h2 class="section-title">Program Kami</h2>
            <p class="section-description">Berbagai program inovatif dirancang untuk mempersiapkan siswa menghadapi tantangan masa depan dengan percaya diri.</p>
        </div>
        <div class="programs-grid">
            <?php foreach ($programs as $program): ?>
                <?php
                // Parse features if stored as JSON or use directly
                $featuresList = isset($program['features']) ? json_decode($program['features'], true) : [];
                if (!is_array($featuresList)) {
                    $featuresList = [];
                }
                ?>
                <div class="program-card">
                    <div class="program-image">
                        <img src="<?= htmlspecialchars($program['image'] ?? '') ?>" alt="<?= htmlspecialchars($program['title'] ?? '') ?>" style="width: 100%; height: 100%; object-fit: cover;">
                    </div>
                    <div class="program-content">
                        <h3 class="program-title"><?= htmlspecialchars($program['title'] ?? '') ?></h3>
                        <p class="program-description"><?= htmlspecialchars($program['description'] ?? '') ?></p>
                        <?php if (!empty($featuresList)): ?>
                        <ul class="program-features">
                            <?php foreach ($featuresList as $feature): ?>
                                <li><?= htmlspecialchars($feature) ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
