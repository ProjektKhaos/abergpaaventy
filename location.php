<?php
// Senast uppdaterad: 2026-05-28 08:05 | av: KlⒶssⓔ & Ⓐberg
// location.php – visar inlägg från en specifik plats

require_once __DIR__ . '/app/config.php';
require_once __DIR__ . '/app/db.php';
require_once __DIR__ . '/app/helpers.php';
require_once __DIR__ . '/app/public_layout.php';
require_once __DIR__ . '/app/Post.php';

$location = trim($_GET['location'] ?? '');

if (!$location) {
    header('Location: ' . url());
    exit;
}

$post_model = new Post($pdo);
$posts      = $post_model->get_by_location($location);
$title      = $location;
?>
<!doctype html>
<html lang="sv">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <title><?= e($title) ?> - Hasse i Thailand</title>
  <link rel="stylesheet" href="<?= asset_url('assets/style.css') ?>">
</head>
<body>
  <?php public_header('home'); ?>

  <main class="posts-section">
    <div class="container">

      <p style="margin-bottom:0.5rem;">
        <a href="<?= url() ?>" style="color:var(--ink-muted);font-size:0.9rem;">← Alla inlägg</a>
      </p>
      <h1><?= e($title) ?></h1>

      <?php if (empty($posts)): ?>
        <div class="empty-state">
          <div class="empty-state__icon">📍</div>
          <p>Inga publicerade inlägg från den här platsen ännu.</p>
        </div>
      <?php else: ?>
        <div class="posts-grid">
          <?php foreach ($posts as $p): ?>
          <article class="post-card">
            <?php if ($p['cover_file']): ?>
            <img class="post-card__image"
                 src="<?= UPLOAD_URL . e($p['cover_file']) ?>"
                 alt="<?= e($p['title']) ?>"
                 loading="lazy">
            <?php else: ?>
            <div class="post-card__image" style="display:flex;align-items:center;justify-content:center;font-size:2.5rem;">📷</div>
            <?php endif; ?>

            <div class="post-card__body">
              <div class="post-card__meta">
                <?php if ($p['post_date']): ?>
                  <span><?= e(format_date($p['post_date'])) ?></span>
                <?php endif; ?>
                <?php if ($p['location']): ?>
                  <span class="sep">•</span>
                  <a href="<?= url('location.php?location=' . urlencode($p['location'])) ?>" class="location-badge"><?= e($p['location']) ?></a>
                <?php endif; ?>
              </div>
              <h2 class="post-card__title">
                <a href="<?= url('post.php?slug=' . e($p['slug'])) ?>"><?= e($p['title']) ?></a>
              </h2>
              <?php if ($p['intro']): ?>
              <p class="post-card__intro"><?= e($p['intro']) ?></p>
              <?php endif; ?>
              <div class="post-card__footer">
                <a href="<?= url('post.php?slug=' . e($p['slug'])) ?>" class="btn btn--ghost btn--sm">Läs mer →</a>
              </div>
            </div>
          </article>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

    </div>
  </main>
  <?php public_footer(); ?>

  <script src="<?= url('assets/script.js') ?>"></script>
</body>
</html>
