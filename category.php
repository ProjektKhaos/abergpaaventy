<?php
// Senast uppdaterad: 2026-05-28 08:05 | av: KlⒶssⓔ & Ⓐberg
// category.php – visar inlägg i en kategori Ⓐ Style

require_once __DIR__ . '/app/config.php';
require_once __DIR__ . '/app/db.php';
require_once __DIR__ . '/app/helpers.php';
require_once __DIR__ . '/app/public_layout.php';
require_once __DIR__ . '/app/Category.php';

$slug = trim($_GET['slug'] ?? '');

if (!$slug) {
    header('Location: ' . url());
    exit;
}

$category_model = new Category($pdo);
$category       = $category_model->get_by_slug($slug);

if (!$category) {
    http_response_code(404);
    $posts = [];
    $title = 'Kategori hittades inte';
} else {
    $posts = $category_model->get_posts($category['id']);
    $title = $category['name'];
}

$pageTitle       = $title . ' - Hasse i Thailand';
$pageDescription = $category
    ? 'Inlägg i kategorin ' . $category['name'] . ' från Hasses tid i Thailand.'
    : 'Kategorin hittades inte.';
$canonicalUrl    = query_url('category.php', ['slug' => $slug]);
$ogImageUrl      = asset_url('assets/img/studera_fb_og.png');
?>
<!doctype html>
<html lang="sv">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <?php public_meta($pageTitle, $pageDescription, $canonicalUrl, $ogImageUrl); ?>
  <link rel="stylesheet" href="<?= e(asset_url('assets/style.css')) ?>">
</head>
<body>
  <?php public_header('home'); ?>

  <main id="main-content" class="posts-section">
    <div class="container">

      <p class="back-link-row">
        <a href="<?= e(url()) ?>" class="back-link">← Alla inlägg</a>
      </p>
      <h1><?= e($title) ?></h1>

      <?php if (empty($posts)): ?>
        <div class="empty-state">
          <div class="empty-state__icon">📂</div>
          <p>Inga publicerade inlägg i den här kategorin ännu.</p>
        </div>
      <?php else: ?>
        <div class="posts-grid">
          <?php foreach ($posts as $p): ?>
          <article class="post-card">
            <?php if ($p['cover_file']): ?>
            <img class="post-card__image"
                 src="<?= e(upload_url($p['cover_file'])) ?>"
                 alt="<?= e($p['title']) ?>"
                 loading="lazy">
            <?php else: ?>
            <div class="post-card__image post-card__image--empty">📷</div>
            <?php endif; ?>

            <div class="post-card__body">
              <div class="post-card__meta">
                <?php if ($p['post_date']): ?>
                  <span><?= e(format_date($p['post_date'])) ?></span>
                <?php endif; ?>
                <?php if ($p['location']): ?>
                  <span class="sep">•</span>
                  <a href="<?= e(query_url('location.php', ['location' => $p['location']])) ?>" class="location-badge"><?= e($p['location']) ?></a>
                <?php endif; ?>
              </div>
              <h2 class="post-card__title">
                <a href="<?= e(query_url('post.php', ['slug' => $p['slug']])) ?>"><?= e($p['title']) ?></a>
              </h2>
              <?php if ($p['intro']): ?>
              <p class="post-card__intro"><?= e($p['intro']) ?></p>
              <?php endif; ?>
              <div class="post-card__footer">
                <a href="<?= e(query_url('post.php', ['slug' => $p['slug']])) ?>" class="btn btn--ghost btn--sm">Läs mer →</a>
              </div>
            </div>
          </article>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

    </div>
  </main>
  <?php public_footer(); ?>

  <script src="<?= e(asset_url('assets/script.js')) ?>" defer></script>
</body>
</html>
