<?php
/**
 * Achievements Section - Galeri prestasi
 */

// Fetch achievements from database
$achievements = getAchievements(4);

// Fallback to default data if database is empty
if (empty($achievements)) {
    $achievements = [
        ['icon' => '🏆', 'number' => '150+', 'label' => 'Piala Penghargaan'],
        ['icon' => '🥇', 'number' => '85+', 'label' => 'Juara 1 Kabupaten'],
        ['icon' => '🥈', 'number' => '45+', 'label' => 'Juara 1 Provinsi'],
        ['icon' => '🎯', 'number' => '12+', 'label' => 'Juara Nasional']
    ];
}
?>

<section class="achievements">
    <div class="achievements-container">
        <div class="section-header">
            <div class="section-label" style="color: var(--accent);">Prestasi Kami</div>
            <h2 class="section-title" style="color: white;">Galeri Prestasi</h2>
            <p class="section-description" style="color: rgba(255,255,255,0.8);">Bukti nyata komitmen kami dalam mencetak generasi unggul dan berprestasi.</p>
        </div>
        <div class="achievements-grid">
            <?php foreach ($achievements as $achievement): ?>
                <div class="achievement-item">
                    <div class="achievement-icon"><?= htmlspecialchars($achievement['icon'] ?? '') ?></div>
                    <div class="achievement-number"><?= htmlspecialchars($achievement['number'] ?? '') ?></div>
                    <div class="achievement-label"><?= htmlspecialchars($achievement['label'] ?? '') ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
