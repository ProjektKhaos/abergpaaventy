<?php
// Senast uppdaterad: 2026-07-11 20:20 | av: KlⒶssⓔ & Ⓐberg
// Chiang Mai Metro – lokal tid och väder från det interna väder-API:t.

require_once __DIR__ . '/app/config.php';
require_once __DIR__ . '/app/helpers.php';
require_once __DIR__ . '/app/public_layout.php';

$pageTitle       = 'Chiang Mai Metro - Hasse i Thailand';
$pageDescription = 'Lokal tid, aktuellt väder, timprognos och sjudygnsprognos för Chiang Mai.';
$canonicalUrl    = url('metro.php');
$ogImageUrl      = asset_url('assets/img/studera_fb_og.png');
$apiEndpoint     = url('api/chiangmai_status.php');
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
  <?php public_header('metro'); ?>

  <main id="main-content" class="metro-page" data-weather-endpoint="<?= e($apiEndpoint) ?>">
    <div class="container">
      <section class="metro-hero" aria-labelledby="metro-title">
        <div>
          <p class="kicker">Chiang Mai just nu</p>
          <h1 id="metro-title">Chiang Mai Metro</h1>
          <p class="metro-hero__intro">Tid, datum och väder inför Åbergs äventyr i Thailand.</p>
        </div>
        <div class="metro-clock" aria-label="Lokal tid i Chiang Mai">
          <strong data-cm-clock>--:--:--</strong>
          <span data-cm-date>Hämtar lokal tid…</span>
        </div>
      </section>

      <div class="metro-status" data-cm-status role="status" aria-live="polite">
        <span class="metro-status__pulse" aria-hidden="true"></span>
        Hämtar den senaste väderrapporten…
      </div>

      <div class="metro-content" data-cm-content hidden>
        <section class="metro-now" aria-labelledby="metro-now-title">
          <div class="metro-now__primary">
            <span class="metro-weather-symbol" data-cm-symbol aria-hidden="true">◌</span>
            <div>
              <p id="metro-now-title" class="metro-eyebrow">Aktuellt väder</p>
              <strong class="metro-temperature" data-cm-temperature>—</strong>
              <p class="metro-description" data-cm-description>—</p>
            </div>
          </div>
          <div class="metro-now__summary">
            <p>Känns som <strong data-cm-feels-like>—</strong></p>
            <p data-cm-day-status>—</p>
            <p class="metro-travel-note" data-cm-travel-note></p>
          </div>
        </section>

        <section class="metro-section" aria-labelledby="metro-details-title">
          <div class="metro-section__heading">
            <div>
              <p class="kicker">Mätvärden</p>
              <h2 id="metro-details-title">Vädret i detalj</h2>
            </div>
            <span class="metro-source-time" data-cm-source-time>—</span>
          </div>

          <dl class="metro-detail-grid">
            <div><dt>Luftfuktighet</dt><dd data-current="humidity_percent">—</dd></div>
            <div><dt>Molntäcke</dt><dd data-current="cloud_cover_percent">—</dd></div>
            <div><dt>Nederbörd</dt><dd data-current="precipitation_mm">—</dd></div>
            <div><dt>Regn</dt><dd data-current="rain_mm">—</dd></div>
            <div><dt>Skurar</dt><dd data-current="showers_mm">—</dd></div>
            <div><dt>Lufttryck, havsnivå</dt><dd data-current="pressure_msl_hpa">—</dd></div>
            <div><dt>Lufttryck, marknivå</dt><dd data-current="surface_pressure_hpa">—</dd></div>
            <div><dt>Vind</dt><dd data-current="wind_speed_kmh">—</dd></div>
            <div><dt>Vindbyar</dt><dd data-current="wind_gusts_kmh">—</dd></div>
            <div><dt>Vindriktning</dt><dd data-current="wind_direction">—</dd></div>
            <div><dt>Väderkod</dt><dd data-current="weather_code">—</dd></div>
            <div><dt>Dag/natt</dt><dd data-current="day_status">—</dd></div>
          </dl>
        </section>

        <section class="metro-section" aria-labelledby="metro-hourly-title">
          <div class="metro-section__heading">
            <div>
              <p class="kicker">Nästa 24 timmar</p>
              <h2 id="metro-hourly-title">Timprognos</h2>
            </div>
            <span class="metro-scroll-hint">Dra i sidled →</span>
          </div>
          <div class="metro-hourly" data-cm-hourly tabindex="0" aria-label="Timprognos, rulla i sidled"></div>
        </section>

        <section class="metro-section" aria-labelledby="metro-daily-title">
          <div class="metro-section__heading">
            <div>
              <p class="kicker">Veckoläge</p>
              <h2 id="metro-daily-title">7-dygnsprognos</h2>
            </div>
          </div>
          <div class="metro-daily" data-cm-daily></div>
        </section>

        <p class="metro-attribution">
          Väderdata från <a href="https://open-meteo.com/" target="_blank" rel="noopener noreferrer">Open-Meteo</a>.
          Lokal tid visas i tidszonen Asia/Bangkok.
          <span data-cm-updated></span>
        </p>
      </div>

      <noscript>
        <div class="empty-state">JavaScript behöver vara aktiverat för att visa den aktuella väderrapporten.</div>
      </noscript>
    </div>
  </main>

  <?php public_footer(); ?>
  <script src="<?= e(asset_url('assets/metro.js')) ?>" defer></script>
</body>
</html>
