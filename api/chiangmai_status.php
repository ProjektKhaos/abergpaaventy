<?php
// Senast uppdaterad: 2026-07-11 20:10 | av: KlⒶssⓔ & Ⓐberg
// Internt JSON-API för Chiang Mai-tid och väder.

require_once __DIR__ . '/../app/ChiangMaiWeather.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

$service = new ChiangMaiWeatherService();

try {
    $payload = $service->status();
    http_response_code(200);
} catch (Throwable) {
    http_response_code(502);
    $payload = [
        'success' => false,
        'message' => 'Kunde inte hämta väderdata just nu.',
        'local_time' => $service->localTime(),
        'meta' => [
            'provider' => 'Open-Meteo',
            'generated_at' => (new DateTimeImmutable('now', new DateTimeZone(ChiangMaiWeatherService::TIMEZONE)))
                ->format(DateTimeInterface::ATOM),
            'cache_seconds' => ChiangMaiWeatherService::CACHE_SECONDS,
        ],
    ];
}

echo json_encode(
    $payload,
    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE
);
