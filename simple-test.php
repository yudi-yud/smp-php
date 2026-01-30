<?php
// Direct test - bypass config
require_once __DIR__ . '/config/database.php';

echo "<h2>Direct Test</h2>";

try {
    $pdo = getDB();
    echo "<p>✅ Koneksi berhasil</p>";

    // Query langsung
    $stmt = $pdo->query("SELECT * FROM testimonials WHERE status = 'published'");
    $result = $stmt->fetchAll();

    echo "<h3>Hasil Query Langsung:</h3>";
    echo "<pre>";
    print_r($result);
    echo "</pre>";
    echo "<p>Jumlah: " . count($result) . "</p>";

} catch (PDOException $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
}

// Test via config functions
echo "<hr>";
echo "<h2>Via config.php Functions</h2>";

require_once __DIR__ . '/config.php';

$testimonials = getTestimonials(10);
echo "<pre>";
print_r($testimonials);
echo "</pre>";
echo "<p>Jumlah via getTestimonials(): " . count($testimonials) . "</p>";
