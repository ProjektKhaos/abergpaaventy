<?php
// Senast uppdaterad: 2026-05-28 08:05 | av: KlⒶssⓔ & Ⓐberg
// gallery.php – fotogalleri för alla publicerade bilder Ⓐ Style

require_once __DIR__ . '/app/config.php';
require_once __DIR__ . '/app/db.php';
require_once __DIR__ . '/app/helpers.php';
require_once __DIR__ . '/app/public_layout.php';
require_once __DIR__ . '/app/Media.php';

$media_model = new Media($pdo);
$images      = $media_model->get_gallery_images(80);
?>
<!doctype html>
<html lang="sv">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <title>Foton - Hasse i Thailand</title>
  <link rel="stylesheet" href="<?= asset_url('assets/style.css') ?>">
</head>
<body>
  <?php public_header('gallery'); ?>

  <main class="posts-section">
    <div class="container">

      <p class="kicker">Galleri</p>
      <h1>Fotogalleri</h1>
      <p style="color:var(--ink-muted);margin-bottom:var(--s3);position:relative;z-index:1;">
        Bilder från Thailand – klicka på en bild för att förstora den.
      </p>

      <?php if (empty($images)): ?>
        <div class="empty-state">
          <div class="empty-state__icon">📷</div>
          <p>Inga bilder uppladdade ännu.</p>
        </div>
      <?php else: ?>
        <div class="gallery-grid">
          <?php foreach ($images as $img): ?>
          <a href="<?= UPLOAD_URL . e($img['file_name']) ?>"
             class="gallery-item"
             data-lightbox>
            <img
              src="<?= UPLOAD_URL . e($img['file_name']) ?>"
              alt="<?= e($img['alt_text'] ?: $img['post_title']) ?>"
              loading="lazy"
            >
            <div class="gallery-item__overlay">
              <?= e($img['post_title']) ?>
              <?php if ($img['location']): ?>
                &middot; <?= e($img['location']) ?>
              <?php endif; ?>
            </div>
          </a>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

    </div>
  </main>
  <?php public_footer(); ?>

  <script src="<?= url('assets/script.js') ?>"></script>
</body>
</html>
