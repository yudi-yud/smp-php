<?php
/**
 * Footer Section
 */
?>

<footer>
    <div class="footer-content">
        <div class="footer-about">
            <h3><?= SITE_NAME ?></h3>
            <p>Lembaga pendidikan menengah pertama yang berkomitmen mencetak generasi unggul dengan pendidikan berkualitas dan pembinaan karakter yang kuat.</p>
            <p><strong>Alamat:</strong><br><?= str_replace(', ', ',<br>', SCHOOL_ADDRESS) ?></p>
        </div>
        <div class="footer-section">
            <h4>Navigasi</h4>
            <ul class="footer-links">
                <li><a href="#beranda">Beranda</a></li>
                <li><a href="#tentang">Tentang Kami</a></li>
                <li><a href="#program">Program</a></li>
                <li><a href="#kontak">Kontak</a></li>
            </ul>
        </div>
        <div class="footer-section">
            <h4>Informasi</h4>
            <ul class="footer-links">
                <li><a href="#">PPDB</a></li>
                <li><a href="#">Prestasi</a></li>
                <li><a href="#">Galeri</a></li>
                <li><a href="#">Berita</a></li>
            </ul>
        </div>
        <div class="footer-section">
            <h4>Kontak Kami</h4>
            <ul class="footer-links">
                <li>📞 <?= SCHOOL_PHONE ?></li>
                <li>📱 <?= SCHOOL_WHATSAPP ?></li>
                <li>✉️ <?= SCHOOL_EMAIL ?></li>
            </ul>
            <div class="footer-hours">
                <h5>Jam Operasional</h5>
                <p>Senin - Kamis: 06.30 - 15.00 WIB<br>Jumat: 06.30 - 11.30 WIB<br>Sabtu: 07.00 - 13.00 WIB</p>
            </div>
            <div class="footer-accreditation">
                <h5>Akreditasi: <?= ACCREDITATION ?></h5>
                <p>NPSN: <?= NPSN ?><br>NISN: <?= NISN ?></p>
            </div>
        </div>
        <div class="footer-section">
            <h4>Lokasi</h4>
            <div class="footer-map">
                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3322.778887862278!2d108.79924867414917!3d-7.4181161730615806!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e6581d470881649%3A0x76a0729e709f697c!2sSMP%20N%203%20SATAP%20CIPARI!5e1!3m2!1sid!2sid!4v1769488025461!5m2!1sid!2sid" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
            </div>
            <ul class="footer-links" style="margin-top: 1rem;">
                <li><a href="https://www.facebook.com/smpn3cipari" target="_blank">📘 Facebook</a></li>
                <li><a href="https://www.instagram.com/smpn3cipari" target="_blank">📷 Instagram</a></li>
                <li><a href="https://www.youtube.com/@smpn3cipari" target="_blank">▶️ YouTube</a></li>
            </ul>
        </div>
    </div>
    <div class="footer-bottom">
        <p>&copy; <?= date('Y') ?> <?= SITE_NAME ?>. All Rights Reserved.</p>
    </div>
</footer>

<script src="assets/js/script.js"></script>
</body>
</html>
