<?php
/**
 * Testimonials Section - Kata mereka tentang kami
 */

try {
    $pdo = getDB();
    $stmt = $pdo->query("SELECT * FROM testimonials WHERE status = 'published' ORDER BY sort_order");
    $testimonials = $stmt->fetchAll();
} catch (Exception $e) {
    $testimonials = [];
}
?>

<section class="testimonials">
    <div class="testimonials-container">
        <div class="section-header">
            <div class="section-label">Testimonial</div>
            <h2 class="section-title">Kata Mereka Tentang Kami</h2>
            <p class="section-description">Apa kata siswa, orang tua, dan alumni tentang pengalaman mereka di SMPN 3 Satu Atap Cipari.</p>
        </div>
        <div class="testimonials-grid">
            <?php if (empty($testimonials)): ?>
                <p style="text-align: center; color: #666;">Belum ada testimonial.</p>
            <?php else: ?>
                <?php foreach ($testimonials as $testimonial): ?>
                    <div class="testimonial-card">
                        <div class="testimonial-rating">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="fas fa-star" style="color: <?= $i <= ($testimonial['rating'] ?? 5) ? '#f59e0b' : '#e2e8f0' ?>; font-size: 0.9rem;"></i>
                            <?php endfor; ?>
                        </div>
                        <p class="testimonial-text">"<?= htmlspecialchars($testimonial['content']) ?>"</p>
                        <div class="testimonial-author">
                            <?php if (!empty($testimonial['image'])): ?>
                                <img src="<?= htmlspecialchars($testimonial['image']) ?>" alt="<?= htmlspecialchars($testimonial['name']) ?>" class="testimonial-avatar-img">
                            <?php else: ?>
                                <div class="testimonial-avatar"><?= strtoupper(substr($testimonial['name'] ?? '?', 0, 1)) ?></div>
                            <?php endif; ?>
                            <div class="testimonial-info">
                                <h4><?= htmlspecialchars($testimonial['name']) ?></h4>
                                <span><?= htmlspecialchars($testimonial['role'] ?? '') ?></span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>
