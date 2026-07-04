<?php
// public_layout.php – gemensam publik layout för Thailand-sidan Ⓐ Style
// Uppdaterad: 2026-05-28 | serietidnings-/vykortstema

function public_meta(string $title, string $description, string $canonicalUrl, ?string $ogImageUrl = null, string $type = 'website'): void
{
    $imageUrl = $ogImageUrl ?: asset_url('assets/img/studera_fb_og.png');
    ?>
  <title><?= e($title) ?></title>
  <meta name="description" content="<?= e($description) ?>">
  <link rel="canonical" href="<?= e($canonicalUrl) ?>">
  <meta property="og:title" content="<?= e($title) ?>">
  <meta property="og:description" content="<?= e($description) ?>">
  <meta property="og:type" content="<?= e($type) ?>">
  <meta property="og:image" content="<?= e($imageUrl) ?>">
  <meta property="og:url" content="<?= e($canonicalUrl) ?>">
    <?php
}

function public_header(string $active = ''): void
{
    // Menyval i toppmenyn.
    // Texten i första värdet styr vad som syns på knappen, t.ex. "Om".
    // Länken i andra värdet styr vart knappen går.
    // Utseendet på knapparna styrs i assets/style.css under .nav-button.
    // Lägg till fler rader här om menyn ska få fler knappar.
    $items = [
        'home'    => ['Hem',   url()],
        'gallery' => ['Foton', url('gallery.php')],
        'about'   => ['Om',    url('about.php')],
    ];
    ?>
    <a class="skip-link" href="#main-content">Hoppa till innehåll</a>
    <header class="site-header">
      <a class="brand" href="<?= e(url()) ?>">Hasse i Thailand - 6 månader i Chiang Mai</a>
    </header>
    <div class="top-nav-bar">
      <nav class="top-nav" aria-label="Huvudmeny">
        <?php foreach ($items as $key => [$label, $href]): ?>
          <a
            class="nav-button <?= $active === $key ? 'active' : '' ?>"
            href="<?= e($href) ?>"
            <?= $active === $key ? 'aria-current="page"' : '' ?>
          ><?= e($label) ?></a>
        <?php endforeach; ?>
      </nav>
    </div>
    <?php
}

function public_footer(): void
{
    ?>
    <footer class="site-footer">
      <div class="wrap footer-inner">
        <img src="<?= e(asset_url('assets/img/cm_sign.png')) ?>" alt="Chiang Mai, Thailand" width="138" height="65" loading="lazy">
        <div>
          Hasse i Thailand &middot; Thailand 2026
          <a class="footer-link" href="<?= e(url('about.php')) ?>">Om sidan</a>
        </div>
      </div>
    </footer>
    <?php
}
