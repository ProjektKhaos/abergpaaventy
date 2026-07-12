// Senast uppdaterad: 2026-07-11 20:20 | av: KlⒶssⓔ & Ⓐberg
// Frontend för Chiang Mai Metro. All extern text skrivs med textContent.
'use strict';

(() => {
  const root = document.querySelector('.metro-page[data-weather-endpoint]');
  if (!root) return;

  const endpoint = root.dataset.weatherEndpoint;
  const status = root.querySelector('[data-cm-status]');
  const content = root.querySelector('[data-cm-content]');
  const clock = root.querySelector('[data-cm-clock]');
  const dateLabel = root.querySelector('[data-cm-date]');
  let clockInstant = null;
  let clockStartedAt = 0;
  let clockTimer = null;
  let hasSuccessfulData = false;

  const numberFormatter = new Intl.NumberFormat('sv-SE', { maximumFractionDigits: 1 });
  const integerFormatter = new Intl.NumberFormat('sv-SE', { maximumFractionDigits: 0 });

  function hasValue(value) {
    return value !== null && value !== undefined && value !== '' && Number.isFinite(Number(value));
  }

  function number(value, suffix = '', integer = false) {
    if (!hasValue(value)) return '—';
    return `${(integer ? integerFormatter : numberFormatter).format(Number(value))}${suffix}`;
  }

  function weatherSymbol(code) {
    const value = Number(code);
    if (value === 0) return '☀';
    if ([1, 2].includes(value)) return '⛅';
    if (value === 3) return '☁';
    if ([45, 48].includes(value)) return '≋';
    if ([51, 53, 55, 56, 57].includes(value)) return '🌦';
    if ([61, 63, 65, 66, 67, 80, 81, 82].includes(value)) return '🌧';
    if ([71, 73, 75, 77, 85, 86].includes(value)) return '❄';
    if ([95, 96, 99].includes(value)) return '⛈';
    return '◌';
  }

  function setText(selector, value) {
    const element = root.querySelector(selector);
    if (element) element.textContent = value ?? '—';
  }

  function timePart(iso) {
    return typeof iso === 'string' && iso.length >= 16 ? iso.slice(11, 16) : '—';
  }

  function datePart(date) {
    if (typeof date !== 'string') return '—';
    const parsed = new Date(`${date}T12:00:00+07:00`);
    return Number.isNaN(parsed.getTime())
      ? date
      : new Intl.DateTimeFormat('sv-SE', { day: 'numeric', month: 'short', timeZone: 'Asia/Bangkok' }).format(parsed);
  }

  function startClock(iso) {
    const parsed = new Date(iso);
    if (Number.isNaN(parsed.getTime())) return;
    clockInstant = parsed;
    clockStartedAt = Date.now();

    if (!clockTimer) {
      clockTimer = window.setInterval(renderClock, 1000);
    }
    renderClock();
  }

  function renderClock() {
    if (!clockInstant) return;
    const now = new Date(clockInstant.getTime() + (Date.now() - clockStartedAt));
    clock.textContent = new Intl.DateTimeFormat('sv-SE', {
      hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false,
      timeZone: 'Asia/Bangkok'
    }).format(now);
    dateLabel.textContent = new Intl.DateTimeFormat('sv-SE', {
      weekday: 'long', day: 'numeric', month: 'long', year: 'numeric',
      timeZone: 'Asia/Bangkok'
    }).format(now).replace(/^./, character => character.toUpperCase());
  }

  function metric(parent, label, value) {
    const row = document.createElement('div');
    const term = document.createElement('dt');
    const detail = document.createElement('dd');
    term.textContent = label;
    detail.textContent = value;
    row.append(term, detail);
    parent.appendChild(row);
  }

  function renderHourly(items) {
    const container = root.querySelector('[data-cm-hourly]');
    container.replaceChildren();

    items.forEach((item, index) => {
      const card = document.createElement('article');
      card.className = 'metro-hour-card';
      if (index === 0) card.classList.add('metro-hour-card--current');

      const time = document.createElement('h3');
      time.textContent = index === 0 ? `Nu · ${item.time_label}` : item.time_label;
      const symbol = document.createElement('span');
      symbol.className = 'metro-hour-card__symbol';
      symbol.setAttribute('aria-hidden', 'true');
      symbol.textContent = weatherSymbol(item.weather_code);
      const description = document.createElement('p');
      description.className = 'metro-hour-card__description';
      description.textContent = item.description_sv;
      const temperature = document.createElement('strong');
      temperature.className = 'metro-hour-card__temperature';
      temperature.textContent = number(item.temperature_c, '°');
      const details = document.createElement('dl');
      details.className = 'metro-hour-card__details';
      metric(details, 'Känns', number(item.feels_like_c, '°'));
      metric(details, 'Regnrisk', number(item.precipitation_probability_percent, '%', true));
      metric(details, 'Nederbörd', number(item.precipitation_mm, ' mm'));
      metric(details, 'Moln', number(item.cloud_cover_percent, '%', true));
      metric(details, 'Vind', number(item.wind_speed_kmh, ' km/h'));
      metric(details, 'Byar', number(item.wind_gusts_kmh, ' km/h'));
      metric(details, 'UV', number(item.uv_index));
      metric(details, 'Sikt', hasValue(item.visibility_m) ? number(Number(item.visibility_m) / 1000, ' km') : '—');

      card.append(time, symbol, temperature, description, details);
      container.appendChild(card);
    });
  }

  function renderDaily(items) {
    const container = root.querySelector('[data-cm-daily]');
    container.replaceChildren();

    items.forEach((item, index) => {
      const card = document.createElement('article');
      card.className = 'metro-day-card';
      const heading = document.createElement('div');
      heading.className = 'metro-day-card__heading';
      const titleWrap = document.createElement('div');
      const title = document.createElement('h3');
      title.textContent = index === 0 ? 'Idag' : item.weekday;
      const date = document.createElement('span');
      date.textContent = datePart(item.date);
      titleWrap.append(title, date);
      const symbol = document.createElement('span');
      symbol.className = 'metro-day-card__symbol';
      symbol.setAttribute('aria-hidden', 'true');
      symbol.textContent = weatherSymbol(item.weather_code);
      heading.append(titleWrap, symbol);

      const description = document.createElement('p');
      description.className = 'metro-day-card__description';
      description.textContent = item.description_sv;
      const temperatures = document.createElement('p');
      temperatures.className = 'metro-day-card__temperature';
      temperatures.textContent = `${number(item.temperature_max_c, '°')} / ${number(item.temperature_min_c, '°')}`;
      const details = document.createElement('dl');
      details.className = 'metro-day-card__details';
      metric(details, 'Känns som', `${number(item.feels_like_max_c, '°')} / ${number(item.feels_like_min_c, '°')}`);
      metric(details, 'Regnrisk', number(item.precipitation_probability_max_percent, '%', true));
      metric(details, 'Nederbörd', number(item.precipitation_sum_mm, ' mm'));
      metric(details, 'Regn / skurar', `${number(item.rain_sum_mm, ' mm')} / ${number(item.showers_sum_mm, ' mm')}`);
      metric(details, 'UV max', number(item.uv_index_max));
      metric(details, 'Vind max', number(item.wind_speed_max_kmh, ' km/h'));
      metric(details, 'Vindbyar', number(item.wind_gusts_max_kmh, ' km/h'));
      metric(details, 'Sol upp / ner', `${timePart(item.sunrise)} / ${timePart(item.sunset)}`);
      card.append(heading, description, temperatures, details);
      container.appendChild(card);
    });
  }

  function render(data) {
    const current = data.current_weather;
    startClock(data.local_time.iso);
    setText('[data-cm-symbol]', weatherSymbol(current.weather_code));
    setText('[data-cm-temperature]', number(current.temperature_c, '°C'));
    setText('[data-cm-description]', current.description_sv);
    setText('[data-cm-feels-like]', number(current.feels_like_c, '°C'));
    setText('[data-cm-day-status]', current.is_day ? 'Dagsljus i Chiang Mai' : 'Natt i Chiang Mai');
    setText('[data-cm-travel-note]', current.travel_note_sv);
    setText('[data-cm-source-time]', `Källtid ${timePart(current.source_time)}`);
    setText('[data-cm-updated]', `Senast hämtad ${timePart(data.meta.generated_at)}.`);

    const values = {
      humidity_percent: number(current.humidity_percent, '%', true),
      cloud_cover_percent: number(current.cloud_cover_percent, '%', true),
      precipitation_mm: number(current.precipitation_mm, ' mm'),
      rain_mm: number(current.rain_mm, ' mm'),
      showers_mm: number(current.showers_mm, ' mm'),
      pressure_msl_hpa: number(current.pressure_msl_hpa, ' hPa'),
      surface_pressure_hpa: number(current.surface_pressure_hpa, ' hPa'),
      wind_speed_kmh: number(current.wind_speed_kmh, ' km/h'),
      wind_gusts_kmh: number(current.wind_gusts_kmh, ' km/h'),
      wind_direction: `${current.wind_direction_text_sv ?? '—'} · ${number(current.wind_direction_degrees, '°', true)}`,
      weather_code: current.weather_code ?? '—',
      day_status: current.is_day ? 'Dag' : 'Natt'
    };
    Object.entries(values).forEach(([key, value]) => setText(`[data-current="${key}"]`, String(value)));

    renderHourly(data.hourly_forecast);
    renderDaily(data.daily_forecast);
    content.hidden = false;
    status.hidden = true;
    hasSuccessfulData = true;
  }

  async function loadWeather() {
    if (!hasSuccessfulData) {
      status.hidden = false;
      status.className = 'metro-status';
      status.textContent = 'Hämtar den senaste väderrapporten…';
    }

    try {
      const response = await fetch(endpoint, { headers: { Accept: 'application/json' }, cache: 'no-store' });
      const data = await response.json();
      // Felresponsen innehåller fortfarande lokal Chiang Mai-tid.
      if (data?.local_time?.iso) startClock(data.local_time.iso);
      if (!response.ok || !data.success) throw new Error(data.message || 'Väderrapporten kunde inte hämtas.');
      render(data);
    } catch (error) {
      status.hidden = false;
      status.className = hasSuccessfulData ? 'metro-status metro-status--warning' : 'metro-status metro-status--error';
      status.textContent = hasSuccessfulData
        ? 'Kunde inte uppdatera just nu. Senast hämtade väderdata visas fortfarande.'
        : 'Kunde inte hämta väderdata just nu. Försök igen om en stund.';
    }
  }

  loadWeather();
  window.setInterval(loadWeather, 5 * 60 * 1000);
})();
