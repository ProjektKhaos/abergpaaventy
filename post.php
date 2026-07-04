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
  <?php public_header('post'); ?>

  <main class="post-page">
    <div class="container">

      <?php if (!$post): ?>
        <div class="empty-state">
          <div class="empty-state__icon">🔍</div>
          <h1>Inlägg hittades inte</h1>
          <p><a href="<?= url() ?>" class="btn btn--primary">← Tillbaka till startsidan</a></p>
        </div>

      <?php else: ?>

        <!-- Navigering tillbaka -->
        <p style="margin-bottom:var(--s2);">
          <a href="<?= url() ?>" style="color:var(--ink-muted);font-size:0.9rem;">← Alla inlägg</a>
        </p>

        <!-- Header -->
        <header class="post-page__header">
          <div class="post-page__meta">
            <?php if ($post['post_date']): ?>
              <span><?= e(format_date($post['post_date'])) ?></span>
            <?php endif; ?>
            <?php if ($post['location']): ?>
              <span>•</span>
              <a href="<?= url('location.php?location=' . urlencode($post['location'])) ?>" class="location-badge"><?= e($post['location']) ?></a>
            <?php endif; ?>
          </div>

          <h1><?= e($post['title']) ?></h1>

          <?php if ($post['intro']): ?>
          <p style="font-size:1.1rem;color:var(--ink-mid);line-height:1.65;margin-top:var(--s1);">
            <?= e($post['intro']) ?>
          </p>
          <?php endif; ?>
        </header>

        <!-- Omslagsbild -->
        <?php if ($post['cover_file']): ?>
        <img
          class="post-page__cover"
          src="<?= UPLOAD_URL . e($post['cover_file']) ?>"
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
        <section style="margin-top:var(--s4);">
          <p class="section-title">Bilder från inlägget</p>
          <div class="gallery-grid">
            <?php foreach ($gallery as $img): ?>
            <a href="<?= UPLOAD_URL . e($img['file_name']) ?>" class="gallery-item" data-lightbox>
              <img
                src="<?= UPLOAD_URL . e($img['file_name']) ?>"
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
        <div style="margin-top:var(--s3);border-top:1px solid var(--border);padding-top:var(--s2);">
          <?php if (!empty($categories)): ?>
          <div class="tags" style="margin-bottom:var(--s1);">
            <?php foreach ($categories as $cat): ?>
            <a href="<?= url('category.php?slug=' . e($cat['slug'])) ?>"
               style="background:var(--accent-light);color:var(--accent-dark);"
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
        <div style="margin-top:var(--s4);">
          <a href="<?= url() ?>" class="btn btn--ghost">← Fler inlägg</a>
        </div>

      <?php endif; ?>

    </div>
  </main>
  <?php public_footer(); ?>

  <script src="<?= url('assets/script.js') ?>"></script>
</body>
</html>
