<?php
// Senast uppdaterad: 2026-05-28 08:05 | av: KlⒶssⓔ & Ⓐberg
// about.php – om sidan för Thailand-bloggen Ⓐ Style

require_once __DIR__ . '/app/config.php';
require_once __DIR__ . '/app/helpers.php';
require_once __DIR__ . '/app/public_layout.php';
?>
<!doctype html>
<html lang="sv">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <title>Om sidan - Hasse i Thailand</title>
  <link rel="stylesheet" href="<?= asset_url('assets/style.css') ?>">
</head>
<body>
  <?php public_header('about'); ?>

  <main class="post-page about-page">
    <div class="section-panel">
      <div class="section-heading"><p class="kicker">Om projektet</p></div>
      <div class="split-section">
        <img
          src="<?= url('assets/img/scrapbook.jpg') ?>"
          alt="Vykortscollage från Chiang Mai"
          loading="eager"
          style="width:100%;border:6px solid #fff;border-radius:3px;box-shadow:var(--shadow-card);"
        >
        <div class="quote-bubble" style="align-self:start;">
          <p>Här samlar jag mina bilder, inlägg och små rapporter från tiden i Thailand under studierna i språk och kultur i Chiang Mai.</p>
        </div>
      </div>
      <div style="margin-top:var(--s3);position:relative;z-index:1;">
        <a href="<?= url() ?>" class="btn btn--ghost">← Till inläggen</a>
      </div>
    </div>
  </main>
  <?php public_footer(); ?>

  <script src="<?= url('assets/script.js') ?>"></script>
</body>
</html>
