# MISSION: Personlig Thailand-sida för Hasse

## Projektidé
Bygg en personlig webbsida där Hasse kan skriva inlägg, lägga upp foton och dokumentera tiden i Thailand under studierna. Sidan ska fungera som en enkel blogg, men kännas mer som en personlig rese- och studie-dagbok än en klassisk bloggportal.

Syftet är att Hasse enkelt ska kunna berätta:

- hur studierna går
- vad som händer i vardagen
- bilder från Chiang Mai och Thailand
- små reflektioner, äventyr och minnen
- praktiska saker kring boende, skola, mat, människor och livet där

Sidan ska vara enkel att sköta från mobil, surfplatta och dator.

---

## Grundprinciper

1. **Enkelhet först**
   - Inga onödiga communityfunktioner.
   - Ingen öppen medlemsdel.
   - Fokus på Hasses egna inlägg och foton.

2. **Personlig känsla**
   - Ska kännas som “Hasse i Thailand” snarare än en vanlig nyhetssida.
   - Varm, enkel, reseinspirerad design.
   - Gärna med känsla av vykort, dagbok, fotoalbum och studieresa.

3. **Mobilvänlig admin**
   - Hasse ska kunna lägga upp inlägg direkt från mobilen.
   - Bilduppladdning ska vara smidig.
   - Admin ska vara ren, tydlig och snabb.

4. **Portabel kod**
   - All kod ska följa Hasses standard med `BASE_URL` och `url()`-helper.
   - Sidan ska fungera oavsett om den ligger i root eller undermapp.

---

## Föreslagen teknik

- PHP 8.2+
- MariaDB/MySQL
- PDO
- HTML
- CSS
- Lite JavaScript vid behov
- Sessionsbaserad admininloggning
- Bilduppladdning till `/uploads/`

---

## Användarnivåer

I första versionen räcker en adminanvändare.

### Admin
Kan:

- logga in
- skapa inlägg
- redigera inlägg
- radera eller avpublicera inlägg
- ladda upp foton
- välja omslagsbild
- skapa bildgallerier till inlägg
- hantera enkla kategorier/taggar

Första admin kan sättas lokalt på servern:

- Namn: byt lokalt
- User: byt lokalt
- Pass: byt lokalt

**OBS:** Lösenordet ska hashats med `password_hash()` i seed/install-scriptet. Det får inte sparas i klartext i databasen.

---

## Sidstruktur frontend

### 1. Startsida
Visar senaste inläggen som kort/kortare utdrag.

För varje inlägg:

- titel
- datum
- plats, om angiven
- omslagsbild
- kort ingress
- knapp/länk: Läs mer

Startsidan ska gärna ha en tydlig toppsektion, exempelvis:

> Hasse i Thailand 2026
> Studier, vardag, bilder och små äventyr från Chiang Mai.

### 2. Inläggssida
Visar ett enskilt inlägg.

Ska innehålla:

- titel
- datum
- plats
- huvudbild
- brödtext
- bildgalleri
- taggar/kategorier
- eventuell kort “dagens notering”

### 3. Fotoalbum / Galleri
En sida som visar foton från alla inlägg.

Funktioner:

- klickbar bild
- koppling till inlägg
- datum/plats om möjligt

### 4. Kategorier / Taggar
Exempel:

- Studier
- Chiang Mai
- Mat
- Boende
- Utflykter
- Vardag
- Tankar
- Praktiskt

### 5. Om sidan
Kort text om varför sidan finns.

Exempel:

> Här samlar jag bilder, inlägg och små rapporter från min tid i Thailand när jag studerar språk och kultur i Chiang Mai.

---

## Adminstruktur

### `/admin/login.php`
Inloggning.

### `/admin/dashboard.php`
Översikt:

- antal publicerade inlägg
- antal utkast
- senaste inlägg
- snabbknapp: Nytt inlägg

### `/admin/posts.php`
Lista inlägg.

Kolumner:

- titel
- status
- datum
- plats
- skapad/uppdaterad
- redigera-knapp

### `/admin/post_edit.php`
Skapa/redigera inlägg.

Fält:

- titel
- slug
- ingress
- brödtext
- plats
- datum för inlägg
- status: draft/published
- kategori
- taggar
- omslagsbild
- bildgalleri

### `/admin/media.php`
Enkel medieöversikt.

- lista uppladdade bilder
- filnamn
- uppladdningsdatum
- förhandsvisning

---

## Databasförslag

### `users`
```sql
id INT AUTO_INCREMENT PRIMARY KEY,
name VARCHAR(120) NOT NULL,
username VARCHAR(80) NOT NULL UNIQUE,
password_hash VARCHAR(255) NOT NULL,
role VARCHAR(40) NOT NULL DEFAULT 'admin',
created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
```

### `posts`
```sql
id INT AUTO_INCREMENT PRIMARY KEY,
title VARCHAR(255) NOT NULL,
slug VARCHAR(255) NOT NULL UNIQUE,
intro TEXT NULL,
body MEDIUMTEXT NULL,
location VARCHAR(255) NULL,
post_date DATE NULL,
status ENUM('draft','published') NOT NULL DEFAULT 'draft',
cover_image_id INT NULL,
created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP
```

### `media`
```sql
id INT AUTO_INCREMENT PRIMARY KEY,
original_name VARCHAR(255) NOT NULL,
file_name VARCHAR(255) NOT NULL,
file_path VARCHAR(255) NOT NULL,
mime_type VARCHAR(120) NULL,
file_size INT NULL,
alt_text VARCHAR(255) NULL,
caption VARCHAR(255) NULL,
created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
```

### `post_media`
```sql
id INT AUTO_INCREMENT PRIMARY KEY,
post_id INT NOT NULL,
media_id INT NOT NULL,
sort_order INT NOT NULL DEFAULT 0
```

### `categories`
```sql
id INT AUTO_INCREMENT PRIMARY KEY,
name VARCHAR(120) NOT NULL,
slug VARCHAR(120) NOT NULL UNIQUE
```

### `post_categories`
```sql
post_id INT NOT NULL,
category_id INT NOT NULL
```

### `tags`
```sql
id INT AUTO_INCREMENT PRIMARY KEY,
name VARCHAR(120) NOT NULL,
slug VARCHAR(120) NOT NULL UNIQUE
```

### `post_tags`
```sql
post_id INT NOT NULL,
tag_id INT NOT NULL
```

---

## Filstruktur

```text
/thailand/
  app/
    config.php
    db.php
    helpers.php
    Auth.php
    Post.php
    Media.php
    Category.php
  admin/
    login.php
    logout.php
    dashboard.php
    posts.php
    post_edit.php
    media.php
    includes/
      header.php
      footer.php
    assets/
      admin.css
      admin.js
  assets/
    style.css
    script.js
  uploads/
    .htaccess
  sql/
    schema.sql
    seed.php
  docs/
    MISSION_THAILAND_STUDIEBLOGG.md
    DESIGN_BRIEF.md
    ACCEPTANCE_CRITERIA.md
  index.php
  post.php
  gallery.php
  category.php
  about.php
```

---

## Kodstandard

Alla nya PHP-filer ska ha full HTML-struktur där det är en sida.

Alla PHP-filer ska ha tydlig kommentar på rad 2 enligt Hasses standard, exempel:

```php
<?php
// post.php med visning av enskilt inlägg Ⓐ Style
```

HTML-filer:

```html
<!-- filnamn.html med beskrivning Ⓐ Style -->
```

CSS-filer:

```css
/* style.css med huvuddesign för Thailand-sidan Ⓐ Style */
/* Uppdaterad: 2026-05-27 15:45 | av: KlⒶssⓔ & Ⓐberg */
```

Kod ska ha pedagogiska kommentarer där det är relevant.

---

## Bildhantering

Bilduppladdning ska:

- tillåta jpg, jpeg, png, webp
- kontrollera MIME-type
- skapa säkra filnamn
- spara i `/uploads/`
- gärna skapa framtida möjlighet för thumbnails
- visa förhandsvisning i admin

Första versionen kan spara originalbilden direkt, men kodstrukturen ska göra det lätt att lägga till thumbnails senare.

---

## Designkänsla

Stilen ska vara:

- varm
- personlig
- lättläst
- reseinspirerad
- enkel att använda
- inte för corporate

Färgidé:

- sand/beige bakgrund
- varm vit kortyta
- mörk text
- accent i teak/orange/grön/turkos
- små detaljer som känns Thailand/Chiang Mai utan att bli turistkitsch

Designord:

- vykort
- dagbok
- studieäventyr
- fotoalbum
- solvarm vardag

---

## Viktiga funktioner version 1

Måste finnas:

- Admin-login
- Skapa inlägg
- Redigera inlägg
- Publicera/utkast
- Ladda upp omslagsbild
- Visa inlägg på startsida
- Visa enskilt inlägg
- Mobilanpassad design
- Enkel säkerhet med sessioner, CSRF och prepared statements

Bra om det finns:

- Bildgalleri per inlägg
- Kategorier
- Taggar
- Galleri-sida
- Sökfunktion

Kan vänta:

- Kommentarer
- Newsletter
- Kartfunktion
- Automatisk bildkomprimering
- API
- Import/export

---

## Säkerhetskrav

- PDO prepared statements
- `password_hash()` och `password_verify()`
- CSRF-token i formulär
- Adminsidor skyddade med session
- Inga uppladdade PHP-filer tillåts
- Filnamn saneras
- Output escapes med `htmlspecialchars()`
- Uploads-katalog ska hindra PHP-körning via `.htaccess`

Exempel `.htaccess` i uploads:

```apache
php_flag engine off
Options -Indexes
<FilesMatch "\.(php|php3|php4|php5|phtml)$">
  Require all denied
</FilesMatch>
```

---

## Acceptanskriterier

Projektet är godkänt när:

1. Hasse kan logga in som admin.
2. Hasse kan skapa ett nytt inlägg med titel, text, datum och plats.
3. Hasse kan ladda upp minst en bild till inlägget.
4. Publicerade inlägg visas på startsidan.
5. Utkast visas inte publikt.
6. Enskilda inlägg kan öppnas via slug/URL.
7. Sidan fungerar bra på mobil.
8. Koden är portabel via `BASE_URL` och `url()`.
9. Databasen kan installeras via `schema.sql` och `seed.php`.
10. Koden har tydliga kommentarer och följer Hasses filstandard.

---

## Extra idéer för senare version

- “Dagens bild”
- “Studievecka 1, 2, 3...”
- Karta med platser
- Privat/lösenordsskyddade inlägg
- Automatisk delningsbild för Facebook/Open Graph
- Enkel tidslinje över resan
- Export till PDF/minnesbok när studietiden är slut
