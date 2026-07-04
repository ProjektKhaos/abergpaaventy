# Åberg På Äventyr

Publik PHP-sajt för **Åberg På Äventyr** på `https://abergpaaventyr.se`.

## Repo

GitHub: `https://github.com/ProjektKhaos/abergpaaventy.git`

Aktiv serverkatalog:

```text
/var/www/abergpaaventyr
```

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
- `uploads/` är runtime-uppladdningar och ignoreras av Git, förutom `.htaccess`.

## Snabb kontroll

```bash
php -l index.php
php -l app/helpers.php
curl -I https://abergpaaventyr.se/
```
