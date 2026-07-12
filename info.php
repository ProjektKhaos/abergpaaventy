<?php
// info.php – publik information från projektets README

require_once __DIR__ . '/app/config.php';
require_once __DIR__ . '/app/helpers.php';
require_once __DIR__ . '/app/public_layout.php';

$pageTitle       = 'Info - Åberg På Äventyr';
$pageDescription = 'Projektinformation och viktiga filer för Åberg På Äventyr.';
$canonicalUrl    = url('info.php');
$ogImageUrl      = asset_url('assets/img/studera_fb_og.png');
?>
<!doctype html>
<html lang="sv">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <?php public_meta($pageTitle, $pageDescription, $canonicalUrl, $ogImageUrl); ?>
  <link rel="stylesheet" href="<?= e(asset_url('assets/style.css')) ?>">
</head>
<body>
  <?php public_header('info'); ?>

  <main id="main-content" class="post-page info-page">
    <article class="section-panel info-content">
      <header class="info-intro">
        <p class="kicker">Bakom kulisserna</p>
        <h1>Så fungerar Åberg På Äventyr</h1>
        <p class="info-lead">Åberg På Äventyr är en egenutvecklad, serverrenderad PHP-sajt för artiklar, nyheter, Chiang Mai-tips, bilder och aktuell väderinformation. Den publika adressen är <a href="https://abergpaaventyr.se">abergpaaventyr.se</a>. Sidan använder inget stort CMS eller frontendramverk: PHP bygger HTML på servern, MySQL lagrar innehållet och vanlig CSS samt JavaScript sköter utseende och interaktion i webbläsaren.</p>
      </header>

      <section aria-labelledby="info-numbers">
        <h2 id="info-numbers">Sajten i siffror</h2>
        <div class="info-stats">
          <div><strong>63</strong><span>källkodsfiler</span></div>
          <div><strong>7 675</strong><span>rader kod</span></div>
          <div><strong>285 781</strong><span>tecken</span></div>
          <div><strong>288 215</strong><span>byte källkod</span></div>
        </div>

        <div class="info-table-wrap">
          <table class="info-table">
            <caption>Fördelning per filtyp</caption>
            <thead>
              <tr><th>Teknik</th><th>Filer</th><th>Rader</th><th>Tecken</th></tr>
            </thead>
            <tbody>
              <tr><th>PHP</th><td>51</td><td>5 301</td><td>207 234</td></tr>
              <tr><th>CSS</th><td>2</td><td>1 945</td><td>60 031</td></tr>
              <tr><th>JavaScript</th><td>3</td><td>302</td><td>12 587</td></tr>
              <tr><th>SQL</th><td>7</td><td>127</td><td>5 929</td></tr>
              <tr class="info-table__total"><th>Totalt</th><td>63</td><td>7 675</td><td>285 781</td></tr>
            </tbody>
          </table>
        </div>
        <p class="info-note">Mätningen gjordes den 12 juli 2026 på den aktuella arbetskopian. Den omfattar sajtens <code>.php</code>-, <code>.css</code>-, <code>.js</code>- och <code>.sql</code>-filer. Bilder, uppladdade filer, Git-historik, Markdown-dokumentation och den separata arbetsmappen <code>mobile-first-web</code> är inte medräknade. Tecken är Unicode-tecken; byte är filernas faktiska storlek och blir något högre eftersom svenska tecken tar flera byte i UTF-8.</p>
      </section>

      <section aria-labelledby="info-stack">
        <h2 id="info-stack">Vad sajten drivs av</h2>
        <ul>
          <li><strong>PHP</strong> körs på servern och bygger de publika sidorna, adminpanelen och det interna väder-API:t. Koden använder moderna PHP-funktioner som typdeklarationer, <code>match</code>, <code>DateTimeImmutable</code> och null-safe-anrop.</li>
          <li><strong>MySQL eller MariaDB</strong> lagrar användare, inlägg, kategorier, taggar, media, externa länkar och inställningar. Anslutningen sker genom PHP:s PDO-lager med riktiga prepared statements och teckenkodningen <code>utf8mb4</code>.</li>
          <li><strong>HTML5</strong> ger sidorna semantisk struktur med bland annat <code>header</code>, <code>nav</code>, <code>main</code>, <code>article</code> och tillgänglighetsattribut.</li>
          <li><strong>CSS</strong> skapar det responsiva serietidnings- och scrapbookutseendet. Publika sidor använder <code>assets/style.css</code>; adminpanelen har en separat <code>admin/assets/admin.css</code>.</li>
          <li><strong>Vanilla JavaScript</strong>, utan React, Vue eller jQuery, hanterar bland annat mobilbeteende, galleri/lightbox och den dynamiska väderpresentationen.</li>
          <li><strong>Open-Meteo</strong> levererar väderprognosen. Integrationen kräver ingen API-nyckel och är frikopplad från databasen.</li>
          <li><strong>Filsystemet</strong> lagrar uppladdade bilder i <code>uploads/</code> och en kortlivad vädercache i <code>storage/cache/</code>.</li>
        </ul>
        <p class="info-note">Utvecklingsmiljön som sidan mättes i kör PHP 8.3.6 och har PDO-drivrutiner för MySQL och SQLite. Projektet visar inte om produktionsservern använder Apache eller Nginx, så den delen anges inte som ett faktum här.</p>
      </section>

      <section aria-labelledby="info-request">
        <h2 id="info-request">Vad som händer när en sida öppnas</h2>
        <ol class="info-flow">
          <li><strong>Webbläsaren begär en PHP-adress.</strong> Exempelvis visar <code>articles.php</code> artikellistan och <code>post.php?slug=...</code> visar ett enskilt inlägg.</li>
          <li><strong>PHP laddar konfiguration och hjälpfunktioner.</strong> <code>app/config.php</code> innehåller serverns privata inställningar. <code>app/helpers.php</code> bygger säkra URL:er, formaterar datum, hanterar CSRF-skydd och kodar text för HTML.</li>
          <li><strong>Databasdrivna sidor ansluter med PDO.</strong> <code>app/db.php</code> skapar anslutningen och modeller som <code>Post</code>, <code>Category</code> och <code>Media</code> hämtar rätt poster.</li>
          <li><strong>Endast publicerbart innehåll väljs ut.</strong> Publika frågor filtrerar på statusen <code>published</code>, innehållstyp och eventuellt publiceringsdatum. Framtida publiceringsdatum kan därför användas för schemaläggning.</li>
          <li><strong>Gemensam layout byggs.</strong> <code>app/public_layout.php</code> skapar metadata, toppheader, vädervisning, meny och footer. Den aktiva menyknappen bestäms av sidans nyckel.</li>
          <li><strong>Färdig HTML skickas till besökaren.</strong> Webbläsaren laddar därefter CSS, bilder och den JavaScript-fil som sidan behöver. Statisk CSS och JavaScript får en versionsparameter baserad på filens ändringstid, vilket motverkar gammal webbläsarcache efter en uppdatering.</li>
        </ol>
      </section>

      <section aria-labelledby="info-public">
        <h2 id="info-public">De publika delarna</h2>
        <dl class="info-components">
          <div><dt>Startsida</dt><dd><code>index.php</code> är just nu en fristående visuell landningssida med en centrerad collagebild. Den äldre, databasdrivna startsidan ligger kvar i <code>index_old.php</code> men laddas inte av den nuvarande startsidan.</dd></div>
          <div><dt>Artiklar</dt><dd><code>articles.php</code> hämtar publicerade poster av typen <code>article</code>. <code>post.php</code> visar hela innehållet, omslagsbild, galleri, kategorier, taggar, plats och eventuella tipplänkar.</dd></div>
          <div><dt>Nyheter</dt><dd><code>nyheter.php</code> använder samma postmodell men filtrerar på innehållstypen <code>news</code>.</dd></div>
          <div><dt>Chiang Mai Tips</dt><dd><code>cmt.php</code> visar poster av typen <code>cmt</code>. Tips kan innehålla koordinater och sorterade externa länkar.</dd></div>
          <div><dt>Foton</dt><dd><code>gallery.php</code> hämtar bilder som är kopplade till publicerade artiklar. JavaScript kan öppna dem i en lightbox ovanpå sidan.</dd></div>
          <div><dt>Kategori och plats</dt><dd><code>category.php</code> och <code>location.php</code> ger alternativa vägar till innehållet genom kategori-slug respektive platsnamn.</dd></div>
          <div><dt>Om och Info</dt><dd><code>about.php</code> beskriver projektet för besökaren. Den här sidan, <code>info.php</code>, dokumenterar den tekniska lösningen.</dd></div>
        </dl>
      </section>

      <section aria-labelledby="info-data">
        <h2 id="info-data">Databas och innehållsmodell</h2>
        <p>Grundschemat innehåller tio tabeller. <code>posts</code> är navet och kan representera artikel, nyhet eller Chiang Mai-tips genom fältet <code>content_type</code>. Ett inlägg har bland annat rubrik, unik slug, ingress, brödtext, plats, koordinater, innehållsdatum, publiceringsdatum, status och valfri omslagsbild.</p>
        <ul>
          <li><code>users</code> lagrar administratörer och deras hashade lösenord.</li>
          <li><code>posts</code> lagrar de tre innehållstyperna och deras publiceringsstatus.</li>
          <li><code>media</code> lagrar metadata om uppladdade bilder; själva filerna ligger i <code>uploads/</code>.</li>
          <li><code>categories</code> och <code>tags</code> organiserar innehållet.</li>
          <li><code>post_media</code>, <code>post_categories</code> och <code>post_tags</code> är relationstabeller som kan koppla flera objekt till samma inlägg.</li>
          <li><code>post_links</code> lagrar namngivna och sorterade externa länkar.</li>
          <li><code>site_settings</code> lagrar nyckel/värde-inställningar för sajten.</li>
        </ul>
        <p>Databasen använder InnoDB, främmande nycklar och <code>ON DELETE</code>-regler för att relationer ska städas korrekt. SQL-filerna i <code>sql/migrations/</code> dokumenterar framtida schemaändringar, medan <code>sql/schema.sql</code> kan skapa grundstrukturen från början.</p>
      </section>

      <section aria-labelledby="info-admin">
        <h2 id="info-admin">Adminpanelen och publiceringsflödet</h2>
        <p>Adminpanelen ligger under <code>admin/</code>. <code>admin/bootstrap.php</code> startar den gemensamma miljön, laddar modeller och tjänsteklasser och kräver normalt en inloggad session. Efter inloggning finns moduler för dashboard, artiklar, nyheter, Chiang Mai-tips, media, kategorier, taggar och inställningar.</p>
        <ol class="info-flow">
          <li>Administratören loggar in med användarnamn och lösenord. PHP verifierar lösenordet mot databasens hash och skapar en session med ett nytt sessions-ID.</li>
          <li>Ett nytt innehållsobjekt skrivs i formuläret och sparas som utkast, publicerat eller schemalagt.</li>
          <li><code>PostService</code> samordnar validering och sparande. Modellerna sköter SQL och synkroniserar kategorier, taggar, länkar och galleri.</li>
          <li>Bilder valideras mot verklig MIME-typ, får genererade filnamn och registreras i databasen. Tillåtna format är JPEG, PNG och WebP.</li>
          <li>När posten är publicerad och publiceringsdatumet har infallit blir den synlig på motsvarande publik sida.</li>
        </ol>
      </section>

      <section aria-labelledby="info-weather">
        <h2 id="info-weather">Tid och väder för Chiang Mai</h2>
        <p>Väderdelen har ett eget flöde och behöver ingen databas. <code>metro.php</code> laddar <code>assets/metro.js</code>, som anropar sajtens interna endpoint <code>api/chiangmai_status.php</code>. Endpointen använder <code>ChiangMaiWeatherService</code> för att hämta prognosdata från Open-Meteo för koordinaterna 18.7883, 98.9853 och tidszonen <code>Asia/Bangkok</code>.</p>
        <ol class="info-flow">
          <li>Tjänsten kontrollerar först om en giltig cachefil yngre än 900 sekunder, alltså 15 minuter, finns.</li>
          <li>Vid cachemiss byggs ett HTTPS-anrop med aktuella mätvärden, timdata och sju dygns prognos. cURL används i första hand och PHP-strömmar är reservväg.</li>
          <li>Svaret valideras och normaliseras till ett internt JSON-format med svenska veckodagar, vädertexter, vindriktningar och reseråd.</li>
          <li>JavaScript fyller den aktuella temperaturen, mätvärdena, 24 timkort och sju dagskort utan att ladda om sidan. Klockan tickar lokalt varje sekund och väderrapporten uppdateras var femte minut.</li>
          <li>Headerns mindre väderruta använder samma interna endpoint. Vid fel visas ett begripligt felmeddelande i stället för tekniska detaljer.</li>
        </ol>
      </section>

      <section aria-labelledby="info-frontend">
        <h2 id="info-frontend">Design, responsivitet och tillgänglighet</h2>
        <p>Det publika gränssnittet är mobile first. Flexbox, CSS Grid, <code>clamp()</code> och media queries anpassar menyer, prognoskort och innehåll efter skärmbredd. Menyknapparnas lutning skapas med ett fast <code>nth-child</code>-mönster: riktningen ser blandad ut men ändras inte vid omladdning, och nya knappar får automatiskt tilt.</p>
        <p>Tillgängligheten stöds av svensk sidlang, viewport-anpassning, semantiska rubriknivåer, alternativtexter, skip-länk, <code>aria-current</code> för aktiv meny, fokusmarkeringar, statusregioner med <code>aria-live</code>, minst 44 pixlar höga tryckytor på pekskärmar och reducerade animationer när användaren har valt <code>prefers-reduced-motion</code>.</p>
      </section>

      <section aria-labelledby="info-security">
        <h2 id="info-security">Säkerhet och robusthet</h2>
        <ul>
          <li>Databasfrågor använder PDO och parametriserade prepared statements för användarstyrda värden.</li>
          <li>Text som skrivs till HTML kodas normalt med <code>htmlspecialchars</code> genom hjälpfunktionen <code>e()</code>.</li>
          <li>Artikel-HTML saneras mot en begränsad lista av element och attribut. Script, style, iframe, object och embed tas bort.</li>
          <li>Adminformulär skyddas med kryptografiska CSRF-token som verifieras med <code>hash_equals</code>.</li>
          <li>Lösenord verifieras med PHP:s <code>password_verify()</code>; klartextlösenord lagras inte.</li>
          <li>Bilduppladdningar kontrolleras med serverläst MIME-typ och får ett nytt unikt filnamn.</li>
          <li>Vädertjänsten har korta anslutnings- och svarstider, validerar JSON och skriver cache via temporär fil för att minska risken för en halvskriven cache.</li>
          <li>Databasuppgifter ligger i <code>app/config.php</code>, som är avsedd att stanna på servern och inte läggas i Git. En ofarlig mall finns i <code>app/config.example.php</code>.</li>
        </ul>
      </section>

      <section aria-labelledby="info-files">
        <h2 id="info-files">Viktigaste mapparna och filerna</h2>
        <div class="info-table-wrap">
          <table class="info-table">
            <thead><tr><th>Sökväg</th><th>Ansvar</th></tr></thead>
            <tbody>
              <tr><th><code>*.php</code> i roten</th><td>Publika sidkontroller och HTML-mallar.</td></tr>
              <tr><th><code>app/</code></th><td>Konfiguration, databasanslutning, modeller, hjälpfunktioner och tjänstelogik.</td></tr>
              <tr><th><code>app/public_layout.php</code></th><td>Gemensam metadata, header, toppmeny, header-väder och footer.</td></tr>
              <tr><th><code>admin/</code></th><td>Inloggad redaktionell miljö för att skapa och hantera innehåll.</td></tr>
              <tr><th><code>api/</code></th><td>Internt JSON-API för Chiang Mai-väder och lokal tid.</td></tr>
              <tr><th><code>assets/</code></th><td>Publik CSS, JavaScript och fasta designbilder.</td></tr>
              <tr><th><code>uploads/</code></th><td>Runtime-lagring för bilder som laddats upp via admin.</td></tr>
              <tr><th><code>storage/cache/</code></th><td>Tillfällig cache för externa väderdata.</td></tr>
              <tr><th><code>sql/</code></th><td>Grundschema, migrationer och seeddata.</td></tr>
            </tbody>
          </table>
        </div>
      </section>

      <section aria-labelledby="info-maintenance">
        <h2 id="info-maintenance">Drift och vidareutveckling</h2>
        <p>Sajten är avsiktligt liten och direkt: inga pakethanterare, kompileringssteg eller genererade frontendpaket krävs för den nuvarande koden. En ändring i en PHP-, CSS- eller JavaScript-fil kan publiceras som en vanlig filuppdatering. Databasändringar ska däremot göras med en ny migrationsfil, och serverns privata <code>config.php</code> samt innehållet i <code>uploads/</code> behöver hanteras separat vid backup eller flytt.</p>
        <p>Källkoden versionshanteras i Git. Projektets repository finns på <a href="https://github.com/ProjektKhaos/abergpaaventy.git">GitHub</a>. En grundkontroll efter en ändring är att syntaxkontrollera berörda PHP-filer, kontrollera CSS-diffen och begära den publika sidan via HTTP.</p>
        <pre><code>php -l info.php
php -l app/helpers.php
git diff --check
curl -I https://abergpaaventyr.se/</code></pre>
      </section>
    </article>
  </main>

  <?php public_footer(); ?>
  <script src="<?= e(asset_url('assets/script.js')) ?>" defer></script>
</body>
</html>
