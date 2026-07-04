---
name: mobile-first-web
description: Build websites, web apps, dashboards, landing pages, and HTML/CSS interfaces that work great on phones first, then scale up to tablet and desktop. Use this skill whenever you're writing HTML, CSS, or a frontend component — even if the user doesn't explicitly say "mobile" or "responsive". Most people read on phones now, and Claude's default tends to be desktop-shaped, so this skill exists to correct that. Also use it when reviewing or refactoring an existing site that feels broken on a phone (squished layout, tiny tap targets, horizontal scroll, zoomed-in inputs on iOS, etc.).
---

# Mobile-first web

## Why this skill exists

The web is mostly read on phones. But left to default habits, web code tends to come out desktop-shaped: fixed pixel widths, `max-width` media queries that strip features as screens shrink, button rows that wrap into a mess, inputs that trigger zoom on iOS, fonts that wrap awkwardly, modals that overflow the viewport, navigation that assumes a mouse.

Mobile-first inverts that. Design and code the small screen first — where space is scarce and constraints are real — then progressively add layout for larger screens. The result is faster, more accessible, and usually simpler.

This skill is the checklist and the patterns to make that happen on every web build.

## The non-negotiables

Every HTML page you ship has these. No exceptions, no excuses:

**1. Viewport meta tag in `<head>`:**
```html
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
```
`viewport-fit=cover` lets the page extend under notches so you can use safe-area-insets.

**2. Inputs at `font-size: 16px` minimum.** Smaller fonts cause iOS Safari to zoom in on focus, which is jarring and rarely intentional. Set `font-size: 16px` on `input`, `textarea`, and `select`.

**3. `box-sizing: border-box` globally.** Without it, padding blows out widths and breaks layouts on narrow screens.
```css
*, *::before, *::after { box-sizing: border-box; }
```

**4. No fixed pixel widths on layout containers.** Use `%`, `rem`, `min()`, `max()`, `clamp()`, or `fr` (in grid). A `width: 1200px` div will cause horizontal scroll on every phone.

**5. Mobile-first media queries — `min-width`, not `max-width`.** Write the base styles for phones, then add `@media (min-width: ...)` blocks to enhance for larger screens. This keeps the small-screen CSS the default, which is usually shorter and faster to load.

## The default breakpoints

You can name them whatever you want, but stick to a small set. These map to real device classes:

```css
/* Base: phones (no media query, ~320–640px) */

@media (min-width: 640px)  { /* large phones, small tablets in landscape */ }
@media (min-width: 900px)  { /* tablets, small laptops */ }
@media (min-width: 1200px) { /* desktops */ }
```

Don't add breakpoints "because the design breaks at 743px". Fix the layout to be fluid through that range using `clamp()`, `flex-wrap`, or `auto-fit` grids.

## Layout patterns that just work

**Fluid container with sensible max:**
```css
.container {
  width: 100%;
  max-width: 72rem;       /* ~1152px on default root font */
  margin-inline: auto;
  padding-inline: 1rem;   /* breathing room on the edges of phones */
}
```

**Card grid that reflows:**
```css
.cards {
  display: grid;
  gap: 1rem;
  grid-template-columns: repeat(auto-fit, minmax(min(100%, 18rem), 1fr));
}
```
The `min(100%, 18rem)` is the magic — it stops the column from being wider than the viewport on narrow screens, which is the most common cause of horizontal scroll.

**Stack on phone, row on tablet+:**
```css
.row { display: flex; flex-direction: column; gap: 1rem; }
@media (min-width: 640px) { .row { flex-direction: row; } }
```

**Full viewport height that respects mobile browser chrome:**
```css
.hero { min-height: 100svh; }   /* small viewport height — accounts for browser UI */
/* Avoid 100vh on its own; it's broken on iOS Safari with the address bar */
```

## Touch targets

Anything tappable needs to be at least **44×44px** (Apple's number; Google says 48dp, same ballpark). This includes the *whole hit area*, not just the visual button. For icon-only buttons in a tight bar:

```css
.icon-btn {
  min-width: 44px;
  min-height: 44px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}
```

Tap targets need spacing too. Two 44px buttons jammed against each other will still get mis-tapped. Aim for ≥8px between adjacent targets.

## Typography that scales

Use `clamp()` to size headings fluidly between phone and desktop without media queries:

```css
h1 { font-size: clamp(1.75rem, 4vw + 1rem, 3rem); line-height: 1.15; }
h2 { font-size: clamp(1.35rem, 2.5vw + 1rem, 2rem); }
body { font-size: 1rem; line-height: 1.6; }   /* never below 16px on mobile */
```

Set generous line-height (1.5–1.7) for body text — mobile reading needs more breathing room than desktop print habits suggest. Keep line length under ~75 characters with `max-width: 65ch` on prose containers.

## Forms that don't fight the phone keyboard

Pick the right input type and `inputmode`. The phone keyboard adapts to it:

| Use case          | Markup                                                            |
| ----------------- | ----------------------------------------------------------------- |
| Email             | `<input type="email" autocomplete="email">`                       |
| Phone number      | `<input type="tel" autocomplete="tel">`                           |
| Integer (PIN, qty)| `<input type="text" inputmode="numeric" pattern="[0-9]*">`        |
| Decimal (money)   | `<input type="text" inputmode="decimal">`                         |
| URL               | `<input type="url" autocomplete="url">`                           |
| Search            | `<input type="search">`                                           |
| New password      | `<input type="password" autocomplete="new-password">`             |
| Current password  | `<input type="password" autocomplete="current-password">`         |
| One-time code     | `<input type="text" inputmode="numeric" autocomplete="one-time-code">` |

`type="number"` looks tempting but is broken in many subtle ways (scroll wheels change values, leading zeros get stripped, etc.). For most numeric inputs prefer `type="text" inputmode="numeric"`.

Set `autocomplete` honestly — modern browsers and password managers rely on it.

## Safe areas for notches and home bars

Phones with notches or rounded corners need padding so content doesn't get clipped. The `env(safe-area-inset-*)` variables come from the viewport-fit=cover meta you already added:

```css
.app-header {
  padding-top: max(1rem, env(safe-area-inset-top));
  padding-inline: max(1rem, env(safe-area-inset-left), env(safe-area-inset-right));
}
.bottom-nav {
  padding-bottom: max(0.5rem, env(safe-area-inset-bottom));
}
```

## Hover is a desktop luxury

`:hover` doesn't exist on touch. Don't hide important affordances behind hover (e.g. a "delete" button that only appears on row-hover is invisible on a phone).

If you have hover-only reveals, either:
- Always show them on touch devices, or
- Use `@media (hover: hover)` to scope hover styles to mice:

```css
@media (hover: hover) {
  .row:hover .delete-btn { opacity: 1; }
}
.delete-btn { opacity: 1; }   /* default: always visible */
```

## Navigation that fits

A horizontal nav bar with 7 links works on a 1440px monitor and dies on a 360px phone. Two patterns to reach for:

**Bottom tab bar (app-style):** Best for primary apps with 3–5 main destinations. Fixed to the bottom, thumb-reachable, follows native conventions. Always pair with `padding-bottom: env(safe-area-inset-bottom)`.

**Hamburger / drawer:** Best when there are too many destinations for a tab bar, or the nav is secondary. Keep the toggle button ≥44×44px and label it (`aria-label="Open menu"`).

For content sites, a sticky simplified header on phone and a fuller nav on desktop is the usual move — write the simple version as the default, expand at `min-width: 900px`.

## Performance basics (mobile is on cellular)

- Use `<img>` with `loading="lazy"`, `width`, `height`, and `srcset` so the browser can pick the right size and reserve space.
- Prefer system fonts or `font-display: swap` so text shows immediately.
- Avoid loading 200KB of JS to render a static page. Server-render or use plain HTML where you can.
- Don't ship desktop-sized hero images to phones. Use `<picture>` with `media` queries if you must.

## Tables on phones

Wide data tables don't fit. Two solid options:

**1. Horizontal scroll wrapper** (keeps the table format intact):
```html
<div style="overflow-x: auto; -webkit-overflow-scrolling: touch;">
  <table>...</table>
</div>
```

**2. Card stack on phone, table on desktop** (better UX for short rows):
On small screens, restyle each row as a card with the column header shown as a label inline with the value. Use CSS to switch between layouts at a breakpoint.

## The self-review checklist

Before considering an HTML/CSS deliverable done, walk through this mentally — or literally, by resizing your browser to 360px wide:

1. **Does the viewport meta tag exist?**
2. **Resize the window to 360px.** Anything overflow horizontally? Any horizontal scrollbar? If yes, find the offending element (usually a fixed width, a too-wide image, a `white-space: nowrap`, or a grid column that won't shrink).
3. **Are inputs at least 16px font-size?**
4. **Tap every button mentally on a phone.** Is each ≥44×44px with enough spacing?
5. **Are media queries `min-width` (mobile-first)?**
6. **Does any interaction require `:hover` to discover?**
7. **Forms: right input types? `autocomplete` set?**
8. **For full-height sections: `100svh` (or `100dvh`), not `100vh`?**
9. **Touch the bottom of an iPhone-shaped screen mentally — is anything tucked under a notch or home indicator? If so, add `env(safe-area-inset-*)`.**
10. **Does text wrap cleanly at narrow widths, or does it cut off?**

If something fails, fix it now, not later.

## A starter template

When creating a brand-new mobile-first page from scratch, use `references/starter.html` as the base. It's a self-contained file with the viewport meta, sensible CSS resets, a working layout, and accessible defaults — all the non-negotiables baked in. Copy it, then build from there.

## Common anti-patterns and the fixes

| Anti-pattern                                          | Fix                                                                       |
| ----------------------------------------------------- | ------------------------------------------------------------------------- |
| `width: 1200px;` on a container                       | `width: 100%; max-width: 75rem; margin-inline: auto;`                     |
| `@media (max-width: 768px) { ... }` desktop-first    | Flip it: base styles for phone, `@media (min-width: ...)` for bigger     |
| `min-height: 100vh;` (jumpy on iOS)                   | `min-height: 100svh;` or `100dvh`                                         |
| Buttons that are just an icon glyph with no padding   | `min-width: 44px; min-height: 44px;` + visible focus state                |
| `<input type="number">` for a phone number            | `<input type="tel" autocomplete="tel">`                                   |
| `<table>` overflowing the viewport                    | Wrap in `<div style="overflow-x:auto">` or restyle as cards on phone     |
| Hover-revealed actions invisible on touch             | Always show on touch, scope hover styles to `@media (hover: hover)`       |
| Fixed footer overlapping content                      | Reserve space with `padding-bottom` on the page body or main wrapper      |
| Hero image is a 2MB JPG sent to every device          | `<picture>` with `srcset` and `sizes`, or scaled-down mobile source       |
| Modals taller than the phone with no scroll           | `max-height: 90svh; overflow-y: auto;` on the modal body                  |
| `position: fixed` element that disappears behind iOS keyboard | Use `position: sticky` where possible; test with keyboard open  |

## When the user has stronger opinions

This skill describes defaults. If the user has explicit instructions ("desktop-only internal tool", "I want max-width media queries because I'm extending a legacy stylesheet"), follow their lead — but flag any mobile gotchas you spot. The point is to bake mobile-friendliness in unless someone consciously opts out, not to override deliberate decisions.
