---
name: mobile-first-web-codex
description: >
  Use this skill when Codex creates, reviews, or refactors HTML, CSS, PHP templates,
  frontend components, dashboards, landing pages, admin pages, or content pages.
  The default target is mobile-first, accessible, fast, portable, and safe web code.
  This skill is especially important for Åberg projects where the project directory
  is normally the DocumentRoot and links must be portable via BASE_URL/url()-style helpers.
---

# Mobile-first web for Codex

## Core mission

Build the small-screen version first, then enhance upward.

Codex must not produce desktop-shaped web code and then patch it with narrow-screen fixes.
The base CSS is for phones. Larger layouts are added with `@media (min-width: ...)`.

This skill applies whenever Codex touches:

- HTML
- CSS
- JavaScript UI
- PHP templates/views/includes
- admin dashboards
- landing pages
- web app screens
- printable HTML pages when they still need a browser preview
- existing sites that may have mobile, accessibility, SEO, or path issues

The result should be:

- usable on a 360px wide phone
- readable without zooming
- tappable without precision fingers
- free from accidental horizontal scroll
- portable between folders/domains
- safe enough to publish without exposing secrets
- simple enough for a future human to understand

## Åberg project defaults

Unless the user explicitly says otherwise, assume these defaults for Åberg projects:

1. The project folder is the web DocumentRoot.
2. Do not hardcode the project folder name in URLs or paths.
3. Use existing helpers such as `url()`, `asset_url()`, `e()`, `csrf_field()`, or project equivalents when they exist.
4. If no helper exists, propose or add a minimal helper instead of scattering hardcoded paths.
5. New PHP and HTML files should have a clear version/update line near the top.
6. Code should include practical, educational comments where future changes are likely.
7. Do not remove existing user text/content unless the task explicitly asks for removal.
8. Keep changes focused. Do not rewrite unrelated parts of the site just because they could be prettier.
9. Prefer plain, maintainable HTML/CSS/PHP over unnecessary frameworks.
10. If uploaded photos/assets are excluded from a ZIP, do not treat missing image files as fatal unless the code path itself is broken.

Suggested PHP version header style:

```php
<?php
// Senast uppdaterad: YYYY-MM-DD HH:MM | av: Codex / Klasse för Åberg
```

Suggested CSS version header style:

```css
/*
  Fil: assets/css/style.css
  Senast uppdaterad: YYYY-MM-DD HH:MM
  Syfte: Kort beskrivning av vad filen styr.
*/
```

## Working method for Codex

Before editing code, Codex should quickly inspect:

1. directory structure
2. entry points such as `index.php`, `header.php`, `footer.php`, `admin/*`
3. existing helpers/config files
4. current CSS architecture
5. upload/media handling
6. whether `.git`, secrets, local dumps, or generated files are accidentally present
7. whether the app assumes a subfolder, root folder, or custom `BASE_URL`

When changing code:

1. Preserve existing behavior unless the task says otherwise.
2. Make the smallest clean change that solves the real issue.
3. Prefer shared CSS classes over inline style duplication.
4. Keep public and admin styles separated if the project already does so.
5. Do not introduce dependencies unless there is a strong reason.
6. Do not add CDN dependencies for core layout unless the project already uses them.
7. Validate PHP syntax after PHP edits when possible.
8. For CSS/HTML changes, mentally test 360px, 640px, 900px, and desktop.
9. Mention any risky assumptions or files that could not be tested.

## Non-negotiable HTML

Every normal HTML page must include:

```html
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
```

Use semantic structure where possible:

```html
<header>
<nav aria-label="Huvudmeny">...</nav>
</header>

<main id="main-content">
  ...
</main>

<footer>
  ...
</footer>
```

Rules:

- One clear `<h1>` per page.
- Buttons that perform actions use `<button>`.
- Links that navigate use `<a>`.
- Images need meaningful `alt` text unless decorative.
- Decorative images use `alt=""`.
- Forms need labels associated with inputs.
- Do not rely on placeholder text as the only label.
- Use `lang="sv"` for Swedish pages unless the project is in another language.

## Non-negotiable CSS baseline

Every frontend stylesheet should effectively include these principles:

```css
*,
*::before,
*::after {
  box-sizing: border-box;
}

html {
  -webkit-text-size-adjust: 100%;
}

body {
  margin: 0;
  min-width: 0;
  font-size: 1rem;
  line-height: 1.5;
}

img,
svg,
video,
canvas {
  max-width: 100%;
  height: auto;
}

button,
input,
textarea,
select {
  font: inherit;
}

input,
textarea,
select {
  font-size: 16px;
}
```

Why `16px` on inputs: smaller input text can trigger unwanted iOS zoom.

Avoid:

```css
.container { width: 1200px; }
.page { min-width: 1000px; }
.hero { height: 100vh; }
```

Prefer:

```css
.container {
  width: min(100% - 2rem, 72rem);
  margin-inline: auto;
}

.hero {
  min-height: 100svh;
}
```

## Mobile-first breakpoints

Base styles are for phones. Enhance upward:

```css
/* Base: phones, approximately 320px and up */

@media (min-width: 640px) {
  /* large phones and small tablets */
}

@media (min-width: 900px) {
  /* tablets and small laptops */
}

@media (min-width: 1200px) {
  /* desktops */
}
```

Do not add many breakpoints just to chase individual devices.
Fix the layout with fluid CSS first: `clamp()`, `min()`, `max()`, `auto-fit`, `flex-wrap`, and sensible content widths.

## Layout patterns Codex should prefer

### Fluid container

```css
.container {
  width: min(100% - 2rem, 72rem);
  margin-inline: auto;
}
```

### Responsive card grid

```css
.card-grid {
  display: grid;
  gap: 1rem;
  grid-template-columns: repeat(auto-fit, minmax(min(100%, 18rem), 1fr));
}
```

### Stack on phone, row on larger screens

```css
.stack-row {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

@media (min-width: 700px) {
  .stack-row {
    flex-direction: row;
    align-items: center;
  }
}
```

### Admin action rows

Admin buttons often break on phones. Default to wrapping:

```css
.action-row {
  display: flex;
  flex-wrap: wrap;
  gap: .5rem;
  align-items: center;
}
```

For narrow admin tables, either wrap them in a scroll container or convert rows to cards.

```html
<div class="table-scroll">
  <table>...</table>
</div>
```

```css
.table-scroll {
  width: 100%;
  overflow-x: auto;
  -webkit-overflow-scrolling: touch;
}
```

## Touch targets

Anything clickable/tappable should have a comfortable hit area.

Minimum:

```css
.button,
.nav-link,
.icon-button {
  min-height: 44px;
  min-width: 44px;
}
```

Rules:

- Use at least 44×44px hit area.
- Keep at least 8px spacing between adjacent tap targets.
- Icon-only buttons need visible text for screen readers via `aria-label`.
- Do not make important actions hover-only.

For dense admin UIs, if visual buttons must look small, keep the hit area large with padding or wrapper styles.

## Typography

Use fluid type where useful:

```css
h1 {
  font-size: clamp(1.8rem, 5vw, 3.25rem);
  line-height: 1.1;
}

h2 {
  font-size: clamp(1.4rem, 3vw, 2.2rem);
  line-height: 1.2;
}

.prose {
  max-width: 65ch;
}
```

Rules:

- Body text should not go below 16px.
- Use line-height around 1.5–1.7 for text-heavy pages.
- Avoid long all-caps text on mobile.
- Prevent awkward overflow with `overflow-wrap: anywhere;` only where needed, such as long URLs or tokens.
- Do not use tiny grey text for important information.

## Forms

Use the right input type and autocomplete.

```html
<input type="email" autocomplete="email">
<input type="tel" autocomplete="tel">
<input type="text" inputmode="numeric" pattern="[0-9]*">
<input type="text" inputmode="decimal">
<input type="search">
<input type="password" autocomplete="current-password">
<input type="text" inputmode="numeric" autocomplete="one-time-code">
```

Prefer `type="text" inputmode="numeric"` over `type="number"` for values where leading zeros matter, such as codes, phone numbers, postal codes, and IDs.

Form rules:

- Labels are required.
- Error messages should be close to the relevant field.
- Required fields should be visibly marked.
- Use clear submit buttons.
- On mobile, avoid two-column forms unless the screen is wide enough.
- Long forms should be divided into clear sections.

## Navigation

Phone navigation must be designed, not squeezed.

Good defaults:

- 3–5 main destinations: bottom nav or simple tab row.
- Many destinations: hamburger/drawer or grouped admin menu.
- Content site: simple sticky header on phone, fuller nav on desktop.
- Admin site: clear dashboard links and a collapsible or stacked nav.

Rules:

- Menu button must be at least 44×44px.
- Menu button needs `aria-label`.
- Current page should be visibly marked.
- Do not hide critical actions behind hover.

## Hover, focus, and motion

Touch devices do not have reliable hover.

Use hover as an enhancement only:

```css
@media (hover: hover) and (pointer: fine) {
  .card:hover {
    transform: translateY(-2px);
  }
}
```

Always provide focus states:

```css
:focus-visible {
  outline: 3px solid currentColor;
  outline-offset: 3px;
}
```

Respect reduced motion:

```css
@media (prefers-reduced-motion: reduce) {
  *,
  *::before,
  *::after {
    animation-duration: .01ms !important;
    animation-iteration-count: 1 !important;
    scroll-behavior: auto !important;
    transition-duration: .01ms !important;
  }
}
```

## Images and media

Use responsive images.

Basic image markup:

```html
<img
  src="<?= e(asset_url('assets/img/example.jpg')) ?>"
  alt="Beskrivande text"
  width="1200"
  height="800"
  loading="lazy"
>
```

For important above-the-fold hero images, do not lazy-load unless there is a reason.

Rules:

- Always set `width` and `height` when dimensions are known.
- Use `loading="lazy"` for below-the-fold images.
- Use `srcset`/`sizes` or generated thumbnails for image-heavy pages.
- Avoid sending huge desktop images to phones.
- Keep Open Graph images around 1200×630 when possible.
- Do not assume excluded uploaded photos are present during static review.

## PHP/template portability

In PHP projects, Codex must avoid hardcoded root-relative paths unless the project has explicitly chosen them.

Avoid:

```php
<link rel="stylesheet" href="/assets/css/style.css">
<a href="/admin/posts.php">
<img src="/uploads/example.jpg">
header('Location: /admin/');
```

Prefer project helpers:

```php
<link rel="stylesheet" href="<?= e(asset_url('assets/css/style.css')) ?>">
<a href="<?= e(url('admin/posts.php')) ?>">
<img src="<?= e(asset_url('uploads/example.jpg')) ?>" alt="">
header('Location: ' . url('admin/'));
exit;
```

If helpers do not exist, propose a small helper file such as:

```php
<?php
function base_url(): string
{
    return defined('BASE_URL') ? rtrim((string) BASE_URL, '/') : '';
}

function url(string $path = ''): string
{
    return base_url() . '/' . ltrim($path, '/');
}

function asset_url(string $path): string
{
    return url($path);
}

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
```

Rules:

- Escape HTML output with `e()` or equivalent.
- Use `rawurlencode()` for dynamic URL segments.
- Use `http_build_query()` for query strings.
- Redirects should call `exit;` after `header('Location: ...')`.
- Do not echo untrusted values directly into HTML, attributes, JavaScript, or CSS.
- Keep config/secrets out of public downloadable packages.

## Security checks Codex should not skip

When reviewing a ZIP or repo, check for:

- `.git/` included in a deployment/export ZIP
- `app/config.php` with real DB credentials
- `.env` with secrets
- SQL dumps with production data
- seed files with plaintext admin passwords
- backup files under public webroot
- writable upload folders that can execute PHP
- debug/test files left public
- admin pages without authentication
- forms without CSRF protection
- SQL queries using string interpolation
- unescaped output in templates

Upload folders should block script execution. For Apache projects, consider an `uploads/.htaccess` like:

```apache
Options -Indexes
php_flag engine off

<FilesMatch "\.(php|phtml|phar|cgi|pl|py|sh|asp|aspx|js)$">
  Require all denied
</FilesMatch>
```

If `php_flag` is not allowed by the server, use a safer vhost-level rule or a minimal `.htaccess` that denies dangerous extensions.

## Admin UI rules

Admin pages are often used quickly and under pressure. Make them boringly clear.

Rules:

- Show clear page titles.
- Put primary action near the top.
- Make status visible.
- Use confirm dialogs for destructive actions.
- Keep edit/delete buttons visually distinct.
- Do not rely only on color to communicate state.
- Keep tables readable on phone with scroll wrappers or card layout.
- Preserve filters/search when returning to lists where practical.
- Show success/error feedback after actions.
- Keep forms easy to scan.

## SEO and sharing basics

For public pages, include:

```html
<title>Specific page title</title>
<meta name="description" content="Short useful description.">
<link rel="canonical" href="<?= e($canonicalUrl) ?>">

<meta property="og:title" content="Specific page title">
<meta property="og:description" content="Short useful description.">
<meta property="og:type" content="website">
<meta property="og:image" content="<?= e($absoluteOgImageUrl) ?>">
<meta property="og:url" content="<?= e($canonicalUrl) ?>">
```

Rules:

- OG image URLs should usually be absolute.
- Do not invent fake canonical domains.
- If the project has config for site URL, use that.
- Keep titles unique and human.
- Avoid stuffing keywords.

## Performance basics

Codex should avoid making mobile pages heavy.

Rules:

- No unnecessary JS for static pages.
- Defer scripts where possible.
- Prefer CSS over JS for simple UI states.
- Use system fonts unless custom fonts are part of the design.
- If using web fonts, use `font-display: swap`.
- Compress images.
- Avoid giant background images for mobile.
- Keep CSS maintainable and not overly specific.

## Tables and data views

Tables need a phone strategy.

Acceptable options:

1. horizontal scroll wrapper
2. card layout on phone, table on desktop
3. simplified columns on phone with full detail page

Do not let tables force page-wide horizontal scroll.

Good scroll wrapper:

```css
.table-wrap {
  overflow-x: auto;
  -webkit-overflow-scrolling: touch;
}

.table-wrap table {
  min-width: 42rem;
}
```

## Modals and fixed elements

Mobile modals must fit real screens.

```css
.modal {
  width: min(100% - 2rem, 42rem);
  max-height: 90svh;
  overflow: auto;
}
```

Rules:

- Avoid fixed footers covering content.
- Reserve bottom padding when using bottom nav/fixed action bars.
- Account for `env(safe-area-inset-bottom)`.
- Be careful with fixed elements when the keyboard opens.

## Common anti-patterns and required fixes

| Anti-pattern | Fix |
| --- | --- |
| `width: 1200px` | `width: min(100% - 2rem, 72rem)` |
| Desktop-first `@media (max-width)` everywhere | Base mobile styles + `@media (min-width)` enhancements |
| Tiny buttons | 44×44px hit area |
| Inputs under 16px | `input, textarea, select { font-size: 16px; }` |
| Hover-only controls | Always visible on touch, hover only as enhancement |
| Unwrapped wide tables | `.table-wrap { overflow-x: auto; }` |
| `100vh` hero only | `100svh` or `100dvh` |
| Hardcoded `/assets/...` in portable PHP app | `asset_url('assets/...')` |
| Hardcoded `/admin/...` redirects | `url('admin/...')` + `exit;` |
| Echoing raw DB content | Escape with `e()` |
| Upload folder can run PHP | Block script execution |
| `.git` included in ZIP | Exclude it from deployment/export package |
| Seed/admin password in repo | Use env/config and force password change |

## Review checklist before Codex says done

Codex should verify or report on these:

### Mobile/layout

- Viewport meta exists.
- 360px width does not create horizontal page scroll.
- Tap targets are at least 44px where practical.
- Inputs are at least 16px.
- Media queries are mobile-first unless legacy CSS requires otherwise.
- Tables have a mobile strategy.
- Important actions do not require hover.
- Modals fit within `90svh`.
- Images do not overflow.

### PHP/paths

- Internal URLs use helpers or consistent project base handling.
- Redirects are portable and followed by `exit;`.
- Output is escaped.
- Query strings and dynamic URL parts are encoded correctly.
- Includes do not depend on fragile relative paths when `__DIR__` is safer.

### Security

- No secrets exposed in files intended for sharing.
- Uploads cannot execute scripts.
- Admin pages require authentication.
- Mutating admin actions use CSRF.
- SQL uses prepared statements.
- Debug/test files are not public.

### Quality

- Changes are focused.
- Comments explain the parts a human will later edit.
- Existing user content has not been silently removed.
- File headers/version rows are updated when expected.
- No unrelated mass reformatting.
- The final response says what changed and what still needs manual testing.

## Output style for Codex

When Codex finishes a task, it should summarize:

1. changed files
2. what was fixed or improved
3. anything not tested
4. any manual follow-up needed
5. security warnings, if found

Keep the summary concrete. Avoid vague “improved responsiveness” claims unless the exact changes are named.

Example:

```text
Changed:
- assets/css/style.css: made buttons 44px on touch devices, added table scroll wrapper.
- includes/header.php: added viewport meta and portable asset_url() link.

Not tested:
- Browser rendering on a real iPhone.
- Production upload permissions.

Manual follow-up:
- Replace placeholder OG image with final 1200x630 image.
```

## When to bend these rules

Follow explicit user instructions when they conflict with this skill, but still warn about concrete risks.

Examples:

- If the user says the tool is desktop-only, still avoid needless fixed widths.
- If the project is legacy desktop-first CSS, do not rewrite the entire CSS unless asked.
- If the user asks for a quick patch, keep it quick, but mention larger mobile/security issues separately.
- If exact visual matching matters more than ideal CSS, preserve the visual result and improve safety around it.

The goal is not theoretical perfection.
The goal is web code that works well on real phones, is easy to maintain, and does not surprise Åberg later.
