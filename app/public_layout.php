<?php
// public_layout.php – gemensam publik layout för Thailand-sidan Ⓐ Style
// Uppdaterad: 2026-05-28 | serietidnings-/vykortstema

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
    <header class="site-header">
      <a class="brand" href="<?= url() ?>">Hasse i Thailand - 6 månader i Chiang Mai</a>
    </header>
    <div class="top-nav-bar">
      <nav class="top-nav" aria-label="Huvudmeny">
        <?php foreach ($items as $key => [$label, $href]): ?>
          <a class="nav-button <?= $active === $key ? 'active' : '' ?>" href="<?= e($href) ?>"><?= e($label) ?></a>
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
        <img src="<?= url('assets/img/cm_sign.png') ?>" alt="Chiang Mai, Thailand" width="120" loading="lazy">
        <div>
          Hasse i Thailand &middot; Thailand 2026
          <a class="footer-link" href="<?= url('about.php') ?>">Om sidan</a>
        </div>
      </div>
    </footer>
    <?php
}
