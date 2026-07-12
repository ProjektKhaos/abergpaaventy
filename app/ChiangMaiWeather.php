<?php
// Senast uppdaterad: 2026-07-11 20:10 | av: KlⒶssⓔ & Ⓐberg
// Fristående vädertjänst för Chiang Mai. Kräver varken databas eller API-nyckel.

final class ChiangMaiWeatherService
{
    public const LATITUDE = 18.7883;
    public const LONGITUDE = 98.9853;
    public const TIMEZONE = 'Asia/Bangkok';
    public const CACHE_SECONDS = 900;

    private string $cacheFile;
    private string $endpoint;
    private DateTimeZone $timezone;

    public function __construct(?string $cacheFile = null, ?string $endpoint = null)
    {
        $this->cacheFile = $cacheFile ?? __DIR__ . '/../storage/cache/chiangmai_weather.json';
        $this->endpoint = $endpoint ?? 'https://api.open-meteo.com/v1/forecast';
        $this->timezone = new DateTimeZone(self::TIMEZONE);
    }

    /**
     * Hämtar och normaliserar all data som den interna endpointen ska exponera.
     */
    public function status(): array
    {
        [$upstream, $cacheHit] = $this->weatherData();
        $now = new DateTimeImmutable('now', $this->timezone);
        $daily = $this->normalizeDaily($upstream['daily'] ?? []);
        $current = $this->normalizeCurrent($upstream['current'] ?? [], $daily[0] ?? null);

        return [
            'success' => true,
            'location' => [
                'name' => 'Chiang Mai',
                'country' => 'Thailand',
                'latitude' => self::LATITUDE,
                'longitude' => self::LONGITUDE,
                'timezone' => self::TIMEZONE,
            ],
            'local_time' => $this->localTime($now),
            'current_weather' => $current,
            'hourly_forecast' => $this->normalizeHourly($upstream['hourly'] ?? [], $now),
            'daily_forecast' => $daily,
            'meta' => [
                'provider' => 'Open-Meteo',
                'generated_at' => $now->format(DateTimeInterface::ATOM),
                'cache_seconds' => self::CACHE_SECONDS,
                'cache_hit' => $cacheHit,
            ],
        ];
    }

    public function localTime(?DateTimeImmutable $now = null): array
    {
        $now ??= new DateTimeImmutable('now', $this->timezone);

        return [
            'date' => $now->format('Y-m-d'),
            'time' => $now->format('H:i:s'),
            'weekday' => self::weekdaySv($now),
            'iso' => $now->format(DateTimeInterface::ATOM),
        ];
    }

    public static function weatherTextSv(?int $code): string
    {
        return match ($code) {
            0 => 'Klart väder',
            1 => 'Mestadels klart',
            2 => 'Delvis molnigt',
            3 => 'Mulet',
            45, 48 => 'Dimma',
            51, 53, 55 => 'Duggregn',
            56, 57 => 'Underkylt duggregn',
            61, 63, 65 => 'Regn',
            66, 67 => 'Underkylt regn',
            71, 73, 75 => 'Snöfall',
            77 => 'Snökorn',
            80, 81, 82 => 'Regnskurar',
            85, 86 => 'Snöbyar',
            95 => 'Åska',
            96, 99 => 'Åska med hagel',
            default => 'Okänt väderläge',
        };
    }

    public static function windDirectionSv(?float $degrees): string
    {
        if ($degrees === null) {
            return 'Okänd riktning';
        }

        $directions = ['Norr', 'Nordost', 'Öst', 'Sydost', 'Syd', 'Sydväst', 'Väst', 'Nordväst'];
        $normalized = fmod($degrees, 360.0);
        if ($normalized < 0) {
            $normalized += 360.0;
        }

        return $directions[((int)round($normalized / 45)) % 8];
    }

    public static function weekdaySv(DateTimeInterface $date): string
    {
        $days = [1 => 'Måndag', 'Tisdag', 'Onsdag', 'Torsdag', 'Fredag', 'Lördag', 'Söndag'];
        return $days[(int)$date->format('N')];
    }

    private function weatherData(): array
    {
        $cached = $this->readFreshCache();
        if ($cached !== null) {
            return [$cached, true];
        }

        $url = $this->buildUrl();
        $body = $this->requestWithCurl($url);
        if ($body === null) {
            $body = $this->requestWithStreams($url);
        }
        if ($body === null) {
            throw new RuntimeException('Kunde inte kontakta Open-Meteo.');
        }

        try {
            $data = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new RuntimeException('Open-Meteo returnerade ogiltig JSON.', 0, $e);
        }

        if (!$this->isValidWeatherData($data)) {
            throw new RuntimeException('Open-Meteo returnerade ofullständig väderdata.');
        }

        $this->writeCache($body);
        return [$data, false];
    }

    private function buildUrl(): string
    {
        $params = [
            'latitude' => self::LATITUDE,
            'longitude' => self::LONGITUDE,
            'current' => implode(',', [
                'temperature_2m', 'relative_humidity_2m', 'apparent_temperature', 'is_day',
                'precipitation', 'rain', 'showers', 'weather_code', 'cloud_cover',
                'pressure_msl', 'surface_pressure', 'wind_speed_10m',
                'wind_direction_10m', 'wind_gusts_10m',
            ]),
            'hourly' => implode(',', [
                'temperature_2m', 'relative_humidity_2m', 'apparent_temperature',
                'precipitation_probability', 'precipitation', 'rain', 'showers',
                'weather_code', 'cloud_cover', 'visibility', 'wind_speed_10m',
                'wind_direction_10m', 'wind_gusts_10m', 'uv_index',
            ]),
            'daily' => implode(',', [
                'weather_code', 'temperature_2m_max', 'temperature_2m_min',
                'apparent_temperature_max', 'apparent_temperature_min', 'sunrise', 'sunset',
                'uv_index_max', 'precipitation_sum', 'rain_sum', 'showers_sum',
                'precipitation_probability_max', 'wind_speed_10m_max', 'wind_gusts_10m_max',
            ]),
            'timezone' => self::TIMEZONE,
            'forecast_days' => 7,
        ];

        return $this->endpoint . '?' . http_build_query($params, '', '&', PHP_QUERY_RFC3986);
    }

    private function requestWithCurl(string $url): ?string
    {
        if (!function_exists('curl_init')) {
            return null;
        }

        $curl = curl_init($url);
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 3,
            CURLOPT_TIMEOUT => 7,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_HTTPHEADER => ['Accept: application/json'],
            CURLOPT_USERAGENT => 'AbergPaAventyr-Weather/1.0',
        ]);
        if (defined('CURLOPT_PROTOCOLS') && defined('CURLPROTO_HTTPS')) {
            curl_setopt($curl, CURLOPT_PROTOCOLS, CURLPROTO_HTTPS);
        }

        $body = curl_exec($curl);
        $status = (int)curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
        $failed = $body === false || $status !== 200;
        curl_close($curl);

        return $failed ? null : (string)$body;
    }

    private function requestWithStreams(string $url): ?string
    {
        if (!filter_var(ini_get('allow_url_fopen'), FILTER_VALIDATE_BOOLEAN)) {
            return null;
        }

        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'timeout' => 7,
                'ignore_errors' => false,
                'header' => "Accept: application/json\r\nUser-Agent: AbergPaAventyr-Weather/1.0\r\n",
            ],
        ]);
        $body = @file_get_contents($url, false, $context);
        return $body === false ? null : $body;
    }

    private function readFreshCache(): ?array
    {
        if (!is_file($this->cacheFile) || (int)filemtime($this->cacheFile) < time() - self::CACHE_SECONDS) {
            return null;
        }

        $body = @file_get_contents($this->cacheFile);
        if ($body === false) {
            return null;
        }

        try {
            $data = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return null;
        }

        return $this->isValidWeatherData($data) ? $data : null;
    }

    private function writeCache(string $body): void
    {
        $directory = dirname($this->cacheFile);
        if (!is_dir($directory) && !@mkdir($directory, 0775, true) && !is_dir($directory)) {
            return;
        }
        if (!is_writable($directory)) {
            return;
        }

        $temporary = @tempnam($directory, 'weather_');
        if ($temporary === false) {
            return;
        }

        $written = @file_put_contents($temporary, $body, LOCK_EX);
        if ($written === false || !@rename($temporary, $this->cacheFile)) {
            @unlink($temporary);
        }
    }

    private function isValidWeatherData(mixed $data): bool
    {
        return is_array($data)
            && isset($data['current']['time'], $data['hourly']['time'], $data['daily']['time'])
            && is_array($data['hourly']['time'])
            && is_array($data['daily']['time'])
            && count($data['hourly']['time']) >= 24
            && count($data['daily']['time']) >= 7;
    }

    private function normalizeCurrent(array $current, ?array $today): array
    {
        $code = $this->intOrNull($current['weather_code'] ?? null);
        $windDirection = $this->floatOrNull($current['wind_direction_10m'] ?? null);
        $isDay = $this->intOrNull($current['is_day'] ?? null) === 1;
        $precipitation = $this->floatOrNull($current['precipitation'] ?? null);
        $temperature = $this->floatOrNull($current['temperature_2m'] ?? null);
        $uvMax = $this->floatOrNull($today['uv_index_max'] ?? null);

        return [
            'description_sv' => self::weatherTextSv($code),
            'weather_code' => $code,
            'temperature_c' => $temperature,
            'feels_like_c' => $this->floatOrNull($current['apparent_temperature'] ?? null),
            'humidity_percent' => $this->intOrNull($current['relative_humidity_2m'] ?? null),
            'precipitation_mm' => $precipitation,
            'rain_mm' => $this->floatOrNull($current['rain'] ?? null),
            'showers_mm' => $this->floatOrNull($current['showers'] ?? null),
            'cloud_cover_percent' => $this->intOrNull($current['cloud_cover'] ?? null),
            'pressure_msl_hpa' => $this->floatOrNull($current['pressure_msl'] ?? null),
            'surface_pressure_hpa' => $this->floatOrNull($current['surface_pressure'] ?? null),
            'wind_speed_kmh' => $this->floatOrNull($current['wind_speed_10m'] ?? null),
            'wind_direction_degrees' => $windDirection,
            'wind_direction_text_sv' => self::windDirectionSv($windDirection),
            'wind_gusts_kmh' => $this->floatOrNull($current['wind_gusts_10m'] ?? null),
            'is_day' => $isDay,
            'source_time' => is_string($current['time'] ?? null) ? $current['time'] : null,
            'travel_note_sv' => $this->travelNote($precipitation, $uvMax, $temperature, $isDay),
        ];
    }

    private function normalizeHourly(array $hourly, DateTimeImmutable $now): array
    {
        $result = [];
        $currentHour = $now->setTime((int)$now->format('H'), 0, 0);

        foreach (($hourly['time'] ?? []) as $index => $time) {
            if (!is_string($time)) {
                continue;
            }

            try {
                $date = new DateTimeImmutable($time, $this->timezone);
            } catch (Exception) {
                continue;
            }
            if ($date < $currentHour) {
                continue;
            }

            $code = $this->intOrNull($this->at($hourly, 'weather_code', $index));
            $windDirection = $this->floatOrNull($this->at($hourly, 'wind_direction_10m', $index));
            $result[] = [
                'time' => $time,
                'time_label' => $date->format('H:i'),
                'description_sv' => self::weatherTextSv($code),
                'weather_code' => $code,
                'temperature_c' => $this->floatOrNull($this->at($hourly, 'temperature_2m', $index)),
                'feels_like_c' => $this->floatOrNull($this->at($hourly, 'apparent_temperature', $index)),
                'humidity_percent' => $this->intOrNull($this->at($hourly, 'relative_humidity_2m', $index)),
                'precipitation_probability_percent' => $this->intOrNull($this->at($hourly, 'precipitation_probability', $index)),
                'precipitation_mm' => $this->floatOrNull($this->at($hourly, 'precipitation', $index)),
                'rain_mm' => $this->floatOrNull($this->at($hourly, 'rain', $index)),
                'showers_mm' => $this->floatOrNull($this->at($hourly, 'showers', $index)),
                'cloud_cover_percent' => $this->intOrNull($this->at($hourly, 'cloud_cover', $index)),
                'visibility_m' => $this->floatOrNull($this->at($hourly, 'visibility', $index)),
                'wind_speed_kmh' => $this->floatOrNull($this->at($hourly, 'wind_speed_10m', $index)),
                'wind_direction_degrees' => $windDirection,
                'wind_direction_text_sv' => self::windDirectionSv($windDirection),
                'wind_gusts_kmh' => $this->floatOrNull($this->at($hourly, 'wind_gusts_10m', $index)),
                'uv_index' => $this->floatOrNull($this->at($hourly, 'uv_index', $index)),
            ];

            if (count($result) === 24) {
                break;
            }
        }

        return $result;
    }

    private function normalizeDaily(array $daily): array
    {
        $result = [];
        foreach (array_slice($daily['time'] ?? [], 0, 7, true) as $index => $dateString) {
            if (!is_string($dateString)) {
                continue;
            }

            try {
                $date = new DateTimeImmutable($dateString, $this->timezone);
            } catch (Exception) {
                continue;
            }

            $code = $this->intOrNull($this->at($daily, 'weather_code', $index));
            $result[] = [
                'date' => $dateString,
                'weekday' => self::weekdaySv($date),
                'description_sv' => self::weatherTextSv($code),
                'weather_code' => $code,
                'temperature_max_c' => $this->floatOrNull($this->at($daily, 'temperature_2m_max', $index)),
                'temperature_min_c' => $this->floatOrNull($this->at($daily, 'temperature_2m_min', $index)),
                'feels_like_max_c' => $this->floatOrNull($this->at($daily, 'apparent_temperature_max', $index)),
                'feels_like_min_c' => $this->floatOrNull($this->at($daily, 'apparent_temperature_min', $index)),
                'sunrise' => $this->stringOrNull($this->at($daily, 'sunrise', $index)),
                'sunset' => $this->stringOrNull($this->at($daily, 'sunset', $index)),
                'uv_index_max' => $this->floatOrNull($this->at($daily, 'uv_index_max', $index)),
                'precipitation_sum_mm' => $this->floatOrNull($this->at($daily, 'precipitation_sum', $index)),
                'rain_sum_mm' => $this->floatOrNull($this->at($daily, 'rain_sum', $index)),
                'showers_sum_mm' => $this->floatOrNull($this->at($daily, 'showers_sum', $index)),
                'precipitation_probability_max_percent' => $this->intOrNull($this->at($daily, 'precipitation_probability_max', $index)),
                'wind_speed_max_kmh' => $this->floatOrNull($this->at($daily, 'wind_speed_10m_max', $index)),
                'wind_gusts_max_kmh' => $this->floatOrNull($this->at($daily, 'wind_gusts_10m_max', $index)),
            ];
        }

        return $result;
    }

    private function travelNote(?float $precipitation, ?float $uvMax, ?float $temperature, bool $isDay): string
    {
        if (($precipitation ?? 0) >= 1) {
            return 'Perfekt dag för café, planering och lite Åberg-mys under tak.';
        }
        if ($isDay && ($uvMax ?? 0) >= 7) {
            return 'Soligt läge – keps, vatten och skugga är kung idag.';
        }
        if (!$isDay && $temperature !== null && $temperature <= 28) {
            return 'Bra kväll för promenad, street food och upptäcktsläge.';
        }
        return $isDay
            ? 'Bra läge för ett lagom äventyr i Chiang Mai.'
            : 'Chiang Mai-kväll – ta det lugnt och njut av staden.';
    }

    private function at(array $source, string $key, int|string $index): mixed
    {
        return is_array($source[$key] ?? null) ? ($source[$key][$index] ?? null) : null;
    }

    private function floatOrNull(mixed $value): ?float
    {
        return is_numeric($value) ? (float)$value : null;
    }

    private function intOrNull(mixed $value): ?int
    {
        return is_numeric($value) ? (int)$value : null;
    }

    private function stringOrNull(mixed $value): ?string
    {
        return is_string($value) && $value !== '' ? $value : null;
    }
}
