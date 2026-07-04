<?php
// index.php – startsida för Thailand-bloggen Ⓐ Style
// Uppdaterad: 2026-05-28 | serietidnings-/vykortstema med hero "Den stora flytten"

require_once __DIR__ . '/app/config.php';
require_once __DIR__ . '/app/db.php';
require_once __DIR__ . '/app/helpers.php';
require_once __DIR__ . '/app/Post.php';
require_once __DIR__ . '/app/Category.php';
require_once __DIR__ . '/app/Media.php';
require_once __DIR__ . '/app/public_layout.php';

$post_model     = new Post($pdo);
$category_model = new Category($pdo);
$media_model    = new Media($pdo);

$posts      = $post_model->get_published(12);
$categories = $category_model->get_all_with_count();
$images     = $media_model->get_gallery_images(6);

$pageTitle       = 'Hasse i Thailand - 6 månader i Chiang Mai';
$pageDescription = 'Studier, vardag, bilder och små äventyr från Chiang Mai.';
$canonicalUrl    = url();
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

  <main id="main-content">

  <!--
    Hero: startsidans stora första yta.
    Texten här styr "Kapitel 1", "Den stora flytten" och knapparna.
    Storlek/färg/form styrs i assets/style.css:
    - .kicker, .stamp och .stamp--pink för de små serierute-skyltarna.
    - .hero__title, .hero__desc och .speech för rubrik och brödtext.
    - .btn och .btn--ghost för knapparna.
  -->
  <section class="hero">
    <div class="hero__content">
      <div class="hero__poster">
        <img src="<?= e(asset_url('assets/img/poster.jpg')) ?>" alt="Hasse i Chiang Mai" width="760" height="950" loading="eager" fetchpriority="high">
      </div>
      <div class="hero__text">
        <h1 class="hero__title">Hasse i Thailand <em>2026</em></h1>
        <p class="hero__desc">Studier, sol och stark kaffe i Chiang Mai ♡</p>
        <p class="speech">
          Här skriver jag lite om hur det går med studierna, livet i Chiang Mai,
          maten, människorna och allt annat som händer längs vägen.
        </p>
        <div class="hero__actions">
          <a href="#inlagg" class="btn">Läs senaste →</a>
          <a href="<?= e(url('gallery.php')) ?>" class="btn btn--ghost">Se foton</a>
        </div>
      </div>
    </div>
  </section>

  <!-- Inlägg -->
  <section id="inlagg" class="posts-section">
    <div class="container">

      <?php if (!empty($categories)): ?>
      <div class="tags tags--spaced">
        <?php foreach ($categories as $cat): ?>
          <?php if ($cat['post_count'] > 0): ?>
          <a href="<?= e(query_url('category.php', ['slug' => $cat['slug']])) ?>" class="tag">
            <?= e($cat['name']) ?> [ <?= (int)$cat['post_count'] ?> ]
          </a>
          <?php endif; ?>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>

      <!--
        Sektionsrubrik för inläggslistan.
        Texten ändras här. Storlek/färg ändras i assets/style.css under .section-title.
      -->
      <p class="section-title">Senaste rutorna</p>

      <?php if (empty($posts)): ?>
        <div class="empty-state">
          <div class="empty-state__icon">✈️</div>
          <p>Inga inlägg än – resan börjar snart!</p>
        </div>
      <?php else: ?>
        <div class="posts-grid posts-grid--spaced">
          <?php foreach ($posts as $p): ?>
          <article class="post-card">
            <?php if ($p['cover_file']): ?>
            <img class="post-card__image" src="<?= e(upload_url($p['cover_file'])) ?>" alt="<?= e($p['title']) ?>" loading="lazy">
            <?php else: ?>
            <div class="post-card__image">📷</div>
            <?php endif; ?>

            <div class="post-card__body">
              <div class="post-card__meta">
                <?php if ($p['post_date']): ?><span><?= e(format_date($p['post_date'])) ?></span><?php endif; ?>
                <?php if ($p['location']): ?>
                  <a href="<?= e(query_url('location.php', ['location' => $p['location']])) ?>" class="location-badge"><?= e($p['location']) ?></a>
                <?php endif; ?>
              </div>
              <h2 class="post-card__title">
                <a href="<?= e(query_url('post.php', ['slug' => $p['slug']])) ?>"><?= e($p['title']) ?></a>
              </h2>
              <?php if ($p['intro']): ?><p class="post-card__intro"><?= e($p['intro']) ?></p><?php endif; ?>
              <div class="post-card__footer">
                <a href="<?= e(query_url('post.php', ['slug' => $p['slug']])) ?>" class="btn btn--ghost btn--sm">Läs mer →</a>
              </div>
            </div>
          </article>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

    </div>
  </section>

  <!-- Bildrutor från resan -->
  <section class="comic-section">
    <div class="section-panel">
      <div class="section-heading"><p class="kicker">📷 Bildrutor från resan</p></div>
      <?php if (empty($images)): ?>
        <div class="empty-state"><div class="empty-state__icon">📷</div><p>Inga bilder uppladdade ännu.</p></div>
      <?php else: ?>
        <div class="gallery-grid">
          <?php foreach ($images as $img): ?>
            <a href="<?= e(query_url('post.php', ['slug' => $img['post_slug']])) ?>" class="gallery-item">
              <img src="<?= e(upload_url($img['file_name'])) ?>" alt="<?= e($img['alt_text'] ?: $img['post_title']) ?>" loading="lazy">
              <div class="gallery-item__overlay"><?= e($img['post_title']) ?></div>
            </a>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </section>

  <!-- Vykort från resan -->
  <section class="comic-section">
    <div class="section-panel">
      <div class="section-heading"><p class="kicker">✉️ Vykort från Mae Kampong</p></div>
      <div class="split-section">
        <img class="framed-image" src="<?= e(asset_url('assets/img/scrapbook.jpg')) ?>" alt="Vykortscollage från Chiang Mai" width="820" height="1025" loading="lazy">
        <div class="quote-bubble align-center">
          <p>"Amy, vi saknar dig jätte mycket och önskar du vore här i Chiang Mai."</p>
        </div>
      </div>
    </div>
  </section>

  </main>

  <?php public_footer(); ?>

  <script src="<?= e(asset_url('assets/script.js')) ?>" defer></script>
</body>
</html>
