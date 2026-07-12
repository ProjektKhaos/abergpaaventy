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
 * Sanerar enkel artikel-HTML från admineditorn.
 */
function sanitize_html(string $html): string
{
    $html = trim($html);
    if ($html === '') {
        return '';
    }

    $allowed_tags = ['p','br','strong','b','em','i','u','ul','ol','li','a','h2','h3','blockquote'];
    $drop_tags = ['script','style','iframe','object','embed'];
    $allowed_attrs = ['a' => ['href','target','rel']];

    $dom = new DOMDocument('1.0', 'UTF-8');
    $previous = libxml_use_internal_errors(true);
    $dom->loadHTML(
        '<?xml encoding="utf-8" ?><div id="__html_root__">' . $html . '</div>',
        LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
    );
    libxml_clear_errors();
    libxml_use_internal_errors($previous);

    $root = $dom->getElementById('__html_root__');
    if (!$root) {
        return '';
    }

    $clean_node = static function (DOMNode $node) use (&$clean_node, $allowed_tags, $allowed_attrs, $drop_tags): void {
        if ($node instanceof DOMElement) {
            $tag = strtolower($node->tagName);
            if (in_array($tag, $drop_tags, true)) {
                $node->parentNode?->removeChild($node);
                return;
            }

            if (!in_array($tag, $allowed_tags, true) && $node->getAttribute('id') !== '__html_root__') {
                $parent = $node->parentNode;
                $children = [];
                while ($node->firstChild) {
                    $child = $node->firstChild;
                    $children[] = $child;
                    $parent->insertBefore($child, $node);
                }
                $parent->removeChild($node);
                foreach ($children as $child) {
                    $clean_node($child);
                }
                return;
            }

            foreach (iterator_to_array($node->attributes) as $attr) {
                $name = strtolower($attr->name);
                if (!in_array($name, $allowed_attrs[$tag] ?? [], true)) {
                    $node->removeAttribute($attr->name);
                }
            }

            if ($tag === 'a') {
                $href = trim($node->getAttribute('href'));
                $is_safe_href = preg_match('/^(https?:|mailto:|\\/|#)/i', $href) === 1;
                if ($href === '' || !$is_safe_href) {
                    $node->removeAttribute('href');
                    $node->removeAttribute('target');
                    $node->removeAttribute('rel');
                } elseif ($node->getAttribute('target') === '_blank') {
                    $node->setAttribute('rel', 'noopener noreferrer');
                }
            }
        }

        foreach (iterator_to_array($node->childNodes) as $child) {
            $clean_node($child);
        }
    };

    $clean_node($root);

    $output = '';
    foreach ($root->childNodes as $child) {
        $output .= $dom->saveHTML($child);
    }

    return trim($output);
}

/**
 * Förbereder befintlig brödtext för WYSIWYG-editorn.
 */
function editor_body_html(string $body): string
{
    if ($body === '') {
        return '';
    }

    if ($body !== strip_tags($body)) {
        return sanitize_html($body);
    }

    return nl2br(e($body));
}

/**
 * Renderar artikelns brödtext publikt.
 */
function render_post_body(string $body): string
{
    if ($body !== strip_tags($body)) {
        return sanitize_html($body);
    }

    return nl2br(e($body));
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
