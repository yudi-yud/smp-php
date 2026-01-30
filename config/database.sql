-- ===============================================
-- SMPN 3 Satu Atap Cipari - Database Schema
-- ===============================================
-- Created: 2026-01-30
-- Description: Database schema for school website content management

-- Create database if not exists
CREATE DATABASE IF NOT EXISTS smpn3_cipari
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE smpn3_cipari;

-- ===============================================
-- TABLE: admin_users
-- ===============================================
CREATE TABLE IF NOT EXISTS admin_users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    role ENUM('super_admin', 'admin', 'editor') DEFAULT 'admin',
    status ENUM('active', 'inactive') DEFAULT 'active',
    last_login DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_email (email),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default admin user (password: admin123)
-- Password hash for 'admin123'
INSERT INTO admin_users (username, password, full_name, email, role) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin@smpn3cipari.sch.id', 'super_admin');

-- ===============================================
-- TABLE: site_settings
-- ===============================================
CREATE TABLE IF NOT EXISTS site_settings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    setting_type ENUM('text', 'textarea', 'number', 'boolean', 'image') DEFAULT 'text',
    category VARCHAR(50) DEFAULT 'general',
    description VARCHAR(255),
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_key (setting_key),
    INDEX idx_category (category)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default settings
INSERT INTO site_settings (setting_key, setting_value, setting_type, category, description) VALUES
('site_name', 'SMPN 3 Satu Atap Cipari', 'text', 'general', 'Nama Website'),
('site_title', 'SMP Negeri 3 Satu Atap Cipari', 'text', 'general', 'Judul Website'),
('school_address', 'Jl. Raya Cipari, Cilacap, Jawa Tengah', 'textarea', 'general', 'Alamat Sekolah'),
('school_phone', '(0280) 123-4567', 'text', 'general', 'Nomor Telepon'),
('school_email', 'info@smpn3cipari.sch.id', 'text', 'general', 'Email Sekolah'),
('principal_name', 'Drs. H. Ahmad Fauzi, M.Pd', 'text', 'general', 'Nama Kepala Sekolah'),
('accreditation', 'A', 'text', 'general', 'Akreditasi Sekolah'),
('facebook_url', 'https://facebook.com/smpn3cipari', 'text', 'social', 'URL Facebook'),
('instagram_url', 'https://instagram.com/smpn3cipari', 'text', 'social', 'URL Instagram'),
('youtube_url', 'https://youtube.com/@smpn3cipari', 'text', 'social', 'URL YouTube');

-- ===============================================
-- TABLE: news
-- ===============================================
CREATE TABLE IF NOT EXISTS news (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    excerpt TEXT,
    content MEDIUMTEXT NOT NULL,
    featured_image VARCHAR(255),
    category VARCHAR(100) DEFAULT 'umum',
    status ENUM('draft', 'published') DEFAULT 'draft',
    author_id INT UNSIGNED,
    view_count INT UNSIGNED DEFAULT 0,
    published_at DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (author_id) REFERENCES admin_users(id) ON DELETE SET NULL,
    INDEX idx_slug (slug),
    INDEX idx_status (status),
    INDEX idx_category (category),
    INDEX idx_published (published_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===============================================
-- TABLE: achievements
-- ===============================================
CREATE TABLE IF NOT EXISTS achievements (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    category ENUM('akademik', 'non_akademik', 'ekstrakurikuler') DEFAULT 'akademik',
    level ENUM('sekolah', 'kecamatan', 'kabupaten', 'provinsi', 'nasional', 'internasional') DEFAULT 'kabupaten',
    year YEAR NOT NULL,
    image VARCHAR(255),
    recipient VARCHAR(255),
    status ENUM('draft', 'published') DEFAULT 'draft',
    sort_order INT UNSIGNED DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_year (year),
    INDEX idx_category (category),
    INDEX idx_level (level),
    INDEX idx_sort (sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===============================================
-- TABLE: programs
-- ===============================================
CREATE TABLE IF NOT EXISTS programs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    description TEXT,
    icon VARCHAR(100),
    image VARCHAR(255),
    status ENUM('draft', 'published') DEFAULT 'draft',
    sort_order INT UNSIGNED DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_slug (slug),
    INDEX idx_status (status),
    INDEX idx_sort (sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===============================================
-- TABLE: gallery
-- ===============================================
CREATE TABLE IF NOT EXISTS gallery (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    image VARCHAR(255) NOT NULL,
    category VARCHAR(100) DEFAULT 'kegiatan',
    status ENUM('draft', 'published') DEFAULT 'draft',
    sort_order INT UNSIGNED DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_category (category),
    INDEX idx_sort (sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===============================================
-- TABLE: testimonials
-- ===============================================
CREATE TABLE IF NOT EXISTS testimonials (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    role VARCHAR(100),
    content TEXT NOT NULL,
    image VARCHAR(255),
    rating TINYINT UNSIGNED DEFAULT 5,
    status ENUM('draft', 'published') DEFAULT 'draft',
    sort_order INT UNSIGNED DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_sort (sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===============================================
-- TABLE: features
-- ===============================================
CREATE TABLE IF NOT EXISTS features (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    icon VARCHAR(100),
    status ENUM('draft', 'published') DEFAULT 'draft',
    sort_order INT UNSIGNED DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_sort (sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===============================================
-- TABLE: statistics
-- ===============================================
CREATE TABLE IF NOT EXISTS statistics (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    label VARCHAR(100) NOT NULL,
    value INT UNSIGNED NOT NULL,
    icon VARCHAR(100),
    status ENUM('draft', 'published') DEFAULT 'draft',
    sort_order INT UNSIGNED DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_sort (sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===============================================
-- TABLE: contacts
-- ===============================================
CREATE TABLE IF NOT EXISTS contacts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    subject VARCHAR(255),
    message TEXT NOT NULL,
    status ENUM('new', 'read', 'replied') DEFAULT 'new',
    ip_address VARCHAR(45),
    user_agent VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_email (email),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===============================================
-- Insert Sample Data
-- ===============================================

-- Sample news
INSERT INTO news (title, slug, excerpt, content, category, status, author_id) VALUES
('Penerimaan Peserta Didik Baru Tahun 2026', 'penerimaan-peserta-didik-baru-2026',
'SMPN 3 Satu Atap Cipari membuka pendaftaran peserta didik baru untuk tahun ajaran 2026/2027.',
'<p>SMPN 3 Satu Atap Cipari dengan bangga mengumumkan pembukaan pendaftaran peserta didik baru untuk tahun ajaran 2026/2027.</p><p>Persyaratan pendaftaran:</p><ul><li>Mengisi formulir pendaftaran</li><li>Fotokopi ijazah/SLTP yang dilegalisir</li><li>Fotokopi kartu keluarga</li><li>Pas foto 3x4 (4 lembar)</li></ul>',
'pendidikan', 'published', 1),
('Prestasi Siswa di Olimpiade Sains', 'prestasi-siswa-olimpiade-sains',
'Selamat kepada siswa SMPN 3 Satu Atap Cipari yang meraih juara dalam Olimpiade Sains tingkat kabupaten.',
'<p>Selamat kepada tim sains SMPN 3 Satu Atap Cipari yang berhasil meraih juara 2 dalam Olimpiade Sains tingkat kabupaten Cilacap.</p><p>Tim kami terdiri dari 3 siswa berprestasi yang telah berlatih keras selama 3 bulan.</p>',
'prestasi', 'published', 1);

-- Sample achievements
INSERT INTO achievements (title, description, category, level, year, status) VALUES
('Juara 2 Olimpiade Sains Kabupaten', 'Tim sains berhasil meraih juara 2 dalam kompetisi sains tingkat kabupaten', 'akademik', 'kabupaten', 2025, 'published'),
('Juara 1 Lomba Futsal Kecamatan', 'Tim futsal sekolah meraih juara pertama dalam kejuaraan futsal kecamatan', 'non_akademik', 'kecamatan', 2025, 'published'),
('Juara Harapan 1 Lomba Puisi Provinsi', 'Siswa kami meraih juara harapan 1 dalam lomba puisi tingkat provinsi', 'ekstrakurikuler', 'provinsi', 2025, 'published');

-- Sample programs
INSERT INTO programs (title, slug, description, icon, status) VALUES
('Program Literasi Digital', 'program-literasi-digital', 'Membekali siswa dengan kemampuan literasi digital untuk menghadapi era teknologi', 'fas fa-laptop', 'published'),
('Pembinaan Karakter', 'pembinaan-karakter', 'Membentuk karakter siswa yang berakhlak mulia dan berwawasan lingkungan', 'fas fa-heart', 'published'),
('Olimpiade Sains', 'olimpiade-sains', 'Program intensif untuk persiapan olimpiade sains tingkat kabupaten hingga nasional', 'fas fa-flask', 'published'),
('Ekstrakurikuler Olahraga & Seni', 'ekstrakurikuler-olahraga-seni', 'Berbagai pilihan kegiatan ekstrakurikuler untuk pengembangan bakat siswa', 'fas fa-futbol', 'published');

-- Sample testimonials
INSERT INTO testimonials (name, role, content, rating, status) VALUES
('Budi Santoso', 'Wali Murid Kelas 9', 'SMPN 3 Cipari sangat baik dalam membentuk karakter anak saya. Guru-gurunya sangat dedikatif.', 5, 'published'),
('Siti Aminah', 'Alumni Angkatan 2023', 'Sekolah ini memberikan fondasi pendidikan yang kuat untuk melanjutkan ke SMA.', 5, 'published'),
('Drs. H. Rahman', 'Ketua Komite Sekolah', 'Kami bangga dengan kemajuan SMPN 3 Cipari dalam berbagai bidang akademik dan non-akademik.', 5, 'published');

-- Sample features
INSERT INTO features (title, description, icon, status, sort_order) VALUES
('Kurikulum Merdeka', 'Menerapkan kurikulum merdeka yang fleksibel dan berfokus pada pengembangan potensi siswa', 'fas fa-book-open', 'published', 1),
('Guru Berkompeten', 'Tenaga pendidik profesional dan berpengalaman di bidangnya', 'fas fa-chalkboard-teacher', 'published', 2),
('Fasilitas Lengkap', 'Laboratorium, perpustakaan, dan sarana olahraga yang memadai', 'fas fa-school', 'published', 3),
('Lingkungan Asri', 'Suasana belajar yang nyaman dan lingkungan yang hijau', 'fas fa-leaf', 'published', 4);

-- Sample statistics
INSERT INTO statistics (label, value, icon, status, sort_order) VALUES
('Siswa Aktif', 324, 'fas fa-users', 'published', 1),
('Guru & Staff', 28, 'fas fa-chalkboard-user', 'published', 2),
('Ekstrakurikuler', 12, 'fas fa-trophy', 'published', 3),
('Tahun Berdiri', 2005, 'fas fa-calendar-days', 'published', 4);
