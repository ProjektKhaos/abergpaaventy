<?php
// Senast uppdaterad: 2026-05-28 08:05 | av: KlⒶssⓔ & Ⓐberg
// post.php med visning av enskilt inlägg Ⓐ Style

require_once __DIR__ . '/app/config.php';
require_once __DIR__ . '/app/db.php';
require_once __DIR__ . '/app/helpers.php';
require_once __DIR__ . '/app/public_layout.php';
require_once __DIR__ . '/app/Post.php';

$slug = trim($_GET['slug'] ?? '');

if (!$slug) {
    header('Location: ' . url());
    exit;
}

$post_model = new Post($pdo);
$post       = $post_model->get_by_slug($slug);

if (!$post) {
    http_response_code(404);
    $title = 'Inlägg hittades inte';
} else {
    $gallery    = $post_model->get_gallery($post['id']);
    $categories = $post_model->get_categories($post['id']);
    $tags       = $post_model->get_tags($post['id']);
    $title      = $post['title'];
}

$pageTitle       = $title . ' - Hasse i Thailand';
$pageDescription = $post && $post['intro']
    ? $post['intro']
    : ($post ? 'Läs inlägget ' . $post['title'] . ' från Hasses tid i Thailand.' : 'Inlägget hittades inte.');
$canonicalUrl    = $post
    ? query_url('post.php', ['slug' => $post['slug']])
    : query_url('post.php', ['slug' => $slug]);
$ogImageUrl      = $post && $post['cover_file']
    ? upload_url($post['cover_file'])
    : asset_url('assets/img/studera_fb_og.png');
$ogType          = $post ? 'article' : 'website';
?>
<!doctype html>
<html lang="sv">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <?php public_meta($pageTitle, $pageDescription, $canonicalUrl, $ogImageUrl, $ogType); ?>
  <link rel="stylesheet" href="<?= e(asset_url('assets/style.css')) ?>">
</head>
<body>
  <?php public_header('post'); ?>

  <main id="main-content" class="post-page">
    <div class="container">

      <?php if (!$post): ?>
        <div class="empty-state">
          <div class="empty-state__icon">🔍</div>
          <h1>Inlägg hittades inte</h1>
          <p><a href="<?= e(url()) ?>" class="btn btn--primary">← Tillbaka till startsidan</a></p>
        </div>

      <?php else: ?>

        <!-- Navigering tillbaka -->
        <p class="back-link-row back-link-row--loose">
          <a href="<?= e(url()) ?>" class="back-link">← Alla inlägg</a>
        </p>

        <!-- Header -->
        <header class="post-page__header">
          <div class="post-page__meta">
            <?php if ($post['post_date']): ?>
              <span><?= e(format_date($post['post_date'])) ?></span>
            <?php endif; ?>
            <?php if ($post['location']): ?>
              <span>•</span>
              <a href="<?= e(query_url('location.php', ['location' => $post['location']])) ?>" class="location-badge"><?= e($post['location']) ?></a>
            <?php endif; ?>
          </div>

          <h1><?= e($post['title']) ?></h1>

          <?php if ($post['intro']): ?>
          <p class="post-intro">
            <?= e($post['intro']) ?>
          </p>
          <?php endif; ?>
        </header>

        <!-- Omslagsbild -->
        <?php if ($post['cover_file']): ?>
        <img
          class="post-page__cover"
          src="<?= e(upload_url($post['cover_file'])) ?>"
          alt="<?= e($post['title']) ?>"
          loading="eager"
        >
        <?php endif; ?>

        <!-- Brödtext -->
        <?php if ($post['body']): ?>
        <div class="post-body">
          <?= nl2br(e($post['body'])) ?>
        </div>
        <?php endif; ?>

        <!-- Bildgalleri -->
        <?php if (!empty($gallery)): ?>
        <section class="section-spacer">
          <p class="section-title">Bilder från inlägget</p>
          <div class="gallery-grid">
            <?php foreach ($gallery as $img): ?>
            <a href="<?= e(upload_url($img['file_name'])) ?>" class="gallery-item" data-lightbox>
              <img
                src="<?= e(upload_url($img['file_name'])) ?>"
                alt="<?= e($img['alt_text'] ?: $post['title']) ?>"
                loading="lazy"
              >
              <?php if ($img['caption']): ?>
              <div class="gallery-item__overlay"><?= e($img['caption']) ?></div>
              <?php endif; ?>
            </a>
            <?php endforeach; ?>
          </div>
        </section>
        <?php endif; ?>

        <!-- Kategorier & taggar -->
        <?php if (!empty($categories) || !empty($tags)): ?>
        <div class="post-taxonomy">
          <?php if (!empty($categories)): ?>
          <div class="tags tags--compact">
            <?php foreach ($categories as $cat): ?>
            <a href="<?= e(query_url('category.php', ['slug' => $cat['slug']])) ?>"
               class="tag">
              <?= e($cat['name']) ?>
            </a>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>

          <?php if (!empty($tags)): ?>
          <div class="tags">
            <?php foreach ($tags as $tag): ?>
            <span class="tag"><?= e($tag['name']) ?></span>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Tillbaka-knapp -->
        <div class="section-actions section-actions--large">
          <a href="<?= e(url()) ?>" class="btn btn--ghost">← Fler inlägg</a>
        </div>

      <?php endif; ?>

    </div>
  </main>
  <?php public_footer(); ?>

  <script src="<?= e(asset_url('assets/script.js')) ?>" defer></script>
</body>
</html>
