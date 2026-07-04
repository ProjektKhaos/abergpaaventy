<?php
// helpers.php – hjälpfunktioner för Thailand-bloggen Ⓐ Style

/**
 * Bygger en fullständig URL från en sökväg relativ till BASE_URL.
 * Exempel: url('post.php?slug=hello') → 'https://abergpaaventyr.se/post.php?slug=hello'
 */
function url(string $path = ''): string
{
    return BASE_URL . ($path ? '/' . ltrim($path, '/') : '');
}

/**
 * Bygger en URL till en statisk fil och lägger på filens ändringstid som cache-buster.
 */
function asset_url(string $path): string
{
    $cleanPath = strtok($path, '?') ?: $path;
    $filePath = __DIR__ . '/../' . ltrim($cleanPath, '/');
    $version = is_file($filePath) ? filemtime($filePath) : time();
    $separator = str_contains($path, '?') ? '&' : '?';

    return url($path) . $separator . 'v=' . $version;
}

/**
 * Bygger en URL med query string pa ett sakert och portabelt satt.
 */
function query_url(string $path, array $params = []): string
{
    $filtered = array_filter(
        $params,
        static fn ($value): bool => $value !== null
    );

    if (!$filtered) {
        return url($path);
    }

    $separator = str_contains($path, '?') ? '&' : '?';

    return url($path) . $separator . http_build_query($filtered, '', '&', PHP_QUERY_RFC3986);
}

/**
 * Bygger URL till en fil i uploads utan att tillata godtyckliga sokvagar.
 */
function upload_url(string $fileName): string
{
    $safeName = basename(str_replace('\\', '/', $fileName));

    return url('uploads/' . rawurlencode($safeName));
}

/**
 * Escaped output för HTML – skyddar mot XSS.
 * Används alltid när användardata skrivs ut i HTML.
 */
function e(mixed $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Genererar en CSRF-token och sparar den i sessionen.
 * Anropas en gång per formulär.
 */
function csrf_token(): string
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verifierar att POST-formulärets CSRF-token stämmer.
 * Avbryter skriptet om token saknas eller är fel.
 */
function csrf_verify(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(403);
        die('Ogiltig CSRF-token. Försök igen.');
    }
}

/**
 * Skapar ett URL-vänligt slug från en textsträng.
 * Exempel: "Mina tankar om livet" → "mina-tankar-om-livet"
 */
function slugify(string $text): string
{
    // Byt ut åäö mot ae/a/o
    $trans = ['å'=>'a','ä'=>'a','ö'=>'o','Å'=>'a','Ä'=>'a','Ö'=>'o'];
    $text  = strtr($text, $trans);
    // Bara a-z, 0-9 och bindestreck
    $text  = preg_replace('/[^a-z0-9]+/i', '-', strtolower($text));
    return trim($text, '-');
}

/**
 * Formaterar ett datum på ett trevligt svenskt sätt.
 * Exempel: "2026-05-27" → "27 maj 2026"
 */
function format_date(string $date): string
{
    $months = [
        1=>'januari',2=>'februari',3=>'mars',4=>'april',
        5=>'maj',6=>'juni',7=>'juli',8=>'augusti',
        9=>'september',10=>'oktober',11=>'november',12=>'december'
    ];
    $ts = strtotime($date);
    return date('j', $ts) . ' ' . $months[(int)date('n', $ts)] . ' ' . date('Y', $ts);
}

/**
 * Returnerar en säker bildlänk, eller placeholder om inget media finns.
 */
function cover_url(?array $media): string
{
    if (!$media) {
        return asset_url('assets/img/placeholder.svg');
    }
    return upload_url((string)$media['file_name']);
}
