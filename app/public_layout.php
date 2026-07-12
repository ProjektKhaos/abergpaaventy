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
        'home'     => ['Hem',      url()],
        'articles' => ['Artiklar', url('articles.php')],
        'news'     => ['Nyheter',  url('nyheter.php')],
        'cmt'      => ['Chiang Mai Tips', url('cmt.php')],
        'metro'    => ['Metro',     url('metro.php')],
        'gallery'  => ['Foton',    url('gallery.php')],
        'about'    => ['Om',       url('about.php')],
        'info'     => ['INFO',     url('info.php')],
    ];
    ?>
    <a class="skip-link" href="#main-content">Hoppa till innehåll</a>
    <header class="site-header">
      <div class="site-header__weather" data-header-weather-endpoint="<?= e(url('api/chiangmai_status.php')) ?>" aria-live="polite">
        <p id="header-weather-title" class="metro-eyebrow">Aktuellt väder</p>
        <strong class="metro-temperature" data-cm-temperature>—</strong>
        <p class="metro-description" data-cm-description>Hämtar väder…</p>
      </div>
      <a class="brand" href="<?= e(url()) ?>">Hasse i Thailand - 6 månader i Chiang Mai</a>
      <?php // Aktivera vid behov: require __DIR__ . '/partials/chiangmai_widget.php'; ?>
    </header>
    <script>
    (() => {
      const weather = document.querySelector('.site-header__weather[data-header-weather-endpoint]');
      if (!weather) return;

      const endpoint = weather.dataset.headerWeatherEndpoint;
      const temperature = weather.querySelector('[data-cm-temperature]');
      const description = weather.querySelector('[data-cm-description]');

      fetch(endpoint, { headers: { Accept: 'application/json' }, cache: 'no-store' })
        .then(response => response.ok ? response.json() : Promise.reject())
        .then(data => {
          if (!data?.success || !data?.current_weather) throw new Error('Missing weather data');

          const current = data.current_weather;
          const value = Number(current.temperature_c);
          temperature.textContent = Number.isFinite(value)
            ? `${new Intl.NumberFormat('sv-SE', { maximumFractionDigits: 1 }).format(value)}°C`
            : '—';
          description.textContent = current.description_sv || 'Väder saknas';
        })
        .catch(() => {
          if (temperature) temperature.textContent = '—';
          if (description) description.textContent = 'Väder tillfälligt otillgängligt';
        });
    })();
    </script>
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
        <a href="<?= e(url('admin/')) ?>" aria-label="Gå till admin">
          <img src="<?= e(asset_url('assets/img/cm_sign.png')) ?>" alt="Chiang Mai, Thailand" width="138" height="65" loading="lazy">
        </a>
        <div>
          Hasse i Thailand &middot; Thailand 2026
          <a class="footer-link" href="<?= e(url('about.php')) ?>">Om sidan</a>
        </div>
      </div>
    </footer>
    <?php
}
