<?php
// Skapar 20 idempotenta dummy-nyheter och återanvänder bilder ur mediabiblioteket.

require_once __DIR__ . '/../app/config.php';
require_once __DIR__ . '/../app/db.php';

$news = [
    ['Morgonmarknaden vaknar tidigt', 'Färska frukter, kryddor och de första kaffevagnarna fyller kvarteren.', 'Chiang Mai', '2026-07-04', 2, [3]],
    ['Nya språklektioner drar igång', 'En ny kursvecka börjar med uttal, vardagsfraser och många anteckningar.', 'Chiang Mai', '2026-07-03', 5, []],
    ['Regn över gamla stan', 'Ett snabbt monsunregn gav svalka och blanka gator innanför vallgraven.', 'Old City', '2026-07-02', 12, [13]],
    ['Helgutflykt till bergen planeras', 'Ryggsäcken packas för en tur till grönare och svalare höjder.', 'Chiang Mai', '2026-07-01', 7, []],
    ['Caféet runt hörnet har öppnat', 'Kvarteret har fått ett nytt litet café med lokala bönor och lugn innergård.', 'Nimman', '2026-06-30', 13, [18]],
    ['Templet förbereder kvällsceremoni', 'Lyktor och blommor sätts upp inför kvällens samling.', 'Chiang Mai', '2026-06-29', 14, []],
    ['Söndagsmarknaden blir bilfri', 'Gatorna fylls av hantverk, matstånd och musik från sen eftermiddag.', 'Ratchadamnoen Road', '2026-06-28', 15, [9]],
    ['Ny busslinje förenklar vardagen', 'En lokal linje gör resan mellan skolan och centrum lite smidigare.', 'Chiang Mai', '2026-06-27', null, []],
    ['Kvällsmat vid norra porten', 'Dagens nyhet är enkel: favoritsoppan är tillbaka på menyn.', 'Chang Phuak Gate', '2026-06-26', 17, [16]],
    ['Studiegruppen växer', 'Fler studenter har anslutit och veckans gemensamma träff flyttar till biblioteket.', 'Chiang Mai', '2026-06-25', null, []],
    ['Teprovning efter lektionen', 'En lokal tebutik bjöd på smaker från norra Thailand.', 'Nimman', '2026-06-24', 18, []],
    ['Trafikträning på lugna gator', 'Dagens övning handlade om vänstertrafik, rondeller och att ta det lugnt.', 'Chiang Mai', '2026-06-23', 19, [20]],
    ['Poolen öppnar tidigare under värmen', 'De varmaste dagarna får boende möjlighet till ett tidigt morgondopp.', 'Santitham', '2026-06-22', 20, []],
    ['En omväg blev dagens bästa promenad', 'Ett felval i en korsning ledde till en grön gränd och en liten park.', 'Chiang Mai', '2026-06-21', 16, [6]],
    ['Fruktsäsongen är i full gång', 'Mango, mangostan och rambutan syns nu överallt på marknaderna.', 'Muang Mai Market', '2026-06-20', 22, []],
    ['Skolan ordnar kulturdag', 'Mat, musik och korta presentationer står på programmet nästa vecka.', 'Chiang Mai', '2026-06-19', 23, [4]],
    ['Nya öppettider inför helgen', 'Biblioteket och studiehallen håller extraöppet på lördag.', 'Chiang Mai', '2026-06-18', null, []],
    ['Varm kväll med sval bris', 'Temperaturen sjönk till slut och kvarteret vaknade till liv efter solnedgången.', 'Santitham', '2026-06-17', 8, [21]],
    ['Lokalt hantverk visas på torget', 'Keramik, textil och träarbete från regionen samlas i en tillfällig utställning.', 'Tha Phae Gate', '2026-06-16', 6, []],
    ['Veckans sammanfattning från Chiang Mai', 'Studier, nya platser och många små vardagsäventyr avslutar veckan.', 'Chiang Mai', '2026-06-15', 26, [24, 25]],
];

$find = $pdo->prepare("SELECT id FROM posts WHERE slug = ? LIMIT 1");
$insert = $pdo->prepare("
    INSERT INTO posts
        (title, slug, content_type, intro, body, location, post_date, publish_date, status, cover_image_id)
    VALUES
        (?, ?, 'news', ?, ?, ?, ?, NULL, 'published', ?)
");
$attach = $pdo->prepare("
    INSERT INTO post_media (post_id, media_id, sort_order)
    SELECT ?, id, ? FROM media WHERE id = ?
");

$created = 0;
$skipped = 0;

$pdo->beginTransaction();
try {
    foreach ($news as [$title, $intro, $location, $date, $coverId, $galleryIds]) {
        $slug = 'nyhet-' . strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', strtr($title, [
            'å' => 'a', 'ä' => 'a', 'ö' => 'o', 'Å' => 'a', 'Ä' => 'a', 'Ö' => 'o',
        ])), '-'));

        $find->execute([$slug]);
        if ($find->fetchColumn()) {
            $skipped++;
            continue;
        }

        $body = '<p>' . htmlspecialchars($intro, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</p>'
            . '<p>Det här är en dummy-nyhet skapad för att visa hur nyhetsflödet fungerar. Texten kan redigeras eller ersättas från adminpanelen.</p>';

        $insert->execute([$title, $slug, $intro, $body, $location, $date, $coverId]);
        $postId = (int)$pdo->lastInsertId();

        foreach ($galleryIds as $sort => $mediaId) {
            $attach->execute([$postId, $sort, $mediaId]);
        }

        $created++;
    }

    $pdo->commit();
} catch (Throwable $e) {
    $pdo->rollBack();
    throw $e;
}

echo "Skapade: {$created}\n";
echo "Fanns redan: {$skipped}\n";
