<?php
/**
 * Contact Section - Formulir kontak dan informasi
 */

$contact_info = [
    [
        'icon' => '📍',
        'title' => 'Alamat',
        'content' => SCHOOL_ADDRESS
    ],
    [
        'icon' => '📞',
        'title' => 'Telepon',
        'content' => SCHOOL_PHONE
    ],
    [
        'icon' => '📱',
        'title' => 'WhatsApp',
        'content' => SCHOOL_WHATSAPP
    ],
    [
        'icon' => '✉️',
        'title' => 'Email',
        'content' => SCHOOL_EMAIL
    ]
];
?>

<section class="contact-section" id="kontak">
    <div class="section-header">
        <div class="section-label">Hubungi Kami</div>
        <h2 class="section-title">Bergabunglah Bersama Kami!</h2>
        <p class="section-description">Punya pertanyaan atau ingin mendaftar? Hubungi kami melalui formulir di bawah ini.</p>
    </div>
    <div class="contact-container">
        <div class="contact-info-box">
            <h3>Informasi Kontak</h3>
            <?php foreach ($contact_info as $info): ?>
                <div class="contact-info-item">
                    <div class="icon"><?= $info['icon'] ?></div>
                    <div>
                        <h4><?= $info['title'] ?></h4>
                        <p><?= $info['content'] ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
            <div class="social-links">
                <a href="#" title="Facebook">📘</a>
                <a href="#" title="Instagram">📷</a>
                <a href="#" title="YouTube">▶️</a>
                <a href="#" title="Twitter">🐦</a>
            </div>
        </div>
        <div class="contact-form-box">
            <h3>Formulir Pesan</h3>
            <form class="contact-form" action="process_contact.php" method="POST">
                <div class="form-group">
                    <label for="nama">Nama Lengkap *</label>
                    <input type="text" id="nama" name="nama" required placeholder="Masukkan nama lengkap">
                </div>
                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" required placeholder="Masukkan email">
                </div>
                <div class="form-group">
                    <label for="telepon">No. Telepon</label>
                    <input type="tel" id="telepon" name="telepon" placeholder="Masukkan no. telepon">
                </div>
                <div class="form-group">
                    <label for="keperluan">Keperluan *</label>
                    <select id="keperluan" name="keperluan" required>
                        <option value="">Pilih keperluan</option>
                        <option value="ppdb">Informasi PPDB</option>
                        <option value="pertanyaan">Pertanyaan Umum</option>
                        <option value="kerjasama">Kerjasama</option>
                        <option value="lainnya">Lainnya</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="pesan">Pesan *</label>
                    <textarea id="pesan" name="pesan" required placeholder="Tulis pesan Anda di sini"></textarea>
                </div>
                <button type="submit" class="form-submit">Kirim Pesan</button>
            </form>
        </div>
    </div>
</section>
