<?php
require_once __DIR__ . '/config.php';

echo "<h2>Test Database Frontend</h2>";

// Test testimonials
echo "<h3>Testimonials (published):</h3>";
$testimonials = getTestimonials(10);
echo "<pre>";
print_r($testimonials);
echo "</pre>";

echo "<p>Jumlah testimonials: " . count($testimonials) . "</p>";

// Test connection
echo "<h3>Database Connection:</h3>";
try {
    $pdo = getDB();
    echo "<p style='color:green'>✅ Koneksi database berhasil!</p>";

    // Raw query check
    $stmt = $pdo->query("SELECT * FROM testimonials WHERE status = 'published'");
    $raw = $stmt->fetchAll();
    echo "<p>Raw query result count: " . count($raw) . "</p>";
    echo "<pre>";
    print_r($raw);
    echo "</pre>";
} catch (PDOException $e) {
    echo "<p style='color:red'>❌ Error: " . $e->getMessage() . "</p>";
}

// Test all functions
echo "<h3>Test Semua Fungsi:</h3>";
echo "<ul>";
echo "<li>Features: " . count(getFeatures()) . " items</li>";
echo "<li>Programs: " . count(getPrograms()) . " items</li>";
echo "<li>Achievements: " . count(getAchievements()) . " items</li>";
echo "<li>Gallery: " . count(getGallery()) . " items</li>";
echo "<li>News: " . count(getNews()) . " items</li>";
echo "<li>Testimonials: " . count(getTestimonials()) . " items</li>";
echo "</ul>";
