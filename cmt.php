<?php
// cmt.php – publicerade Chiang Mai-tips

require_once __DIR__ . '/app/config.php';
require_once __DIR__ . '/app/db.php';
require_once __DIR__ . '/app/helpers.php';
require_once __DIR__ . '/app/public_layout.php';
require_once __DIR__ . '/app/Post.php';

$post_model = new Post($pdo);
$tips = $post_model->get_published(100, 0, 'cmt');

$pageTitle       = 'Chiang Mai Tips - Hasse i Thailand';
$pageDescription = 'Tips på platser, mat, utflykter och upplevelser i Chiang Mai.';
$canonicalUrl    = url('cmt.php');
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
  <?php public_header('cmt'); ?>

  <main id="main-content" class="posts-section">
    <div class="container">
      <h1>Chiang Mai Tips</h1>
      <p class="page-intro">Platser, mat, utflykter och annat som är värt att upptäcka i Chiang Mai.</p>

      <?php if (empty($tips)): ?>
        <div class="empty-state">
          <div class="empty-state__icon">📍</div>
          <p>Inga publicerade Chiang Mai-tips ännu.</p>
        </div>
      <?php else: ?>
        <div class="posts-grid">
          <?php foreach ($tips as $tip): ?>
          <article class="post-card">
            <?php if ($tip['cover_file']): ?>
            <img class="post-card__image"
                 src="<?= e(upload_url($tip['cover_file'])) ?>"
                 alt="<?= e($tip['title']) ?>"
                 loading="lazy">
            <?php else: ?>
            <div class="post-card__image post-card__image--empty">📍</div>
            <?php endif; ?>

            <div class="post-card__body">
              <div class="post-card__meta">
                <?php if ($tip['post_date']): ?>
                  <span><?= e(format_date($tip['post_date'])) ?></span>
                <?php endif; ?>
                <?php if ($tip['location']): ?>
                  <span class="sep">•</span>
                  <span class="location-badge"><?= e($tip['location']) ?></span>
                <?php endif; ?>
              </div>
              <h2 class="post-card__title">
                <a href="<?= e(query_url('post.php', ['slug' => $tip['slug']])) ?>"><?= e($tip['title']) ?></a>
              </h2>
              <?php if ($tip['intro']): ?>
              <p class="post-card__intro"><?= e($tip['intro']) ?></p>
              <?php endif; ?>
              <div class="post-card__footer">
                <a href="<?= e(query_url('post.php', ['slug' => $tip['slug']])) ?>" class="btn btn--ghost btn--sm">Läs mer →</a>
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
