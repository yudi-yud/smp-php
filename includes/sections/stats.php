<?php
/**
 * Stats Section - Statistik sekolah
 */

$stats = [
    ['number' => '500+', 'label' => 'Siswa Aktif'],
    ['number' => '35+', 'label' => 'Tenaga Pendidik'],
    ['number' => '15+', 'label' => 'Tahun Berpengalaman'],
    ['number' => '95%', 'label' => 'Tingkat Kelulusan']
];
?>

<section class="stats">
    <div class="stats-grid">
        <?php foreach ($stats as $stat): ?>
            <div class="stat-item">
                <span class="stat-number"><?= $stat['number'] ?></span>
                <span class="stat-label"><?= $stat['label'] ?></span>
            </div>
        <?php endforeach; ?>
    </div>
</section>
