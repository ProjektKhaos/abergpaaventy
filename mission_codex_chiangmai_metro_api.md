# Mission till Codex: Chiang Mai Metro-sida med väder, tid och återanvändbar widget

## Projekt
Åberg På Äventyr / abergpaaventyr.se

## Datum
2026-07-11

## Mål
Bygg en ny sida `metro.php` som visar aktuell lokal tid, datum och mycket väderinformation för Chiang Mai, Thailand.

Bygg samtidigt en egen intern API-endpoint som hämtar väderdata och returnerar strukturerad JSON. Lösningen ska vara enkel att återanvända var som helst på siten, exempelvis i headern, som liten textrad, kort/widget eller på egna sidor.

Sidan `metro.php` ska först vara huvudplatsen där allt visas. Header-widgeten ska förberedas, men inte nödvändigtvis aktiveras överallt om det riskerar att störa nuvarande layout.

---

## Viktigt
Ändra inte sitens grunddesign i onödan. Följ befintlig struktur, helpers, layout och CSS-principer.

Siten är plain PHP/PDO + MariaDB, men denna funktion ska helst fungera utan databas.

Använd Open-Meteo för väderdata. Open-Meteo kräver ingen API-nyckel för normal användning och har Forecast API med current/hourly/daily-värden.

Källor att utgå från:
- Open-Meteo Weather Forecast API: https://open-meteo.com/en/docs
- Open-Meteo About / no API key: https://open-meteo.com/en/about
- PHP timezone: `Asia/Bangkok`

---

## Filer som ska skapas

### 1. `api/chiangmai_status.php`
Skapa en ny API-fil:

```text
/api/chiangmai_status.php
```

Den ska returnera JSON med:

- `success`
- `location`
- `local_time`
- `current_weather`
- `hourly_forecast`
- `daily_forecast`
- `meta`

API:t ska använda Chiang Mai-koordinater:

```php
$latitude = 18.7883;
$longitude = 98.9853;
$timezone = 'Asia/Bangkok';
```

API:t ska hämta så mycket relevant väderdata som möjligt utan att bli onödigt tungt.

Rekommenderad Open-Meteo-query:

```text
https://api.open-meteo.com/v1/forecast
?latitude=18.7883
&longitude=98.9853
&current=temperature_2m,relative_humidity_2m,apparent_temperature,is_day,precipitation,rain,showers,weather_code,cloud_cover,pressure_msl,surface_pressure,wind_speed_10m,wind_direction_10m,wind_gusts_10m
&hourly=temperature_2m,relative_humidity_2m,apparent_temperature,precipitation_probability,precipitation,rain,showers,weather_code,cloud_cover,visibility,wind_speed_10m,wind_direction_10m,wind_gusts_10m,uv_index
&daily=weather_code,temperature_2m_max,temperature_2m_min,apparent_temperature_max,apparent_temperature_min,sunrise,sunset,uv_index_max,precipitation_sum,rain_sum,showers_sum,precipitation_probability_max,wind_speed_10m_max,wind_gusts_10m_max
&timezone=Asia%2FBangkok
&forecast_days=7
```

Skriv URL:en i PHP med `http_build_query()` så den blir lätt att underhålla.

---

## API-krav

### JSON-struktur
API:t ska returnera ungefär detta:

```json
{
  "success": true,
  "location": {
    "name": "Chiang Mai",
    "country": "Thailand",
    "latitude": 18.7883,
    "longitude": 98.9853,
    "timezone": "Asia/Bangkok"
  },
  "local_time": {
    "date": "2026-07-11",
    "time": "23:42:10",
    "weekday": "Lördag",
    "iso": "2026-07-11T23:42:10+07:00"
  },
  "current_weather": {
    "description_sv": "Delvis molnigt",
    "weather_code": 2,
    "temperature_c": 29.5,
    "feels_like_c": 34.1,
    "humidity_percent": 78,
    "precipitation_mm": 0,
    "rain_mm": 0,
    "showers_mm": 0,
    "cloud_cover_percent": 62,
    "pressure_msl_hpa": 1007.2,
    "surface_pressure_hpa": 987.4,
    "wind_speed_kmh": 8.2,
    "wind_direction_degrees": 210,
    "wind_direction_text_sv": "Sydväst",
    "wind_gusts_kmh": 18.5,
    "is_day": true,
    "source_time": "2026-07-11T23:30"
  },
  "hourly_forecast": [],
  "daily_forecast": [],
  "meta": {
    "provider": "Open-Meteo",
    "generated_at": "2026-07-11T23:42:10+07:00",
    "cache_seconds": 900
  }
}
```

### Felhantering
Om Open-Meteo inte svarar ska API:t returnera:

- HTTP-status `502`
- `success: false`
- lokal tid/datum ska ändå returneras
- tydligt felmeddelande på svenska

Exempel:

```json
{
  "success": false,
  "message": "Kunde inte hämta väderdata just nu.",
  "local_time": {}
}
```

### Caching
Lägg gärna in enkel filcache för att inte hämta vädret vid varje sidladdning.

Förslag:

```text
storage/cache/chiangmai_weather.json
```

Cachetid:

```php
$cacheSeconds = 900; // 15 minuter
```

Om `storage/cache/` inte finns, skapa den automatiskt om rättigheter tillåter. Om cache inte kan skrivas ska API:t ändå fungera utan att krascha.

### Säkerhet
- Ingen API-nyckel ska användas.
- Ingen databas ska krävas.
- Ingen användarinput behövs.
- Sätt JSON-header:

```php
header('Content-Type: application/json; charset=utf-8');
```

- Använd rimlig timeout vid extern hämtning, exempelvis 5–8 sekunder.
- Använd cURL om möjligt, annars fallback till `file_get_contents()`.

---

## Hjälpfunktioner

Skapa gärna en separat hjälpfunktion i API-filen eller i en ny include om det passar strukturen bättre.

Behövs:

### 1. Väderkod till svensk text
Implementera WMO weather code till svensk text.

Exempel:

```php
function weatherTextSv(?int $code): string
{
    return match ($code) {
        0 => 'Klart väder',
        1 => 'Mestadels klart',
        2 => 'Delvis molnigt',
        3 => 'Mulet',
        45, 48 => 'Dimma',
        51, 53, 55 => 'Duggregn',
        61, 63, 65 => 'Regn',
        80, 81, 82 => 'Regnskurar',
        95 => 'Åska',
        96, 99 => 'Åska med hagel',
        default => 'Okänt väderläge',
    };
}
```

### 2. Vindriktning till svensk text
Implementera grader till text:

- Norr
- Nordost
- Öst
- Sydost
- Syd
- Sydväst
- Väst
- Nordväst

Exempel:

```php
function windDirectionSv(?float $degrees): string
{
    if ($degrees === null) {
        return 'Okänd riktning';
    }

    $directions = [
        'Norr', 'Nordost', 'Öst', 'Sydost',
        'Syd', 'Sydväst', 'Väst', 'Nordväst'
    ];

    $index = (int) round($degrees / 45) % 8;
    return $directions[$index];
}
```

### 3. Veckodag på svenska
Returnera veckodag på svenska:

- Måndag
- Tisdag
- Onsdag
- Torsdag
- Fredag
- Lördag
- Söndag

---

## Filer som ska ändras

### 2. `metro.php`
Skapa ny publik sida:

```text
/metro.php
```

Sidan ska använda befintlig publik layout om möjligt, exempelvis `app/public_layout.php`, `require_once app/helpers.php`, eller motsvarande enligt projektets struktur.

Sidan ska heta ungefär:

```text
Chiang Mai Metro
```

eller:

```text
Metro: Chiang Mai just nu
```

Sidan ska visa mycket väderinformation, inte bara en liten ruta.

### Innehåll på sidan
Sidan ska visa:

#### Toppsektion
- Rubrik: `Chiang Mai Metro`
- Undertitel: exempelvis `Tid, datum och väder just nu inför Åbergs äventyr i Thailand.`
- Lokal tid i Chiang Mai, stor och tydlig
- Datum och veckodag
- Aktuell temperatur
- Känns som
- Väderbeskrivning
- Dag/natt-status

#### Aktuellt väderkort
Visa:

- Temperatur
- Känns som
- Luftfuktighet
- Molntäcke
- Nederbörd
- Regn
- Skurar
- Lufttryck
- Vindhastighet
- Vindbyar
- Vindriktning
- Väderkod
- Källtid från Open-Meteo

#### Timprognos
Visa kommande 12–24 timmar.

Minst:

- Tid
- Temperatur
- Känns som
- Regnsannolikhet
- Nederbörd
- Molntäcke
- Vind
- UV-index om tillgängligt
- Ikon/text för väderläge

Det kan vara en horisontell scroll-lista på mobil.

#### 7-dygnsprognos
Visa 7 dagar.

Minst:

- Datum/veckodag
- Vädertext
- Min/max temperatur
- Känns som min/max
- Regnsumma
- Regnsannolikhet max
- UV max
- Vind max
- Vindbyar max
- Soluppgång
- Solnedgång

#### Datakälla
Liten text längst ner:

```text
Väderdata från Open-Meteo. Lokal tid visas i tidszonen Asia/Bangkok.
```

---

## Frontend-logik

`metro.php` får gärna rendera grundlayouten i PHP och sedan hämta API:t med JavaScript:

```js
fetch('/api/chiangmai_status.php')
```

Men tänk på portabilitet. Om projektet har `url()`-helper ska endpoint byggas så portabelt som möjligt.

Exempel i PHP:

```php
const API_ENDPOINT = '/api/chiangmai_status.php';
```

Eller om helper finns:

```php
$apiEndpoint = url('/api/chiangmai_status.php');
```

Sedan:

```html
<script>
const CHIANGMAI_API_ENDPOINT = <?= json_encode($apiEndpoint) ?>;
</script>
```

### Uppdatering
- Hämta data vid sidladdning.
- Uppdatera automatiskt var 5:e minut.
- Visa laddningsläge.
- Visa felmeddelande om API:t inte svarar.

---

## Återanvändbar widget för header

Förbered en liten återanvändbar komponent som senare kan placeras i headern eller var som helst.

Förslag:

```text
partials/chiangmai_widget.php
```

eller om siten redan använder annan struktur:

```text
app/partials/chiangmai_widget.php
```

Widgeten ska vara liten och visa text i headern, exempelvis:

```text
Chiang Mai: 29°C · Kl 23:42 · Delvis molnigt
```

Krav:

- Ska kunna inkluderas var som helst.
- Ska inte krascha sidan om API:t inte svarar.
- Ska kunna köras som enkel HTML + JavaScript.
- Ska ha unik CSS-klass så den inte stör annan design.
- Den ska länka till `metro.php`.

Exempel på output:

```html
<a class="chiangmai-mini-widget" href="/metro.php">
    <span>Chiang Mai</span>
    <strong data-cm-temp>--°C</strong>
    <span data-cm-time>--:--</span>
</a>
```

Codex får skapa widgeten men ska inte automatiskt lägga in den globalt i headern om det riskerar layoutstrul. Lägg gärna en tydlig kommentar i `app/public_layout.php` där den kan aktiveras senare.

Exempel:

```php
<?php // include __DIR__ . '/partials/chiangmai_widget.php'; ?>
```

Om det finns en bättre naturlig plats i headern, lägg widgeten där men håll den diskret.

---

## CSS

Lägg CSS i befintlig publik CSS-fil, troligen:

```text
assets/style.css
```

Skapa tydligt kommenterad sektion:

```css
/* =========================================================
   Chiang Mai Metro / Weather page
   Senast uppdaterad: 2026-07-11
   ========================================================= */
```

CSS ska stödja:

- Mobil först
- Stora tydliga kort
- Responsiv grid
- Horisontell scroll för timprognos på små skärmar
- Mörk/läsbar text
- Stil som passar befintlig rese-/scrapbook-känsla

Lägg även mini-widget CSS:

```css
.chiangmai-mini-widget { ... }
```

Undvik att skriva CSS som påverkar globala element för mycket.

---

## Meny/länk

Lägg till en länk till `metro.php` i publik navigation om det finns en tydlig nav-lista i `app/public_layout.php`.

Länktext:

```text
Metro
```

eller:

```text
Chiang Mai Metro
```

Välj det som passar befintlig meny bäst.

---

## Portabilitet

Projektet ska kunna ligga i root eller undermapp.

Använd befintlig `url()`-helper om den finns.

Undvik hårdkodade länkar där projektets BASE_URL kan behöva respekteras.

Bra:

```php
href="<?= e(url('/metro.php')) ?>"
```

Dåligt:

```html
href="/abergpaaventyr/metro.php"
```

---

## Kodstil

Följ Hasses standard:

- Pedagogiska kommentarer i kod som kan behöva ändras senare.
- Nya PHP/HTML-filer ska ha tydlig versionsrad nära toppen.
- Skriv svenska kommentarer där det hjälper.
- Behåll befintlig struktur.
- Skapa inte onödiga externa beroenden.

Exempel på filhuvud:

```php
<?php
// Senast uppdaterad: 2026-07-11 19:30 | av: KlⒶssⓔ & Ⓐberg
```

---

## Testkrav

Efter implementation ska Codex testa:

### PHP-syntax

```bash
find . -name "*.php" -not -path "./vendor/*" -print0 | xargs -0 -n1 php -l
```

### API-test

```bash
curl -s http://localhost/api/chiangmai_status.php | jq .
```

Om `jq` saknas:

```bash
curl -s http://localhost/api/chiangmai_status.php
```

Kontrollera att:

- `success` är `true`
- `local_time.time` visas
- `current_weather.temperature_c` finns
- `hourly_forecast` innehåller data
- `daily_forecast` innehåller 7 dagar

### Sidtest

Öppna:

```text
/metro.php
```

Kontrollera att:

- sidan laddar utan PHP-fel
- laddningsläge visas kort
- väderdata visas
- timprognos syns
- 7-dygnsprognos syns
- mobilvy fungerar
- navigationen har länk till sidan om det lades till

---

## Acceptanskriterier

Mission är klart när:

- `api/chiangmai_status.php` finns och returnerar strukturerad JSON.
- `metro.php` finns och visar aktuell tid/datum/väder för Chiang Mai.
- Sidan visar mycket väderinformation, inklusive aktuell data, timprognos och 7-dygnsprognos.
- En liten återanvändbar widget är förberedd för header/valfri plats.
- Länk till `metro.php` finns i navigationen, om det passar befintlig layout.
- Lösningen fungerar utan API-nyckel och utan databas.
- PHP lint passerar.
- Inga befintliga sidor går sönder.
- Koden är portabel och följer befintlig helper-struktur.

---

## Viktigt att inte göra

- Bygg inte om hela siten.
- Ändra inte startsidan `index.php` i detta mission.
- Lägg inte in känsliga nycklar eller hemligheter.
- Skapa inte databasberoende för vädret.
- Lägg inte in tungt JS-ramverk.
- Lägg inte widgeten globalt om det riskerar att förstöra headern.

---

## Extra förbättring om tid finns

Om allt ovan är klart, lägg till en enkel “resefeeling”-text baserad på vädret.

Exempel:

- Vid mycket regn: `Perfekt dag för café, planering och lite Åberg-mys under tak.`
- Vid hög UV: `Soligt läge – keps, vatten och skugga är kung idag.`
- Vid behaglig kvällstemperatur: `Bra kväll för promenad, street food och upptäcktsläge.`

Detta ska vara dekorativt och får inte störa väderinformationen.

