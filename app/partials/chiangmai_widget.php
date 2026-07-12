<?php
// Senast uppdaterad: 2026-07-11 20:20 | av: KlⒶssⓔ & Ⓐberg
// Fristående mini-widget. Kan inkluderas på valfri publik sida.

if (!function_exists('url')) {
    require_once __DIR__ . '/../config.php';
    require_once __DIR__ . '/../helpers.php';
}

$chiangmaiWidgetId = 'chiangmai-widget-' . bin2hex(random_bytes(4));
?>
<a id="<?= e($chiangmaiWidgetId) ?>"
   class="chiangmai-mini-widget"
   href="<?= e(url('metro.php')) ?>"
   data-endpoint="<?= e(url('api/chiangmai_status.php')) ?>"
   aria-label="Chiang Mai tid och väder">
  <span>Chiang Mai</span>
  <strong data-cm-widget-temp>--°C</strong>
  <span data-cm-widget-time>--:--</span>
  <span data-cm-widget-weather>Hämtar väder…</span>
</a>
<script>
(() => {
  const widget = document.getElementById(<?= json_encode($chiangmaiWidgetId) ?>);
  if (!widget) return;
  fetch(widget.dataset.endpoint, { headers: { Accept: 'application/json' }, cache: 'no-store' })
    .then(response => response.ok ? response.json() : Promise.reject())
    .then(data => {
      if (!data.success) throw new Error();
      const temperature = Number(data.current_weather.temperature_c);
      widget.querySelector('[data-cm-widget-temp]').textContent = Number.isFinite(temperature)
        ? `${new Intl.NumberFormat('sv-SE', { maximumFractionDigits: 1 }).format(temperature)}°C`
        : '—';
      widget.querySelector('[data-cm-widget-time]').textContent = data.local_time.time?.slice(0, 5) || '—';
      widget.querySelector('[data-cm-widget-weather]').textContent = data.current_weather.description_sv || 'Väder saknas';
    })
    .catch(() => {
      widget.classList.add('chiangmai-mini-widget--unavailable');
      widget.querySelector('[data-cm-widget-weather]').textContent = 'Väder tillfälligt otillgängligt';
    });
})();
</script>
