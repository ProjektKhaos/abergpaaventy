<?php
// Senast uppdaterad: 2026-05-28 08:05 | av: KlⒶssⓔ & Ⓐberg
// about.php – om sidan för Thailand-bloggen Ⓐ Style

require_once __DIR__ . '/app/config.php';
require_once __DIR__ . '/app/helpers.php';
require_once __DIR__ . '/app/public_layout.php';

$pageTitle       = 'Om sidan - Hasse i Thailand';
$pageDescription = 'Om Åberg På Äventyr och rapporterna från Hasses tid i Chiang Mai.';
$canonicalUrl    = url('about.php');
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
  <?php public_header('about'); ?>

  <main id="main-content" class="post-page about-page">
    <div class="section-panel">
      <div class="section-heading">
        <p class="kicker">Om projektet</p>
        <h1>Om sidan</h1>
      </div>
      <div class="split-section">
        <img
          class="framed-image"
          src="<?= e(asset_url('assets/img/scrapbook.jpg')) ?>"
          alt="Vykortscollage från Chiang Mai"
          width="820"
          height="1025"
          loading="eager"
        >
        <div class="quote-bubble align-start">
          <p>Här samlar jag mina bilder, inlägg och små rapporter från tiden i Thailand under studierna i språk och kultur i Chiang Mai.</p>
        </div>
      </div>
      <div class="section-actions">
        <a href="<?= e(url()) ?>" class="btn btn--ghost">← Till inläggen</a>
      </div>
    </div>
  </main>
  <?php public_footer(); ?>

  <script src="<?= e(asset_url('assets/script.js')) ?>" defer></script>
</body>
</html>
