<?php
// nyheter.php – lista alla publicerade nyheter

require_once __DIR__ . '/app/config.php';
require_once __DIR__ . '/app/db.php';
require_once __DIR__ . '/app/helpers.php';
require_once __DIR__ . '/app/public_layout.php';
require_once __DIR__ . '/app/Post.php';

$post_model = new Post($pdo);
$news = $post_model->get_published(100, 0, 'news');

$pageTitle       = 'Nyheter - Hasse i Thailand';
$pageDescription = 'Senaste nytt från Hasses tid i Thailand.';
$canonicalUrl    = url('nyheter.php');
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
  <?php public_header('news'); ?>

  <main id="main-content" class="posts-section">
    <div class="container">
      <h1>Nyheter</h1>

      <?php if (empty($news)): ?>
        <div class="empty-state">
          <div class="empty-state__icon">📰</div>
          <p>Inga publicerade nyheter ännu.</p>
        </div>
      <?php else: ?>
        <div class="posts-grid">
          <?php foreach ($news as $item): ?>
          <article class="post-card">
            <?php if ($item['cover_file']): ?>
            <img class="post-card__image"
                 src="<?= e(upload_url($item['cover_file'])) ?>"
                 alt="<?= e($item['title']) ?>"
                 loading="lazy">
            <?php else: ?>
            <div class="post-card__image post-card__image--empty">📰</div>
            <?php endif; ?>

            <div class="post-card__body">
              <div class="post-card__meta">
                <?php if ($item['post_date']): ?>
                  <span><?= e(format_date($item['post_date'])) ?></span>
                <?php endif; ?>
                <?php if ($item['location']): ?>
                  <span class="sep">•</span>
                  <span class="location-badge"><?= e($item['location']) ?></span>
                <?php endif; ?>
              </div>
              <h2 class="post-card__title">
                <a href="<?= e(query_url('post.php', ['slug' => $item['slug']])) ?>"><?= e($item['title']) ?></a>
              </h2>
              <?php if ($item['intro']): ?>
              <p class="post-card__intro"><?= e($item['intro']) ?></p>
              <?php endif; ?>
              <div class="post-card__footer">
                <a href="<?= e(query_url('post.php', ['slug' => $item['slug']])) ?>" class="btn btn--ghost btn--sm">Läs mer →</a>
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
