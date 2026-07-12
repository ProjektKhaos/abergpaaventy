# Åberg På Äventyr

![Åberg På Äventyr](assets/img/studera_fb_og.png)

Publik PHP-sajt för **Åberg På Äventyr** på `https://abergpaaventyr.se`.

## Repo

GitHub: `https://github.com/ProjektKhaos/abergpaaventy.git`


## Lokal konfiguration

Riktiga databasuppgifter ligger i `app/config.php` på servern och ska inte commitas.

För ny miljö:

```bash
cp app/config.example.php app/config.php
```

Fyll sedan i databasvärden i `app/config.php`.

## Viktiga filer

- `index.php` laddar startsidan via `index_old.php`.
- `assets/style.css` är aktiv publik CSS.
- `app/public_layout.php` styr gemensam publik header/meny/footer.
- `admin/bootstrap.php` laddar gemensam adminmiljö, modeller, services och modulmeny.
- `admin/news.php` och `admin/news_edit.php` hanterar nyheter som visas på `nyheter.php`.
- `admin/cmt.php` och `admin/cmt_edit.php` hanterar Chiang Mai-tips med externa länkar och koordinater som visas på `cmt.php`.
- `metro.php` visar Chiang Mai-tid och väder via det databasfria API:t `api/chiangmai_status.php`.
- `app/partials/chiangmai_widget.php` är en förberedd, återanvändbar mini-widget för vädret.
- `app/Services/` innehåller affärslogik för adminflöden som artiklar och media.
- `uploads/` är runtime-uppladdningar och ignoreras av Git, förutom `.htaccess`.
- `sql/migrations/` innehåller en SQL-fil per framtida schemaändring.

## Snabb kontroll

```bash
php -l index.php
php -l app/helpers.php
curl -I https://abergpaaventyr.se/
```
