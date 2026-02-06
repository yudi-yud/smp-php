<?php

/**
 * Halaman Detail Berita - SMPN 3 Satu Atap Cipari
 * Menampilkan detail berita/artikel
 */

require_once __DIR__ . '/config.php';

// Get slug from URL
$slug = $_GET['slug'] ?? '';

// Get news detail
$news = null;
if ($slug) {
      try {
            $pdo = getDB();
            $stmt = $pdo->prepare("
            SELECT * FROM news
            WHERE slug = ? AND status = 'published'
            LIMIT 1
        ");
            $stmt->execute([$slug]);
            $news = $stmt->fetch();
      } catch (PDOException $e) {
            $news = null;
      }
}

// If news not found, redirect to home
if (!$news) {
      header('Location: index.php');
      exit;
}

// Get related news
$related_news = [];
try {
      $pdo = getDB();
      $stmt = $pdo->prepare("
        SELECT id, title, slug, description, published_at
        FROM news
        WHERE id != ? AND status = 'published'
        ORDER BY published_at DESC
        LIMIT 3
    ");
      $stmt->execute([$news['id']]);
      $related_news = $stmt->fetchAll();
} catch (PDOException $e) {
      $related_news = [];
}

// Format date - use published_at if available, otherwise created_at
$news_date = date('d M Y', strtotime($news['published_at'] ?? $news['created_at']));
?>
<!DOCTYPE html>
<html lang="id">

<head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title><?= htmlspecialchars($news['title']) ?> - <?= SITE_NAME ?></title>
      <meta name="description" content="<?= htmlspecialchars(substr(strip_tags($news['content']), 0, 160)) ?>">
      <link rel="stylesheet" href="assets/css/style.css">
      <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body>
      <?php require_once __DIR__ . '/includes/header.php'; ?>

      <main class="main-content">
            <!-- Breadcrumb -->
            <div class="breadcrumb-container">
                  <div class="container">
                        <nav class="breadcrumb">
                              <a href="index.php"><i class="fas fa-home"></i> Beranda</a>
                              <span class="breadcrumb-separator">/</span>
                              <span>Berita</span>
                              <span class="breadcrumb-separator">/</span>
                              <span class="breadcrumb-current"><?= htmlspecialchars($news['title']) ?></span>
                        </nav>
                  </div>
            </div>

            <!-- Article Header -->
            <section class="article-header">
                  <div class="container">
                        <div class="article-meta">
                              <span class="article-date"><i class="far fa-calendar-alt"></i> <?= $news_date ?></span>
                              <span class="article-category"><i class="far fa-folder"></i> Berita</span>
                        </div>
                        <h1 class="article-title"><?= htmlspecialchars($news['title']) ?></h1>
                        <?php if (!empty($news['excerpt'])): ?>
                              <p class="article-excerpt"><?= htmlspecialchars($news['excerpt']) ?></p>
                        <?php endif; ?>
                  </div>
            </section>

            <!-- Article Content -->
            <section class="article-content">
                  <div class="container">
                        <div class="article-wrapper">
                              <article class="article-body">
                                    <?= $news['content'] ?>
                              </article>

                              <!-- Share Buttons -->
                              <div class="article-share">
                                    <span class="share-label">Bagikan:</span>
                                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode(BASE_URL . '/berita.php?slug=' . $news['slug']) ?>"
                                          target="_blank" class="share-btn share-facebook">
                                          <i class="fab fa-facebook-f"></i>
                                    </a>
                                    <a href="https://twitter.com/intent/tweet?url=<?= urlencode(BASE_URL . '/berita.php?slug=' . $news['slug']) ?>&text=<?= urlencode($news['title']) ?>"
                                          target="_blank" class="share-btn share-twitter">
                                          <i class="fab fa-twitter"></i>
                                    </a>
                                    <a href="https://wa.me/?text=<?= urlencode($news['title'] . ' - ' . BASE_URL . '/berita.php?slug=' . $news['slug']) ?>"
                                          target="_blank" class="share-btn share-whatsapp">
                                          <i class="fab fa-whatsapp"></i>
                                    </a>
                              </div>
                        </div>

                        <!-- Sidebar -->
                        <aside class="article-sidebar">
                              <div class="sidebar-widget">
                                    <h3 class="widget-title">Berita Terkait</h3>
                                    <?php if ($related_news): ?>
                                          <div class="related-news">
                                                <?php foreach ($related_news as $related): ?>
                                                      <a href="berita.php?slug=<?= htmlspecialchars($related['slug']) ?>" class="related-news-item">
                                                            <span class="related-news-date"><?= date('d M Y', strtotime($related['published_at'])) ?></span>
                                                            <h4><?= htmlspecialchars($related['title']) ?></h4>
                                                      </a>
                                                <?php endforeach; ?>
                                          </div>
                                    <?php else: ?>
                                          <p class="text-muted">Tidak ada berita terkait.</p>
                                    <?php endif; ?>
                              </div>

                              <div class="sidebar-widget">
                                    <h3 class="widget-title">Kembali ke Beranda</h3>
                                    <a href="index.php" class="btn btn-primary btn-block">
                                          <i class="fas fa-home"></i> Ke Beranda
                                    </a>
                              </div>
                        </aside>
                  </div>
            </section>
      </main>

      <?php require_once __DIR__ . '/includes/footer.php'; ?>

      <style>
            .breadcrumb-container {
                  background: #f8fafc;
                  padding: 15px 0;
                  border-bottom: 1px solid #e2e8f0;
            }

            .breadcrumb {
                  display: flex;
                  align-items: center;
                  gap: 10px;
                  font-size: 14px;
            }

            .breadcrumb a {
                  color: #64748b;
                  text-decoration: none;
                  transition: color 0.3s;
            }

            .breadcrumb a:hover {
                  color: #0891b2;
            }

            .breadcrumb-separator {
                  color: #cbd5e1;
            }

            .breadcrumb-current {
                  color: #0891b2;
                  font-weight: 500;
            }

            .article-header {
                  background: linear-gradient(135deg, #0891b2 0%, #06b6d4 100%);
                  color: white;
                  padding: 60px 0;
                  text-align: center;
            }

            .article-meta {
                  display: flex;
                  justify-content: center;
                  gap: 20px;
                  margin-bottom: 20px;
            }

            .article-meta span {
                  display: flex;
                  align-items: center;
                  gap: 5px;
                  font-size: 14px;
                  opacity: 0.9;
            }

            .article-title {
                  font-size: 2.5rem;
                  margin: 0 0 20px 0;
                  font-weight: 700;
                  line-height: 1.2;
            }

            .article-excerpt {
                  font-size: 1.1rem;
                  opacity: 0.9;
                  max-width: 800px;
                  margin: 0 auto;
            }

            .article-content {
                  padding: 60px 0;
            }

            .article-wrapper {
                  display: grid;
                  grid-template-columns: 1fr 350px;
                  gap: 40px;
            }

            .article-body {
                  background: white;
                  padding: 40px;
                  border-radius: 12px;
                  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
                  line-height: 1.8;
            }

            .article-body img {
                  max-width: 100%;
                  height: auto;
                  border-radius: 8px;
                  margin: 20px 0;
            }

            .article-body h2 {
                  font-size: 1.8rem;
                  margin: 30px 0 15px 0;
                  color: #1e293b;
            }

            .article-body h3 {
                  font-size: 1.4rem;
                  margin: 25px 0 12px 0;
                  color: #334155;
            }

            .article-body p {
                  margin-bottom: 15px;
                  color: #475569;
            }

            .article-body ul,
            .article-body ol {
                  margin: 15px 0;
                  padding-left: 25px;
            }

            .article-body li {
                  margin-bottom: 8px;
                  color: #475569;
            }

            .article-share {
                  display: flex;
                  align-items: center;
                  gap: 15px;
                  margin-top: 30px;
                  padding-top: 30px;
                  border-top: 1px solid #e2e8f0;
            }

            .share-label {
                  font-weight: 500;
                  color: #64748b;
            }

            .share-btn {
                  width: 40px;
                  height: 40px;
                  border-radius: 50%;
                  display: flex;
                  align-items: center;
                  justify-content: center;
                  color: white;
                  transition: transform 0.3s, box-shadow 0.3s;
            }

            .share-btn:hover {
                  transform: translateY(-3px);
                  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            }

            .share-facebook {
                  background: #1877f2;
            }

            .share-twitter {
                  background: #1da1f2;
            }

            .share-whatsapp {
                  background: #25d366;
            }

            .sidebar-widget {
                  background: white;
                  padding: 25px;
                  border-radius: 12px;
                  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
                  margin-bottom: 25px;
            }

            .widget-title {
                  font-size: 1.1rem;
                  margin: 0 0 20px 0;
                  padding-bottom: 10px;
                  border-bottom: 2px solid #0891b2;
                  color: #1e293b;
            }

            .related-news-item {
                  display: block;
                  padding: 15px;
                  border-radius: 8px;
                  margin-bottom: 12px;
                  background: #f8fafc;
                  text-decoration: none;
                  transition: all 0.3s;
            }

            .related-news-item:hover {
                  background: #e0f2fe;
                  transform: translateX(5px);
            }

            .related-news-date {
                  font-size: 12px;
                  color: #64748b;
                  display: block;
                  margin-bottom: 5px;
            }

            .related-news-item h4 {
                  margin: 0;
                  font-size: 14px;
                  color: #1e293b;
                  line-height: 1.4;
            }

            .btn-block {
                  display: flex;
                  width: 100%;
                  justify-content: center;
                  align-items: center;
                  gap: 8px;
            }

            @media (max-width: 992px) {
                  .article-wrapper {
                        grid-template-columns: 1fr;
                  }

                  .article-title {
                        font-size: 1.8rem;
                  }
            }

            @media (max-width: 576px) {
                  .article-header {
                        padding: 40px 0;
                  }

                  .article-meta {
                        flex-direction: column;
                        gap: 10px;
                  }

                  .article-body {
                        padding: 20px;
                  }
            }
      </style>
</body>

</html>